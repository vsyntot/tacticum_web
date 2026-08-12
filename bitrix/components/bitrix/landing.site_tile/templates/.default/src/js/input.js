export class LandingSitesAiInput
{
	static instance = null;

	static init(options)
	{
		this.instance = new this(options);

		return this.instance;
	}

	static setActive(active, options)
	{
		this.instance?.setActive(active, options);
	}

	static activate()
	{
		this.setActive(true);
	}

	static deactivate()
	{
		this.setActive(false);
	}

	static toggle()
	{
		this.instance?.toggle();
	}

	static getValue()
	{
		return this.instance?.getValue() || '';
	}

	static setValue(value)
	{
		this.instance?.setValue(value);
	}

	static getState()
	{
		return this.instance?.getState() || {
			isActive: false,
			isInactive: true,
			isAnimating: false,
			isMultiline: false,
			isNoticeVisible: true,
			isControlVisible: false,
			value: '',
		};
	}

	static get isActive()
	{
		return this.getState().isActive;
	}

	static get isInactive()
	{
		return this.getState().isInactive;
	}

	static get isAnimating()
	{
		return this.getState().isAnimating;
	}

	static get isMultiline()
	{
		return this.getState().isMultiline;
	}

	static get isNoticeVisible()
	{
		return this.getState().isNoticeVisible;
	}

	static get isControlVisible()
	{
		return this.getState().isControlVisible;
	}

	constructor(options)
	{
		this.box = options.box || null;
		this.frame = options.frame || null;
		this.notice = options.notice || null;
		this.control = options.control || null;
		this.input = options.input || null;
		this.valueInput = options.valueInput || null;
		this.icon = options.icon || null;
		this.isActive = false;
		this.isInactive = true;
		this.isAnimating = false;
		this.isMultiline = false;
		this.resizeObserver = null;

		this.handleInput = this.handleInput.bind(this);
		this.handlePaste = this.handlePaste.bind(this);
		this.handleKeyDown = this.handleKeyDown.bind(this);
		this.handleIconClick = this.handleIconClick.bind(this);
		this.handleTransitionEnd = this.handleTransitionEnd.bind(this);
		this.updateRowsState = this.updateRowsState.bind(this);
		this.updateIconOffset = this.updateIconOffset.bind(this);
		this.updateHeight = this.updateHeight.bind(this);

		this.initEvents();
		this.renderState();
		requestAnimationFrame(() => {
			this.updateRowsState();
			this.updateIconOffset();
			this.updateHeight();
		});
	}

	initEvents()
	{
		if (!this.box || !this.input)
		{
			return;
		}

		this.input.addEventListener('input', this.handleInput);
		this.input.addEventListener('paste', this.handlePaste);
		this.input.addEventListener('keydown', this.handleKeyDown);
		this.box.addEventListener('transitionend', this.handleTransitionEnd);
		this.icon?.addEventListener('click', this.handleIconClick);
		window.addEventListener('resize', this.updateHeight);

		if (window.ResizeObserver)
		{
			this.resizeObserver = new ResizeObserver(() => {
				this.updateRowsState();
				this.updateIconOffset();
				this.updateHeight();
			});
			this.resizeObserver.observe(this.input);
		}
	}

	handleInput()
	{
		if (this.input.innerText.trim() === '')
		{
			this.input.innerHTML = '';
		}

		this.syncValue();
		this.updateRowsState();
		this.updateIconOffset();
		this.updateHeight();
	}

	handlePaste(event)
	{
		event.preventDefault();

		const text = event.clipboardData.getData('text/plain');
		document.execCommand('insertText', false, text);
	}

	handleKeyDown(event)
	{
		if (event.key !== 'Enter' || event.shiftKey || event.altKey || event.ctrlKey || event.metaKey)
		{
			return;
		}

		if (event.isComposing || event.keyCode === 229)
		{
			return;
		}

		event.preventDefault();

		if (event.repeat || this.isInactive || this.isAnimating)
		{
			return;
		}

		this.submitInitialPrompt();
	}

	handleIconClick(event)
	{
		if (this.isInactive || this.isAnimating)
		{
			event.preventDefault();
			return;
		}

		event.preventDefault();
		this.submitInitialPrompt();
	}

	submitInitialPrompt()
	{
		const initialPrompt = this.getValue();
		if (!initialPrompt)
		{
			return;
		}

		if (!this.icon)
		{
			return;
		}

		const action = this.icon.dataset.action || this.icon.getAttribute('href');
		if (!action)
		{
			return;
		}

		const form = document.createElement('form');
		form.method = 'post';
		form.action = action;
		form.acceptCharset = 'UTF-8';
		form.style.display = 'none';

		form.appendChild(this.createHiddenInput('initial_prompt', initialPrompt));

		const sessid = this.getSessid();
		if (sessid)
		{
			form.appendChild(this.createHiddenInput('sessid', sessid));
		}

		document.body.appendChild(form);
		form.submit();
	}

	createHiddenInput(name, value)
	{
		const input = document.createElement('input');
		input.type = 'hidden';
		input.name = name;
		input.value = value || '';

		return input;
	}

	getSessid()
	{
		if (typeof BX !== 'undefined' && typeof BX.bitrix_sessid === 'function')
		{
			return BX.bitrix_sessid();
		}

		if (typeof BX !== 'undefined' && typeof BX.message === 'function')
		{
			return BX.message('bitrix_sessid') || '';
		}

		return '';
	}

	handleTransitionEnd(event)
	{
		if (event.target !== this.box || event.propertyName !== 'height')
		{
			return;
		}

		this.isAnimating = false;
		this.frame?.classList.remove('--animating');
		this.box.classList.remove('--animating');
		this.box.style.height = 'auto';
	}

	setActive(active, options = {})
	{
		const nextState = active === true;

		if (this.isActive === nextState)
		{
			return;
		}

		this.isActive = nextState;
		this.isInactive = !nextState;
		this.isAnimating = true;
		const previousHeight = this.box?.offsetHeight || 0;
		this.renderState();
		this.updateRowsState();
		this.updateIconOffset();
		this.updateHeight(previousHeight);

		if (this.isActive && options.focus === true)
		{
			this.input?.focus();
		}
	}

	toggle()
	{
		this.setActive(!this.isActive);
	}

	getValue()
	{
		return this.input ? this.input.innerText.trim() : '';
	}

	setValue(value)
	{
		if (!this.input)
		{
			return;
		}

		this.input.innerText = value || '';
		this.syncValue();
		this.updateRowsState();
		this.updateIconOffset();
		this.updateHeight();
	}

	getState()
	{
		return {
			isActive: this.isActive,
			isInactive: this.isInactive,
			isAnimating: this.isAnimating,
			isMultiline: this.isMultiline,
			isNoticeVisible: this.isInactive,
			isControlVisible: this.isActive,
			value: this.getValue(),
		};
	}

	syncValue()
	{
		if (this.valueInput)
		{
			this.valueInput.value = this.getValue();
		}
	}

	renderState()
	{
		if (!this.box)
		{
			return;
		}

		this.frame?.classList.toggle('--active', this.isActive);
		this.frame?.classList.toggle('--inactive', this.isInactive);
		this.frame?.classList.toggle('--animating', this.isAnimating);
		this.box.classList.toggle('--active', this.isActive);
		this.box.classList.toggle('--inactive', this.isInactive);
		this.box.classList.toggle('--animating', this.isAnimating);
		this.notice?.classList.toggle('--active', this.isInactive);
		this.control?.classList.toggle('--active', this.isActive);

		if (this.frame)
		{
			this.frame.tabIndex = -1;
		}

		if (this.input)
		{
			this.input.contentEditable = this.isActive ? 'true' : 'false';
			this.input.setAttribute('aria-disabled', this.isActive ? 'false' : 'true');
			this.input.tabIndex = this.isActive ? 0 : -1;
		}

		if (this.icon)
		{
			this.icon.setAttribute('aria-disabled', this.isActive ? 'false' : 'true');
			this.icon.tabIndex = this.isActive ? 0 : -1;
		}
	}

	updateRowsState()
	{
		if (!this.box || !this.input)
		{
			return;
		}

		const lineHeight = parseFloat(getComputedStyle(this.input).lineHeight);
		this.isMultiline = Number.isFinite(lineHeight) && this.input.scrollHeight > lineHeight * 1.5;
		this.box.classList.toggle('--multiline', this.isMultiline);
	}

	updateIconOffset()
	{
		if (!this.input || !this.icon)
		{
			return;
		}

		const iconOffset = Math.max(0, this.input.offsetHeight - this.icon.offsetHeight);
		this.icon.style.setProperty('--landing-sites-ai-input-icon-offset', `${iconOffset}px`);
	}

	updateHeight(previousHeight = null)
	{
		if (!this.box)
		{
			return;
		}

		if (this.isAnimating && previousHeight === null)
		{
			return;
		}

		const fromHeight = Number.isFinite(previousHeight) ? previousHeight : this.box.offsetHeight;
		this.box.style.height = 'auto';
		const nextHeight = this.box.offsetHeight;

		if (fromHeight === nextHeight)
		{
			this.box.style.height = `${nextHeight}px`;
			this.isAnimating = false;
			this.frame?.classList.remove('--animating');
			this.box.classList.remove('--animating');
			return;
		}

		this.box.style.height = `${fromHeight}px`;

		requestAnimationFrame(() => {
			this.box.style.height = `${nextHeight}px`;
		});
	}
}
