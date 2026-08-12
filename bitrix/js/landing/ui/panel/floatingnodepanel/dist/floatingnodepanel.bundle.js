/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports, main_core, landing_ui_panel_base) {
	'use strict';

	const PANEL_INSET = 12;
	class FloatingNodePanel extends landing_ui_panel_base.BasePanel {
		#buttons;
		#targetNode = null;
		constructor(options) {
			super();
			this.setEventNamespace('BX.Landing.UI.Panel.FloatingNodePanel');
			this.#buttons = main_core.Type.isArrayFilled(options?.buttons) ? options.buttons : [];
			main_core.Dom.addClass(this.layout, 'landing-ui-panel-floating-node');
			this.#buttons.forEach(button => this.appendContent(this.#renderButton(button)));
			main_core.Event.bind(this.layout, 'mouseenter', this.#onMouseEnter);
			main_core.Event.bind(this.layout, 'mouseleave', this.#onMouseLeave);
		}
		attach(targetNode) {
			if (!main_core.Type.isDomNode(targetNode)) {
				return;
			}
			this.detach();
			this.#targetNode = targetNode;
			main_core.Event.bind(targetNode, 'mouseenter', this.#onMouseEnter);
			main_core.Event.bind(targetNode, 'mouseleave', this.#onMouseLeave);
			main_core.Event.bind(targetNode.ownerDocument, 'keydown', this.#onKeyDown);
			this.renderTo(targetNode.ownerDocument.body);
		}
		detach() {
			if (!this.#targetNode) {
				return;
			}
			main_core.Event.unbind(this.#targetNode, 'mouseenter', this.#onMouseEnter);
			main_core.Event.unbind(this.#targetNode, 'mouseleave', this.#onMouseLeave);
			main_core.Event.unbind(this.#targetNode.ownerDocument, 'keydown', this.#onKeyDown);
			this.#targetNode = null;
			this.remove();
		}
		show() {
			if (!this.isShown()) {
				main_core.Dom.removeClass(this.layout, this.classHide);
				main_core.Dom.addClass(this.layout, this.classShow);
			}
			this.#adjustPosition();
			return Promise.resolve();
		}
		hide() {
			if (this.isShown()) {
				main_core.Dom.removeClass(this.layout, this.classShow);
				main_core.Dom.addClass(this.layout, this.classHide);
			}
			return Promise.resolve();
		}
		#renderButton(button) {
			const layout = main_core.Tag.render`
			<button
				type="button"
				class="landing-ui-panel-floating-node__button"
				data-id="${main_core.Text.encode(button.id)}"
				data-testid="${main_core.Text.encode(`landing-floating-node-${button.id}-btn`)}"
				title="${main_core.Text.encode(button.title)}"
			>
				<span class="ui-icon-set ${main_core.Text.encode(button.iconClass)} landing-ui-panel-floating-node__icon"></span>
			</button>
		`;
			main_core.Event.bind(layout, 'click', event => {
				event.preventDefault();
				event.stopPropagation();
				button.onClick(event);
			});
			return layout;
		}
		#onMouseEnter = () => {
			void this.show();
		};
		#onMouseLeave = event => {
			const relatedTarget = event.relatedTarget;
			if (main_core.Type.isDomNode(relatedTarget) && (this.layout.contains(relatedTarget) || this.#targetNode && this.#targetNode.contains(relatedTarget))) {
				return;
			}
			void this.hide();
		};
		#onKeyDown = event => {
			if (event.key === 'Escape' && this.isShown()) {
				void this.hide();
			}
		};
		#adjustPosition() {
			if (!this.#targetNode) {
				return;
			}
			const targetWindow = this.#targetNode.ownerDocument.defaultView;
			if (!targetWindow) {
				return;
			}
			const targetRect = this.#targetNode.getBoundingClientRect();
			const panelRect = this.layout.getBoundingClientRect();
			const centerX = targetRect.left + targetRect.width / 2;
			const centerY = targetRect.top + targetRect.height / 2;
			const targetWidth = this.#targetNode.offsetWidth || targetRect.width;
			const targetHeight = this.#targetNode.offsetHeight || targetRect.height;
			const targetTop = centerY - targetHeight / 2;
			const targetLeft = centerX - targetWidth / 2;
			const targetRight = centerX + targetWidth / 2;
			const top = targetTop + targetWindow.pageYOffset + PANEL_INSET;
			const left = Math.max(targetLeft + targetWindow.pageXOffset + PANEL_INSET, targetRight + targetWindow.pageXOffset - panelRect.width - PANEL_INSET);
			main_core.Dom.style(this.layout, {
				top: `${top}px`,
				left: `${left}px`
			});
		}
	}

	exports.FloatingNodePanel = FloatingNodePanel;

})(this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}, BX, BX.Landing.UI.Panel);
//# sourceMappingURL=floatingnodepanel.bundle.js.map
