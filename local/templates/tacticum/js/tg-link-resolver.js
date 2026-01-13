document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumTgResolverInit === "true") return;
    root.dataset.tacticumTgResolverInit = "true";

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

            requestWithTimeout(ENDPOINT_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    url: pageUrl,
                    bot_name: originalHref,
                }),
            })
                .then((r) => (r.ok ? r.json() : Promise.reject(new Error("Bad response: " + r.status))))
                .then((data) => {
                    const newLink = data && typeof data.link === "string" ? data.link.trim() : "";
                    if (!newLink) return;
                    setCached(originalHref, newLink);
                    links.forEach((a) => {
                        a.href = newLink;
                    });
                })
                .catch((err) => {
                    console.warn("[tg-link-resolver]", originalHref, "failed:", err?.message || err);
                });
        });
    } catch (e) {
        console.warn("[tg-link-resolver] init failed:", e);
    }
});
