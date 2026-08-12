/* eslint-disable */
this.BX = this.BX || {};
(function (exports, main_core, main_core_events, landing_env, landing_loc, landing_ui_panel_content, landing_ui_panel_saveblock, landing_ui_panel_floatingnodepanel, landing_sliderhacks, landing_pageobject, landing_backend) {
	'use strict';

	class ExternalControls {
		#postMessages = {
			mode: 'mode',
			register: 'register',
			changeState: 'changestate',
			editorEnable: 'editorenable',
			showControls: 'showcontrols',
			showBlockControls: 'showblockcontrols',
			hideAll: 'hideall',
			backendAction: 'backendaction'
		};
		#currentMobileTop = -1;
		#mouseEntered = false;
		#disableControls = false;
		#currentMousePosition = 0;
		#blocksMobileTops = [];
		constructor() {
			if (Main.isExternalControlsEnabled() && landing_env.Env.getInstance().isBlockControlsEnabled()) {
				this.#registerListeners();
			}
		}

		/**
		 * Registers all required listeners.
		 */
		#registerListeners() {
			setTimeout(() => {
				this.#registerBlocks();
			}, 0);

			// listening commands from outer frame
			window.addEventListener('message', event => {
				if (this.isControlsExternal()) {
					this.listenExternalCommands(event.data.action, event.data.payload);
				}
			});

			// catching the mouse and scrolling
			document.addEventListener('mouseenter', event => {
				this.#mouseEntered = true;
			});
			document.addEventListener('mouseleave', event => {
				this.#mouseEntered = false;
			});
			document.addEventListener('mousemove', event => {
				this.onMobileMouseMove(event.y);
			});
			document.addEventListener('scroll', () => {
				if (this.#mouseEntered) {
					this.recalculateTopsIfExternals();
				}
			});

			// checking when external commands become enabled
			BX.addCustomEvent('BX.Landing.Main:changeControls', (type, topInPercent) => {
				if (type === 'internal') {
					this.postExternalCommand(this.#postMessages.hideAll, {});
				} else {
					// mode switching some time
					setTimeout(() => {
						this.recalculateTops(true);
					}, 400);
				}
			});

			// checking inline editor — enabled or disabled
			BX.addCustomEvent('BX.Landing.Editor:enable', () => {
				this.#disableControls = true;
				if (this.isControlsExternal()) {
					this.postExternalCommand(this.#postMessages.hideAll, {});
				}
			});
			BX.addCustomEvent('BX.Landing.Editor:disable', () => {
				this.#disableControls = false;
				this.recalculateTopsIfExternals(true);
			});

			// checking that new block was added and any block changed its active status
			BX.addCustomEvent('BX.Landing.Block:onAfterAdd', event => {
				setTimeout(() => {
					const blockData = event.getData();
					this.#registerNewBlock(blockData.id);
				}, 500);
			});
			BX.addCustomEvent('BX.Landing.Block:changeState', (blockId, state) => {
				this.postExternalCommand(this.#postMessages.changeState, {
					blockId,
					state
				});
			});

			// form's settings were opened and then closed
			BX.addCustomEvent('BX.Landing.Block:onFormSettingsOpen', () => {
				if (this.isControlsExternal()) {
					this.postExternalCommand(this.#postMessages.hideAll, {});
				}
				this.#disableControls = true;
			});
			BX.addCustomEvent('BX.Landing.Block:onFormSettingsClose', blockId => {
				// after form completely closed
				setTimeout(() => {
					this.#disableControls = false;
					this.recalculateTopsIfExternals(true);
				}, 400);
				this.postExternalCommand(this.#postMessages.hideAll, {});
			});
			BX.addCustomEvent('BX.Landing.Block:onAfterFormSave', blockId => {
				setTimeout(() => {
					this.postExternalCommand(this.#postMessages.backendAction, {
						action: 'Landing\\Block::saveForm',
						data: {
							block: blockId
						}
					});
				}, 1000);
			});
			BX.addCustomEvent('BX.Landing.Block:onBlockEditClose', () => {
				this.#disableControls = false;
				this.recalculateTopsIfExternals(true);
			});
			BX.addCustomEvent('BX.Landing.Block:onContentSave', this.recalculateTopsIfExternals.bind(this));
			BX.addCustomEvent('BX.Landing.Block:onDesignerBlockSave', this.recalculateTopsIfExternals.bind(this));
			BX.addCustomEvent('BX.Landing.Block:Card:add', this.recalculateTopsIfExternals.bind(this));
			BX.addCustomEvent('BX.Landing.Block:Card:remove', this.recalculateTopsIfExternals.bind(this));
			BX.addCustomEvent('BX.Landing.Block:afterRemove', this.recalculateTopsIfExternals.bind(this));
			BX.addCustomEvent('BX.Landing.Backend:action', this.onBackendAction.bind(this));
			BX.addCustomEvent('BX.Landing.Backend:batch', this.onBackendAction.bind(this));
		}

		/**
		 * Invokes when backend action occurred.
		 */
		onBackendAction(action, data) {
			this.#disableControls = false;
			this.postExternalCommand(this.#postMessages.backendAction, {
				action,
				data
			});
		}

		/**
		 * Creates and returns Block object for sending to external window.
		 *
		 * @param {BX.Landing.} block
		 * @return {Block}
		 */
		#createBlockObject(block) {
			return {
				id: parseInt(block.id),
				state: block.isEnabled(),
				permissions: {
					allowDesignBlock: block.isDesignBlockAllowed(),
					allowModifyStyles: block.isStyleModifyAllowed(),
					allowEditContent: block.isEditBlockAllowed(),
					allowSorting: block.isEditBlockAllowed(),
					allowRemove: block.isRemoveBlockAllowed(),
					allowChangeState: block.isChangeStateBlockAllowed(),
					allowPaste: block.isPasteBlockAllowed(),
					allowSaveInLibrary: block.isSaveBlockInLibraryAllowed()
				}
			};
		}

		/**
		 * Registers all blocks on entire page.
		 */
		#registerBlocks() {
			const blocksCollection = BX.Landing.PageObject.getBlocks();
			const data = [];
			[...blocksCollection].map(block => data.push(this.#createBlockObject(block)));
			this.postExternalCommand(this.#postMessages.register, {
				blocks: data
			});
		}

		/**
		 * Registers new block.
		 *
		 * @param {number} blockId
		 */
		#registerNewBlock(blockId) {
			const block = BX.Landing.PageObject.getBlocks().get(blockId);
			if (block) {
				this.postExternalCommand(this.#postMessages.register, {
					blocks: [this.#createBlockObject(block)]
				});
				// because new block adding some time
				if (this.isControlsExternal()) {
					this.recalculateTops();
				} else {
					this.postExternalCommand(this.#postMessages.hideAll, {});
				}
			}
		}

		/**
		 * Checks that landing controls is external
		 *
		 * @return {boolean}
		 */
		isControlsExternal() {
			return main_core.Dom.hasClass(document.body, 'landing-ui-external-controls');
		}

		/**
		 * Recalculates block tops.
		 *
		 * @param {boolean} resetMobileTop
		 */
		recalculateTops(resetMobileTop) {
			this.#blocksMobileTops = [];
			if (resetMobileTop) {
				this.#currentMobileTop = -1;
			}
			[...document.body.querySelectorAll('.block-wrapper')].map(block => {
				const blockRect = block.getBoundingClientRect();
				if (blockRect.height > 1)
					// hidden on mobile blocks
					{
						this.#blocksMobileTops.push({
							blockId: parseInt(block.getAttribute('data-id')),
							top: blockRect.top,
							height: blockRect.height
						});
					}
			});
			this.onMobileMouseMove(this.#currentMousePosition);
		}

		/**
		 * Recalculates block tops only if external controls are enabled.
		 *
		 * @param {boolean} resetMobileTop
		 */
		recalculateTopsIfExternals(resetMobileTop) {
			if (this.isControlsExternal()) {
				this.recalculateTops(resetMobileTop);
			}
		}

		/**
		 * Call when user moves mouse over the mobile page.
		 *
		 * @param {number} top
		 */
		onMobileMouseMove(top) {
			if (this.#disableControls || !this.isControlsExternal()) {
				return;
			}
			if (top <= 0) {
				this.#currentMobileTop = -1;
				return;
			}
			this.#currentMousePosition = top;
			for (let i = 0, c = this.#blocksMobileTops.length; i < c; i++) {
				if (top >= this.#blocksMobileTops[i]['top'] && (!this.#blocksMobileTops[i + 1] || top < this.#blocksMobileTops[i + 1]['top'])) {
					if (this.#blocksMobileTops[i]['top'] !== this.#currentMobileTop) {
						this.#currentMobileTop = this.#blocksMobileTops[i]['top'];
						this.postExternalCommand(this.#postMessages.showControls, {
							blockId: this.#blocksMobileTops[i]['blockId'],
							top: this.#blocksMobileTops[i]['top'],
							height: this.#blocksMobileTops[i]['height']
						});
					}
					break;
				}
			}
		}

		/**
		 * Sends action with payload to parent window.
		 *
		 * @param {string} action
		 * @param {Object} payload
		 */
		postExternalCommand(action, payload) {
			if (window.parent) {
				window.parent.postMessage({
					action,
					payload
				}, window.location.origin);
			}
		}

		/**
		 * Receives actions with payload from parent window.
		 *
		 * @param {string} action
		 * @param {Object} payload
		 */
		listenExternalCommands(action, payload) {
			const block = BX.Landing.PageObject.getBlocks().get(payload?.blockId ? payload.blockId : -1);
			if (payload?.blockId && !block) {
				return;
			}
			const successCallback = () => {
				setTimeout(() => {
					this.#currentMousePosition = 0;
					this.recalculateTops();
				}, 300);
			};
			switch (action) {
				case 'onDesignerBlockClick':
					{
						block.onDesignerBlockClick();
						break;
					}
				case 'onEditBlockClick':
					{
						block.onShowContentPanel();
						break;
					}
				case 'onStyleBlockClick':
					{
						block.onStyleShow();
						break;
					}
				case 'onSortDownBlockClick':
					{
						block.moveDown();
						successCallback();
						break;
					}
				case 'onSortUpBlockClick':
					{
						block.moveUp();
						successCallback();
						break;
					}
				case 'onRemoveBlockClick':
					{
						block.deleteBlock();
						break;
					}
				case 'onChangeStateBlockClick':
					{
						block.onStateChange();
						break;
					}
				case 'onCutBlockClick':
					{
						Main.getInstance().onCutBlock.bind(Main.getInstance(), block)();
						break;
					}
				case 'onCopyBlockClick':
					{
						Main.getInstance().onCopyBlock.bind(Main.getInstance(), block)();
						break;
					}
				case 'onPasteBlockClick':
					{
						Main.getInstance().onPasteBlock.bind(Main.getInstance(), block, blockId => {
							setTimeout(() => {
								this.#registerNewBlock(blockId);
							}, 300);
						})();
						break;
					}
				case 'onFeedbackClick':
					{
						block.showFeedbackForm();
						break;
					}
				case 'onSaveInLibraryClick':
					{
						block.saveBlock();
						break;
					}
				case 'onHideEditorPanel':
					{
						BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
						break;
					}
			}
		}
	}

	/**
	 * Checks that element contains block
	 * @param {HTMLElement} element
	 * @return {boolean}
	 */
	function hasBlock(element) {
		return !!element && !!element.querySelector('.block-wrapper');
	}

	/**
	 * Checks that element contains "Add new Block" button
	 * @param {HTMLElement} element
	 * @return {boolean}
	 */
	function hasCreateButton(element) {
		return !!element && !!element.querySelector('button[data-id="insert_first_block"]');
	}

	function onAnimationEnd(element, animationName) {
		return new Promise(resolve => {
			const onAnimationEndListener = event => {
				if (event.animationName === animationName) {
					resolve(event);
					main_core.Event.bind(element, 'animationend', onAnimationEndListener);
				}
			};
			main_core.Event.bind(element, 'animationend', onAnimationEndListener);
		});
	}

	function isEmpty(value) {
		if (main_core.Type.isNil(value)) {
			return true;
		}
		if (main_core.Type.isArrayLike(value)) {
			return !value.length;
		}
		if (main_core.Type.isObject(value)) {
			return Object.keys(value).length <= 0;
		}
		return true;
	}

	BX.Landing.getMode = () => 'edit';

	/**
	 * @memberOf BX.Landing
	 */
	class Main extends main_core_events.EventEmitter {
		static TYPE_PAGE = 'PAGE';
		static TYPE_STORE = 'STORE';
		static TYPE_KNOWLEDGE = 'KNOWLEDGE';
		static TYPE_GROUP = 'GROUP';
		static getMode() {
			return 'edit';
		}
		static createInstance(id) {
			const rootWindow = BX.Landing.PageObject.getRootWindow();
			if (rootWindow.BX.Landing.Main.instance) {
				rootWindow.BX.Landing.Main.instance.clear();
			}
			rootWindow.BX.Landing.Main.instance = new BX.Landing.Main(id);
		}
		static getInstance() {
			const rootWindow = BX.Landing.PageObject.getRootWindow();
			rootWindow.BX.Reflection.namespace('BX.Landing.Main');
			if (rootWindow.BX.Landing.Main.instance) {
				return rootWindow.BX.Landing.Main.instance;
			}
			rootWindow.BX.Landing.Main.instance = new Main(-1);
			return rootWindow.BX.Landing.Main.instance;
		}

		/**
		 * Returns true, if current page is Editor.
		 * @return {boolean}
		 */
		static isEditorMode() {
			return main_core.Dom.hasClass(document.body, 'landing-editor');
		}

		/**
		 * Returns true, if external controls is enabled.
		 * @return {boolean}
		 */
		static isExternalControlsEnabled() {
			return main_core.Dom.hasClass(document.body, 'enable-external-controls');
		}

		/**
		 * Returns in percent scroll position of page.
		 *
		 * @return {number}
		 */
		static topInPercent() {
			const scrollHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight, document.body.offsetHeight, document.documentElement.offsetHeight, document.body.clientHeight, document.documentElement.clientHeight);
			const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
			return scrollTop / scrollHeight * 100;
		}

		/**
		 * Maps site type to analytics category.
		 *
		 * @return {string}
		 */
		static getAnalyticsCategoryByType() {
			const siteType = BX.Landing.Env.getInstance().getType();
			switch (siteType) {
				case 'STORE':
					return 'shop';
				case 'KNOWLEDGE':
				case 'GROUP':
					return 'kb';
				case 'VIBE':
					return 'vibe';
				default:
					return 'site';
			}
		}

		/**
		 * Landing ID
		 * @type {number}
		 */

		constructor(id) {
			super();
			this.setEventNamespace('BX.Landing.Main');
			const options = landing_env.Env.getInstance().getOptions();
			this.id = id;
			this.options = Object.freeze(options);
			this.blocks = this.options.blocks;
			this.currentBlock = null;
			this.isDesignBlockModeFlag = this.options["design_block"] === true;
			this.loadedDeps = {};
			this.cache = new main_core.Cache.MemoryCache();
			this.externalControls = new ExternalControls();
			this.onSliderFormLoaded = this.onSliderFormLoaded.bind(this);
			this.onBlockDelete = this.onBlockDelete.bind(this);
			BX.addCustomEvent('Landing.Block:onAfterDelete', this.onBlockDelete);
			this.adjustEmptyAreas();
			BX.Landing.UI.Panel.StatusPanel.setLastModified(options.lastModified);
			if (!this.isDesignBlockModeFlag) {
				BX.Landing.UI.Panel.StatusPanel.getInstance().show();
			}
			const pageType = landing_env.Env.getInstance().getType();
			if (pageType === Main.TYPE_KNOWLEDGE || pageType === Main.TYPE_GROUP) {
				const mainArea = document.querySelector('.landing-main');
				if (main_core.Type.isDomNode(mainArea)) {
					main_core.Dom.addClass(mainArea, 'landing-ui-collapse');
				}
			}
		}
		clear() {
			BX.removeCustomEvent('Landing.Block:onAfterDelete', this.onBlockDelete);
		}
		isCrmFormPage() {
			return landing_env.Env.getInstance().getSpecialType() === 'crm_forms';
		}
		isDesignBlockMode() {
			return this.isDesignBlockModeFlag;
		}
		getSaveBlockPanel() {
			const panel = new landing_ui_panel_saveblock.SaveBlock('save_block_panel', {
				block: this.currentBlock
			});
			panel.layout.hidden = true;
			panel.content.hidden = false;
			main_core.Dom.append(panel.layout, window.parent.document.body);
			return panel;
		}
		getBlocksPanel() {
			return this.cache.remember('blockPanel', () => {
				const blocksPanel = this.createBlocksPanel();
				setTimeout(() => {
					if (blocksPanel.sidebarButtons.get(this.options.default_section)) {
						blocksPanel.sidebarButtons.get(this.options.default_section).layout.click();
					} else {
						[...blocksPanel.sidebarButtons][0].layout.click();
					}
				});
				blocksPanel.layout.hidden = true;
				blocksPanel.content.hidden = false;
				main_core.Dom.append(blocksPanel.layout, window.parent.document.body);
				return blocksPanel;
			});
		}
		getBlocksPanelContent() {
			return this.getBlocksPanel().content;
		}
		hideBlocksPanel() {
			if (this.getBlocksPanel()) {
				return this.getBlocksPanel().hide();
			}
			return Promise.resolve();
		}
		getLayoutAreas() {
			return this.cache.remember('layoutAreas', () => {
				return [...document.body.querySelectorAll('.landing-header'), ...document.body.querySelectorAll('.landing-sidebar'), ...document.body.querySelectorAll('.landing-main'), ...document.body.querySelectorAll('.landing-footer')];
			});
		}

		/**
		 * Creates insert block button
		 * @param {HTMLElement} area
		 * @return {BX.Landing.UI.Button.Plus}
		 */
		createInsertBlockButton(area) {
			const button = new BX.Landing.UI.Button.Plus('insert_first_block', {
				text: landing_loc.Loc.getMessage('ACTION_BUTTON_CREATE')
			});
			button.on('click', this.showBlocksPanel.bind(this, null, area, button));
			button.on('mouseover', this.onCreateButtonMouseover.bind(this, area, button));
			button.on('mouseout', this.onCreateButtonMouseout.bind(this, area, button));
			return button;
		}
		onCreateButtonMouseover(area, button) {
			if (main_core.Dom.hasClass(area, 'landing-header') || main_core.Dom.hasClass(area, 'landing-footer')) {
				const areas = this.getLayoutAreas();
				if (areas.length > 1) {
					const createText = landing_loc.Loc.getMessage('ACTION_BUTTON_CREATE');
					if (main_core.Dom.hasClass(area, 'landing-main')) {
						button.setText(`${createText} ${landing_loc.Loc.getMessage('LANDING_ADD_BLOCK_TO_MAIN')}`);
					}
					if (main_core.Dom.hasClass(area, 'landing-header')) {
						button.setText(`${createText} ${landing_loc.Loc.getMessage('LANDING_ADD_BLOCK_TO_HEADER')}`);
					}
					if (main_core.Dom.hasClass(area, 'landing-sidebar')) {
						button.setText(`${createText} ${landing_loc.Loc.getMessage('LANDING_ADD_BLOCK_TO_SIDEBAR')}`);
					}
					if (main_core.Dom.hasClass(area, 'landing-footer')) {
						button.setText(`${createText} ${landing_loc.Loc.getMessage('LANDING_ADD_BLOCK_TO_FOOTER')}`);
					}
					clearTimeout(this.fadeTimeout);
					this.fadeTimeout = setTimeout(() => {
						main_core.Dom.addClass(area, 'landing-area-highlight');
						areas.filter(currentArea => currentArea !== area).forEach(currentArea => {
							main_core.Dom.addClass(currentArea, 'landing-area-fade');
						});
					}, 400);
				}
			}
		}
		onCreateButtonMouseout(area, button) {
			clearTimeout(this.fadeTimeout);
			if (main_core.Dom.hasClass(area, 'landing-header') || main_core.Dom.hasClass(area, 'landing-footer')) {
				const areas = this.getLayoutAreas();
				if (areas.length > 1) {
					button.setText(landing_loc.Loc.getMessage('ACTION_BUTTON_CREATE'));
					areas.forEach(currentArea => {
						main_core.Dom.removeClass(currentArea, 'landing-area-highlight');
						main_core.Dom.removeClass(currentArea, 'landing-area-fade');
					});
				}
			}
		}
		initEmptyArea(area) {
			if (area) {
				area.innerHTML = '';
				main_core.Dom.append(this.createInsertBlockButton(area).layout, area);
				main_core.Dom.addClass(area, 'landing-empty');
			}
		}

		// eslint-disable-next-line class-methods-use-this
		destroyEmptyArea(area) {
			if (area) {
				const button = area.querySelector('button[data-id="insert_first_block"]');
				if (button) {
					main_core.Dom.remove(button);
				}
				main_core.Dom.removeClass(area, 'landing-empty');
			}
		}

		/**
		 * Adjusts areas
		 */
		adjustEmptyAreas() {
			this.getLayoutAreas().filter(area => {
				return hasBlock(area) && hasCreateButton(area);
			}).forEach(this.destroyEmptyArea, this);
			this.getLayoutAreas().filter(area => {
				return !hasBlock(area) && !hasCreateButton(area);
			}).forEach(this.initEmptyArea, this);
			const main = document.body.querySelector('main.landing-edit-mode');
			const isAllEmpty = !this.getLayoutAreas().some(hasBlock);
			if (main) {
				if (isAllEmpty) {
					main_core.Dom.addClass(main, 'landing-empty');
					return;
				}
				main_core.Dom.removeClass(main, 'landing-empty');
			}
		}

		/**
		 * Enables landing controls
		 */
		// eslint-disable-next-line class-methods-use-this
		enableControls() {
			main_core.Dom.removeClass(document.body, 'landing-ui-hide-controls');
		}

		/**
		 * Disables landing controls
		 */
		// eslint-disable-next-line class-methods-use-this
		disableControls() {
			main_core.Dom.addClass(document.body, 'landing-ui-hide-controls');
		}

		/**
		 * Checks that landing controls is enabled
		 * @return {boolean}
		 */
		// eslint-disable-next-line class-methods-use-this
		isControlsEnabled() {
			return !main_core.Dom.hasClass(document.body, 'landing-ui-hide-controls');
		}

		/**
		 * Makes landing controls internal.
		 */
		// eslint-disable-next-line class-methods-use-this
		makeControlsInternal() {
			BX.onCustomEvent('BX.Landing.Main:changeControls', ['internal', Main.topInPercent()]);
			main_core.Dom.removeClass(document.body, 'landing-ui-external-controls');
		}

		/**
		 * Makes landing controls external.
		 */
		// eslint-disable-next-line class-methods-use-this
		makeControlsExternal() {
			BX.onCustomEvent('BX.Landing.Main:changeControls', ['external', Main.topInPercent()]);
			main_core.Dom.addClass(document.body, 'landing-ui-external-controls');
		}

		/**
		 * Checks that landing controls is external.
		 * @return {boolean}
		 */
		// eslint-disable-next-line class-methods-use-this
		isControlsExternal() {
			return main_core.Dom.hasClass(document.body, 'landing-ui-external-controls');
		}

		/**
		 * Set device code in body data-attribute.
		 * @param {string} code
		 */
		setDeviceCode(code) {
			document.body.setAttribute('data-device', code);
		}

		/**
		 * Get device code from body attribute.
		 * @return {string}
		 */
		getDeviceCode() {
			return document.body.getAttribute('data-device');
		}

		/**
		 * Set BX classes to mark this landing frame as mobile (touch) device
		 */
		setTouchDevice() {
			main_core.Dom.removeClass(document.documentElement, 'bx-no-touch');
			main_core.Dom.addClass(document.documentElement, 'bx-touch');
		}

		/**
		 * Set BX classes to mark this landing frame as desktop (no touch) device
		 */
		setNoTouchDevice() {
			main_core.Dom.removeClass(document.documentElement, 'bx-touch');
			main_core.Dom.addClass(document.documentElement, 'bx-no-touch');
		}

		/**
		 * Appends block
		 * @param {addBlockResponse} data
		 * @param {boolean} [withoutAnimation]
		 * @returns {HTMLElement}
		 */
		appendBlock(data, withoutAnimation) {
			if (!this.isAllowedAppendBlock(data)) {
				return main_core.Tag.render``;
			}
			const block = main_core.Tag.render`${data.content}`;
			block.id = `block${data.id}`;
			if (!withoutAnimation) {
				main_core.Dom.addClass(block, 'landing-ui-show');
				onAnimationEnd(block, 'showBlock').then(() => {
					main_core.Dom.removeClass(block, 'landing-ui-show');
				});
			}
			this.insertToBlocksFlow(block);
			return block;
		}

		/**
		 * Check if the block can be appended
		 * @param {addBlockResponse} data
		 * @returns {boolean} - Returns true if the block can be appended, otherwise false
		 */
		isAllowedAppendBlock(data) {
			const type = BX.Landing.Env.getInstance().getType().toLowerCase();
			let allowedBlockTypes = data.manifest.block.type ?? [];
			if (type === 'mainpage' || allowedBlockTypes.includes('mainpage')) {
				if (main_core.Type.isString(allowedBlockTypes)) {
					allowedBlockTypes = [allowedBlockTypes];
				}
				if (!allowedBlockTypes.includes(type)) {
					return false;
				}
			}
			return true;
		}

		/**
		 * Shows blocks list panel
		 * @param {BX.Landing.Block} block
		 * @param {HTMLElement} [area]
		 * @param [button]
		 * @param [insertBefore]
		 */
		showBlocksPanel(block, area, button, insertBefore) {
			BX.UI.Analytics.sendData({
				tool: BX.Landing.Main.getAnalyticsCategoryByType(),
				category: 'widget_list',
				event: 'open_widget_list'
			});
			this.currentBlock = block;
			this.currentArea = area;
			this.insertBefore = insertBefore;
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			if (this.isCrmFormPage() || this.isControlsExternal()) {
				const rootWindow = landing_pageobject.PageObject.getRootWindow();
				main_core.Dom.append(this.getBlocksPanel().layout, rootWindow.document.body);
				main_core.Dom.append(this.getBlocksPanel().overlay, rootWindow.document.body);
			}
			this.getBlocksPanel().show();
			this.disableAddBlockButtons();
			if (!!area && !!button) {
				this.onCreateButtonMouseout(area, button);
			}
		}
		showSaveBlock(block) {
			this.currentBlock = block;
			this.getSaveBlockPanel().show();
		}
		disableAddBlockButtons() {
			landing_pageobject.PageObject.getBlocks().forEach(block => {
				const panel = block.panels.get('create_action');
				if (panel) {
					const button = panel.buttons.get('insert_after');
					if (button) {
						button.disable();
					}
				}
			});
		}
		enableAddBlockButtons() {
			landing_pageobject.PageObject.getBlocks().forEach(block => {
				const panel = block.panels.get('create_action');
				if (panel) {
					const button = panel.buttons.get('insert_after');
					if (button) {
						button.enable();
					}
				}
			});
		}

		/**
		 * Creates blocks list panel
		 * @returns {BX.Landing.UI.Panel.Content}
		 */
		createBlocksPanel() {
			const {
				blocks
			} = this.options;
			const categories = Object.keys(blocks);
			const panel = new landing_ui_panel_content.Content('blocks_panel', {
				title: landing_loc.Loc.getMessage('LANDING_CONTENT_BLOCKS_TITLE'),
				className: 'landing-ui-panel-block-list',
				scrollAnimation: true
			});
			panel.subscribe('onCancel', () => {
				this.enableAddBlockButtons();
			});
			categories.forEach(categoryId => {
				const hasItems = !isEmpty(blocks[categoryId].items);
				const isPopular = categoryId === 'popular';
				const isSeparator = blocks[categoryId].separator;
				const isFavourite = categoryId === 'favourite';
				if (hasItems && !isPopular || isSeparator || isFavourite) {
					panel.appendSidebarButton(this.createBlockPanelSidebarButton(categoryId, blocks[categoryId]));
				}
			});
			panel.appendSidebarButton(new BX.Landing.UI.Button.SidebarButton('feedback_button', {
				className: 'landing-ui-button-sidebar-feedback',
				text: landing_loc.Loc.getMessage('LANDING_BLOCKS_LIST_FEEDBACK_BUTTON'),
				onClick: this.showFeedbackForm.bind(this)
			}));
			return panel;
		}

		/**
		 * Shows feedback form
		 * @param data
		 */
		showSliderFeedbackForm(data = {}) {
			main_core.Runtime.loadExtension('ui.feedback.form').then(() => {
				const data = {};
				data.bitrix24 = this.options.server_name;
				data.siteId = this.options.site_id;
				data.siteUrl = this.options.url;
				data.siteTemplate = this.options.xml_id;
				data.productType = this.options.productType || 'Undefined';
				data.typeproduct = (() => {
					if (this.options.params.type === Main.TYPE_GROUP) {
						return 'KNOWLEDGE_GROUP';
					}
					return this.options.params.type;
				})();
				BX.UI.Feedback.Form.open({
					id: Math.random() + '',
					forms: this.getFeedbackFormOptions(),
					presets: data
				});
			});
		}

		/**
		 * Gets feedback form options
		 * @return {{id: string, sec: string, lang: string}}
		 */
		// eslint-disable-next-line class-methods-use-this
		getFeedbackFormOptions() {
			return [{
				zones: ['en', 'eu', 'in', 'uk'],
				id: 16,
				lang: 'en',
				sec: '3h483y'
			}, {
				zones: ['ru', 'by', 'kz'],
				id: 8,
				lang: 'ru',
				sec: 'x80yjw'
			}, {
				zones: ['ua'],
				id: 18,
				lang: 'ua',
				sec: 'd9e09o'
			}, {
				zones: ['la', 'co', 'mx'],
				id: 14,
				lang: 'la',
				sec: 'wu561i'
			}, {
				zones: ['de'],
				id: 10,
				lang: 'de',
				sec: 'eraz2q'
			}, {
				zones: ['com.br', 'br'],
				id: 12,
				lang: 'br',
				sec: 'r6wvge'
			}];
		}

		/**
		 * Handles feedback loaded event
		 */
		onSliderFormLoaded() {
			this.sliderFormLoader.hide();
		}

		/**
		 * Shows feedback form for blocks list panel
		 */
		showFeedbackForm() {
			this.showSliderFeedbackForm({
				target: 'blocksList'
			});
		}

		/**
		 * Creates blocks list panel sidebar button
		 * @param {string} category
		 * @param {object} options
		 * @returns {BX.Landing.UI.Button.SidebarButton}
		 */
		createBlockPanelSidebarButton(category, options) {
			return new BX.Landing.UI.Button.SidebarButton(category, {
				text: options.name,
				child: !options.separator,
				className: options.new ? 'landing-ui-new-section' : '',
				onClick: this.onBlocksListCategoryChange.bind(this, category)
			});
		}

		/**
		 * Adds dynamically new block to the category.
		 * @param {string} category Category code.
		 * @param {{code: string, name: string, preview: string, section: Array<string>}} block Block data.
		 */
		addNewBlockToCategory(category, block) {
			if (this.blocks[category]) {
				const blockCode = block['codeOriginal'] || block['code'];
				if (category === 'last') {
					if (!this.lastBlocks) {
						this.lastBlocks = Object.keys(this.blocks.last.items);
					}
					this.lastBlocks.unshift(blockCode);
				} else {
					this.blocks[category].items[blockCode] = block;
				}
				this.onBlocksListCategoryChange(category);
			}
		}
		removeBlockFromList(blockCode) {
			let removed = false;
			for (let category in this.blocks) {
				if (this.blocks[category].items[blockCode] !== undefined) {
					delete this.blocks[category].items[blockCode];
					removed = true;
				}
			}
			if (this.lastBlocks.indexOf(blockCode) !== -1) {
				this.lastBlocks.splice(this.lastBlocks.indexOf(blockCode), 1);
				removed = true;
			}

			// refresh panel
			if (removed) {
				const activeCategoryButton = this.getBlocksPanel().sidebarButtons.find(button => {
					return main_core.Dom.hasClass(button.layout, 'landing-ui-active');
				});
				if (activeCategoryButton) {
					this.onBlocksListCategoryChange(activeCategoryButton.id);
				}
			}
		}

		/**
		 * Returns page's template code if exists.
		 * @return {string|null}
		 */
		getTemplateCode() {
			let {
				tplCode
			} = landing_env.Env.getInstance().getOptions();
			if (tplCode.indexOf('@') > 0) {
				tplCode = tplCode.split('@')[1];
			}
			if (!tplCode || tplCode.length <= 0) {
				tplCode = null;
			}
			return tplCode;
		}

		/**
		 * Handles event on blocks list category change
		 * @param {string} category - Category id
		 */
		async onBlocksListCategoryChange(category) {
			this.currentCategory = category;
			if (this.currentCategory === 'favourite') {
				BX.UI.Analytics.sendData({
					tool: BX.Landing.Main.getAnalyticsCategoryByType(),
					category: 'widget_list',
					event: 'open_favorites',
					c_section: 'site_editor'
				});
			}
			const templateCode = this.getTemplateCode();
			this.getBlocksPanel().content.hidden = false;
			this.getBlocksPanel().sidebarButtons.forEach(button => {
				const action = button.id === category ? 'add' : 'remove';
				button.layout.classList[action]('landing-ui-active');
			});
			this.getBlocksPanel().content.innerHTML = '';
			const loader = new BX.Loader({
				target: this.getBlocksPanel().content,
				size: 90
			});
			loader.show();
			try {
				this.favouriteBlocks = await BX.Landing.Backend.getInstance().action('Landing::getFavouriteBlocks');
			} catch (e) {
				console.warn('Failed to fetch favourite blocks', e);
				this.favouriteBlocks = [];
			}
			loader.hide();
			if (category === 'last') {
				if (!this.lastBlocks) {
					this.lastBlocks = Object.keys(this.blocks.last.items);
				}
				this.lastBlocks = [...new Set(this.lastBlocks)];
				this.lastBlocks.forEach(blockKey => {
					const block = this.getBlockFromRepository(blockKey);
					if (block) {
						block.currentCategory = category;
						this.getBlocksPanel().appendCard(this.createBlockCard(blockKey, block));
					}
				});
				return;
			}
			if (category === 'favourite') {
				if (!this.favouriteBlocks) {
					this.favouriteBlocks = Object.keys(this.blocks.favourite.items);
				}
				const blockCards = [];
				this.favouriteBlocks = [...new Set(this.favouriteBlocks)];
				this.favouriteBlocks.forEach(blockKey => {
					const block = this.getBlockFromRepository(blockKey);
					if (block) {
						block.currentCategory = category;
						blockCards.push(this.createBlockCard(blockKey, block));
					}
				});
				if (blockCards.length === 0) {
					main_core.Dom.append(this.createFavouriteCategoryEmptyState(), this.getBlocksPanelContent());
					return;
				}
				blockCards.forEach(blockCard => {
					this.getBlocksPanel().appendCard(blockCard);
				});
				return;
			}
			Object.keys(this.blocks[category].items).forEach(blockKey => {
				const block = this.blocks[category].items[blockKey];
				const blockTplCode = block['tpl_code'] && block['tpl_code'].length > 0 ? block['tpl_code'] : null;
				if (!templateCode || !blockTplCode || blockTplCode && blockTplCode === templateCode) {
					block.currentCategory = category;
					this.getBlocksPanel().appendCard(this.createBlockCard(blockKey, block));
				}
			});
			if (this.getBlocksPanel().content.scrollTop) {
				requestAnimationFrame(() => {
					this.getBlocksPanel().content.scrollTop = 0;
				});
			}
		}

		// eslint-disable-next-line consistent-return
		getBlockFromRepository(code) {
			const {
				blocks
			} = this.options;
			const categories = Object.keys(blocks);
			const category = categories.find(categoryId => {
				return code in blocks[categoryId].items;
			});
			if (category) {
				return blocks[category].items[code];
			}
		}

		/**
		 * Handles copy block event
		 * @param {BX.Landing.Block} block
		 */
		// eslint-disable-next-line class-methods-use-this
		onCopyBlock(block) {
			window.localStorage.landingBlockId = block.id;
			window.localStorage.landingBlockName = block.manifest.block.name;
			window.localStorage.landingBlockAction = 'copy';
			try {
				window.localStorage.requiredUserAction = JSON.stringify(block.requiredUserActionOptions);
			} catch (err) {
				window.localStorage.requiredUserAction = '';
			}
		}

		/**
		 * Handles cut block event
		 * @param {BX.Landing.Block} block
		 */
		// eslint-disable-next-line class-methods-use-this
		onCutBlock(block) {
			window.localStorage.landingBlockId = block.id;
			window.localStorage.landingBlockName = block.manifest.block.name;
			window.localStorage.landingBlockAction = 'cut';
			try {
				window.localStorage.requiredUserAction = JSON.stringify(block.requiredUserActionOptions);
			} catch (err) {
				window.localStorage.requiredUserAction = '';
			}
			BX.Landing.PageObject.getBlocks().remove(block);
			main_core.Dom.remove(block.node);
			BX.onCustomEvent('Landing.Block:onAfterDelete', [block]);
		}

		/**
		 * Handles paste block event
		 * @param {BX.Landing.Block} block
		 * @param {() => {}} callback
		 */
		onPasteBlock(block, callback) {
			if (window.localStorage.landingBlockId) {
				let action = 'Landing::copyBlock';
				if (window.localStorage.landingBlockAction === 'cut') {
					action = 'Landing::moveBlock';
				}
				const requestBody = {};
				requestBody[action] = {
					action,
					data: {
						lid: block.lid || BX.Landing.Main.getInstance().id,
						block: window.localStorage.landingBlockId,
						params: {
							AFTER_ID: block.id,
							RETURN_CONTENT: 'Y'
						}
					}
				};
				landing_backend.Backend.getInstance().batch(action, requestBody, {
					action
				}).then(res => {
					this.currentBlock = block;
					return this.addBlock(res[action].result.content, false, false, callback);
				});
			}
		}

		/**
		 * Adds block from server response
		 * @param {addBlockResponse} res
		 * @param {boolean} [withoutAnimation = false]
		 * @param {boolean} [insertBefore = false]
		 * @param {() => {}} callback
		 * @return {Promise<T>}
		 */
		addBlock(res, withoutAnimation, insertBefore = false, callback) {
			if (this.lastBlocks) {
				this.lastBlocks.unshift(res.manifest.codeOriginal || res.manifest.code);
			}
			const self = this;
			const block = this.appendBlock(res, withoutAnimation);
			return this.loadBlockDeps(res).then(blockRes => {
				self.currentBlock = null;
				self.currentArea = null;
				const blockId = parseInt(res.id);
				const allOldBlocks = BX.Landing.PageObject.getBlocks();
				if (allOldBlocks) {
					allOldBlocks.forEach(oldBlock => {
						if (oldBlock.id === blockId) {
							main_core.Dom.remove(oldBlock.node);
							BX.Landing.PageObject.getBlocks().remove(oldBlock);
						}
					});
				}

				// Init block entity
				void new BX.Landing.Block(block, {
					id: blockId,
					sections: res.sections,
					requiredUserAction: res.requiredUserAction,
					manifest: res.manifest,
					access: res.access,
					active: main_core.Text.toBoolean(res.active),
					php: res.php,
					designed: res.designed,
					anchor: res.anchor,
					dynamicParams: res.dynamicParams,
					repoId: res.repoId
				});
				return self.runBlockScripts(res).then(() => {
					if (callback) {
						callback(blockId);
					}
					return block;
				});
			}).catch(err => {
				console.warn(err);
			});
		}

		/**
		 * Handles edd block event
		 * @param {string} blockCode
		 * @param {*} [restoreId]
		 * @param {?boolean} [preventHistory = false]
		 * @return {Promise<BX.Landing.Block>}
		 */
		onAddBlock(blockCode, restoreId, preventHistory = false) {
			const id = main_core.Text.toNumber(restoreId);
			this.hideBlocksPanel();
			return this.showBlockLoader().then(this.loadBlock(blockCode, id, preventHistory)).then(res => {
				return new Promise(resolve => {
					setTimeout(() => {
						resolve(res);
					}, 500);
				});
			}).then(res => {
				res.manifest.codeOriginal = blockCode;
				const p = this.addBlock(res, false, this.insertBefore);
				this.insertBefore = false;
				this.adjustEmptyAreas();
				void this.hideBlockLoader();
				this.enableAddBlockButtons();
				BX.onCustomEvent('BX.Landing.Block:onAfterAdd', res);
				return p;
			});
		}

		/**
		 * Inserts element to blocks flow.
		 * Element can be inserted after current block or after last block
		 * @param {HTMLElement} element
		 */
		insertToBlocksFlow(element) {
			const isCurrentBlockAvailable = this.currentBlock && this.currentBlock.node && this.currentBlock.node.parentNode;
			if (isCurrentBlockAvailable && !this.insertBefore) {
				main_core.Dom.insertAfter(element, this.currentBlock.node);
				return;
			}
			if (isCurrentBlockAvailable && this.insertBefore) {
				main_core.Dom.insertBefore(element, this.currentBlock.node);
			}
			main_core.Dom.prepend(element, this.currentArea);
		}

		/**
		 * Gets block loader
		 * @return {HTMLElement}
		 */
		getBlockLoader() {
			if (!this.blockLoader) {
				this.blockLoader = new BX.Loader({
					size: 60
				});
				this.blockLoaderContainer = main_core.Dom.create('div', {
					props: {
						className: 'landing-block-loader-container'
					},
					children: [this.blockLoader.layout]
				});
			}
			return this.blockLoaderContainer;
		}

		/**
		 * Shows block loader
		 * @return {Function}
		 */
		showBlockLoader() {
			this.insertToBlocksFlow(this.getBlockLoader());
			this.blockLoader.show();
			return Promise.resolve();
		}

		/**
		 * Hides block loader
		 * @return {Function}
		 */
		hideBlockLoader() {
			main_core.Dom.remove(this.getBlockLoader());
			this.blockLoader = null;
			return Promise.resolve();
		}

		/**
		 * Loads block dependencies
		 * @param {addBlockResponse} data
		 * @returns {Promise<addBlockResponse>}
		 */
		loadBlockDeps(data) {
			const ext = BX.processHTML(data.content_ext);
			if (BX.type.isArray(ext.SCRIPT)) {
				ext.SCRIPT = ext.SCRIPT.filter(item => {
					return !item.isInternal;
				});
			}
			if (BX.type.isObject(data.lang)) {
				landing_loc.Loc.setMessage(data.lang);
			}
			let loadedScripts = 0;
			const scriptsCount = data.js.length + ext.SCRIPT.length + ext.STYLE.length + data.css.length;
			let resPromise = null;
			if (!this.loadedDeps[data.manifest.code] && scriptsCount > 0) {
				resPromise = new Promise(resolve => {
					function onLoad() {
						loadedScripts += 1;
						if (loadedScripts === scriptsCount) {
							resolve(data);
						}
					}
					if (scriptsCount > loadedScripts) {
						// Load extensions files
						ext.SCRIPT.forEach(item => {
							if (!item.isInternal) {
								BX.loadScript(item.JS, onLoad);
							}
						});
						ext.STYLE.forEach(item => {
							BX.loadScript(item, onLoad);
						});

						// Load block files
						data.css.forEach(item => {
							BX.loadScript(item, onLoad);
						});
						data.js.forEach(item => {
							BX.loadScript(item, onLoad);
						});
					} else {
						onLoad();
					}
					this.loadedDeps[data.manifest.code] = true;
				});
			} else {
				resPromise = Promise.resolve(data);
			}
			return resPromise.then(data => {
				if (BX.type.isArray(data.assetStrings)) {
					const head = document.head;
					data.assetStrings.forEach(string => {
						const element = main_core.Tag.render`${string}`;
						main_core.Dom.insertAfter(element, head.lastChild);
					});
				}
				return data;
			});
		}

		/**
		 * Executes block scripts
		 * @param data
		 * @return {Promise}
		 */
		// eslint-disable-next-line class-methods-use-this
		runBlockScripts(data) {
			return new Promise(resolve => {
				const scripts = BX.processHTML(data.content).SCRIPT;
				if (scripts.length) {
					BX.ajax.processScripts(scripts, undefined, () => {
						resolve(data);
					});
				} else {
					resolve(data);
				}
			});
		}

		/**
		 * Load new block from server
		 * @param {string} blockCode
		 * @param {int} [restoreId]
		 * @param {boolean} [preventHistory = false]
		 * @returns {Function}
		 */
		loadBlock(blockCode, restoreId, preventHistory) {
			return () => {
				let lid = this.id;
				let siteId = this.options.site_id;
				if (this.currentBlock) {
					lid = this.currentBlock.lid;
					siteId = this.currentBlock.siteId;
				}
				if (this.currentArea) {
					lid = main_core.Dom.attr(this.currentArea, 'data-landing');
					siteId = main_core.Dom.attr(this.currentArea, 'data-site');
				}
				let requestBody = {
					lid,
					siteId,
					preventHistory: preventHistory ? 1 : 0
				};
				const fields = {
					ACTIVE: 'Y',
					CODE: blockCode,
					AFTER_ID: this.currentBlock ? this.currentBlock.id : 0,
					RETURN_CONTENT: 'Y',
					CATEGORY: this.currentCategory
				};
				if (!main_core.Type.isBoolean(preventHistory) || preventHistory === false) {
					// Change history steps
					BX.Landing.History.getInstance().push();
				}
				if (!restoreId) {
					requestBody.fields = fields;
					return landing_backend.Backend.getInstance().action('Landing::addBlock', requestBody, {
						code: blockCode
					}).then(result => {
						if (this.insertBefore) {
							return landing_backend.Backend.getInstance().action('Landing::upBlock', {
								lid,
								siteId,
								block: result.id
							}).then(() => {
								return result;
							});
						}
						return result;
					});
				}
				return landing_backend.Backend.getInstance().action('Block::getContent', {
					block: restoreId,
					lid,
					fields,
					editMode: 1
				}).then(res => {
					res.id = restoreId;
					return res;
				});
			};
		}

		/**
		 * Creates block preview card
		 * @param {string} blockKey - Block key (folder name)
		 * @param {{name: string, [preview]: ?string, [new]: ?boolean}} block - Object with block data
		 * @param {string} [mode]
		 * @returns {BX.Landing.UI.Card.BlockPreviewCard}
		 */
		createBlockCard(blockKey, block, mode) {
			return new BX.Landing.UI.Card.BlockPreviewCard({
				title: block.name,
				image: block.preview,
				code: blockKey,
				app_expired: block.app_expired,
				favorite: !!block.favorite,
				favoriteMy: !!block.favoriteMy,
				repo_id: block.repo_id,
				mode,
				isNew: block.new === true,
				onClick: this.onAddBlock.bind(this, blockKey),
				currentCategory: block.currentCategory,
				useFavouriteBadge: true,
				isFavorite: Array.isArray(this.favouriteBlocks) && this.favouriteBlocks.includes(blockKey)
			});
		}
		createFavouriteCategoryEmptyState() {
			return main_core.Tag.render`
			<div class="landing-favourite-category-empty-state text-center">
				<img 
					class="landing-favourite-category-empty-state--image" 
					src="/bitrix/images/landing/empty-favourite.png" 
					style="margin-bottom: 14px;"
				/>
				<p 
					class="landing-favourite-category-empty-state--title"
					style="color: #333333; font-weight: 500; font-size: 19px; line-height: 26px; margin-bottom: 10px;"
				>
					${landing_loc.Loc.getMessage('LANDING_SECTION_FAVOURITE_EMPTY_STATE_TITLE')}
				</p>
				<p 
					class="landing-favourite-category-empty-state--text"
					style="color: #414A56; font-weight: 400; font-size: 16px; line-height: 21px; max-width: 340px; margin: auto;"
				>
					${landing_loc.Loc.getMessage('LANDING_SECTION_FAVOURITE_EMPTY_STATE_TEXT')}
				</p>
			</div>
		`;
		}

		/**
		 * Handles block delete event
		 */
		onBlockDelete(block) {
			if (!block.parent.querySelector('.block-wrapper')) {
				this.adjustEmptyAreas();
			}
		}

		/**
		 * Shows page overlay
		 */
		// eslint-disable-next-line class-methods-use-this
		showOverlay() {
			const main = document.querySelector('main.landing-edit-mode');
			if (main) {
				main_core.Dom.addClass(main, 'landing-ui-overlay');
			}
		}

		/**
		 * Hides page overlay
		 */
		// eslint-disable-next-line class-methods-use-this
		hideOverlay() {
			const main = document.querySelector('main.landing-edit-mode');
			if (main) {
				main_core.Dom.removeClass(main, 'landing-ui-overlay');
			}
		}
		reloadSlider(url) {
			return landing_sliderhacks.SliderHacks.reloadSlider(url, window.parent);
		}
	}

	exports.Main = Main;

})(this.BX.Landing = this.BX.Landing || {}, BX, BX.Event, BX.Landing, BX.Landing, BX.Landing.UI.Panel, BX.Landing.UI.Panel, BX.Landing.UI.Panel, BX.Landing, BX.Landing, BX.Landing);
//# sourceMappingURL=main.bundle.js.map
