document.addEventListener('DOMContentLoaded', function () {
    // --- 1. ФИЛЬТРЫ ---
    const tabButtons = document.querySelectorAll('.filter-tab');
    const searchInput = document.querySelector('#specialist-search');

    function filterCards() {
        const activeTab = document.querySelector('.filter-tab.bg-primary');
        const activeCategory = activeTab ? activeTab.dataset.category : '';
        const searchTerm = searchInput.value.toLowerCase();

        document.querySelectorAll('h3.section-title + .grid').forEach(grid => {
            const cards = Array.from(grid.children);
            let visibleCards = cards.filter(card => {
                const category = card.dataset.category;
                const name = card.dataset.name.toLowerCase();

                const matchesCategory = activeCategory === 'all' || category === activeCategory;
                const matchesName = name.includes(searchTerm);

                const isVisible = matchesCategory && matchesName;
                card.style.display = isVisible ? '' : 'none';
                return isVisible;
            });

            // Показываем или скрываем раздел
            const heading = grid.previousElementSibling;
            if (visibleCards.length > 0) {
                grid.style.display = 'grid';
                heading.style.display = '';
            } else {
                grid.style.display = 'none';
                heading.style.display = 'none';
            }
        });
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('bg-primary', 'text-white'));
            tabButtons.forEach(b => b.classList.add('bg-white', 'text-gray-700'));
            btn.classList.add('bg-primary', 'text-white');
            btn.classList.remove('bg-white', 'text-gray-700');
            filterCards();
        });
    });

    searchInput.addEventListener('input', filterCards);

    // --- 2. ЛОГИКА СМЕНЫ УРОВНЯ В КАРТОЧКЕ ---
    document.querySelectorAll('.pricing-card').forEach(function(card) {
        const select = card.querySelector('.level-select');
        const priceBlock = card.querySelector('.price-value');
        const priceMapJson = card.querySelector('.level-prices-json');
        if (!select || !priceBlock || !priceMapJson) return;
        let prices = {};
        try { prices = JSON.parse(priceMapJson.textContent); } catch(e) {}

        // По умолчанию выставляем Middle, если есть
        if (select && prices['Middle']) {
            select.value = 'Middle';
            let price = prices['Middle'];
            let priceNum = parseFloat(('' + price).replace(',', '.').replace(/\s/g, '')) || 0;
            priceBlock.innerHTML = 'от ' + priceNum.toLocaleString('ru-RU') + ' ₽<span class="text-sm text-gray-500 font-normal">/час</span>';
            card.dataset.price = priceNum;
        } else if (select) {
            // select.value уже selected, но dataset.price надо инициализировать
            let price = prices[select.value];
            let priceNum = parseFloat(('' + price).replace(',', '.').replace(/\s/g, '')) || 0;
            card.dataset.price = priceNum;
        }

        select.addEventListener('change', function() {
            const level = select.value;
            let price = prices[level];
            let priceNum = parseFloat(('' + price).replace(',', '.').replace(/\s/g, '')) || 0;
            priceBlock.innerHTML = 'от ' + priceNum.toLocaleString('ru-RU') + ' ₽<span class="text-sm text-gray-500 font-normal">/час</span>';
            card.dataset.price = priceNum;
        });
    });

    // --- 3. МОДАЛКА: СОЗДАНИЕ И ЛОГИКА ОТКРЫТИЯ/ЗАКРЫТИЯ ---
    if (!document.getElementById('specialistOrderModal')) {
        const modalTemplate = document.createElement("div");
        modalTemplate.id = "specialistOrderModal";
        modalTemplate.className =
            "fixed inset-0 bg-black/50 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300";
        modalTemplate.innerHTML = `
        <div class="bg-white rounded-xl p-8 max-w-xl w-full mx-4 transform scale-95 transition-transform duration-300 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6 sticky top-0 bg-white z-10">
                <h3 class="text-2xl font-bold text-secondary">Заказать специалиста</h3>
                <button id="closeOrderModal" type="button" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100">
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
                        <input type="text" id="orderName" name="name" required placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 peer">
                        <label for="orderName" class="input-label">Имя</label>
                    </div>
                    <div class="relative">
                        <input type="email" id="orderEmail" name="email" required placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 peer">
                        <label for="orderEmail" class="input-label">Email</label>
                    </div>
                    <div class="relative">
                        <input type="tel" id="orderPhone" name="phone" required placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 peer">
                        <label for="orderPhone" class="input-label">Телефон</label>
                    </div>
                    <div class="relative">
                        <input type="text" id="orderCompany" name="company" required placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 peer">
                        <label for="orderCompany" class="input-label">Компания</label>
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
<!--                                <option value="custom">Другой срок</option>-->
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i class="ri-arrow-down-s-line text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <textarea id="orderDescription" name="description" required rows="4" placeholder=" " class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 peer"></textarea>
                    <label for="orderDescription" class="input-label">Описание задачи</label>
                </div>
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="orderAgreement" required class="mt-1 w-5 h-5 border border-gray-300 rounded bg-white checked:bg-primary checked:border-0 relative">
                    <label for="orderAgreement" class="text-sm text-gray-600 leading-5 pt-0.5">Я согласен на обработку персональных данных и принимаю условия <a href="#" class="text-primary hover:underline">политики конфиденциальности</a></label>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 sticky bottom-0 bg-white pt-4">
                    <button type="submit" class="w-full sm:flex-1 bg-primary text-white px-6 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">Отправить заявку</button>
                    <button type="button" id="cancelOrderModal" class="w-full sm:flex-1 bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-button hover:bg-gray-50 transition-colors whitespace-nowrap">Отмена</button>
                </div>
            </form>
        </div>
        `;
        document.body.appendChild(modalTemplate);
    }

    // --- 3.2. Логика открытия модалки по кнопке ---
    const modal = document.getElementById("specialistOrderModal");
    const closeButton = document.getElementById("closeOrderModal");
    const cancelButton = document.getElementById("cancelOrderModal");
    const orderForm = document.getElementById("specialistOrderForm");

    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.order-specialist-btn');
        if (!btn) return;

        const card = btn.closest('.pricing-card');
        const specialist = card.dataset.name;
        // Находим выбранный уровень и ставку
        const levelSelect = card.querySelector('.level-select');
        let selectedLevel = levelSelect ? levelSelect.value : '';
        let price = null;
        if (levelSelect) {
            const priceMapJson = card.querySelector('.level-prices-json');
            let prices = {};
            try { prices = JSON.parse(priceMapJson.textContent); } catch(e) {}
            price = prices[selectedLevel] || '';
        } else {
            price = card.querySelector('.price-value')?.innerText || '';
        }

        document.getElementById("selectedSpecialist").textContent = specialist + (selectedLevel ? ` (${selectedLevel})` : '');
        document.getElementById("selectedRate").textContent = price ? `Ставка: от ${price} ₽/час` : "Ставка: —";

        // Для отправки на сервер сохраняем текущий специалист, уровень и ставку (в dataset модалки)
        modal.dataset.specialist = specialist;
        modal.dataset.level = selectedLevel;
        modal.dataset.rate = price;

        modal.classList.remove("opacity-0", "pointer-events-none");
        modal.querySelector(".bg-white").classList.remove("scale-95");
        document.body.classList.add("overflow-hidden");
    });

    // --- 3.3. Закрытие модалки ---
    function closeModal() {
        modal.classList.add("opacity-0", "pointer-events-none");
        modal.querySelector(".bg-white").classList.add("scale-95");
        document.body.classList.remove("overflow-hidden");
    }
    closeButton.addEventListener("click", closeModal);
    cancelButton.addEventListener("click", closeModal);
    modal.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });

    // --- 3.4. Сабмит формы (отправка на endpoint!) ---
    orderForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const formData = new FormData(orderForm);
        const data = Object.fromEntries(formData);

        // Добавляем выбранного специалиста, уровень, ставку
        data.specialist = modal.dataset.specialist || '';
        data.level = modal.dataset.level || '';
        data.rate = modal.dataset.rate || '';

        fetch('/local/rest/tacticum_sale_staff.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        })
            .then(async res => {
                if (!res.ok) {
                    let msg = "Ошибка при отправке заявки";
                    try {
                        const json = await res.json();
                        msg = json.error || msg;
                    } catch(_) {}
                    throw new Error(msg);
                }
                return res.json();
            })
            .then(() => {
                // Сообщение об успехе
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
            })
            .catch(err => {
                alert(err.message || "Ошибка отправки формы");
            });
    });

    // --- 3.5. Анимация лейблов ---
    function updateLabelState(input) {
        const label = input.parentElement.querySelector('.input-label');
        if (!label) return;
        if (input.value || input === document.activeElement) {
            label.style.transform = "translateY(-21px) scale(0.90)";
            label.style.fontSize = "0.75rem";
            label.style.color = "#0066CC";
            label.style.left = "10px";
            label.style.top = "4px";
            label.style.background = "#fff";
            label.style.padding = "0 4px";
            label.style.zIndex = "10";
        } else {
            label.style.transform = "";
            label.style.fontSize = "";
            label.style.color = "";
            label.style.left = "";
            label.style.top = "";
            label.style.background = "";
            label.style.padding = "";
            label.style.zIndex = "";
        }
    }
    document.querySelectorAll('#specialistOrderForm input[type="text"], #specialistOrderForm input[type="email"], #specialistOrderForm input[type="tel"], #specialistOrderForm textarea').forEach(input => {
        input.addEventListener("focus", function () { updateLabelState(this); });
        input.addEventListener("blur", function () { updateLabelState(this); });
        input.addEventListener("input", function () { updateLabelState(this); });
        updateLabelState(input);
    });

    // --- 3.6. Checkbox стилизация ---
    const checkboxes = document.querySelectorAll('#specialistOrderForm input[type="checkbox"]');
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

    // --- Инициализация ---
    filterCards();
});
