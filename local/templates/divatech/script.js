'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.diva-menu-toggle');
  const nav = document.querySelector('#diva-nav');
  toggle?.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(open));
    nav?.classList.toggle('is-open', open);
  });
  nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    toggle?.setAttribute('aria-expanded', 'false');
    nav?.classList.remove('is-open');
  }));

  const dialog = document.querySelector('#diva-callback-dialog');
  document.querySelector('button.diva-callback')?.addEventListener('click', () => dialog?.showModal());
  document.querySelector('.diva-dialog-close')?.addEventListener('click', () => dialog?.close());

  const createRequestId = () => {
    if (globalThis.crypto?.randomUUID) return globalThis.crypto.randomUUID();
    const bytes = new Uint8Array(16);
    if (globalThis.crypto?.getRandomValues) {
      globalThis.crypto.getRandomValues(bytes);
    } else {
      for (let index = 0; index < bytes.length; index += 1) bytes[index] = Math.floor(Math.random() * 256);
    }
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    const hex = [...bytes].map((value) => value.toString(16).padStart(2, '0')).join('');
    return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
  };

  const setFieldError = (field, visible) => {
    if (!field) return;
    field.setAttribute('aria-invalid', visible ? 'true' : 'false');
    const error = field.id ? document.getElementById(`${field.id}-error`) : null;
    if (error) error.hidden = !visible;
  };

  const setStatus = (form, message, variant = '') => {
    const status = form.querySelector('[data-form-status]');
    if (!status) return;
    status.textContent = message;
    status.dataset.status = variant;
  };

  const setLoading = (form, loading) => {
    const button = form.querySelector('[type=submit]');
    const label = button?.querySelector('[data-submit-label]');
    const spinner = button?.querySelector('[data-submit-spinner]');
    form.setAttribute('aria-busy', loading ? 'true' : 'false');
    form.querySelectorAll('input:not([type=hidden]), textarea, button').forEach((control) => {
      control.disabled = loading;
    });
    if (button) button.setAttribute('aria-disabled', loading ? 'true' : 'false');
    if (label) label.textContent = loading ? 'Sending…' : 'Send';
    if (spinner) spinner.hidden = !loading;
  };

  const postForm = async (form, payload) => {
    const controller = globalThis.AbortController ? new AbortController() : null;
    const timeout = controller ? globalThis.setTimeout(() => controller.abort(), 20000) : null;
    try {
      const response = await fetch(form.action, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
        body: JSON.stringify(payload),
        ...(controller ? {signal: controller.signal} : {}),
      });
      const result = await response.json().catch(() => null);
      return {ok: response.ok && result?.success === true, status: response.status};
    } finally {
      if (timeout) globalThis.clearTimeout(timeout);
    }
  };

  document.querySelectorAll('[data-divatech-form]').forEach((form) => {
    let requestId = createRequestId();
    let pending = false;

    form.addEventListener('input', (event) => {
      if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
        setFieldError(event.target, false);
      }
    });

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (pending) return;
      const valid = form.reportValidity();
      form.querySelectorAll('input[aria-describedby], textarea[aria-describedby]').forEach((field) => {
        setFieldError(field, !field.checkValidity());
      });
      if (!valid) {
        setStatus(form, 'Please complete the required fields and accept the privacy policy.', 'error');
        return;
      }

      pending = true;
      const body = Object.fromEntries(new FormData(form));
      const consent = form.querySelector('input[name="consent"]');
      const kind = form.querySelector('input[name="kind"]');
      body.consent = consent?.checked === true;
      body.kind = kind?.value || '';
      body.website = '';
      body.request_id = requestId;
      setLoading(form, true);
      setStatus(form, 'Sending…');

      try {
        const result = await postForm(form, body);
        if (!result.ok) throw new Error('Request failed');
        setStatus(form, form.dataset.successMessage || 'Thank you for your request! We will contact you soon.', 'success');
        form.reset();
        requestId = createRequestId();
      } catch {
        setStatus(form, form.dataset.errorMessage || 'We could not confirm your request. Please try again or email info@divatech.space.', 'error');
      } finally {
        pending = false;
        setLoading(form, false);
      }
    });
  });
});
