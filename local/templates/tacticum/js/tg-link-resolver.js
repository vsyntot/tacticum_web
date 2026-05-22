document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumTgResolverInit === "true") return;
    root.dataset.tacticumTgResolverInit = "true";

    const trackEvent = (eventName, params = {}) => {
        if (typeof window.tacticumTrackEvent === "function") {
            window.tacticumTrackEvent(eventName, params);
        }
    };

    try {
        const pageUrl = window.location.href;

        const tgSelectors = [
            'a[data-tacticum-tg-resolve][href^="https://t.me/"]',
            'a[data-tacticum-tg-resolve][href^="http://t.me/"]',
            'a[data-tacticum-tg-resolve][href^="https://telegram.me/"]',
            'a[data-tacticum-tg-resolve][href^="http://telegram.me/"]',
        ];
        const allTgLinks = Array.from(document.querySelectorAll(tgSelectors.join(",")));
        if (allTgLinks.length === 0) return;

        const mapByHref = new Map();
        for (const a of allTgLinks) {
            const rawHref = (a.getAttribute("href") || "").trim();
            if (!rawHref) continue;
            if (!mapByHref.has(rawHref)) mapByHref.set(rawHref, []);
            mapByHref.get(rawHref).push(a);
        }
        if (mapByHref.size === 0) return;

        const ENDPOINT_URL = "/local/rest/resolve_telegram_link.php";

        const cacheKey = (href) => `tg_link_cache::${href}`;
        const getCached = (href) => {
            try {
                const raw = sessionStorage.getItem(cacheKey(href));
                if (!raw) return null;
                const obj = JSON.parse(raw);
                return obj && typeof obj.link === "string" ? obj.link : null;
            } catch {
                return null;
            }
        };
        const setCached = (href, link) => {
            try {
                sessionStorage.setItem(cacheKey(href), JSON.stringify({ link }));
            } catch {}
        };

        const requestWithTimeout = (url, options = {}, ms = 8000) => {
            const controller = new AbortController();
            const id = setTimeout(() => controller.abort(), ms);
            return fetch(url, { ...options, signal: controller.signal }).finally(() => clearTimeout(id));
        };

        const openResolvedLink = (anchor, href, pendingWindow = null) => {
            anchor.href = href;
            if (pendingWindow && !pendingWindow.closed) {
                pendingWindow.location.href = href;
                return;
            }
            if (anchor.target === "_blank") {
                const opened = window.open(href, "_blank");
                if (opened) opened.opener = null;
                return;
            }
            window.location.href = href;
        };

        mapByHref.forEach((links, originalHref) => {
            const cached = getCached(originalHref);
            if (cached) {
                links.forEach((a) => {
                    a.href = cached;
                });
                return;
            }

            links.forEach((a) => {
                a.addEventListener("click", (event) => {
                    const currentCached = getCached(originalHref);
                    if (currentCached) {
                        a.href = currentCached;
                        return;
                    }

                    const sessid = window.BX && typeof BX.bitrix_sessid === "function" ? BX.bitrix_sessid() : "";
                    if (!sessid) {
                        trackEvent("tacticum_tg_resolver_skip", {
                            status: "no_sessid",
                            code: "csrf_unavailable",
                        });
                        return;
                    }

                    event.preventDefault();
                    const pendingWindow = a.target === "_blank" ? window.open("about:blank", "_blank") : null;
                    if (pendingWindow) pendingWindow.opener = null;

                    const payload = {
                        url: pageUrl,
                        bot_name: originalHref,
                        sessid,
                    };

                    requestWithTimeout(ENDPOINT_URL, {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(payload),
                    })
                        .then((response) => {
                            if (!response.ok) {
                                trackEvent("tacticum_tg_resolver_error", {
                                    status: response.status,
                                    code: "http_error",
                                });
                                return null;
                            }
                            if (response.status === 204) {
                                return null;
                            }
                            return response.json().then((data) => ({ data, status: response.status }));
                        })
                        .then((result) => {
                            const data = result?.data || null;
                            const status = result?.status || "empty";
                            const newLink = data && typeof data.link === "string" ? data.link.trim() : "";
                            if (!newLink) {
                                openResolvedLink(a, originalHref, pendingWindow);
                                return;
                            }
                            setCached(originalHref, newLink);
                            links.forEach((link) => {
                                link.href = newLink;
                            });
                            trackEvent("tacticum_tg_resolver_success", {
                                status,
                                links_count: links.length,
                            });
                            openResolvedLink(a, newLink, pendingWindow);
                        })
                        .catch(() => {
                            trackEvent("tacticum_tg_resolver_error", {
                                status: "network",
                                code: "fetch_error",
                            });
                            openResolvedLink(a, originalHref, pendingWindow);
                        });
                });
            });
        });
    } catch (e) {
        trackEvent("tacticum_tg_resolver_error", {
            status: "init",
            code: "runtime_error",
        });
    }
});
