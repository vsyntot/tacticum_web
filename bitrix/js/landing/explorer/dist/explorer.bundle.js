/* eslint-disable */
this.BX = this.BX || {};
(function (exports, landing_backend, landing_loc, main_core, main_popup, ui_dialogs_messagebox) {
	'use strict';

	class ExplorerUI {
		static getLoader() {
			return main_core.Tag.render`<div class="landing-explorer-loader">
			<div class="main-ui-loader">
				<svg class="main-ui-loader-svg" viewBox="25 25 50 50">
					<circle class="main-ui-loader-svg-circle" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
				</svg>
			</div>
		</div>`;
		}
		static getActionButton(title, hadnler) {
			return new BX.UI.Button({
				id: 'landing-explorer-action',
				size: BX.UI.Button.Size.MEDIUM,
				color: BX.UI.Button.Color.SUCCESS,
				text: title,
				events: {
					click: hadnler
				}
			});
		}
		static getCancelButton(hadnler) {
			return new BX.UI.Button({
				id: 'landing-explorer-cancel',
				size: BX.UI.Button.Size.MEDIUM,
				color: BX.UI.Button.Color.LINK,
				text: main_core.Loc.getMessage('LANDING_EXT_EXPLORER_BUTTON_CANCEL'),
				events: {
					click: hadnler
				}
			});
		}
		static getSiteList(data, onClick, siteType) {
			return main_core.Tag.render`
			<ul class="landing-site-selector-list">
				${data.map(item => {
			if (siteType !== 'SMN' && item.TYPE !== siteType) {
				return;
			}
			return main_core.Tag.render`
						<li class="landing-site-selector-item" data-explorer-depth="0" data-explorer-siteId="${item.ID}" onclick="${() => onClick(item.ID)}">
							<span class="ui-icon ui-icon-file-folder"><i></i></span>
							<span class="landing-site-selector-item-value">
								${main_core.Text.encode(item.TITLE)}
							</span>
						</li>
					`;
		})}
			</ul>
		`;
		}
		static getFolderItem(item, depth, onClick) {
			return main_core.Tag.render`
			<li style="padding-left: ${30 * depth}px" class="landing-site-selector-item landing-site-selector-item-lower" data-explorer-depth="${depth}" data-explorer-folderId="${item.ID}" onclick="${() => onClick(item.ID)}">
				<span class="ui-icon ui-icon-file-folder"><i></i></span>
				<span class="landing-site-selector-item-value"> 
					${main_core.Text.encode(item.TITLE)}
				</span>
			</li>
		`;
		}
	}

	class Explorer {
		/** @var {Popup} */
		popupWindow = null;
		constructor(options) {
			this.type = options.type;
			this.currentSiteId = options.siteId;
			this.currentFolderId = options.folderId;
			if (options.startBreadCrumbs) {
				this.startBreadCrumbs = options.startBreadCrumbs;
			}
			this.popupWindow = this.getPopupWindow();
		}
		getPopupWindow() {
			if (this.popupWindow === null) {
				this.popupWindow = new main_popup.Popup({
					bindElement: null,
					className: 'ui-message-box landing-explorer--copy-page',
					content: null,
					titleBar: '&nbsp;',
					overlay: {
						opacity: 30
					},
					closeIcon: false,
					contentBackground: 'transparent',
					padding: 0
				});
			}
			return this.popupWindow;
		}
		open() {
			this.popupWindow.setContent(ExplorerUI.getLoader());
			this.popupWindow.show();
		}
		errorAlert(errors) {
			ui_dialogs_messagebox.MessageBox.show({
				message: errors[0].error_description,
				title: landing_loc.Loc.getMessage('LANDING_EXT_EXPLORER_ALERT_TITLE'),
				buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
				useAirDesign: true,
				onOk: (messageBox, button) => {
					button.setWaiting(false);
					messageBox.close();
					this.popupWindow.close();
				}
			});
		}
		setTitle(type, title) {
			this.popupWindow.setTitleBar(landing_loc.Loc.getMessage('LANDING_EXT_EXPLORER_TITLE_' + type.toUpperCase()).replace('#title#', title));
		}
		setButtons(entityId, type) {
			const typeUpper = type.toUpperCase();
			let action = null;
			let data = null;
			this.popupWindow.setButtons([ExplorerUI.getActionButton(type === 'moveFolder' ? landing_loc.Loc.getMessage('LANDING_EXT_EXPLORER_BUTTON_MOVE') : landing_loc.Loc.getMessage('LANDING_EXT_EXPLORER_BUTTON_' + typeUpper), () => {
				switch (type) {
					case 'copy':
						action = 'Landing::copy';
						data = {
							lid: entityId,
							toSiteId: this.currentSiteId,
							toFolderId: this.currentFolderId,
							skipSystem: true
						};
						break;
					case 'move':
						action = 'Landing::move';
						data = {
							lid: entityId,
							toSiteId: this.currentSiteId,
							toFolderId: this.currentFolderId
						};
						break;
					case 'moveFolder':
						action = 'Site::moveFolder';
						data = {
							folderId: entityId,
							toSiteId: this.currentSiteId,
							toFolderId: this.currentFolderId
						};
						break;
				}
				landing_backend.Backend.getInstance().action(action, data, {
					site_id: this.currentSiteId,
					type: this.type
				}).then(() => {
					this.popupWindow.setContent(ExplorerUI.getLoader());
				}).then(() => {
					setTimeout(() => {
						window.location.reload();
					}, 500);
				}).catch(reason => {
					this.errorAlert(reason.result);
					//return Promise.reject(reason);
				});
			}), ExplorerUI.getCancelButton(() => {
				this.popupWindow.close();
			})]);
		}
		#loadBreadCrumbs(pos) {
			if (this.startBreadCrumbs[pos]) {
				this.#loadFolders(this.currentSiteId, this.startBreadCrumbs[pos].PARENT_ID, () => {
					if (this.startBreadCrumbs[pos + 1]) {
						this.#loadBreadCrumbs(pos + 1);
					} else {
						this.#clickFolder(this.startBreadCrumbs[pos].ID);
					}
				});
			}
		}
		#loadSites() {
			landing_backend.Backend.getInstance().action('Site::getList', {
				params: {
					filter: {
						'=TYPE': this.type,
						'=SPECIAL': 'N'
					},
					order: {
						DATE_MODIFY: 'desc'
					}
				}
			}, {
				type: this.type
			}).then(result => {
				this.popupWindow.setContent(ExplorerUI.getSiteList(result, this.#clickSite.bind(this), this.type));
				this.popupWindow.adjustPosition();
				this.#scrollToSite(this.currentSiteId);
				if (this.startBreadCrumbs.length > 0) {
					this.#selectSite(this.currentSiteId);
					this.#loadBreadCrumbs(0);
				} else {
					this.#clickSite(this.currentSiteId);
				}
			});
		}
		#loadFolders(siteId, parentId, onLoad) {
			landing_backend.Backend.getInstance().action('Site::getFolders', {
				siteId,
				filter: {
					PARENT_ID: parentId ? parentId : 0
				}
			}, {
				site_id: siteId,
				type: this.type
			}).then(result => {
				if (result.length <= 0) {
					return;
				}
				const selectedItem = parentId > 0 ? this.#selectFolder(parentId) : this.#selectSite(siteId);
				result.reverse().map(item => {
					const folderExist = document.querySelector('.landing-site-selector-item[data-explorer-folderId="' + item.ID + '"]');
					if (!folderExist) {
						const depth = parseInt(main_core.Dom.attr(selectedItem, 'data-explorer-depth')) + 1;
						main_core.Dom.insertAfter(ExplorerUI.getFolderItem(item, depth, this.#clickFolder.bind(this)), selectedItem);
					}
				});
				if (onLoad) {
					onLoad();
				}
			});
		}
		#clickSite(siteId) {
			this.currentFolderId = 0;
			this.#selectSite(siteId);
			this.#loadFolders(siteId);
		}
		#clickFolder(folderId) {
			this.#selectFolder(folderId);
			this.#loadFolders(this.currentSiteId, folderId);
		}
		#selectSite(siteId) {
			this.currentSiteId = siteId;
			return this.#selectItem(siteId, 'siteId');
		}
		#selectFolder(folderId) {
			this.currentFolderId = folderId;
			return this.#selectItem(folderId, 'folderId');
		}
		#selectItem(itemId, dataType) {
			const currentSelect = document.querySelector('.landing-site-selector-item-selected');
			const newSelect = document.querySelector('.landing-site-selector-item[data-explorer-' + dataType + '="' + itemId + '"]');
			if (currentSelect) {
				main_core.Dom.removeClass(currentSelect, 'landing-site-selector-item-selected');
			}
			if (newSelect) {
				main_core.Dom.addClass(newSelect, 'landing-site-selector-item-selected');
			}
			return newSelect;
		}
		#scrollToSite(siteId) {
			const siteNode = document.querySelector('[data-explorer-siteId="' + siteId + '"]');
			if (siteNode) {
				// const posY = siteNode.getBoundingClientRect().y;
				// document.querySelector('.landing-site-selector-list').scrollTo(0, posY);
				siteNode.scrollIntoView({
					behavior: 'smooth',
					block: 'nearest',
					inline: 'start'
				});
			}
		}
		copy(landing) {
			this.setTitle('copy', landing.TITLE);
			this.setButtons(landing.ID, 'copy');
			this.open();
			this.#loadSites();
		}
		move(landing) {
			this.setTitle('move', landing.TITLE);
			this.setButtons(landing.ID, 'move');
			this.open();
			this.#loadSites();
		}
		moveFolder(folder) {
			this.setTitle('move', folder.TITLE);
			this.setButtons(folder.ID, 'moveFolder');
			this.open();
			this.#loadSites();
		}
	}

	exports.Explorer = Explorer;

})(this.BX.Landing = this.BX.Landing || {}, BX.Landing, BX.Landing, BX, BX.Main, BX.UI.Dialogs);
//# sourceMappingURL=explorer.bundle.js.map
