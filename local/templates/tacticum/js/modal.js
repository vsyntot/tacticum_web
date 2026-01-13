document.addEventListener("DOMContentLoaded", function () {
    const root = document.documentElement;
    if (root.dataset.tacticumModalInit === "true") return;
    root.dataset.tacticumModalInit = "true";

    const pathName = window.location.pathname || "";
    const isAibotPage = pathName.includes("aibot");

    if (isAibotPage) {
        const openFormBtn = document.getElementById("openFormBtn");
        const closeFormBtn = document.getElementById("closeFormBtn");
        const formModal = document.getElementById("formModal");

        if (openFormBtn && closeFormBtn && formModal && !formModal.dataset.tacticumModalBound) {
            formModal.dataset.tacticumModalBound = "true";

            openFormBtn.addEventListener("click", function () {
                formModal.classList.remove("hidden");
                document.body.style.overflow = "hidden";
            });

            closeFormBtn.addEventListener("click", function () {
                formModal.classList.add("hidden");
                document.body.style.overflow = "auto";
            });

            formModal.addEventListener("click", function (e) {
                if (e.target === formModal) {
                    formModal.classList.add("hidden");
                    document.body.style.overflow = "auto";
                }
            });
        }
    }

    const tacticumModal = document.getElementById("tacticum-modal");
    if (tacticumModal && !tacticumModal.dataset.tacticumModalBound) {
        tacticumModal.dataset.tacticumModalBound = "true";
        const backdrop = tacticumModal.firstElementChild;
        const closeBtn = document.getElementById("tacticum-modal-close");
        const form = document.getElementById("tacticum-modal-form");
        const toast = document.getElementById("tacticum-toast");

        const openModal = () => {
            tacticumModal.classList.remove("hidden");
            document.body.classList.add("overflow-hidden");
            trapFocus();
            setTimeout(() => document.getElementById("modal-name")?.focus(), 50);
        };

        document.getElementById("contactUsBtn")?.addEventListener("click", (e) => {
            e.preventDefault();
            openModal();
        });

        document.querySelectorAll(".tacticum-contact-btn").forEach((btn) =>
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                openModal();
            })
        );

        const closeModal = () => {
            tacticumModal.classList.add("hidden");
            document.body.classList.remove("overflow-hidden");
            form?.reset();
            tacticumModal.querySelectorAll("[data-error]").forEach((el) => el.classList.add("hidden"));
        };

        closeBtn?.addEventListener("click", closeModal);
        backdrop?.addEventListener("click", closeModal);
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && !tacticumModal.classList.contains("hidden")) closeModal();
        });

        function trapFocus() {
            const focusable = tacticumModal.querySelectorAll(
                'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])'
            );
            const list = Array.from(focusable).filter((el) => !el.hasAttribute("disabled"));
            if (!list.length) return;
            const first = list[0];
            const last = list[list.length - 1];
            function loop(e) {
                if (e.key !== "Tab") return;
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
            tacticumModal.addEventListener("keydown", loop);
        }

        function showError(id, cond) {
            const input = document.getElementById(id);
            const hint = tacticumModal.querySelector(`[data-error="${id}"]`);
            if (!input) return false;
            if (!cond) {
                input.classList.add("ring-2", "ring-red-500", "border-transparent");
                hint?.classList.remove("hidden");
                return true;
            }
            input.classList.remove("ring-2", "ring-red-500", "border-transparent");
            hint?.classList.add("hidden");
            return false;
        }

        form?.addEventListener("submit", async (e) => {
            e.preventDefault();

            const name = document.getElementById("modal-name")?.value.trim() || "";
            const company = document.getElementById("modal-company")?.value.trim() || "";
            const email = document.getElementById("modal-email")?.value.trim() || "";
            const phone = document.getElementById("modal-phone")?.value.trim() || "";
            const task = document.getElementById("modal-message")?.value.trim() || "";
            const agreement = document.getElementById("modal-agreement")?.checked || false;

            let hasErr = false;
            hasErr |= showError("modal-name", !!name);
            hasErr |= showError("modal-email", !!email && email.includes("@"));
            hasErr |= showError("modal-phone", !!phone);
            if (!agreement) {
                alert("Подтвердите согласие на обработку персональных данных");
                return;
            }
            if (hasErr) return;

            const btn = form.querySelector('button[type="submit"]');
            const spinner = btn?.querySelector('[data-role="spinner"]');
            const btnText = btn?.querySelector('[data-role="btn-text"]');
            if (btn) btn.disabled = true;
            spinner?.classList.remove("hidden");
            if (btnText) btnText.textContent = "Отправляем...";

            const page_url = window.location.href;

            try {
                const res = await fetch("/local/rest/tacticum_offer.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ name, company, email, phone, task, page_url }),
                });
                const json = await res.json().catch(() => ({ success: false }));
                if (json?.success) {
                    toast?.classList.remove("translate-x-full");
                    setTimeout(() => toast?.classList.add("translate-x-full"), 4500);
                    closeModal();
                } else {
                    alert("Ошибка отправки: " + (json?.error || "попробуйте позже"));
                }
            } catch (err) {
                alert("Ошибка соединения: " + (err?.message || err));
            } finally {
                if (btn) btn.disabled = false;
                spinner?.classList.add("hidden");
                if (btnText) btnText.textContent = "Отправить заявку";
            }
        });

        const agree = document.getElementById("modal-agreement");
        agree?.addEventListener("change", () => {
            agree.style.background = agree.checked
                ? "url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22white%22><path d=%22M9 16.17L4.83 12l-1.42 1.41L9 19l12-12-1.41-1.41z%22/></svg>') center/90% no-repeat, #0066CC"
                : "";
            agree.style.borderColor = agree.checked ? "#0066CC" : "";
        });
    }

    if (!document.querySelector(".order-specialist-btn")) {
        const orderButtons = Array.from(document.querySelectorAll("button")).filter(
            (button) => button.textContent.trim() === "Заказать специалиста"
        );

        if (orderButtons.length && !document.getElementById("specialistOrderModal")) {
            const modalTemplate = document.createElement("div");
            modalTemplate.id = "specialistOrderModal";
            modalTemplate.className =
                "fixed inset-0 bg-black/50 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300";
            modalTemplate.innerHTML = `
        <div class="bg-white rounded-xl p-8 max-w-xl w-full mx-4 transform scale-95 transition-transform duration-300 max-h-[90vh] overflow-y-auto">
          <div class="flex justify-between items-center mb-6 sticky top-0 bg-white z-10">
            <h3 class="text-2xl font-bold text-secondary">Заказать специалиста</h3>
            <button id="closeOrderModal" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100" aria-label="Закрыть">
              <i class="ri-close-line text-xl text-gray-500"></i>
            </button>
          </div>
          <form id="specialistOrderForm" class="space-y-6">
            <div class="relative mb-6">
              <div class="p-4 bg-primary/5 rounded-lg border border-primary/10">
                <p class="text-primary font-medium mb-2">Выбранный специалист:</p>
                <p id="selectedSpecialist" class="text-gray-700">Не выбран</p>
                <p id="selectedRate" class="text-sm text-gray-500 mt-1">Ставка: —</p>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="relative">
                <input type="text" id="orderName" name="name" required placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                <label for="orderName" class="absolute left-4 top-3 text-gray-500 transition-transform origin-left">Имя</label>
              </div>
              <div class="relative">
                <input type="email" id="orderEmail" name="email" required placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                <label for="orderEmail" class="absolute left-4 top-3 text-gray-500 transition-transform origin-left">Email</label>
              </div>
              <div class="relative">
                <input type="tel" id="orderPhone" name="phone" required placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                <label for="orderPhone" class="absolute left-4 top-3 text-gray-500 transition-transform origin-left">Телефон</label>
              </div>
              <div class="relative">
                <input type="text" id="orderCompany" name="company" required placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                <label for="orderCompany" class="absolute left-4 top-3 text-gray-500 transition-transform origin-left">Компания</label>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="relative">
                <label for="orderStartDate" class="block text-gray-500 mb-2">Предпочтительная дата начала</label>
                <input type="date" id="orderStartDate" name="startDate" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
              </div>
              <div class="relative">
                <label for="orderDuration" class="block text-gray-500 mb-2">Предполагаемый срок работы</label>
                <div class="relative">
                  <select id="orderDuration" name="duration" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 appearance-none bg-white">
                    <option value="">Выберите срок</option>
                    <option value="1-month">1 месяц</option>
                    <option value="3-months">3 месяца</option>
                    <option value="6-months">6 месяцев</option>
                    <option value="custom">Другой срок</option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                    <i class="ri-arrow-down-s-line text-gray-400"></i>
                  </div>
                </div>
              </div>
            </div>
            <div class="relative">
              <textarea id="orderDescription" name="description" required rows="4" placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></textarea>
              <label for="orderDescription" class="absolute left-4 top-3 text-gray-500 transition-transform origin-left">Описание задачи</label>
            </div>
            <div class="flex items-start gap-2">
              <input type="checkbox" id="orderAgreement" required class="mt-1 appearance-none w-4 h-4 border border-gray-300 rounded bg-white checked:bg-primary checked:border-0 relative">
              <label for="orderAgreement" class="text-sm text-gray-600">Я согласен на обработку персональных данных и принимаю условия <a href="/policies/" class="text-primary hover:underline">политики конфиденциальности</a></label>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 sticky bottom-0 bg-white pt-4">
              <button type="submit" class="w-full sm:flex-1 bg-primary text-white px-6 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">Отправить заявку</button>
              <button type="button" id="cancelOrderModal" class="w-full sm:flex-1 bg_white border border-gray-200 text-gray-700 px-6 py-3 rounded-button hover:bg-gray-50 transition-colors whitespace-nowrap">Отмена</button>
            </div>
          </form>
        </div>
        `;
            document.body.appendChild(modalTemplate);
        }

        const modal = document.getElementById("specialistOrderModal");
        if (modal && !modal.dataset.tacticumModalBound) {
            modal.dataset.tacticumModalBound = "true";
            const closeButton = document.getElementById("closeOrderModal");
            const cancelButton = document.getElementById("cancelOrderModal");
            const orderForm = document.getElementById("specialistOrderForm");

            orderButtons.forEach((button) => {
                button.addEventListener("click", () => {
                    const card = button.closest(".pricing-card");
                    if (card) {
                        const specialistTitle = card.querySelector("h3")?.textContent || "Специалист";
                        const rate = card.querySelector(".text-2xl")?.textContent || "—";
                        const selectedSpecialist = document.getElementById("selectedSpecialist");
                        const selectedRate = document.getElementById("selectedRate");
                        if (selectedSpecialist) selectedSpecialist.textContent = specialistTitle;
                        if (selectedRate) selectedRate.textContent = `Ставка: ${rate}`;
                    }
                    modal.classList.remove("opacity-0", "pointer-events-none");
                    modal.querySelector(".bg-white")?.classList.remove("scale-95");
                    document.body.classList.add("overflow-hidden");
                });
            });

            const closeModal = () => {
                modal.classList.add("opacity-0", "pointer-events-none");
                modal.querySelector(".bg-white")?.classList.add("scale-95");
                document.body.classList.remove("overflow-hidden");
            };
            closeButton?.addEventListener("click", closeModal);
            cancelButton?.addEventListener("click", closeModal);
            modal.addEventListener("click", (e) => {
                if (e.target === modal) closeModal();
            });

            orderForm?.addEventListener("submit", (e) => {
                e.preventDefault();
                const formData = new FormData(orderForm);
                const data = Object.fromEntries(formData);
                console.log("Form submitted:", data);

                const successMessage = document.createElement("div");
                successMessage.className =
                    "fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-y-[-100%]";
                successMessage.textContent =
                    "Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.";
                document.body.appendChild(successMessage);

                requestAnimationFrame(() => {
                    successMessage.style.transform = "translateY(0)";
                    successMessage.style.transition = "transform 0.3s ease";
                });
                setTimeout(() => {
                    successMessage.style.transform = "translateY(-100%)";
                    setTimeout(() => successMessage.remove(), 300);
                }, 5000);

                closeModal();
                orderForm.reset();
            });
        }
    }
});
