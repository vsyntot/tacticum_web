(function () {
    if (window.tacticumTrackEvent) return;

    const METRIKA_COUNTER_ID = 103471113;
    const MAX_STRING_LENGTH = 80;

    const normalizeKey = (key) =>
        String(key || "")
            .toLowerCase()
            .replace(/[^a-z0-9_]+/g, "_")
            .replace(/^_+|_+$/g, "")
            .slice(0, 40);

    const normalizeEventName = (name) => {
        const normalized = normalizeKey(name);
        return normalized ? normalized.slice(0, 60) : "";
    };

    const normalizeValue = (value) => {
        if (value === null || value === undefined) return "";
        if (typeof value === "number" || typeof value === "boolean") return value;
        return String(value).replace(/\s+/g, " ").trim().slice(0, MAX_STRING_LENGTH);
    };

    const sanitizeParams = (params) => {
        if (!params || typeof params !== "object") return {};

        return Object.entries(params).reduce((acc, [rawKey, rawValue]) => {
            const key = normalizeKey(rawKey);
            if (!key) return acc;
            acc[key] = normalizeValue(rawValue);
            return acc;
        }, {});
    };

    window.tacticumTrackEvent = function (eventName, params = {}) {
        const goal = normalizeEventName(eventName);
        if (!goal) return;

        const safeParams = sanitizeParams({
            page_path: window.location.pathname,
            ...params,
        });

        try {
            if (typeof window.ym === "function") {
                window.ym(METRIKA_COUNTER_ID, "reachGoal", goal, safeParams);
            }
        } catch (error) {}

        try {
            if (Array.isArray(window.dataLayer)) {
                window.dataLayer.push({ event: goal, ...safeParams });
            }
        } catch (error) {}

        try {
            window.dispatchEvent(new CustomEvent("tacticum:analytics", {
                detail: { event: goal, params: safeParams },
            }));
        } catch (error) {}
    };
})();
