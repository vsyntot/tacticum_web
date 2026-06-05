(() => {
    const ns = window.TacticumPriceConfigurator = window.TacticumPriceConfigurator || {};
    const requiredModules = [
        'initCatalog',
        'initFilters',
        'initOrderState',
        'initOrderRender',
        'initModal',
        'createFallbackOrderModal',
    ];
    const modulesReady = () => requiredModules.every((name) => typeof ns[name] === 'function');

    const initPriceList = (attempt = 0) => {
        if (!modulesReady()) {
            if (attempt < 400) {
                window.setTimeout(() => initPriceList(attempt + 1), 25);
            }
            return;
        }

        const legacyCard = document.getElementsByClassName(ns.constants.legacy.card)[0];
        const root = document.querySelector('[data-price-list]')
            || legacyCard?.closest('section')
            || document.getElementById('specialist-search')?.closest('section');
        const scriptVersion = 'multi-staff-v5-modular';
        if (!root || root.dataset.priceInitialized === scriptVersion) return;
        root.dataset.priceInitialized = scriptVersion;

        const classSelector = ns.classSelector;
        const legacy = ns.constants.legacy;
        const selectors = {
            card: `[data-price-card], ${classSelector(legacy.card)}`,
            tab: `[data-price-filter-tab], ${classSelector(legacy.tab)}`,
            select: `[data-price-level-select], ${classSelector(legacy.select)}`,
            levelOption: '[data-price-level-option]',
            priceValue: `[data-price-value], ${classSelector(legacy.price)}`,
            priceLevels: `[data-price-levels], ${classSelector(legacy.prices)}`,
            orderButton: `[data-price-order], ${classSelector(legacy.button)}`,
        };

        const ctx = {
            root,
            selectors,
            tabButtons: Array.from(root.querySelectorAll(selectors.tab)),
            searchInput: root.querySelector('[data-price-search], #specialist-search'),
            resultSummary: root.querySelector('[data-price-results-summary]'),
            emptyState: root.querySelector('[data-price-empty]'),
            resetButtons: Array.from(root.querySelectorAll('[data-price-reset]')),
            priceCards: Array.from(root.querySelectorAll(selectors.card)),
            presetButtons: Array.from(root.querySelectorAll('[data-price-team-preset]')),
            teamSummary: root.querySelector('[data-price-team-summary]'),
            teamSummaryText: root.querySelector('[data-price-team-summary-text]'),
            teamSummaryPreset: root.querySelector('[data-price-team-summary-preset]'),
            teamSummaryList: root.querySelector('[data-price-team-summary-list]'),
            teamSummaryRate: root.querySelector('[data-price-team-summary-rate]'),
            teamSummaryBudget: root.querySelector('[data-price-team-summary-budget]'),
            teamSummaryOpen: root.querySelector('[data-price-team-summary-open]'),
            teamSummaryClear: root.querySelector('[data-price-team-summary-clear]'),
        };

        ns.initCatalog(ctx);
        ns.initFilters(ctx);
        ns.initOrderState(ctx);
        ns.initOrderRender(ctx);
        ns.initModal(ctx);

        ctx.setActiveTeamPreset('');
        ctx.syncEndDateVisibility();
        ctx.renderOrderItems();
        ctx.filterCards();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initPriceList(), { once: true });
    } else {
        initPriceList();
    }
})();
