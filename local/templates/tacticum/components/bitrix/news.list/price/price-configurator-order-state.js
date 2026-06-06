(() => {
    const ns = window.TacticumPriceConfigurator = window.TacticumPriceConfigurator || {};

    ns.initOrderState = (ctx) => {
        ctx.orderItems = [];
        ctx.activeTeamPreset = '';

        ctx.getOrderTotalQuantity = () => ctx.orderItems.reduce((total, item) => total + item.quantity, 0);
        ctx.getOrderHourlyTotal = () => ctx.orderItems.reduce((total, item) => {
            const rate = ns.parsePriceNumber(item.rate);
            return rate > 0 ? total + (rate * item.quantity) : total;
        }, 0);
        ctx.getMonthlyBudgetEstimate = () => {
            const hours = ns.constants.workloadMonthlyHours[ctx.workloadSelect?.value || ''] || 0;
            const hourlyTotal = ctx.getOrderHourlyTotal();
            return hours > 0 && hourlyTotal > 0 ? hourlyTotal * hours : 0;
        };
        ctx.getMonthlyBudgetLabel = () => {
            const monthlyBudget = ctx.getMonthlyBudgetEstimate();
            if (monthlyBudget > 0) return `от ${ns.formatPrice(monthlyBudget)} ₽/мес`;
            if (ctx.getOrderHourlyTotal() > 0) return 'выберите Part-time или Full-time';
            return 'ставки уточняются';
        };
        ctx.buildWorkerPayload = () => ctx.orderItems.map((item) => ({
            role: item.specialist,
            level: item.level,
            cost_per_hour: item.rate,
            amount_of_workers: item.quantity,
        }));

        ctx.setActiveTeamPreset = (presetKey) => {
            ctx.activeTeamPreset = presetKey || '';
            ctx.presetButtons.forEach((button) => {
                const isActive = button.dataset.priceTeamPreset === ctx.activeTeamPreset;
                button.dataset.active = isActive ? 'true' : 'false';
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                button.classList.toggle('border-primary', isActive);
                button.classList.toggle('bg-primary/5', isActive);
            });
        };
        ctx.getActiveTeamPreset = () => (
            ctx.activeTeamPreset
                ? (ctx.teamPresets?.[ctx.activeTeamPreset] || ns.constants.teamPresets[ctx.activeTeamPreset] || null)
                : null
        );
        ctx.applyPresetSelectValue = (select, value) => {
            const normalized = String(value || '').trim();
            if (!select || !normalized || !Array.from(select.options).some((option) => option.value === normalized)) return;
            select.value = normalized;
        };

        ctx.updateHiddenFields = () => {
            const workers = ctx.buildWorkerPayload();
            const totalQuantity = ctx.getOrderTotalQuantity();
            const first = ctx.orderItems[0] || null;
            const monthlyBudget = ctx.getMonthlyBudgetEstimate();
            if (ctx.hiddenWorkers) ctx.hiddenWorkers.value = JSON.stringify(workers);
            if (ctx.hiddenAmount) ctx.hiddenAmount.value = String(totalQuantity || 0);
            if (ctx.hiddenTeamPreset) ctx.hiddenTeamPreset.value = ctx.activeTeamPreset;
            if (ctx.hiddenMonthlyBudget) ctx.hiddenMonthlyBudget.value = monthlyBudget > 0 ? String(monthlyBudget) : '';
            if (ctx.hiddenSpecialist) {
                ctx.hiddenSpecialist.value = ctx.orderItems.map((item) => `${item.specialist}${item.level ? ` (${item.level})` : ''} x${item.quantity}`).join('; ');
            }
            if (ctx.hiddenLevel) ctx.hiddenLevel.value = ctx.orderItems.length === 1 ? first?.level || '' : '';
            if (ctx.hiddenRate) ctx.hiddenRate.value = ctx.orderItems.length === 1 ? first?.rate || '' : String(ctx.getOrderHourlyTotal() || '');
        };

        ctx.addOrderItemFromCard = (card, quantity = 1, shouldRender = true, source = 'manual') => {
            const specialist = card.dataset.name || '';
            const { level, price } = ctx.getCardSelection(card);
            const normalizedRate = ns.parsePriceNumber(price);
            const rateValue = normalizedRate > 0 ? String(normalizedRate) : String(price || '');
            const key = [specialist, level, rateValue].join('|');
            const existing = ctx.orderItems.find((item) => item.key === key);
            const amount = Math.max(1, Math.min(parseInt(quantity, 10) || 1, 99));
            if (source === 'manual') ctx.setActiveTeamPreset('');
            if (existing) {
                existing.quantity = Math.min(existing.quantity + amount, 99);
            } else {
                ctx.orderItems.push({ key, specialist, level, rate: rateValue, quantity: amount });
            }
            if (shouldRender) ctx.renderOrderItems();
        };

        ctx.applyTeamPreset = (presetKey) => {
            const preset = ctx.teamPresets?.[presetKey] || ns.constants.teamPresets[presetKey];
            if (!preset) return;
            const usedCards = new Set();
            const additions = [];
            preset.roles.forEach((role) => {
                const card = ctx.findPresetCard(role, usedCards);
                if (!card) return;
                usedCards.add(card);
                ctx.setPreferredLevel(card, role.preferredLevels || ['Middle', 'Senior', 'Junior', 'Lead']);
                additions.push({ card, quantity: role.quantity || 1 });
            });
            if (additions.length === 0) return;
            ctx.orderItems = [];
            ctx.setActiveTeamPreset(presetKey);
            ctx.applyPresetSelectValue(ctx.workloadSelect, preset.defaultWorkload);
            ctx.applyPresetSelectValue(ctx.durationSelect, preset.recommendedDuration);
            ctx.syncEndDateVisibility?.();
            additions.forEach(({ card, quantity }) => ctx.addOrderItemFromCard(card, quantity, false, 'preset'));
            ctx.renderOrderItems();
            ctx.teamSummary?.scrollIntoView({ block: 'nearest' });
        };

        ctx.presetButtons.forEach((button) => {
            if (button.tagName === 'BUTTON' && !button.getAttribute('type')) button.setAttribute('type', 'button');
            button.setAttribute('aria-pressed', 'false');
            button.addEventListener('click', () => ctx.applyTeamPreset(button.dataset.priceTeamPreset || ''));
        });
    };
})();
