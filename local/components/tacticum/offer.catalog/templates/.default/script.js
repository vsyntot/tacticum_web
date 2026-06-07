(function () {
    'use strict';

    const rootSelector = '[data-offer-catalog-root]';
    const linkSelector = 'a[data-offer-catalog-link]';
    const formSelector = 'form[data-offer-catalog-form]';
    let activeController = null;

    function offerRoot(element) {
        return element ? element.closest(rootSelector) : null;
    }

    function eventElement(event) {
        if (event.target instanceof Element) {
            return event.target;
        }
        return event.target && event.target.parentElement instanceof Element ? event.target.parentElement : null;
    }

    function sameOfferUrl(value) {
        const url = new URL(value, window.location.href);
        return url.origin === window.location.origin && /^\/offer(?:\/|$)/.test(url.pathname);
    }

    function setLoading(root, isLoading) {
        if (!root) return;
        if (isLoading) {
            root.setAttribute('data-offer-catalog-loading', 'true');
            root.setAttribute('aria-busy', 'true');
            root.querySelectorAll('button[type="submit"]').forEach((button) => {
                button.disabled = true;
            });
            return;
        }
        root.removeAttribute('data-offer-catalog-loading');
        root.removeAttribute('aria-busy');
        root.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.disabled = false;
        });
    }

    function updateHead(nextDocument) {
        const nextTitle = nextDocument.querySelector('title');
        if (nextTitle && nextTitle.textContent.trim() !== '') {
            document.title = nextTitle.textContent;
        }

        const nextCanonical = nextDocument.querySelector('link[rel="canonical"]');
        const currentCanonical = document.querySelector('link[rel="canonical"]');
        if (nextCanonical) {
            if (currentCanonical) {
                currentCanonical.setAttribute('href', nextCanonical.getAttribute('href') || '');
            } else {
                document.head.appendChild(nextCanonical.cloneNode(true));
            }
        }

        const nextRobots = nextDocument.querySelector('meta[name="robots"]');
        const currentRobots = document.querySelector('meta[name="robots"]');
        if (nextRobots) {
            if (currentRobots) {
                currentRobots.setAttribute('content', nextRobots.getAttribute('content') || '');
            } else {
                document.head.appendChild(nextRobots.cloneNode(true));
            }
        } else if (currentRobots) {
            currentRobots.remove();
        }
    }

    function announce(nextRoot) {
        const status = nextRoot.querySelector('[data-offer-catalog-status]');
        const live = nextRoot.querySelector('[data-offer-catalog-live]');
        const message = status ? status.textContent.replace(/\s+/g, ' ').trim() : '';
        if (live && message !== '') {
            live.textContent = message;
        }
        if (status) {
            status.focus({preventScroll: true});
        } else {
            nextRoot.focus({preventScroll: true});
        }
        nextRoot.scrollIntoView({block: 'start', behavior: 'smooth'});
    }

    function finalUrl(response, requestedUrl) {
        const responseUrl = new URL(response.url || requestedUrl.href, window.location.href);
        const hash = requestedUrl.hash || '#offer-catalog';
        return responseUrl.pathname + responseUrl.search + hash;
    }

    async function replaceCatalog(root, targetUrl, options) {
        const requestUrl = new URL(targetUrl, window.location.href);
        if (!sameOfferUrl(requestUrl.href)) {
            window.location.href = requestUrl.href;
            return;
        }

        if (activeController) {
            activeController.abort();
        }
        activeController = new AbortController();
        const controller = activeController;
        setLoading(root, true);

        try {
            const response = await fetch(requestUrl.href, {
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                signal: controller.signal
            });
            if (!response.ok) {
                throw new Error('Offer catalog request failed: ' + response.status);
            }

            const html = await response.text();
            const nextDocument = new DOMParser().parseFromString(html, 'text/html');
            const nextRoot = nextDocument.querySelector(rootSelector);
            if (!nextRoot) {
                throw new Error('Offer catalog root was not found in response.');
            }

            updateHead(nextDocument);
            root.replaceWith(nextRoot);

            const nextUrl = finalUrl(response, requestUrl);
            if (options.pushState) {
                window.history.pushState({tacticumOfferCatalog: true}, '', nextUrl);
            }
            announce(nextRoot);
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }
            window.location.href = requestUrl.href;
        } finally {
            if (activeController === controller) {
                const currentRoot = document.querySelector(rootSelector);
                setLoading(currentRoot, false);
                activeController = null;
            }
        }
    }

    function targetFromForm(form) {
        const action = new URL(form.getAttribute('action') || '/offer/#offer-catalog', window.location.href);
        const params = new URLSearchParams();
        const formData = new FormData(form);
        formData.forEach((value, key) => {
            const normalizedValue = String(value || '').trim();
            if (normalizedValue === '') {
                return;
            }
            if (key === 'sort' && normalizedValue === 'new') {
                return;
            }
            params.set(key, normalizedValue);
        });
        action.search = params.toString();
        if (!action.hash) {
            action.hash = 'offer-catalog';
        }
        return action.href;
    }

    document.addEventListener('click', (event) => {
        const target = eventElement(event);
        const link = target ? target.closest(linkSelector) : null;
        if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        const root = offerRoot(link);
        if (!root || !sameOfferUrl(link.href)) {
            return;
        }
        event.preventDefault();
        replaceCatalog(root, link.href, {pushState: true});
    });

    document.addEventListener('submit', (event) => {
        const target = eventElement(event);
        const form = target ? target.closest(formSelector) : null;
        if (!form) {
            return;
        }
        const root = offerRoot(form);
        if (!root) {
            return;
        }
        event.preventDefault();
        replaceCatalog(root, targetFromForm(form), {pushState: true});
    });

    window.addEventListener('popstate', () => {
        const root = document.querySelector(rootSelector);
        if (!root) {
            window.location.reload();
            return;
        }
        replaceCatalog(root, window.location.href, {pushState: false});
    });

    if (document.querySelector(rootSelector) && (!window.history.state || !window.history.state.tacticumOfferCatalog)) {
        window.history.replaceState({tacticumOfferCatalog: true}, '', window.location.href);
    }
})();
