<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$curPage = $APPLICATION->GetCurPage();
?>
<!-- Footer -->
<div id="footer">
    <footer class="bg-secondary text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="flex items-center gap-2 text-2xl font-bold mb-6">
                        <a href="/"><img src="<?=SITE_TEMPLATE_PATH?>/images/logo2.png" width="181" height="50" alt="Tacticum"></a>
                    </div>
                    <p class="text-white/70 mb-6">
                        Помогаем бизнесу внедрять искусственный интеллект для решения
                        реальных задач и достижения измеримых результатов.
                    </p>
                    <div class="flex items-center gap-4">
                        <a href="https://t.me/Tacticum_official_bot" target="_blank" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" aria-label="Telegram"><i class="ri-telegram-fill"></i></a>
                        <?/*<a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors"><i class="ri-vk-fill"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors"><i class="ri-youtube-fill"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors"><i class="ri-linkedin-fill"></i></a>*/?>
                    </div>
                </div>
                <?
                $APPLICATION->IncludeComponent(
                        "bitrix:menu",
                        "bottommenu",
                        [
                                "COMPONENT_TEMPLATE" => ".default",
                                "ROOT_MENU_TYPE" => "bottom",
                                "MENU_CACHE_TYPE" => "A",
                                "MENU_CACHE_TIME" => "3600",
                                "MENU_CACHE_USE_GROUPS" => "Y",
                                "MENU_CACHE_GET_VARS" => [],
                                "MAX_LEVEL" => "1",
                                "CHILD_MENU_TYPE" => "left",
                                "USE_EXT" => "N",
                                "DELAY" => "N",
                                "ALLOW_MULTI_SELECT" => "N"
                        ],
                        false
                );
                ?>
                <div>
                    <h3 class="text-lg font-bold mb-6">Контакты</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <i class="ri-map-pin-line mt-1"></i>
                            <span class="text-white/70">119285, г. Москва, Вн.Тер.г. Муниципальный округ Раменки, Км Мжд Киевское 5-й, д. 1 стр. 1, помещ. 3/3</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="ri-phone-line"></i>
                            <a href="tel:+74955612084" class="text-white/70 hover:text-white transition-colors">+7 (495) 561-20-84</a>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="ri-mail-line"></i>
                            <a href="mailto:project@tacticum.ru" class="text-white/70 hover:text-white transition-colors">project@tacticum.ru</a>
                        </li>
                    </ul>
                    <div class="mt-6 space-y-3">
                        <div class="flex items-start gap-3">
                            <i class="ri-file-list-line mt-1"></i>
                            <div class="text-white/70">
                                <p>ИНН: 9722028080</p>
                                <p>КПП: 772901001</p>
                                <p>ОГРН: 1227700525942</p>
                                <p>ОКВЭД: 62.01 Разработка компьютерного программного обеспечения</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10 pt-6 flex flex-col md:flex-row justify_between items-center gap-4">
                <p class="text-white/50 text-sm">&copy; 2022 - <?=date("Y")?> Tacticum. Все права защищены.</p>
                <div class="flex items-center gap-6">
                    <a href="/policies/" class="text-white/50 text-sm hover:text-white transition-colors">Политика конфиденциальности</a>
                    <?/*<a href="#" class="text-white/50 text-sm hover:text-white transition-colors">Условия использования</a>
                    <a href="#" class="text-white/50 text-sm hover:text-white transition-colors">Карта сайта</a>*/?>
                </div>
            </div>
        </div>
    </footer>
</div>

