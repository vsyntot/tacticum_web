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
            'a[href^="https://t.me/"]',
            'a[href^="http://t.me/"]',
            'a[href^="https://telegram.me/"]',
            'a[href^="http://telegram.me/"]',
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

        mapByHref.forEach((links, originalHref) => {
            const cached = getCached(originalHref);
            if (cached) {
                links.forEach((a) => {
                    a.href = cached;
                });
                return;
            }

            const payload = {
                url: pageUrl,
                bot_name: originalHref,
            };
            if (window.BX && typeof BX.bitrix_sessid === "function") {
                payload.sessid = BX.bitrix_sessid();
            }

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
                        const error = new Error("Bad response: " + response.status);
                        error.analyticsTracked = true;
                        return Promise.reject(error);
                    }
                    return response.json().then((data) => ({ data, status: response.status }));
                })
                .then(({ data, status }) => {
                    const newLink = data && typeof data.link === "string" ? data.link.trim() : "";
                    if (!newLink) return;
                    setCached(originalHref, newLink);
                    links.forEach((a) => {
                        a.href = newLink;
                    });
                    trackEvent("tacticum_tg_resolver_success", {
                        status,
                        links_count: links.length,
                    });
                })
                .catch((err) => {
                    if (!err?.analyticsTracked) {
                        trackEvent("tacticum_tg_resolver_error", {
                            status: "network",
                            code: "fetch_error",
                        });
                    }
                    console.warn("[tg-link-resolver]", originalHref, "failed:", err?.message || err);
                });
        });
    } catch (e) {
        trackEvent("tacticum_tg_resolver_error", {
            status: "init",
            code: "runtime_error",
        });
        console.warn("[tg-link-resolver] init failed:", e);
    }
});
