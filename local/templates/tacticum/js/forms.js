document.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement;
    if (root.dataset.tacticumFormsInit === "true") return;
    if (!window.TacticumFormsRuntime) return;
    root.dataset.tacticumFormsInit = "true";

    const FORM_ENDPOINT = "/local/rest/tacticum_form.php";
    const DEFAULT_SUCCESS_MESSAGE = "Заявка отправлена! Мы скоро свяжемся с вами.";
    const DEFAULT_ERROR_MESSAGE = "Не удалось отправить форму. Попробуйте позже.";

    const {
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
    } = window.TacticumFormsRuntime;

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
            consent.setAttribute("aria-invalid", consentError ? "true" : "false");
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
        form.setAttribute("aria-busy", isLoading ? "true" : "false");
        form.dataset.tacticumSubmitting = isLoading ? "true" : "false";

        const controls = form.querySelectorAll("input:not([type='hidden']), textarea, select, button");
        controls.forEach((control) => {
            if (isLoading) {
                if (!control.dataset.tacticumWasDisabled) {
                    control.dataset.tacticumWasDisabled = control.disabled ? "true" : "false";
                }
                control.disabled = true;
                return;
            }

            if (control.dataset.tacticumWasDisabled === "false") {
                control.disabled = false;
            }
            delete control.dataset.tacticumWasDisabled;
        });

        if (!submitBtn) return;

        const spinner = submitBtn.querySelector("[data-role='spinner']");
        const btnText = submitBtn.querySelector("[data-role='btn-text']");
        submitBtn.classList.toggle("opacity-70", isLoading);
        submitBtn.classList.toggle("cursor-wait", isLoading);
        if (spinner) {
            spinner.classList.toggle("hidden", !isLoading);
        }
        if (btnText) {
            if (!btnText.dataset.defaultText) {
                btnText.dataset.defaultText = btnText.textContent;
            }
            btnText.textContent = isLoading ? "Отправляем..." : btnText.dataset.defaultText;
        } else if (submitBtn.children.length === 0) {
            if (!submitBtn.dataset.defaultText) {
                submitBtn.dataset.defaultText = submitBtn.textContent.trim();
            }
            submitBtn.textContent = isLoading ? "Отправляем..." : submitBtn.dataset.defaultText;
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
