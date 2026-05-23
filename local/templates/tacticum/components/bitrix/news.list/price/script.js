(() => {
    const legacy = {
        card: 'pricing-card',
        tab: 'filter-tab',
        select: 'level-select',
        price: 'price-value',
        prices: 'level-prices-json',
        button: 'order-specialist-btn',
    };

    const classSelector = (className) => `.${className}`;

    const initPriceList = () => {
        const legacyCard = document.getElementsByClassName(legacy.card)[0];
        const root = document.querySelector('[data-price-list]')
            || legacyCard?.closest('section')
            || document.getElementById('specialist-search')?.closest('section');
        const scriptVersion = 'multi-staff-v2';
        if (!root || root.dataset.priceInitialized === scriptVersion) return;
        root.dataset.priceInitialized = scriptVersion;

        const cardSelector = `[data-price-card], ${classSelector(legacy.card)}`;
        const tabSelector = `[data-price-filter-tab], ${classSelector(legacy.tab)}`;
        const selectSelector = `[data-price-level-select], ${classSelector(legacy.select)}`;
        const levelOptionSelector = '[data-price-level-option]';
        const priceValueSelector = `[data-price-value], ${classSelector(legacy.price)}`;
        const priceLevelsSelector = `[data-price-levels], ${classSelector(legacy.prices)}`;
        const orderButtonSelector = `[data-price-order], ${classSelector(legacy.button)}`;
        const activeTabClasses = ['bg-primary', 'text-white', 'border-primary', 'hover:bg-primary/90'];
        const inactiveTabClasses = ['bg-white', 'border', 'border-gray-200', 'text-gray-700', 'hover:bg-gray-50'];

        const tabButtons = Array.from(root.querySelectorAll(tabSelector));
        const searchInput = root.querySelector('[data-price-search], #specialist-search');
        const resultSummary = root.querySelector('[data-price-results-summary]');
        const emptyState = root.querySelector('[data-price-empty]');
        const resetButtons = Array.from(root.querySelectorAll('[data-price-reset]'));
        const priceCards = Array.from(root.querySelectorAll(cardSelector));
        let orderItems = [];

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

        const pluralizeSpecialist = (count) => {
            const abs = Math.abs(count) % 100;
            const last = abs % 10;
            if (abs > 10 && abs < 20) return 'специалистов';
            if (last > 1 && last < 5) return 'специалиста';
            if (last === 1) return 'специалист';
            return 'специалистов';
        };

        const hasOwn = (object, key) => Object.prototype.hasOwnProperty.call(object, key);

        const readPriceMap = (card) => {
            const priceMapJson = card.querySelector(priceLevelsSelector);
            if (priceMapJson) {
                try {
                    const parsed = JSON.parse(priceMapJson.textContent);
                    if (parsed && typeof parsed === 'object') {
                        return parsed;
                    }
                } catch (error) {
                    // Legacy cards can still provide prices on select options.
                }
            }

            const levelOptions = Array.from(card.querySelectorAll(levelOptionSelector));
            if (levelOptions.length > 0) {
                return levelOptions.reduce((prices, option) => {
                    const level = option.dataset.level || '';
                    if (level) {
                        prices[level] = option.dataset.price || '';
                    }
                    return prices;
                }, {});
            }

            const select = card.querySelector(selectSelector);
            return Array.from(select?.options || []).reduce((prices, option) => {
                if (option.value) {
                    prices[option.value] = option.dataset.price || option.value;
                }
                return prices;
            }, {});
        };

        const getPriceForLevel = (prices, level, select, fallback = '') => {
            if (level && hasOwn(prices, level)) {
                return prices[level];
            }
            const option = select?.selectedOptions?.[0];
            return option?.dataset.price || fallback;
        };

        const syncLevelOptions = (card, activeLevel) => {
            Array.from(card.querySelectorAll(levelOptionSelector)).forEach((option) => {
                const isActive = option.dataset.level === activeLevel;
                option.dataset.active = isActive ? 'true' : 'false';
                option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        };

        const renderPrice = (priceBlock, priceValue) => {
            if (!priceBlock) return;

            const suffix = document.createElement('span');
            suffix.className = 'text-sm text-gray-500 font-normal';
            suffix.textContent = '/час';
            priceBlock.replaceChildren(document.createTextNode(`от ${formatPrice(priceValue)} ₽`), suffix);
        };

        const updateCardPrice = (card, level, priceValue) => {
            const priceBlock = card.querySelector(priceValueSelector);
            const normalizedRate = parsePriceNumber(priceValue);

            card.dataset.level = level || '';
            card.dataset.price = normalizedRate > 0 ? String(normalizedRate) : String(priceValue || '');
            syncLevelOptions(card, card.dataset.level);
            renderPrice(priceBlock, card.dataset.price);
        };

        const inferSectionTitle = (card) => {
            const dataSection = card.closest('[data-price-section]');
            if (dataSection?.dataset.category) {
                return dataSection.dataset.category;
            }

            let node = card.closest('.grid')?.previousElementSibling || card.previousElementSibling;
            while (node) {
                if (node.classList?.contains('section-title')) {
                    return (node.textContent || '').trim();
                }
                node = node.previousElementSibling;
            }

            return '';
        };

        const normalizeCards = () => {
            priceCards.forEach((card) => {
                if (!card.dataset.name) {
                    card.dataset.name = (card.querySelector('h3')?.textContent || '').trim();
                }
                if (!card.dataset.category) {
                    card.dataset.category = inferSectionTitle(card);
                }
                if (!card.dataset.price) {
                    const select = card.querySelector(selectSelector);
                    const activeLevelOption = card.querySelector(`${levelOptionSelector}[data-active="true"]`);
                    card.dataset.price = activeLevelOption?.dataset.price
                        || select?.selectedOptions?.[0]?.dataset.price
                        || card.querySelector(priceValueSelector)?.textContent
                        || '';
                }
            });
        };

        const collectSectionBlocks = () => {
            const dataSections = Array.from(root.querySelectorAll('[data-price-section]'));
            if (dataSections.length > 0) {
                return dataSections.map((section) => ({
                    element: section,
                    title: null,
                    cards: Array.from(section.querySelectorAll(cardSelector)),
                }));
            }

            const grids = Array.from(root.querySelectorAll('.section-title + .grid'));
            if (grids.length > 0) {
                return grids.map((grid) => ({
                    element: grid,
                    title: grid.previousElementSibling?.classList.contains('section-title') ? grid.previousElementSibling : null,
                    cards: Array.from(grid.querySelectorAll(cardSelector)),
                }));
            }

            return [{
                element: root,
                title: null,
                cards: priceCards,
            }];
        };

        normalizeCards();
        const sections = collectSectionBlocks();

        const getActiveCategory = () => (
            tabButtons.find((button) => button.dataset.active === 'true')
            || tabButtons.find((button) => button.classList.contains('bg-primary'))
            || tabButtons[0]
        )?.dataset.category || 'all';

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
            let totalVisible = 0;

            sections.forEach((section) => {
                let visibleCount = 0;

                section.cards.forEach((card) => {
                    const category = card.dataset.category || '';
                    const name = (card.dataset.name || '').toLowerCase();
                    const matchesCategory = activeCategory === 'all' || category === activeCategory;
                    const matchesName = searchTerm === '' || name.includes(searchTerm);
                    const isVisible = matchesCategory && matchesName;

                    card.classList.toggle('hidden', !isVisible);
                    if (isVisible) visibleCount += 1;
                });

                const hasVisibleCards = visibleCount > 0;
                totalVisible += visibleCount;
                if (section.element !== root) {
                    section.element.classList.toggle('hidden', !hasVisibleCards);
                }
                section.title?.classList.toggle('hidden', !hasVisibleCards);
            });

            const hasFilters = activeCategory !== 'all' || searchTerm !== '';
            if (resultSummary) {
                resultSummary.textContent = hasFilters
                    ? `Найдено ${totalVisible} ${pluralizeSpecialist(totalVisible)}`
                    : `В каталоге ${totalVisible} ${pluralizeSpecialist(totalVisible)}`;
            }
            emptyState?.classList.toggle('hidden', totalVisible > 0);
            resetButtons.forEach((button) => {
                button.classList.toggle('hidden', !hasFilters);
            });
        };

        tabButtons.forEach((button, index) => {
            if (button.tagName === 'BUTTON' && !button.getAttribute('type')) {
                button.setAttribute('type', 'button');
            }
            if (button.dataset.active !== 'true' && (index === 0 || button.classList.contains('bg-primary'))) {
                button.dataset.active = button.classList.contains('bg-primary') || index === 0 ? 'true' : 'false';
            }
            setActiveTab(tabButtons.find((item) => item.dataset.active === 'true') || tabButtons[0]);
            button.addEventListener('click', () => {
                setActiveTab(button);
                filterCards();
            });
        });
        searchInput?.addEventListener('input', filterCards);
        resetButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                setActiveTab(tabButtons[0]);
                filterCards();
                searchInput?.focus();
            });
        });

        priceCards.forEach((card) => {
            const select = card.querySelector(selectSelector);
            const levelOptions = Array.from(card.querySelectorAll(levelOptionSelector));
            const prices = readPriceMap(card);

            if (levelOptions.length > 0) {
                const activeOption = levelOptions.find((option) => option.dataset.active === 'true');
                const middleOption = levelOptions.find((option) => option.dataset.level === 'Middle');
                const initialOption = middleOption || activeOption || levelOptions[0];
                const initialLevel = initialOption?.dataset.level || card.dataset.level || '';
                const initialPrice = getPriceForLevel(prices, initialLevel, null, initialOption?.dataset.price || card.dataset.price || '');

                updateCardPrice(card, initialLevel, initialPrice);
                levelOptions.forEach((option) => {
                    option.addEventListener('click', () => {
                        const level = option.dataset.level || '';
                        updateCardPrice(card, level, getPriceForLevel(prices, level, null, option.dataset.price || ''));
                    });
                });
                return;
            }

            if (!select) {
                renderPrice(card.querySelector(priceValueSelector), card.dataset.price || '');
                return;
            }

            const initialLevel = hasOwn(prices, 'Middle') ? 'Middle' : select.value;
            const initialPrice = getPriceForLevel(prices, initialLevel, select, card.dataset.price || '');
            select.value = initialLevel;
            updateCardPrice(card, initialLevel, initialPrice);

            select.addEventListener('change', () => {
                const level = select.value;
                updateCardPrice(card, level, getPriceForLevel(prices, level, select, ''));
            });
        });

        const createFallbackOrderModal = () => {
            const modal = document.createElement('div');
            modal.id = 'specialistOrderModal';
            modal.setAttribute('data-price-order-modal', '');
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300 px-4 py-6';
            modal.innerHTML = `
                <div class="bg-white rounded-xl max-w-4xl w-full mx-auto transform scale-95 transition-transform duration-300 max-h-[92vh] overflow-hidden flex flex-col" data-price-order-modal-card>
                    <div class="flex justify-between items-start gap-4 px-6 py-5 border-b border-gray-100 bg-white">
                        <div>
                            <h3 class="text-2xl font-bold text-secondary">Заказать специалистов</h3>
                            <p class="text-sm text-gray-500 mt-1">Состав заявки можно изменить перед отправкой.</p>
                        </div>
                        <button id="closeOrderModal" type="button" data-price-modal-close class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100" aria-label="Закрыть">
                            <i class="ri-close-line text-xl text-gray-500"></i>
                        </button>
                    </div>
                    <form id="specialistOrderForm" class="flex flex-col min-h-0" data-price-order-form data-tacticum-form data-form-id="price-specialist" data-endpoint="/local/rest/tacticum_sale_staff.php" data-tacticum-close-target="#specialistOrderModal" data-tacticum-close-mode="overlay">
                        <div class="overflow-y-auto px-6 py-6 space-y-6">
                            <section class="p-4 bg-primary/5 rounded-lg border border-primary/10" data-price-order-summary>
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div>
                                        <p class="text-primary font-medium">Состав заявки</p>
                                        <p class="text-sm text-gray-500 mt-1" data-price-order-count>1 специалист</p>
                                    </div>
                                    <button type="button" data-price-order-add-more class="inline-flex items-center justify-center px-4 py-2 rounded-button border border-primary text-primary text-sm font-medium hover:bg-primary hover:text-white transition-colors whitespace-nowrap">Добавить ещё</button>
                                </div>
                                <div class="space-y-3 mt-4" data-price-order-list></div>
                                <div class="mt-4 pt-4 border-t border-primary/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <p id="selectedRate" class="text-sm font-medium text-secondary" data-price-selected-rate>Суммарная ставка: —</p>
                                    <button type="button" data-price-order-clear class="text-sm text-gray-500 hover:text-red-600 transition-colors">Очистить состав</button>
                                </div>
                                <p id="selectedSpecialist" class="sr-only" data-price-selected-specialist>Не выбран</p>
                            </section>
                            <section class="space-y-4">
                                <h4 class="text-lg font-semibold text-secondary">Контакты</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Имя</span><input type="text" id="orderName" name="name" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                                    <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Email</span><input type="email" id="orderEmail" name="email" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                                    <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Телефон</span><input type="tel" id="orderPhone" name="phone" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                                    <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Компания</span><input type="text" id="orderCompany" name="company" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                                </div>
                            </section>
                            <section class="space-y-4">
                                <h4 class="text-lg font-semibold text-secondary">Параметры работы</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Дата старта</span><input type="date" id="orderStartDate" name="startDate" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                                    <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Срок работы</span><select id="orderDuration" name="duration" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 appearance-none bg-white"><option value="flexible">Срок обсуждается</option><option value="2-weeks">Короткий спринт: до 2 недель</option><option value="1-month">1 месяц</option><option value="2-3-months">2–3 месяца</option><option value="3-6-months">3–6 месяцев</option><option value="6-plus-months">Дольше 6 месяцев</option><option value="exact-date">До конкретной даты</option></select></label>
                                    <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Загрузка</span><select id="orderWorkload" name="workload" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 appearance-none bg-white"><option value="flexible">Обсуждается</option><option value="part-time">Part-time</option><option value="full-time">Full-time</option></select></label>
                                </div>
                                <label class="hidden" data-price-end-date-wrap><span class="block text-sm font-medium text-gray-600 mb-2">Дата окончания работ</span><input type="date" id="orderEndDate" name="endDate" data-price-end-date class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                            </section>
                            <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Описание задачи</span><textarea id="orderDescription" name="message" required rows="4" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></textarea></label>
                            <input type="hidden" id="orderSpecialist" name="specialist" data-price-order-specialist>
                            <input type="hidden" id="orderLevel" name="level" data-price-order-level>
                            <input type="hidden" id="orderRate" name="rate" data-price-order-rate>
                            <input type="hidden" id="orderAmount" name="amount_of_workers" data-price-order-amount>
                            <input type="hidden" id="orderWorkersJson" name="workers_json" data-price-order-workers>
                            <div class="flex items-start gap-3">
                                <input type="checkbox" id="orderAgreement" data-tacticum-consent required class="appearance-none mt-1 w-5 h-5 border border-gray-300 rounded bg-white checked:bg-primary checked:border-0 relative">
                                <label for="orderAgreement" class="text-sm text-gray-600 leading-5 pt-0.5">Я согласен на обработку персональных данных и принимаю условия <a href="/policies/" target="_blank" rel="noopener" class="text-primary hover:underline">политики конфиденциальности</a></label>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 px-6 py-5 border-t border-gray-100 bg-white">
                            <button type="submit" class="w-full sm:flex-1 bg-primary text-white px-6 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">Отправить заявку</button>
                            <button type="button" id="cancelOrderModal" data-price-modal-cancel class="w-full sm:flex-1 bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-button hover:bg-gray-50 transition-colors whitespace-nowrap">Отмена</button>
                        </div>
                    </form>
                </div>`;
            document.body.appendChild(modal);
            return modal;
        };

        let modal = document.querySelector('[data-price-order-modal], #specialistOrderModal');
        if (!modal || !modal.querySelector('[data-price-order-list]')) {
            modal?.remove();
            modal = createFallbackOrderModal();
        }
        const orderForm = modal.querySelector('[data-price-order-form], #specialistOrderForm');
        const modalCard = modal.querySelector('[data-price-order-modal-card], .bg-white');
        const closeButton = modal.querySelector('[data-price-modal-close], #closeOrderModal');
        const cancelButton = modal.querySelector('[data-price-modal-cancel], #cancelOrderModal');
        const addMoreButton = modal.querySelector('[data-price-order-add-more]');
        const clearButton = modal.querySelector('[data-price-order-clear]');
        const orderList = modal.querySelector('[data-price-order-list]');
        const orderCount = modal.querySelector('[data-price-order-count]');
        const selectedSpecialist = modal.querySelector('[data-price-selected-specialist], #selectedSpecialist');
        const selectedRate = modal.querySelector('[data-price-selected-rate], #selectedRate');
        const hiddenSpecialist = orderForm?.querySelector('[data-price-order-specialist], #orderSpecialist');
        const hiddenLevel = orderForm?.querySelector('[data-price-order-level], #orderLevel');
        const hiddenRate = orderForm?.querySelector('[data-price-order-rate], #orderRate');
        const hiddenAmount = orderForm?.querySelector('[data-price-order-amount], #orderAmount');
        const hiddenWorkers = orderForm?.querySelector('[data-price-order-workers], #orderWorkersJson');
        const submitButton = orderForm?.querySelector('button[type="submit"]');
        const durationSelect = orderForm?.querySelector('#orderDuration');
        const endDateWrap = orderForm?.querySelector('[data-price-end-date-wrap]');
        const endDateInput = orderForm?.querySelector('[data-price-end-date], #orderEndDate');

        const getCardSelection = (card) => {
            const select = card.querySelector(selectSelector);
            const activeLevelOption = card.querySelector(`${levelOptionSelector}[data-active="true"]`);
            const prices = readPriceMap(card);
            const level = activeLevelOption?.dataset.level || select?.value || card.dataset.level || '';
            const price = activeLevelOption?.dataset.price || getPriceForLevel(prices, level, select, card.dataset.price || '');

            return { level, price };
        };

        const getOrderTotalQuantity = () => orderItems.reduce((total, item) => total + item.quantity, 0);

        const getOrderHourlyTotal = () => orderItems.reduce((total, item) => {
            const rate = parsePriceNumber(item.rate);
            return rate > 0 ? total + (rate * item.quantity) : total;
        }, 0);

        const buildWorkerPayload = () => orderItems.map((item) => ({
            role: item.specialist,
            level: item.level,
            cost_per_hour: item.rate,
            amount_of_workers: item.quantity,
        }));

        const updateHiddenFields = () => {
            const workers = buildWorkerPayload();
            const totalQuantity = getOrderTotalQuantity();
            const first = orderItems[0] || null;

            if (hiddenWorkers) hiddenWorkers.value = JSON.stringify(workers);
            if (hiddenAmount) hiddenAmount.value = String(totalQuantity || 0);
            if (hiddenSpecialist) {
                hiddenSpecialist.value = orderItems.map((item) => `${item.specialist}${item.level ? ` (${item.level})` : ''} x${item.quantity}`).join('; ');
            }
            if (hiddenLevel) hiddenLevel.value = orderItems.length === 1 ? first?.level || '' : '';
            if (hiddenRate) hiddenRate.value = orderItems.length === 1 ? first?.rate || '' : String(getOrderHourlyTotal() || '');
        };

        const renderOrderItems = () => {
            if (!orderList) return;

            orderList.replaceChildren();
            orderItems.forEach((item) => {
                const row = document.createElement('div');
                row.className = 'rounded-lg bg-white border border-gray-200 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3';
                row.dataset.priceOrderItem = item.key;

                const info = document.createElement('div');
                info.className = 'min-w-0';
                const title = document.createElement('p');
                title.className = 'font-medium text-secondary';
                title.textContent = item.specialist + (item.level ? ` (${item.level})` : '');
                const meta = document.createElement('p');
                meta.className = 'text-sm text-gray-500 mt-1';
                meta.textContent = parsePriceNumber(item.rate) > 0 ? `от ${formatPrice(item.rate)} ₽/час` : 'Ставка уточняется';
                info.append(title, meta);

                const controls = document.createElement('div');
                controls.className = 'flex items-center gap-3 shrink-0';
                controls.innerHTML = `
                    <button type="button" data-price-worker-action="decrement" data-key="${item.key}" class="w-9 h-9 rounded-full border border-gray-200 text-gray-700 hover:border-primary hover:text-primary transition-colors" aria-label="Уменьшить количество">−</button>
                    <span class="min-w-8 text-center font-medium text-secondary">${item.quantity}</span>
                    <button type="button" data-price-worker-action="increment" data-key="${item.key}" class="w-9 h-9 rounded-full border border-gray-200 text-gray-700 hover:border-primary hover:text-primary transition-colors" aria-label="Увеличить количество">+</button>
                    <button type="button" data-price-worker-action="remove" data-key="${item.key}" class="text-sm text-gray-500 hover:text-red-600 transition-colors">Удалить</button>
                `;

                row.append(info, controls);
                orderList.append(row);
            });

            const totalQuantity = getOrderTotalQuantity();
            if (orderCount) {
                orderCount.textContent = totalQuantity > 0
                    ? `${totalQuantity} ${pluralizeSpecialist(totalQuantity)}`
                    : 'Состав не выбран';
            }
            if (selectedSpecialist) {
                selectedSpecialist.textContent = orderItems.length > 0
                    ? orderItems.map((item) => item.specialist).join(', ')
                    : 'Не выбран';
            }
            if (selectedRate) {
                const totalRate = getOrderHourlyTotal();
                selectedRate.textContent = totalRate > 0
                    ? `Суммарная ставка: от ${formatPrice(totalRate)} ₽/час`
                    : 'Суммарная ставка: —';
            }
            if (submitButton) {
                submitButton.disabled = orderItems.length === 0;
            }
            updateHiddenFields();
        };

        const addOrderItemFromCard = (card) => {
            const specialist = card.dataset.name || '';
            const { level, price } = getCardSelection(card);
            const normalizedRate = parsePriceNumber(price);
            const rateValue = normalizedRate > 0 ? String(normalizedRate) : String(price || '');
            const key = [specialist, level, rateValue].join('|');
            const existing = orderItems.find((item) => item.key === key);

            if (existing) {
                existing.quantity = Math.min(existing.quantity + 1, 99);
            } else {
                orderItems.push({
                    key,
                    specialist,
                    level,
                    rate: rateValue,
                    quantity: 1,
                });
            }
            renderOrderItems();
        };

        const closeModal = () => {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalCard?.classList.add('scale-95');
            document.body.classList.remove('overflow-hidden');
        };

        const syncEndDateVisibility = () => {
            const exactDate = durationSelect?.value === 'exact-date';
            endDateWrap?.classList.toggle('hidden', !exactDate);
            if (endDateInput) {
                endDateInput.required = exactDate;
                if (!exactDate) {
                    endDateInput.value = '';
                    endDateInput.setCustomValidity('');
                }
            }
        };

        const validateDuration = () => {
            if (!durationSelect || durationSelect.value !== 'exact-date' || !endDateInput) {
                return true;
            }
            const valid = endDateInput.value !== '';
            endDateInput.setCustomValidity(valid ? '' : 'Укажите дату окончания работ.');
            if (!valid) {
                endDateWrap?.classList.remove('hidden');
                endDateInput.reportValidity();
            }
            return valid;
        };

        const openModal = (card) => {
            addOrderItemFromCard(card);
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalCard?.classList.remove('scale-95');
            document.body.classList.add('overflow-hidden');
        };

        document.body.addEventListener('click', function (event) {
            const button = event.target.closest(orderButtonSelector);
            if (!button || !root.contains(button)) return;

            const card = button.closest(cardSelector);
            if (card) openModal(card);
        });

        orderList?.addEventListener('click', (event) => {
            const control = event.target.closest('[data-price-worker-action]');
            if (!control) return;

            const key = control.dataset.key || '';
            const item = orderItems.find((entry) => entry.key === key);
            if (!item) return;

            if (control.dataset.priceWorkerAction === 'increment') {
                item.quantity = Math.min(item.quantity + 1, 99);
            } else if (control.dataset.priceWorkerAction === 'decrement') {
                item.quantity -= 1;
                if (item.quantity <= 0) {
                    orderItems = orderItems.filter((entry) => entry.key !== key);
                }
            } else if (control.dataset.priceWorkerAction === 'remove') {
                orderItems = orderItems.filter((entry) => entry.key !== key);
            }
            renderOrderItems();
        });

        addMoreButton?.addEventListener('click', () => {
            closeModal();
            root.scrollIntoView({ block: 'start' });
        });
        clearButton?.addEventListener('click', () => {
            orderItems = [];
            renderOrderItems();
        });
        closeButton?.addEventListener('click', closeModal);
        cancelButton?.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });

        durationSelect?.addEventListener('change', syncEndDateVisibility);
        endDateInput?.addEventListener('input', () => {
            endDateInput.setCustomValidity('');
        });

        orderForm?.addEventListener('submit', (event) => {
            if (!validateDuration()) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }
            updateHiddenFields();
        });
        orderForm?.addEventListener('reset', () => {
            setTimeout(() => {
                orderItems = [];
                syncEndDateVisibility();
                renderOrderItems();
            }, 0);
        });

        syncEndDateVisibility();
        renderOrderItems();
        filterCards();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPriceList, { once: true });
    } else {
        initPriceList();
    }
})();
