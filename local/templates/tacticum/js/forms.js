document.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement;
    if (root.dataset.tacticumFormsInit === "true") return;
    root.dataset.tacticumFormsInit = "true";

    const FORM_ENDPOINT = "/local/rest/tacticum_form.php";
    const DEFAULT_SUCCESS_MESSAGE = "Заявка отправлена! Мы скоро свяжемся с вами.";
    const DEFAULT_ERROR_MESSAGE = "Не удалось отправить форму. Попробуйте позже.";
    const RETURNING_LEAD_STORAGE_KEY = "tacticum:returningLead:v1";

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
    };

    const validateForm = (form) => {
        const requiredFields = ["name", "email", "phone", "message"];
        let isValid = true;

        requiredFields.forEach((fieldName) => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field) {
                isValid = false;
                return;
            }

            let hasError = false;
            const value = (field.value || "").trim();

            if (fieldName === "email") {
                hasError = value === "" || !value.includes("@");
            } else {
                hasError = value === "";
            }

            setFieldError(field, hasError);
            toggleErrorHint(form, field, hasError);
            if (hasError) isValid = false;
        });

        const consent = form.querySelector("[data-tacticum-consent]");
        if (consent) {
            const consentError = !consent.checked;
            if (consentError) {
                consent.classList.add("ring-2", "ring-red-500");
            } else {
                consent.classList.remove("ring-2", "ring-red-500");
            }
            if (consentError) isValid = false;
        }

        return isValid;
    };

    const buildPayload = (form) => {
        const data = Object.fromEntries(new FormData(form).entries());
        data.page_url = window.location.href;
        if (window.BX && typeof BX.bitrix_sessid === "function") {
            data.sessid = BX.bitrix_sessid();
        }

        const scopedGroupId = (form.dataset.tacticumOfferGroupId || "").trim();
        const globalGroupId = acceptsOfferContext(form)
            ? (window.tacticum_offer_context?.groupId || "").trim()
            : "";
        const groupId = scopedGroupId || globalGroupId;
        if (groupId) {
            data.group_id = groupId;
        }

        if (form.dataset.formId) {
            data.form_id = form.dataset.formId;
        }

        return data;
    };

    const getFormEndpoint = (form) => {
        const endpoint = (form.dataset.endpoint || "").trim();
        return endpoint.startsWith("/") && !endpoint.startsWith("//") ? endpoint : FORM_ENDPOINT;
    };

    const getEndpointKey = (endpoint) => {
        const parts = endpoint.split("/");
        return parts.pop() || "unknown";
    };

    const setLoadingState = (form, isLoading) => {
        const submitBtn = form.querySelector("button[type='submit']");
        if (!submitBtn) return;
        const spinner = submitBtn.querySelector("[data-role='spinner']");
        const btnText = submitBtn.querySelector("[data-role='btn-text']");

        submitBtn.disabled = isLoading;
        if (spinner) {
            spinner.classList.toggle("hidden", !isLoading);
        }
        if (btnText) {
            if (!btnText.dataset.defaultText) {
                btnText.dataset.defaultText = btnText.textContent;
            }
            btnText.textContent = isLoading ? "Отправляем..." : btnText.dataset.defaultText;
        }
    };

    const closeModalIfNeeded = (form) => {
        const closeTarget = form.dataset.tacticumCloseTarget;
        if (!closeTarget) return;
        const modal = document.querySelector(closeTarget);
        if (!modal) return;

        const mode = form.dataset.tacticumCloseMode || "hidden";
        if (mode === "overlay") {
            modal.classList.add("opacity-0", "pointer-events-none");
            const panel = modal.querySelector(".bg-white");
            panel?.classList.add("scale-95");
            document.body.classList.remove("overflow-hidden");
            return;
        }

        modal.classList.add("hidden");
        document.body.classList.remove("overflow-hidden");
    };

    document.addEventListener("input", (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement)) return;
        setFieldError(target, false);
        const form = target.closest("form");
        if (form) {
            toggleErrorHint(form, target, false);
        }
    });

    document.addEventListener("submit", async (event) => {
        const form = event.target.closest("[data-tacticum-form]");
        if (!form) return;
        event.preventDefault();

        const valid = validateForm(form);
        if (!valid) {
            trackEvent("tacticum_form_validation_error", {
                form_id: getFormId(form),
            });
            showToast("Пожалуйста, заполните обязательные поля и подтвердите согласие.", "error");
            return;
        }

        const payload = buildPayload(form);
        const endpoint = getFormEndpoint(form);
        const formMeta = {
            form_id: getFormId(form),
            endpoint: getEndpointKey(endpoint),
        };
        const productMeta = buildProductAnalyticsMeta(payload);

        try {
            setLoadingState(form, true);
            trackEvent("tacticum_form_submit", formMeta);
            if (productMeta) {
                trackEvent("tacticum_product_form_submit", {
                    ...formMeta,
                    ...productMeta,
                });
            }
            const response = await fetch(endpoint, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            const json = await response.json().catch(() => null);
            if (!response.ok || !json?.success) {
                const errorMessage = normalizeMessage(json?.error || json?.message) || DEFAULT_ERROR_MESSAGE;
                trackEvent("tacticum_form_error", {
                    ...formMeta,
                    status: response.status,
                    code: json?.code || "unknown",
                });
                if (productMeta) {
                    trackEvent("tacticum_product_form_error", {
                        ...formMeta,
                        ...productMeta,
                        status: response.status,
                        code: json?.code || "unknown",
                    });
                }
                showToast(errorMessage, "error");
                return;
            }

            const successMessage = form.dataset.successMessage || DEFAULT_SUCCESS_MESSAGE;
            markReturningLead(form, payload);
            trackEvent("tacticum_form_success", {
                ...formMeta,
                status: response.status,
            });
            if (productMeta) {
                trackEvent("tacticum_product_form_success", {
                    ...formMeta,
                    ...productMeta,
                    status: response.status,
                });
            }
            showToast(successMessage, "success");
            form.reset();
            applyReturningLeadState(form);
            delete form.dataset.tacticumOfferGroupId;
            closeModalIfNeeded(form);
        } catch (error) {
            trackEvent("tacticum_form_error", {
                ...formMeta,
                status: "network",
                code: "fetch_error",
            });
            if (productMeta) {
                trackEvent("tacticum_product_form_error", {
                    ...formMeta,
                    ...productMeta,
                    status: "network",
                    code: "fetch_error",
                });
            }
            showToast(DEFAULT_ERROR_MESSAGE, "error");
        } finally {
            setLoadingState(form, false);
        }
    });

    const initPrefillTriggers = () => {
        document.addEventListener("click", (event) => {
            const trigger = event.target.closest("[data-tacticum-prefill-value]");
            if (!trigger) return;

            const targetSelector = trigger.dataset.tacticumPrefillTarget;
            const value = trigger.dataset.tacticumPrefillValue;
            if (!targetSelector || !value) return;

            const field = document.querySelector(targetSelector);
            if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement)) return;

            const existingValue = field.value.trim();
            if (existingValue === "") {
                field.value = value;
            } else if (!existingValue.includes(value)) {
                field.value = `${existingValue}\n\n${value}`;
            }
            field.focus();
        });
    };

    initPrefillTriggers();
    initReturningLeadPanels();
});
