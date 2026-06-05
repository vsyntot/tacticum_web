(() => {
    const ns = window.TacticumPriceConfigurator = window.TacticumPriceConfigurator || {};

    ns.initFilters = (ctx) => {
        const getActiveCategory = () => (
            ctx.tabButtons.find((button) => button.dataset.active === 'true')
            || ctx.tabButtons.find((button) => button.classList.contains('bg-primary'))
            || ctx.tabButtons[0]
        )?.dataset.category || 'all';

        ctx.setActiveTab = (activeButton) => {
            ctx.tabButtons.forEach((button) => {
                const isActive = button === activeButton;
                button.dataset.active = isActive ? 'true' : 'false';
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                ns.constants.activeTabClasses.forEach((className) => button.classList.toggle(className, isActive));
                ns.constants.inactiveTabClasses.forEach((className) => button.classList.toggle(className, !isActive));
            });
        };

        ctx.filterCards = () => {
            const activeCategory = getActiveCategory();
            const searchTerm = (ctx.searchInput?.value || '').trim().toLowerCase();
            let totalVisible = 0;

            ctx.sections.forEach((section) => {
                let visibleCount = 0;
                section.cards.forEach((card) => {
                    const matchesCategory = activeCategory === 'all' || (card.dataset.category || '') === activeCategory;
                    const matchesName = searchTerm === '' || (card.dataset.name || '').toLowerCase().includes(searchTerm);
                    const isVisible = matchesCategory && matchesName;
                    card.classList.toggle('hidden', !isVisible);
                    if (isVisible) visibleCount += 1;
                });
                totalVisible += visibleCount;
                if (section.element !== ctx.root) section.element.classList.toggle('hidden', visibleCount === 0);
                section.title?.classList.toggle('hidden', visibleCount === 0);
            });

            const hasFilters = activeCategory !== 'all' || searchTerm !== '';
            if (ctx.resultSummary) {
                ctx.resultSummary.textContent = hasFilters
                    ? `Найдено ${totalVisible} ${ns.pluralizeSpecialist(totalVisible)}`
                    : `В каталоге ${totalVisible} ${ns.pluralizeSpecialist(totalVisible)}`;
            }
            ctx.emptyState?.classList.toggle('hidden', totalVisible > 0);
            ctx.resetButtons.forEach((button) => button.classList.toggle('hidden', !hasFilters));
        };

        ctx.tabButtons.forEach((button, index) => {
            if (button.tagName === 'BUTTON' && !button.getAttribute('type')) button.setAttribute('type', 'button');
            if (button.dataset.active !== 'true' && (index === 0 || button.classList.contains('bg-primary'))) {
                button.dataset.active = button.classList.contains('bg-primary') || index === 0 ? 'true' : 'false';
            }
            button.addEventListener('click', () => {
                ctx.setActiveTab(button);
                ctx.filterCards();
            });
        });
        ctx.setActiveTab(ctx.tabButtons.find((item) => item.dataset.active === 'true') || ctx.tabButtons[0]);
        ctx.searchInput?.addEventListener('input', ctx.filterCards);
        ctx.resetButtons.forEach((button) => button.addEventListener('click', () => {
            if (ctx.searchInput) ctx.searchInput.value = '';
            ctx.setActiveTab(ctx.tabButtons[0]);
            ctx.filterCards();
            ctx.searchInput?.focus();
        }));
    };
})();
