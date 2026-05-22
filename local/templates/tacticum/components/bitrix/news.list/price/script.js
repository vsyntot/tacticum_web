document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('[data-price-list]');
    if (!root) return;

    const tabButtons = Array.from(root.querySelectorAll('[data-price-filter-tab]'));
    const searchInput = root.querySelector('[data-price-search]');
    const sections = Array.from(root.querySelectorAll('[data-price-section]'));
    const priceCards = Array.from(root.querySelectorAll('[data-price-card]'));
    const activeTabClasses = ['bg-primary', 'text-white'];
    const inactiveTabClasses = ['bg-white', 'border', 'border-gray-200', 'text-gray-700'];

    const parsePriceNumber = (value) => {
        const normalized = String(value || '')
            .replace(/\s/g, '')
            .replace(',', '.')
            .replace(/[^\d.]/g, '');
        const price = parseFloat(normalized);
        return Number.isFinite(price) ? price : 0;
    };

    const formatPrice = (value) => {
        const price = parsePriceNumber(value);
        return price.toLocaleString('ru-RU', { maximumFractionDigits: 0 });
    };

    const readPriceMap = (card) => {
        const priceMapJson = card.querySelector('[data-price-levels]');
        if (!priceMapJson) return {};

        try {
            return JSON.parse(priceMapJson.textContent) || {};
        } catch (error) {
            return {};
        }
    };

    const renderPrice = (priceBlock, priceValue) => {
        if (!priceBlock) return;

        const suffix = document.createElement('span');
        suffix.className = 'text-sm text-gray-500 font-normal';
        suffix.textContent = '/час';
        priceBlock.replaceChildren(document.createTextNode(`от ${formatPrice(priceValue)} ₽`), suffix);
    };

    const updateCardPrice = (card, level, priceValue) => {
        const priceBlock = card.querySelector('[data-price-value]');
        const normalizedRate = parsePriceNumber(priceValue);

        card.dataset.level = level || '';
        card.dataset.price = normalizedRate > 0 ? String(normalizedRate) : String(priceValue || '');
        renderPrice(priceBlock, card.dataset.price);
    };

    const getActiveCategory = () => {
        return root.querySelector('[data-price-filter-tab][data-active="true"]')?.dataset.category || 'all';
    };

    const setActiveTab = (activeButton) => {
        tabButtons.forEach((button) => {
            const isActive = button === activeButton;
            button.dataset.active = isActive ? 'true' : 'false';
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            activeTabClasses.forEach((className) => button.classList.toggle(className, isActive));
            inactiveTabClasses.forEach((className) => button.classList.toggle(className, !isActive));
        });
    };

    const filterCards = () => {
        const activeCategory = getActiveCategory();
        const searchTerm = (searchInput?.value || '').trim().toLowerCase();

        sections.forEach((section) => {
            const cards = Array.from(section.querySelectorAll('[data-price-card]'));
            let visibleCount = 0;

            cards.forEach((card) => {
                const category = card.dataset.category || '';
                const name = (card.dataset.name || '').toLowerCase();
                const matchesCategory = activeCategory === 'all' || category === activeCategory;
                const matchesName = searchTerm === '' || name.includes(searchTerm);
                const isVisible = matchesCategory && matchesName;

                card.classList.toggle('hidden', !isVisible);
                if (isVisible) visibleCount += 1;
            });

            section.classList.toggle('hidden', visibleCount === 0);
        });
    };

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            setActiveTab(button);
            filterCards();
        });
    });
    searchInput?.addEventListener('input', filterCards);

    priceCards.forEach((card) => {
        const select = card.querySelector('[data-price-level-select]');
        const prices = readPriceMap(card);
        if (!select) {
            renderPrice(card.querySelector('[data-price-value]'), card.dataset.price || '');
            return;
        }

        const initialLevel = prices.Middle ? 'Middle' : select.value;
        const initialPrice = prices[initialLevel] || card.dataset.price || '';
        select.value = initialLevel;
        updateCardPrice(card, initialLevel, initialPrice);

        select.addEventListener('change', () => {
            const level = select.value;
            updateCardPrice(card, level, prices[level] || '');
        });
    });

    const modal = document.querySelector('[data-price-order-modal]');
    const orderForm = modal?.querySelector('[data-price-order-form]');
    if (!modal || !orderForm) {
        filterCards();
        return;
    }

    const modalCard = modal.querySelector('[data-price-order-modal-card]');
    const closeButton = modal.querySelector('[data-price-modal-close]');
    const cancelButton = modal.querySelector('[data-price-modal-cancel]');
    const selectedSpecialist = modal.querySelector('[data-price-selected-specialist]');
    const selectedRate = modal.querySelector('[data-price-selected-rate]');
    const hiddenSpecialist = orderForm.querySelector('[data-price-order-specialist]');
    const hiddenLevel = orderForm.querySelector('[data-price-order-level]');
    const hiddenRate = orderForm.querySelector('[data-price-order-rate]');

    const getCardSelection = (card) => {
        const select = card.querySelector('[data-price-level-select]');
        const prices = readPriceMap(card);
        const level = select?.value || card.dataset.level || '';
        const price = level ? prices[level] || card.dataset.price || '' : card.dataset.price || '';

        return { level, price };
    };

    const closeModal = () => {
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalCard?.classList.add('scale-95');
        document.body.classList.remove('overflow-hidden');
    };

    const openModal = (card) => {
        const specialist = card.dataset.name || '';
        const { level, price } = getCardSelection(card);
        const normalizedRate = parsePriceNumber(price);
        const rateValue = normalizedRate > 0 ? String(normalizedRate) : String(price || '');

        if (selectedSpecialist) {
            selectedSpecialist.textContent = specialist + (level ? ` (${level})` : '');
        }
        if (selectedRate) {
            selectedRate.textContent = rateValue ? `Ставка: от ${formatPrice(rateValue)} ₽/час` : 'Ставка: —';
        }

        modal.dataset.specialist = specialist;
        modal.dataset.level = level;
        modal.dataset.rate = rateValue;
        if (hiddenSpecialist) hiddenSpecialist.value = specialist;
        if (hiddenLevel) hiddenLevel.value = level;
        if (hiddenRate) hiddenRate.value = rateValue;

        modal.classList.remove('opacity-0', 'pointer-events-none');
        modalCard?.classList.remove('scale-95');
        document.body.classList.add('overflow-hidden');
    };

    document.body.addEventListener('click', function (event) {
        const button = event.target.closest('[data-price-order]');
        if (!button || !root.contains(button)) return;

        const card = button.closest('[data-price-card]');
        if (card) openModal(card);
    });

    closeButton?.addEventListener('click', closeModal);
    cancelButton?.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    orderForm.addEventListener('submit', () => {
        if (hiddenSpecialist && modal.dataset.specialist) hiddenSpecialist.value = modal.dataset.specialist;
        if (hiddenLevel && modal.dataset.level) hiddenLevel.value = modal.dataset.level;
        if (hiddenRate && modal.dataset.rate) hiddenRate.value = modal.dataset.rate;
    });

    const updateLabelState = (input) => {
        const label = input.parentElement.querySelector('.input-label');
        if (!label) return;
        label.classList.toggle('is-floating', Boolean(input.value) || input === document.activeElement);
    };

    orderForm.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], textarea').forEach((input) => {
        input.addEventListener('focus', function () { updateLabelState(this); });
        input.addEventListener('blur', function () { updateLabelState(this); });
        input.addEventListener('input', function () { updateLabelState(this); });
        updateLabelState(input);
    });

    filterCards();
});
