(() => {
    const ns = window.TacticumPriceConfigurator = window.TacticumPriceConfigurator || {};

    ns.initOrderRender = (ctx) => {
        ctx.renderTeamSummary = () => {
            const totalQuantity = ctx.getOrderTotalQuantity();
            const totalRate = ctx.getOrderHourlyTotal();
            const hasItems = totalQuantity > 0;
            ctx.teamSummary?.classList.toggle('hidden', !hasItems);
            if (!hasItems) {
                if (ctx.teamSummaryText) ctx.teamSummaryText.textContent = 'Состав не выбран';
                if (ctx.teamSummaryPreset) ctx.teamSummaryPreset.textContent = '';
                ctx.teamSummaryList?.replaceChildren();
                if (ctx.teamSummaryRate) ctx.teamSummaryRate.textContent = '—';
                if (ctx.teamSummaryBudget) ctx.teamSummaryBudget.textContent = 'Зависит от загрузки';
                if (ctx.monthlyBudgetText) ctx.monthlyBudgetText.textContent = 'Оценка месячного бюджета появится после выбора загрузки.';
                return;
            }

            const preset = ctx.getActiveTeamPreset();
            const budgetLabel = ctx.getMonthlyBudgetLabel();
            if (ctx.teamSummaryText) ctx.teamSummaryText.textContent = `${totalQuantity} ${ns.pluralizeSpecialist(totalQuantity)} в составе`;
            if (ctx.teamSummaryPreset) ctx.teamSummaryPreset.textContent = preset ? `Основа: ${preset.label}` : 'Собрано вручную';
            if (ctx.teamSummaryList) {
                const visibleItems = ctx.orderItems.slice(0, 4);
                const hiddenCount = ctx.orderItems.length - visibleItems.length;
                ctx.teamSummaryList.replaceChildren();
                visibleItems.forEach((item) => {
                    const name = ns.splitSpecialistName(item.specialist);
                    const itemElement = document.createElement('div');
                    itemElement.className = 'tacticum-team-summary-item';
                    itemElement.title = name.full;
                    itemElement.setAttribute('role', 'listitem');
                    itemElement.innerHTML = `<span class="tacticum-team-summary-title"></span><span class="tacticum-team-summary-meta"></span>`;
                    itemElement.children[0].textContent = name.primary;
                    itemElement.children[1].textContent = [item.level, `x${item.quantity}`].filter(Boolean).join(' · ');
                    if (name.extrasCount > 0) {
                        const extra = document.createElement('span');
                        extra.className = 'tacticum-team-summary-extra';
                        extra.textContent = `стек: +${name.extrasCount} ${ns.pluralizeTechnology(name.extrasCount)}`;
                        itemElement.append(extra);
                    }
                    ctx.teamSummaryList.append(itemElement);
                });
                if (hiddenCount > 0) {
                    const hidden = document.createElement('div');
                    hidden.className = 'tacticum-team-summary-item tacticum-team-summary-item--more';
                    hidden.setAttribute('role', 'listitem');
                    hidden.innerHTML = '<span class="tacticum-team-summary-title"></span><span class="tacticum-team-summary-meta">в форме заявки</span>';
                    hidden.children[0].textContent = `ещё ${hiddenCount} ${ns.pluralizeRole(hiddenCount)}`;
                    ctx.teamSummaryList.append(hidden);
                }
            }
            if (ctx.teamSummaryRate) ctx.teamSummaryRate.textContent = totalRate > 0 ? `от ${ns.formatPrice(totalRate)} ₽/час` : 'Ставка уточняется';
            if (ctx.teamSummaryBudget) ctx.teamSummaryBudget.textContent = budgetLabel;
            if (ctx.monthlyBudgetText) ctx.monthlyBudgetText.textContent = `Ориентировочный бюджет: ${budgetLabel}.`;
        };

        ctx.renderOrderItems = () => {
            if (!ctx.orderList) {
                ctx.renderTeamSummary();
                ctx.updateHiddenFields();
                return;
            }

            ctx.orderList.replaceChildren();
            ctx.orderItems.forEach((item) => {
                const row = document.createElement('div');
                row.className = 'rounded-lg bg-white border border-gray-200 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3';
                row.dataset.priceOrderItem = item.key;
                const meta = ns.parsePriceNumber(item.rate) > 0 ? `от ${ns.formatPrice(item.rate)} ₽/час` : 'Ставка уточняется';
                row.innerHTML = `
                    <div class="min-w-0"><p class="font-medium text-secondary"></p><p class="text-sm text-gray-500 mt-1"></p></div>
                    <div class="flex items-center gap-3 shrink-0">
                        <button type="button" data-price-worker-action="decrement" data-key="${item.key}" class="w-9 h-9 rounded-full border border-gray-200 text-gray-700 hover:border-primary hover:text-primary transition-colors" aria-label="Уменьшить количество">−</button>
                        <span class="min-w-8 text-center font-medium text-secondary">${item.quantity}</span>
                        <button type="button" data-price-worker-action="increment" data-key="${item.key}" class="w-9 h-9 rounded-full border border-gray-200 text-gray-700 hover:border-primary hover:text-primary transition-colors" aria-label="Увеличить количество">+</button>
                        <button type="button" data-price-worker-action="remove" data-key="${item.key}" class="text-sm text-gray-500 hover:text-red-600 transition-colors">Удалить</button>
                    </div>`;
                row.querySelector('p').textContent = item.specialist + (item.level ? ` (${item.level})` : '');
                row.querySelectorAll('p')[1].textContent = meta;
                ctx.orderList.append(row);
            });

            const totalQuantity = ctx.getOrderTotalQuantity();
            if (ctx.orderCount) ctx.orderCount.textContent = totalQuantity > 0 ? `${totalQuantity} ${ns.pluralizeSpecialist(totalQuantity)}` : 'Состав не выбран';
            if (ctx.selectedSpecialist) ctx.selectedSpecialist.textContent = ctx.orderItems.length > 0 ? ctx.orderItems.map((item) => item.specialist).join(', ') : 'Не выбран';
            if (ctx.selectedRate) {
                const totalRate = ctx.getOrderHourlyTotal();
                ctx.selectedRate.textContent = totalRate > 0 ? `Суммарная ставка: от ${ns.formatPrice(totalRate)} ₽/час` : 'Суммарная ставка: —';
            }
            if (ctx.submitButton) ctx.submitButton.disabled = ctx.orderItems.length === 0;
            ctx.renderTeamSummary();
            ctx.updateHiddenFields();
        };
    };
})();
