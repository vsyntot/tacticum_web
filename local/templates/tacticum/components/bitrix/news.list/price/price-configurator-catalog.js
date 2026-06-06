(() => {
    const ns = window.TacticumPriceConfigurator = window.TacticumPriceConfigurator || {};

    ns.initCatalog = (ctx) => {
        const selectors = ctx.selectors;
        ctx.readPriceMap = (card) => {
            const priceMapJson = card.querySelector(selectors.priceLevels);
            if (priceMapJson) {
                try {
                    const parsed = JSON.parse(priceMapJson.textContent);
                    if (parsed && typeof parsed === 'object') return parsed;
                } catch (error) {
                    // Legacy cards can still provide prices on controls.
                }
            }

            const levelOptions = Array.from(card.querySelectorAll(selectors.levelOption));
            if (levelOptions.length > 0) {
                return levelOptions.reduce((prices, option) => {
                    const level = option.dataset.level || '';
                    if (level) prices[level] = option.dataset.price || '';
                    return prices;
                }, {});
            }

            const select = card.querySelector(selectors.select);
            return Array.from(select?.options || []).reduce((prices, option) => {
                if (option.value) prices[option.value] = option.dataset.price || option.value;
                return prices;
            }, {});
        };

        ctx.getPriceForLevel = (prices, level, select, fallback = '') => {
            if (level && ns.hasOwn(prices, level)) return prices[level];
            return select?.selectedOptions?.[0]?.dataset.price || fallback;
        };

        ctx.syncLevelOptions = (card, activeLevel) => {
            Array.from(card.querySelectorAll(selectors.levelOption)).forEach((option) => {
                const isActive = option.dataset.level === activeLevel;
                option.dataset.active = isActive ? 'true' : 'false';
                option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        };

        ctx.renderPrice = (priceBlock, priceValue) => {
            if (!priceBlock) return;
            const suffix = document.createElement('span');
            suffix.className = 'text-sm text-gray-500 font-normal';
            suffix.textContent = '/час';
            priceBlock.replaceChildren(document.createTextNode(`от ${ns.formatPrice(priceValue)} ₽`), suffix);
        };

        ctx.updateCardPrice = (card, level, priceValue) => {
            const normalizedRate = ns.parsePriceNumber(priceValue);
            card.dataset.level = level || '';
            card.dataset.price = normalizedRate > 0 ? String(normalizedRate) : String(priceValue || '');
            ctx.syncLevelOptions(card, card.dataset.level);
            ctx.renderPrice(card.querySelector(selectors.priceValue), card.dataset.price);
        };

        ctx.setPreferredLevel = (card, preferredLevels = []) => {
            const select = card.querySelector(selectors.select);
            const prices = ctx.readPriceMap(card);
            const preferredLevel = preferredLevels.find((level) => ns.hasOwn(prices, level));
            if (!preferredLevel) return;
            if (select && Array.from(select.options).some((option) => option.value === preferredLevel)) {
                select.value = preferredLevel;
            }
            const option = Array.from(card.querySelectorAll(selectors.levelOption))
                .find((item) => item.dataset.level === preferredLevel);
            ctx.updateCardPrice(card, preferredLevel, ctx.getPriceForLevel(prices, preferredLevel, select, option?.dataset.price || ''));
        };

        ctx.findPresetCard = (role, usedCards) => {
            const roleRateIds = (role.rateIds || [])
                .map((value) => String(value || '').trim())
                .filter(Boolean);
            const matchedByRateId = roleRateIds.length > 0
                ? ctx.priceCards.find((card) => {
                    if (usedCards.has(card)) return false;
                    const cardRateIds = String(card.dataset.rateIds || '')
                        .split(',')
                        .map((value) => value.trim())
                        .filter(Boolean);
                    return roleRateIds.some((rateId) => cardRateIds.includes(rateId));
                })
                : null;
            if (matchedByRateId) return matchedByRateId;

            return ctx.priceCards.find((card) => (
                !usedCards.has(card)
                && (role.keywords || []).some((keyword) => ns.normalizeText(`${card.dataset.name || ''} ${card.dataset.category || ''}`).includes(ns.normalizeText(keyword)))
            ));
        };

        const inferSectionTitle = (card) => {
            const dataSection = card.closest('[data-price-section]');
            if (dataSection?.dataset.category) return dataSection.dataset.category;
            let node = card.closest('.grid')?.previousElementSibling || card.previousElementSibling;
            while (node) {
                if (node.classList?.contains('section-title')) return (node.textContent || '').trim();
                node = node.previousElementSibling;
            }
            return '';
        };

        ctx.priceCards.forEach((card) => {
            if (!card.dataset.name) card.dataset.name = (card.querySelector('h3')?.textContent || '').trim();
            if (!card.dataset.category) card.dataset.category = inferSectionTitle(card);
            if (!card.dataset.price) {
                const select = card.querySelector(selectors.select);
                const active = card.querySelector(`${selectors.levelOption}[data-active="true"]`);
                card.dataset.price = active?.dataset.price || select?.selectedOptions?.[0]?.dataset.price || card.querySelector(selectors.priceValue)?.textContent || '';
            }
        });

        const dataSections = Array.from(ctx.root.querySelectorAll('[data-price-section]'));
        ctx.sections = dataSections.length > 0 ? dataSections.map((section) => ({
            element: section,
            title: null,
            cards: Array.from(section.querySelectorAll(selectors.card)),
        })) : Array.from(ctx.root.querySelectorAll('.section-title + .grid')).map((grid) => ({
            element: grid,
            title: grid.previousElementSibling?.classList.contains('section-title') ? grid.previousElementSibling : null,
            cards: Array.from(grid.querySelectorAll(selectors.card)),
        }));
        if (ctx.sections.length === 0) {
            ctx.sections = [{ element: ctx.root, title: null, cards: ctx.priceCards }];
        }

        ctx.getCardSelection = (card) => {
            const select = card.querySelector(selectors.select);
            const active = card.querySelector(`${selectors.levelOption}[data-active="true"]`);
            const prices = ctx.readPriceMap(card);
            const level = active?.dataset.level || select?.value || card.dataset.level || '';
            const price = active?.dataset.price || ctx.getPriceForLevel(prices, level, select, card.dataset.price || '');
            return { level, price };
        };

        ctx.priceCards.forEach((card) => {
            const select = card.querySelector(selectors.select);
            const levelOptions = Array.from(card.querySelectorAll(selectors.levelOption));
            const prices = ctx.readPriceMap(card);
            if (levelOptions.length > 0) {
                const initial = levelOptions.find((option) => option.dataset.level === 'Middle')
                    || levelOptions.find((option) => option.dataset.active === 'true')
                    || levelOptions[0];
                ctx.updateCardPrice(card, initial?.dataset.level || card.dataset.level || '', ctx.getPriceForLevel(prices, initial?.dataset.level || '', null, initial?.dataset.price || card.dataset.price || ''));
                levelOptions.forEach((option) => option.addEventListener('click', () => {
                    ctx.updateCardPrice(card, option.dataset.level || '', ctx.getPriceForLevel(prices, option.dataset.level || '', null, option.dataset.price || ''));
                }));
                return;
            }
            if (!select) {
                ctx.renderPrice(card.querySelector(selectors.priceValue), card.dataset.price || '');
                return;
            }
            const initialLevel = ns.hasOwn(prices, 'Middle') ? 'Middle' : select.value;
            select.value = initialLevel;
            ctx.updateCardPrice(card, initialLevel, ctx.getPriceForLevel(prices, initialLevel, select, card.dataset.price || ''));
            select.addEventListener('change', () => ctx.updateCardPrice(card, select.value, ctx.getPriceForLevel(prices, select.value, select, '')));
        });
    };
})();