<?if(substr_count($curPage, "aibot") != 0){?>
    <script id="modalScript">
        document.addEventListener('DOMContentLoaded', function() {
            const openFormBtn = document.getElementById('openFormBtn');
            const closeFormBtn = document.getElementById('closeFormBtn');
            const formModal = document.getElementById('formModal');

            openFormBtn?.addEventListener('click', function() {
                formModal?.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });

            closeFormBtn?.addEventListener('click', function() {
                formModal?.classList.add('hidden');
                document.body.style.overflow = 'auto';
            });

            formModal?.addEventListener('click', function(e) {
                if (e.target === formModal) {
                    formModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            });
        });
    </script>

    <script id="formScript">
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contactForm');
            const contactFormInline = document.getElementById('contactFormInline');

            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Спасибо за вашу заявку! Мы свяжемся с вами в ближайшее время.');
                    contactForm.reset();
                    document.getElementById('formModal')?.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                });
            }

            if (contactFormInline) {
                contactFormInline.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alert('Спасибо за вашу заявку! Мы свяжемся с вами в ближайшее время.');
                    contactFormInline.reset();
                });
            }
        });
    </script>

    <script id="smoothScrollScript">
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({ top: targetElement.offsetTop - 80, behavior: 'smooth' });
                    }
                });
            });
        });
    </script>
<?}?>

<script id="chat-interaction">
    document.addEventListener("DOMContentLoaded", function () {
        const chatMessages = document.getElementById("chatMessages");
        const userMessage = document.getElementById("userMessage");
        const sendMessage = document.getElementById("sendMessage");

        const aiResponses = [
            "Спасибо за информацию! В какой отрасли работает ваша компания?",
            "Какой примерный масштаб проекта вы рассматриваете?",
            "Есть ли у вас уже какие-то наработки или данные для обучения AI?",
            "Какие сроки реализации вы рассматриваете?",
            "Отлично! Давайте я подготовлю предварительную оценку. Оставьте, пожалуйста, ваши контактные данные для связи.",
        ];

        let messageCount = 0;

        function addMessage(text, isUser) {
            const messageDiv = document.createElement("div");
            messageDiv.className = "bg-white/5 rounded-lg p-3";
            messageDiv.innerHTML = `
      <p class="text-sm text-white/70 mb-1">${isUser ? "Вы" : "AI-ассистент"}:</p>
      <p class="text-white">${text}</p>
      `;
            chatMessages && chatMessages.appendChild(messageDiv);
            chatMessages && (chatMessages.scrollTop = chatMessages.scrollHeight);
        }

        function handleUserMessage() {
            const message = (userMessage && userMessage.value || "").trim();
            if (message) {
                addMessage(message, true);
                if (userMessage) userMessage.value = "";

                setTimeout(() => {
                    if (messageCount < aiResponses.length) {
                        addMessage(aiResponses[messageCount], false);
                        messageCount++;
                    }
                }, 1000);
            }
        }

        if (sendMessage) sendMessage.addEventListener("click", handleUserMessage);
        if (userMessage) {
            userMessage.addEventListener("keypress", function (e) {
                if (e.key === "Enter") handleUserMessage();
            });
        }
    });
</script>

<script id="mobile-menu">
    document.addEventListener("DOMContentLoaded", function () {
        const menuButton = document.querySelector(".ri-menu-line")?.parentElement;
        if (!menuButton) return;

        // Create mobile menu (initially hidden)
        const mobileMenu = document.createElement("div");
        mobileMenu.className =
            "fixed inset-0 bg-secondary/95 z-50 flex flex-col items-center justify-center transform translate-x-full transition-transform duration-300";
        mobileMenu.innerHTML = `
      <div class="absolute top-4 right-4 w-10 h-10 flex items-center justify_center cursor-pointer text-white">
        <i class="ri-close-line text-2xl"></i>
      </div>
      <nav class="flex flex-col items-center space-y-6 text-xl">
        <a href="/" class="text-white hover:text-primary transition-colors">Главная</a>
        <a href="/services/" data-readdy="true" class="text-white hover:text-primary transition-colors">Услуги</a>
        <a href="/about/" class="text-white hover:text-primary transition-colors">О компании</a>
        <a href="/contacts/" data-readdy="true" class="text-white hover:text-primary transition-colors">Контакты</a>
      </nav>
      <div class="mt-12">
        <button class="bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap tacticum-contact-btn">Связаться с нами</button>
      </div>
      `;
        document.body.appendChild(mobileMenu);

        // Toggle mobile menu
        menuButton.addEventListener("click", function () {
            mobileMenu.classList.toggle("translate-x-full");
            document.body.classList.toggle("overflow-hidden");
        });
        // Close mobile menu
        const closeButton = mobileMenu.querySelector(".ri-close-line")?.parentElement;
        closeButton && closeButton.addEventListener("click", function () {
            mobileMenu.classList.add("translate-x-full");
            document.body.classList.remove("overflow-hidden");
        });
    });
