(function () {
    if (window.TacticumFormsRuntime) return;

    const RETURNING_LEAD_STORAGE_KEY = "tacticum:returningLead:v1";

    const productAnalyticsValues = {
        product: new Set(["ecosystem", "platform", "agents", "dev", "forum"]),
        pageRole: new Set([
            "ecosystem-router",
            "trust-entry",
            "implementation-entry",
            "team-entry",
            "estimate-entry",
            "contact-entry",
            "offer-detail",
            "telegram-bot-entry",
            "product-page",
        ]),
        scenario: new Set([
            "product-routing",
            "product-delivery",
            "product-estimate",
            "product-team",
            "contact-routing",
            "pilot",
            "architecture-session",
            "procurement-security",
            "team-delivery",
            "estimate",
        ]),
    };

    const trackEvent = (eventName, params = {}) => {
        if (typeof window.tacticumTrackEvent === "function") {
            window.tacticumTrackEvent(eventName, params);
        }
    };

    const getFormId = (form) => form.dataset.formId || form.id || "unknown";
    const acceptsOfferContext = (form) => Boolean(
        form.dataset.tacticumAcceptOfferContext === "true" ||
        form.querySelector('[name^="lead_"]')
    );

    const normalizeControlledValue = (value, allowedValues) => {
        const normalized = String(value || "").trim();
        return allowedValues.has(normalized) ? normalized : "";
    };

    const buildProductAnalyticsMeta = (payload) => {
        const product = normalizeControlledValue(payload.lead_product, productAnalyticsValues.product);
        if (!product) return null;

        const pageRole = normalizeControlledValue(payload.lead_page_role, productAnalyticsValues.pageRole);
        const scenario = normalizeControlledValue(payload.lead_scenario, productAnalyticsValues.scenario);
        const meta = { product };
        if (pageRole) {
            meta.page_role = pageRole;
        }
        if (scenario) {
            meta.scenario = scenario;
        }

        return meta;
    };

    const readReturningLeadState = () => {
        try {
            const raw = window.sessionStorage?.getItem(RETURNING_LEAD_STORAGE_KEY);
            const parsed = raw ? JSON.parse(raw) : null;
            return parsed && typeof parsed === "object" && parsed.entries && typeof parsed.entries === "object"
                ? parsed
                : { entries: {} };
        } catch (error) {
            return { entries: {} };
        }
    };

    const writeReturningLeadState = (state) => {
        try {
            window.sessionStorage?.setItem(RETURNING_LEAD_STORAGE_KEY, JSON.stringify(state));
        } catch (error) {
            // Storage can be unavailable in private or locked-down browser modes.
        }
    };

    const buildReturningLeadKey = (form, payload = null) => {
        const source = payload || Object.fromEntries(new FormData(form).entries());
        const product = normalizeControlledValue(source.lead_product, productAnalyticsValues.product);
        if (!product) return "";

        return `${product}:${getFormId(form)}`;
    };

    const markReturningLead = (form, payload) => {
        const key = buildReturningLeadKey(form, payload);
        if (!key) return;

        const state = readReturningLeadState();
        state.entries[key] = {
            product: normalizeControlledValue(payload.lead_product, productAnalyticsValues.product),
            form_id: getFormId(form),
            updated_at: Date.now(),
        };
        writeReturningLeadState(state);
    };

    const applyReturningLeadState = (form) => {
        const panel = form.querySelector("[data-tacticum-returning-lead-panel]");
        if (!panel) return;

        const key = buildReturningLeadKey(form);
        const hasReturningLead = key !== "" && Boolean(readReturningLeadState().entries[key]);
        form.dataset.tacticumReturningLead = hasReturningLead ? "true" : "false";
        panel.classList.toggle("hidden", !hasReturningLead);
    };

    const initReturningLeadPanels = () => {
        document.querySelectorAll("[data-tacticum-form]").forEach((form) => {
            applyReturningLeadState(form);
        });
    };

    const ensureToastContainer = () => {
        let container = document.getElementById("tacticum-toast-container");
        if (!container) {
            container = document.createElement("div");
            container.id = "tacticum-toast-container";
            container.className = "fixed top-4 right-4 z-[999] flex flex-col gap-3";
            container.setAttribute("aria-live", "polite");
            document.body.appendChild(container);
        }
        return container;
    };

    const normalizeMessage = (message) => {
        if (typeof message !== "string") return "";
        return message.replace(/<[^>]*>/g, "").replace(/\s+/g, " ").trim();
    };

    const showToast = (message, variant = "success") => {
        const container = ensureToastContainer();
        const toast = document.createElement("div");
        const baseClasses =
            "rounded-lg shadow-lg px-5 py-3 flex items-center gap-2 transition-transform duration-300 translate-x-full";
        const variantClasses =
            variant === "error" ? "bg-red-500 text-white" : "bg-green-500 text-white";

        toast.className = `${baseClasses} ${variantClasses}`;
        const icon = document.createElement("i");
        icon.className = `${variant === "error" ? "ri-close-circle-line" : "ri-check-line"} text-xl`;
        const text = document.createElement("span");
        text.textContent = message;
        toast.append(icon, text);

        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.remove("translate-x-full");
        });

        setTimeout(() => {
            toast.classList.add("translate-x-full");
            setTimeout(() => toast.remove(), 300);
        }, 4500);
    };

    const setFieldError = (field, hasError) => {
        if (!field) return;
        field.setAttribute("aria-invalid", hasError ? "true" : "false");
        if (hasError) {
            field.classList.add("ring-2", "ring-red-500", "border-transparent");
        } else {
            field.classList.remove("ring-2", "ring-red-500", "border-transparent");
        }
    };

    const toggleErrorHint = (form, field, show) => {
        if (!field?.id) return;
        const hint = form.querySelector(`[data-error="${field.id}"]`);
        if (!hint) return;
        hint.classList.toggle("hidden", !show);
        hint.id ||= `${field.id}-error`;
        if (show) {
            field.setAttribute("aria-describedby", hint.id);
        } else if (field.getAttribute("aria-describedby") === hint.id) {
            field.removeAttribute("aria-describedby");
        }
    };

    window.TacticumFormsRuntime = {
        acceptsOfferContext,
        applyReturningLeadState,
        buildProductAnalyticsMeta,
        getFormId,
        initReturningLeadPanels,
        markReturningLead,
        normalizeMessage,
        setFieldError,
        showToast,
        toggleErrorHint,
        trackEvent,
    };
})();
