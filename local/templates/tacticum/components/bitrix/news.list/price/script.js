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
                card.classList.toggle('hidden', !isVisible);
                return isVisible;
            });

            // Показываем или скрываем раздел
            const heading = grid.previousElementSibling;
            const sectionVisible = visibleCards.length > 0;
            grid.classList.toggle('hidden', !sectionVisible);
            heading.classList.toggle('hidden', !sectionVisible);
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

    // --- 3. МОДАЛКА: ЛОГИКА ОТКРЫТИЯ/ЗАКРЫТИЯ ---
    const modal = document.getElementById("specialistOrderModal");
    const closeButton = document.getElementById("closeOrderModal");
    const cancelButton = document.getElementById("cancelOrderModal");
    const orderForm = document.getElementById("specialistOrderForm");
    if (!modal || !orderForm) return;

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

        document.getElementById("selectedSpecialist").textContent = specialist + (selectedLevel ? ` (${selectedLevel})` : "");
        document.getElementById("selectedRate").textContent = price ? `Ставка: от ${price} ₽/час` : "Ставка: —";

        // Для отправки на сервер сохраняем текущий специалист, уровень и ставку (в dataset модалки)
        modal.dataset.specialist = specialist;
        modal.dataset.level = selectedLevel;
        modal.dataset.rate = price;
        const hiddenSpecialist = modal.querySelector("#orderSpecialist");
        const hiddenLevel = modal.querySelector("#orderLevel");
        const hiddenRate = modal.querySelector("#orderRate");
        if (hiddenSpecialist) hiddenSpecialist.value = specialist;
        if (hiddenLevel) hiddenLevel.value = selectedLevel;
        if (hiddenRate) hiddenRate.value = price;

        modal.classList.remove("opacity-0", "pointer-events-none");
        modal.querySelector(".bg-white").classList.remove("scale-95");
        document.body.classList.add("overflow-hidden");
    });

    // --- 3.2. Закрытие модалки ---
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

    // --- 3.3. Сабмит формы ---
    orderForm?.addEventListener("submit", () => {
        const hiddenSpecialist = orderForm.querySelector("#orderSpecialist");
        const hiddenLevel = orderForm.querySelector("#orderLevel");
        const hiddenRate = orderForm.querySelector("#orderRate");
        if (hiddenSpecialist && modal.dataset.specialist) hiddenSpecialist.value = modal.dataset.specialist;
        if (hiddenLevel && modal.dataset.level) hiddenLevel.value = modal.dataset.level;
        if (hiddenRate && modal.dataset.rate) hiddenRate.value = modal.dataset.rate;
    });

    // --- 3.4. Анимация лейблов ---
    function updateLabelState(input) {
        const label = input.parentElement.querySelector('.input-label');
        if (!label) return;
        if (input.value || input === document.activeElement) {
            label.classList.add("is-floating");
        } else {
            label.classList.remove("is-floating");
        }
    }
    document.querySelectorAll('#specialistOrderForm input[type="text"], #specialistOrderForm input[type="email"], #specialistOrderForm input[type="tel"], #specialistOrderForm textarea').forEach(input => {
        input.addEventListener("focus", function () { updateLabelState(this); });
        input.addEventListener("blur", function () { updateLabelState(this); });
        input.addEventListener("input", function () { updateLabelState(this); });
        updateLabelState(input);
    });

    // --- Инициализация ---
    filterCards();
});