</script>

<script id="faq-interaction">
    document.addEventListener('DOMContentLoaded', function () {
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            question.addEventListener('click', function () {
                const isActive = item.classList.contains('active');
                faqItems.forEach(faqItem => faqItem.classList.remove('active'));
                if (!isActive) item.classList.add('active');
            });
        });
    });
</script>

<script id="team-interactions">
    document.addEventListener('DOMContentLoaded', function () {
        const teamMembers = document.querySelectorAll('.team-member');
        teamMembers.forEach(member => {
            member.addEventListener('mouseenter', function () {
                const overlay = this.querySelector('.member-overlay');
                overlay.style.opacity = '1';
            });
            member.addEventListener('mouseleave', function () {
                const overlay = this.querySelector('.member-overlay');
                overlay.style.opacity = '0';
            });
        });
    });
</script>

<?/* Глобальная логика AI-чата удалена — перенесена в /calculator/index.php */?>

<?if(substr_count($curPage, "price") == 0):?>
    <script id="form-interaction">
        document.addEventListener("DOMContentLoaded", function () {
            const formInputs = document.querySelectorAll("input, textarea");
            formInputs.forEach((input) => {
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
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach((checkbox) => {
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
        });
    </script>
<?elseif(substr_count($curPage, "services") == 0):?>
    <script id="form-interaction">
        document.addEventListener('DOMContentLoaded', function () {
            const formInputs = document.querySelectorAll('input, textarea');
            formInputs.forEach(input => {
                if (input.value) {
                    const label = input.nextElementSibling;
                    if (label && label.tagName === 'LABEL') {
                        label.style.transform = 'translateY(-24px)';
                        label.style.fontSize = '0.75rem';
                    }
                }
                input.addEventListener('focus', function () {
                    const label = this.nextElementSibling;
                    if (label && label.tagName === 'LABEL') {
                        label.style.transform = 'translateY(-24px)';
                        label.style.fontSize = '0.75rem';
                        label.style.color = '#0066CC';
                    }
                });
                input.addEventListener('blur', function () {
                    const label = this.nextElementSibling;
                    if (label && label.tagName === 'LABEL' && !this.value) {
                        label.style.transform = '';
                        label.style.fontSize = '';
                        label.style.color = '';
                    }
                });
            });
        });
    </script>
<?else:?>
    <script id="form-interaction">
        document.addEventListener("DOMContentLoaded", function () {
            let selectedSpecialistData = null;
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
            const modal = document.getElementById("specialistOrderModal");
            const orderButtons = document.querySelectorAll("button:not(#closeOrderModal):not(#cancelOrderModal)");
            const closeButton = document.getElementById("closeOrderModal");
            const cancelButton = document.getElementById("cancelOrderModal");
            const orderForm = document.getElementById("specialistOrderForm");

            orderButtons.forEach((button) => {
                if (button.textContent.trim() === "Заказать специалиста") {
                    button.addEventListener("click", () => {
                        const card = button.closest(".pricing-card");
                        if (card) {
                            const specialistTitle = card.querySelector("h3")?.textContent || "Специалист";
                            const rate = card.querySelector(".text-2xl")?.textContent || "—";
                            selectedSpecialistData = { title: specialistTitle, rate: rate };
                            document.getElementById("selectedSpecialist").textContent = specialistTitle;
                            document.getElementById("selectedRate").textContent = `Ставка: ${rate}`;
                        }
                        modal.classList.remove("opacity-0", "pointer-events-none");
                        modal.querySelector(".bg-white").classList.remove("scale-95");
                        document.body.classList.add("overflow-hidden");
                    });
                }
            });

            const closeModal = () => {
                modal.classList.add("opacity-0", "pointer-events-none");
                modal.querySelector(".bg-white").classList.add("scale-95");
                document.body.classList.remove("overflow-hidden");
            };
            closeButton.addEventListener("click", closeModal);
            cancelButton.addEventListener("click", closeModal);
            modal.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });

            orderForm.addEventListener("submit", (e) => {
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

            const formInputs = document.querySelectorAll("input, textarea");
            formInputs.forEach((input) => {
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

            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach((checkbox) => {
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
        });
    </script>
<?endif;?>

<script id="contact-form-validation">
    document.addEventListener("DOMContentLoaded", function () {
        const contactForm = document.getElementById("contactForm");
        if (contactForm) {
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
                    email && email.classList.add("ring-2", "ring-red-500", "border-transparent");
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
                    privacy.nextElementSibling && privacy.nextElementSibling.classList.add("ring-2", "ring-red-500");
                    valid = false;
                } else if (privacy && privacy.nextElementSibling) {
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

                    setTimeout(() => { successMessage.classList.remove("translate-x-full"); }, 100);
                    setTimeout(() => {
                        successMessage.classList.add("translate-x-full");
                        setTimeout(() => { document.body.removeChild(successMessage); }, 500);
                    }, 5000);

                    contactForm.reset();
                }
            });
        }

        const consultForm = document.querySelector(".bg-gradient-to-br form");
        if (consultForm) {
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

                setTimeout(() => { successMessage.classList.remove("translate-x-full"); }, 100);
                setTimeout(() => {
                    successMessage.classList.add("translate-x-full");
                    setTimeout(() => { document.body.removeChild(successMessage); }, 500);
                }, 5000);

                this.reset();
            });
        }
    });
</script>

<script id="chart-initialization">
    document.addEventListener("DOMContentLoaded", function () {
        const target = document.getElementById("package-comparison-chart");
        if (!target) return;

        const packageChart = echarts.init(target);
        packageChart.setOption({
            animation: false,
            tooltip: { trigger: "axis", backgroundColor: "rgba(255, 255, 255, 0.9)", borderColor: "#e2e8f0", borderWidth: 1, textStyle: { color: "#1f2937" } },
            legend: { data: ["Стартовый", "Бизнес", "Корпоративный"], bottom: 10, textStyle: { color: "#1f2937" } },
            grid: { left: "3%", right: "4%", bottom: "15%", top: "10%", containLabel: true },
            xAxis: { type: "category", data: ["Скорость запуска","Экономия","Количество специалистов","Поддержка","Гибкость"], axisLine: { lineStyle: { color: "#e2e8f0" } }, axisLabel: { color: "#1f2937" } },
            yAxis: { type: "value", axisLine: { lineStyle: { color: "#e2e8f0" } }, axisLabel: { color: "#1f2937" }, splitLine: { lineStyle: { color: "#f1f5f9" } } },
            series: [
                { name: "Стартовый", type: "bar", data: [90,15,3,60,75], itemStyle: { color: "rgba(87, 181, 231, 1)" }, barWidth: "20%", barGap: "10%", barCategoryGap: "30%", emphasis: { itemStyle: { color: "rgba(87, 181, 231, 0.8)" } } },
                { name: "Бизнес", type: "bar", data: [70,20,5,80,85], itemStyle: { color: "rgba(141, 211, 199, 1)" }, barWidth: "20%", emphasis: { itemStyle: { color: "rgba(141, 211, 199, 0.8)" } } },
                { name: "Корпоративный", type: "bar", data: [50,25,6,95,90], itemStyle: { color: "rgba(251, 191, 114, 1)" }, barWidth: "20%", emphasis: { itemStyle: { color: "rgba(251, 191, 114, 0.8)" } } },
            ],
        });

        window.addEventListener("resize", function () { packageChart.resize(); });
    });
</script>

<script id="scroll-animations">
    document.addEventListener("DOMContentLoaded", function () {
        const header = document.querySelector("header");

        window.addEventListener("scroll", function () {
            if (window.scrollY > 50) header?.classList.add("shadow-md");
            else header?.classList.remove("shadow-md");
        });

        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener("click", function (e) {
                e.preventDefault();
                const targetId = this.getAttribute("href");
                if (targetId === "#") return;
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({ top: targetElement.offsetTop - 100, behavior: "smooth" });
                }
            });
        });

        function updateCountdown() {
            const targetDate = new Date("2025-06-30T23:59:59");
            const now = new Date();
            const difference = targetDate - now;

            if (difference > 0) {
                const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));

                const timerBoxes = document.querySelectorAll(".timer-box");
                if (timerBoxes.length >= 3) {
                    timerBoxes[0].textContent = days.toString().padStart(2, "0");
                    timerBoxes[1].textContent = hours.toString().padStart(2, "0");
                    timerBoxes[2].textContent = minutes.toString().padStart(2, "0");
                }
            }
        }
        updateCountdown();
        setInterval(updateCountdown, 60000);

        const filterTabs = document.querySelectorAll('.container button');
        filterTabs.forEach(tab => {
            const tx = tab.textContent.trim();
            if (tx !== 'Заказать специалиста' && tx !== 'Получить полную оценку' && tx !== 'Скачать полный прайс-лист' && tx !== 'Получить коммерческое предложение' && tx !== 'Связаться с нами') {
                tab.addEventListener('click', function () {
                    filterTabs.forEach(t => {
                        const tt = t.textContent.trim();
                        if (tt !== 'Заказать специалиста' && tt !== 'Получить полную оценку' && tt !== 'Скачать полный прайс-лист' && tt !== 'Получить коммерческое предложение' && tt !== 'Связаться с нами') {
                            t.classList.remove('bg-primary','text-white');
                            t.classList.add('bg-white','border','border-gray-200','text-gray-700');
                        }
                    });
                    this.classList.remove('bg-white','border','border-gray-200','text-gray-700');
                    this.classList.add('bg-primary','text-white');
                });
            }
        });
    });
