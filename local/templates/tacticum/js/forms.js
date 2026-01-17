document.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement;
    if (root.dataset.tacticumFormsInit === "true") return;
    root.dataset.tacticumFormsInit = "true";

    const FORM_ENDPOINT = "/local/rest/tacticum_form.php";
    const DEFAULT_SUCCESS_MESSAGE = "Заявка отправлена! Мы скоро свяжемся с вами.";
    const DEFAULT_ERROR_MESSAGE = "Не удалось отправить форму. Попробуйте позже.";

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

    const showToast = (message, variant = "success") => {
        const container = ensureToastContainer();
        const toast = document.createElement("div");
        const baseClasses =
            "rounded-lg shadow-lg px-5 py-3 flex items-center gap-2 transition-transform duration-300 translate-x-full";
        const variantClasses =
            variant === "error" ? "bg-red-500 text-white" : "bg-green-500 text-white";

        toast.className = `${baseClasses} ${variantClasses}`;
        toast.innerHTML = `
            <i class="${variant === "error" ? "ri-close-circle-line" : "ri-check-line"} text-xl"></i>
            <span>${message}</span>
        `;

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

        if (window.tacticum_offer_context?.groupId) {
            data.group_id = window.tacticum_offer_context.groupId;
        }

        if (form.dataset.formId) {
            data.form_id = form.dataset.formId;
        }

        return data;
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
            showToast("Пожалуйста, заполните обязательные поля и подтвердите согласие.", "error");
            return;
        }

        const payload = buildPayload(form);

        try {
            setLoadingState(form, true);
            const response = await fetch(FORM_ENDPOINT, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            });

            const json = await response.json().catch(() => null);
            if (!response.ok || !json?.success) {
                const errorMessage = json?.error || DEFAULT_ERROR_MESSAGE;
                showToast(errorMessage, "error");
                return;
            }

            const successMessage = form.dataset.successMessage || DEFAULT_SUCCESS_MESSAGE;
            showToast(successMessage, "success");
            form.reset();
            closeModalIfNeeded(form);
        } catch (error) {
            showToast(DEFAULT_ERROR_MESSAGE, "error");
        } finally {
            setLoadingState(form, false);
        }
    });

    const initFloatingLabels = () => {
        const formInputs = document.querySelectorAll("input, textarea");
        formInputs.forEach((input) => {
            if (input.dataset.tacticumLabelBound) return;
            input.dataset.tacticumLabelBound = "true";

            if (input.value) {
                const label = input.nextElementSibling;
                if (label && label.tagName === "LABEL") {
                    label.style.transform = "translateY(-24px)";
                    label.style.fontSize = "0.75rem";
                }
            }

            input.addEventListener("focus", function () {
                const label = this.nextElementSibling;
                if (label && label.tagName === "LABEL") {
                    label.style.transform = "translateY(-24px)";
                    label.style.fontSize = "0.75rem";
                    label.style.color = "#0066CC";
                }
            });

            input.addEventListener("blur", function () {
                const label = this.nextElementSibling;
                if (label && label.tagName === "LABEL" && !this.value) {
                    label.style.transform = "";
                    label.style.fontSize = "";
                    label.style.color = "";
                }
            });
        });
    };

    const initCheckboxes = () => {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach((checkbox) => {
            if (checkbox.dataset.tacticumCheckboxBound) return;
            checkbox.dataset.tacticumCheckboxBound = "true";
            checkbox.addEventListener("change", function () {
                if (this.checked) {
                    this.style.backgroundImage =
                        "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3E%3Cpath d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E\")";
                    this.style.backgroundSize = "70%";
                    this.style.backgroundPosition = "center";
                    this.style.backgroundRepeat = "no-repeat";
                } else {
                    this.style.backgroundImage = "none";
                }
            });
        });
    };

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

    initFloatingLabels();
    initCheckboxes();
    initPrefillTriggers();
});
