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
    nav.classList.remove('is-open');
  }));
  const dialog = document.querySelector('#diva-callback-dialog');
  document.querySelector('button.diva-callback')?.addEventListener('click', () => dialog?.showModal());
  document.querySelector('.diva-dialog-close')?.addEventListener('click', () => dialog?.close());
  document.querySelectorAll('[data-divatech-form]').forEach((form) => {
    let requestId = crypto.randomUUID();
    let pending = false;
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (pending || !form.reportValidity()) return;
      pending = true;
      const button = form.querySelector('[type=submit]');
      const status = form.querySelector('[data-form-status]');
      button.disabled = true;
      status.textContent = 'Sending…';
      const body = Object.fromEntries(new FormData(form));
      body.consent = form.elements.consent.checked;
      body.request_id = requestId;
      try {
        const response = await fetch(form.action, {
          method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(body), signal: AbortSignal.timeout(20000),
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error('Request failed');
        status.textContent = 'Thank you for your request! We will contact you soon.';
        form.reset();
        requestId = crypto.randomUUID();
      } catch {
        status.textContent = 'We could not confirm your request. Please try again or email info@divatech.space.';
      } finally {
        pending = false;
        button.disabled = false;
      }
    });
  });
});