</script>

<script id="formScript">
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("applicationForm");
        if (!form) return;
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            const formValues = {};
            for (let [key, value] of formData.entries()) formValues[key] = value;
            alert("Заявка успешно отправлена! Наш менеджер свяжется с вами в ближайшее время.");
            console.log("Form data:", formValues);
        });
    });
</script>

<script id="backToTopScript">
    document.addEventListener("DOMContentLoaded", function () {
        const backToTopButton = document.getElementById("backToTop");
        if (!backToTopButton) return;
        window.addEventListener("scroll", function () {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove("opacity-0", "invisible");
                backToTopButton.classList.add("opacity-100", "visible");
            } else {
                backToTopButton.classList.remove("opacity-100", "visible");
                backToTopButton.classList.add("opacity-0", "invisible");
            }
        });
        backToTopButton.addEventListener("click", function () {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    });
</script>

<!-- Попап "Связаться с нами" — обновлённый -->
<div id="tacticum-modal" class="fixed inset-0 z-[999] hidden" role="dialog" aria-modal="true" aria-labelledby="tacticum-modal-title">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

    <div class="relative mx-auto my-8 w-[min(92vw,880px)]">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-secondary text-white px-6 md:px-8 py-5 flex items-center justify-between">
                <div>
                    <h2 id="tacticum-modal-title" class="text-xl md:text-2xl font-bold">Связаться с нами</h2>
                    <p class="text-white/70 text-sm mt-1">Оценим задачу и подскажем лучший вариант запуска</p>
                </div>
                <button id="tacticum-modal-close" class="shrink-0 w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center" aria-label="Закрыть диалог">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>

            <form id="tacticum-modal-form" class="px-6 md:px-8 py-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label for="modal-name" class="block text-sm text-gray-600 mb-1">Имя<span class="text-red-500">*</span></label>
                        <input id="modal-name" type="text" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary/60" />
                        <p class="mt-1 text-xs text-red-600 hidden" data-error="modal-name">Укажите имя</p>
                    </div>
                    <div>
                        <label for="modal-company" class="block text-sm text-gray-600 mb-1">Компания</label>
                        <input id="modal-company" type="text"
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary/60" />
                    </div>
                    <div>
                        <label for="modal-email" class="block text-sm text-gray-600 mb-1">Email<span class="text-red-500">*</span></label>
                        <input id="modal-email" type="email" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary/60" />
                        <p class="mt-1 text-xs text-red-600 hidden" data-error="modal-email">Укажите корректный email</p>
                    </div>
                    <div>
                        <label for="modal-phone" class="block text-sm text-gray-600 mb-1">Телефон<span class="text-red-500">*</span></label>
                        <input id="modal-phone" type="tel" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary/60" />
                        <p class="mt-1 text-xs text-red-600 hidden" data-error="modal-phone">Укажите телефон</p>
                    </div>
                </div>

                <div>
                    <label for="modal-message" class="block text-sm text-gray-600 mb-1">Опишите проект или задачу</label>
                    <textarea id="modal-message" rows="4"
                              class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary/60"></textarea>
                </div>

                <div class="flex items-start gap-3">
                    <input id="modal-agreement" type="checkbox" required
                           class="peer mt-0.5 appearance-none w-4 h-4 border border-gray-300 rounded-sm grid place-content-center focus:outline-none focus:ring-2 focus:ring-primary/40">
                    <label for="modal-agreement" class="text-sm text-gray-600">
                        Я согласен на обработку персональных данных и принимаю условия
                        <a href="/policies/" class="text-primary hover:underline">политики конфиденциальности</a>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-primary text-white font-semibold px-6 py-3 rounded-xl hover:bg-primary/90 transition disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 hidden" data-role="spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span data-role="btn-text">Отправить заявку</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="tacticum-toast" class="fixed top-4 right-4 translate-x-full transition-transform duration-300">
        <div class="bg-green-500 text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2">
            <i class="ri-check-line text-xl"></i>
            <span>Заявка отправлена! Мы скоро свяжемся с вами.</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('tacticum-modal');
        const backdrop = modal.firstElementChild;
        const closeBtn = document.getElementById('tacticum-modal-close');
        const form = document.getElementById('tacticum-modal-form');
        const toast = document.getElementById('tacticum-toast');

        const openModal = () => {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            trapFocus();
            setTimeout(() => document.getElementById('modal-name')?.focus(), 50);
        };
        document.getElementById('contactUsBtn')?.addEventListener('click', (e)=>{ e.preventDefault(); openModal(); });
        document.querySelectorAll('.tacticum-contact-btn').forEach(btn => btn.addEventListener('click', (e)=>{ e.preventDefault(); openModal(); }));

        const closeModal = () => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            form.reset();
            modal.querySelectorAll('[data-error]').forEach(el => el.classList.add('hidden'));
        };
        closeBtn.addEventListener('click', closeModal);
        backdrop.addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });

        function trapFocus() {
            const focusable = modal.querySelectorAll('a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])');
            const list = Array.from(focusable).filter(el => !el.hasAttribute('disabled'));
            if (!list.length) return;
            const first = list[0], last = list[list.length - 1];
            function loop(e){
                if (e.key !== 'Tab') return;
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
            modal.addEventListener('keydown', loop);
        }

        function showError(id, cond) {
            const input = document.getElementById(id);
            const hint = modal.querySelector(`[data-error="${id}"]`);
            if (!input) return false;
            if (!cond) {
                input.classList.add('ring-2','ring-red-500','border-transparent');
                hint?.classList.remove('hidden');
                return true;
            } else {
                input.classList.remove('ring-2','ring-red-500','border-transparent');
                hint?.classList.add('hidden');
                return false;
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const name = document.getElementById('modal-name').value.trim();
            const company = document.getElementById('modal-company').value.trim();
            const email = document.getElementById('modal-email').value.trim();
            const phone = document.getElementById('modal-phone').value.trim();
            const task = document.getElementById('modal-message').value.trim();
            const agreement = document.getElementById('modal-agreement').checked;

            let hasErr = false;
            hasErr |= showError('modal-name', !!name);
            hasErr |= showError('modal-email', !!email && email.includes('@'));
            hasErr |= showError('modal-phone', !!phone);
            if (!agreement) { alert('Подтвердите согласие на обработку персональных данных'); return; }
            if (hasErr) return;

            const btn = form.querySelector('button[type="submit"]');
            const spinner = btn.querySelector('[data-role="spinner"]');
            const btnText = btn.querySelector('[data-role="btn-text"]');
            btn.disabled = true; spinner.classList.remove('hidden'); btnText.textContent = 'Отправляем...';

            const page_url = window.location.href;

            try {
                const res = await fetch('/local/rest/tacticum_offer.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ name, company, email, phone, task, page_url })
                });
                const json = await res.json().catch(()=>({success:false}));
                if (json?.success) {
                    toast.classList.remove('translate-x-full');
                    setTimeout(()=> toast.classList.add('translate-x-full'), 4500);
                    closeModal();
                } else {
                    alert('Ошибка отправки: ' + (json?.error || 'попробуйте позже'));
                }
            } catch (err) {
                alert('Ошибка соединения: ' + (err?.message || err));
            } finally {
                btn.disabled = false; spinner.classList.add('hidden'); btnText.textContent = 'Отправить заявку';
            }
        });

        const agree = document.getElementById('modal-agreement');
        agree.addEventListener('change', () => {
            agree.style.background = agree.checked
                ? "url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22white%22><path d=%22M9 16.17L4.83 12l-1.42 1.41L9 19l12-12-1.41-1.41z%22/></svg>') center/90% no-repeat, #0066CC"
                : "";
            agree.style.borderColor = agree.checked ? '#0066CC' : '';
        });
    });
