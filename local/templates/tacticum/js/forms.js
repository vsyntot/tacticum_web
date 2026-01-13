document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumFormsInit === "true") return;
    root.dataset.tacticumFormsInit = "true";

    const pathName = window.location.pathname || "";
    const isAibotPage = pathName.includes("aibot");
    const isPricePage = pathName.includes("price");
    const isServicesPage = pathName.includes("services");

    if (isAibotPage) {
        const contactForm = document.getElementById("contactForm");
        if (contactForm && !contactForm.dataset.tacticumSubmitBound) {
            contactForm.dataset.tacticumSubmitBound = "true";
            contactForm.addEventListener("submit", function (e) {
                e.preventDefault();
                alert("Спасибо за вашу заявку! Мы свяжемся с вами в ближайшее время.");
                contactForm.reset();
                document.getElementById("formModal")?.classList.add("hidden");
                document.body.style.overflow = "auto";
            });
        }

        const contactFormInline = document.getElementById("contactFormInline");
        if (contactFormInline && !contactFormInline.dataset.tacticumSubmitBound) {
            contactFormInline.dataset.tacticumSubmitBound = "true";
            contactFormInline.addEventListener("submit", function (e) {
                e.preventDefault();
                alert("Спасибо за вашу заявку! Мы свяжемся с вами в ближайшее время.");
                contactFormInline.reset();
            });
        }
    }

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

    if (!isPricePage) {
        initFloatingLabels();
        initCheckboxes();
    } else if (!isServicesPage) {
        initFloatingLabels();
    } else {
        initFloatingLabels();
        initCheckboxes();
    }

    const contactForm = document.getElementById("contactForm");
    if (contactForm && !contactForm.dataset.tacticumValidationBound) {
        contactForm.dataset.tacticumValidationBound = "true";
        contactForm.addEventListener("submit", function (e) {
            e.preventDefault();

            let valid = true;
            const name = document.getElementById("name");
            const email = document.getElementById("email");
            const message = document.getElementById("message");
            const privacy = document.getElementById("privacy");

            if (name && !name.value.trim()) {
                name.classList.add("ring-2", "ring-red-500", "border-transparent");
                valid = false;
            } else if (name) {
                name.classList.remove("ring-2", "ring-red-500", "border-transparent");
            }

            if (!email || !email.value.trim() || !email.value.includes("@")) {
                email?.classList.add("ring-2", "ring-red-500", "border-transparent");
                valid = false;
            } else {
                email.classList.remove("ring-2", "ring-red-500", "border-transparent");
            }

            if (message && !message.value.trim()) {
                message.classList.add("ring-2", "ring-red-500", "border-transparent");
                valid = false;
            } else if (message) {
                message.classList.remove("ring-2", "ring-red-500", "border-transparent");
            }

            if (privacy && !privacy.checked) {
                privacy.nextElementSibling?.classList.add("ring-2", "ring-red-500");
                valid = false;
            } else if (privacy?.nextElementSibling) {
                privacy.nextElementSibling.classList.remove("ring-2", "ring-red-500");
            }

            if (valid) {
                const successMessage = document.createElement("div");
                successMessage.className =
                    "fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-500 translate-x-full";
                successMessage.innerHTML = `
      <div class="flex items-center gap-3">
        <i class="ri-check-line text-xl"></i>
        <span>Сообщение успешно отправлено! Мы свяжемся с вами в ближайшее время.</span>
      </div>
      `;
                document.body.appendChild(successMessage);

                setTimeout(() => {
                    successMessage.classList.remove("translate-x-full");
                }, 100);
                setTimeout(() => {
                    successMessage.classList.add("translate-x-full");
                    setTimeout(() => {
                        document.body.removeChild(successMessage);
                    }, 500);
                }, 5000);

                contactForm.reset();
            }
        });
    }

    const consultForm = document.querySelector(".bg-gradient-to-br form");
    if (consultForm && !consultForm.dataset.tacticumSubmitBound) {
        consultForm.dataset.tacticumSubmitBound = "true";
        consultForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const successMessage = document.createElement("div");
            successMessage.className =
                "fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-500 translate-x-full";
            successMessage.innerHTML = `
      <div class="flex items-center gap-3">
        <i class="ri-check-line text-xl"></i>
        <span>Заявка на консультацию успешно отправлена! Мы свяжемся с вами для подтверждения.</span>
      </div>
      `;
            document.body.appendChild(successMessage);

            setTimeout(() => {
                successMessage.classList.remove("translate-x-full");
            }, 100);
            setTimeout(() => {
                successMessage.classList.add("translate-x-full");
                setTimeout(() => {
                    document.body.removeChild(successMessage);
                }, 500);
            }, 5000);

            this.reset();
        });
    }

    const applicationForm = document.getElementById("applicationForm");
    if (applicationForm && !applicationForm.dataset.tacticumSubmitBound) {
        applicationForm.dataset.tacticumSubmitBound = "true";
        applicationForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(applicationForm);
            const formValues = {};
            for (let [key, value] of formData.entries()) formValues[key] = value;
            alert("Заявка успешно отправлена! Наш менеджер свяжется с вами в ближайшее время.");
            console.log("Form data:", formValues);
        });
    }
});