</script>

<script id="tg-link-resolver-universal">
    document.addEventListener('DOMContentLoaded', function () {
        try {
            const pageUrl = window.location.href;

            // Ищем ВСЕ телеграм-ссылки
            const tgSelectors = [
                'a[href^="https://t.me/"]',
                'a[href^="http://t.me/"]',
                'a[href^="https://telegram.me/"]',
                'a[href^="http://telegram.me/"]'
            ];
            const allTgLinks = Array.from(document.querySelectorAll(tgSelectors.join(',')));
            if (allTgLinks.length === 0) return;

            // Группируем по исходному href (целиком), без вычленения username
            const mapByHref = new Map();
            for (const a of allTgLinks) {
                const rawHref = (a.getAttribute('href') || '').trim();
                if (!rawHref) continue;
                if (!mapByHref.has(rawHref)) mapByHref.set(rawHref, []);
                mapByHref.get(rawHref).push(a);
            }
            if (mapByHref.size === 0) return;

            const ENDPOINT_URL = '/local/rest/resolve_telegram_link.php';

            // Кэш по полной исходной ссылке
            const cacheKey = (href) => `tg_link_cache::${href}`;
            const getCached = (href) => {
                try {
                    const raw = sessionStorage.getItem(cacheKey(href));
                    if (!raw) return null;
                    const obj = JSON.parse(raw);
                    return (obj && typeof obj.link === 'string') ? obj.link : null;
                } catch { return null; }
            };
            const setCached = (href, link) => {
                try { sessionStorage.setItem(cacheKey(href), JSON.stringify({ link })); } catch {}
            };

            const requestWithTimeout = (url, options = {}, ms = 8000) => {
                const controller = new AbortController();
                const id = setTimeout(() => controller.abort(), ms);
                return fetch(url, { ...options, signal: controller.signal }).finally(() => clearTimeout(id));
            };

            // Для каждого уникального href — запрос к резолверу
            mapByHref.forEach((links, originalHref) => {
                const cached = getCached(originalHref);
                if (cached) {
                    links.forEach(a => { a.href = cached; });
                    return;
                }

                // ВАЖНО: передаём ПОЛНУЮ Telegram-ссылку в поле bot_name (минимальные изменения API)
                requestWithTimeout(ENDPOINT_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        url: pageUrl,
                        bot_name: originalHref
                        // Если на бэке предпочтительно новое поле, можно параллельно отправлять:
                        // tg_url: originalHref
                    })
                })
                    .then(r => r.ok ? r.json() : Promise.reject(new Error('Bad response: ' + r.status)))
                    .then(data => {
                        const newLink = (data && typeof data.link === 'string') ? data.link.trim() : '';
                        if (!newLink) return;
                        setCached(originalHref, newLink);
                        links.forEach(a => { a.href = newLink; });
                    })
                    .catch(err => {
                        console.warn('[tg-link-resolver]', originalHref, 'failed:', err?.message || err);
                    });
            });

        } catch (e) {
            console.warn('[tg-link-resolver] init failed:', e);
        }
    });
</script>

</body>
</html>