(() => {
    const ns = window.TacticumPriceConfigurator = window.TacticumPriceConfigurator || {};

    ns.initModal = (ctx) => {
        ctx.modal = document.querySelector('[data-price-order-modal], #specialistOrderModal');
        if (!ctx.modal || !ctx.modal.querySelector('[data-price-order-list]')) {
            ctx.modal?.remove();
            ctx.modal = ns.createFallbackOrderModal();
        }

        const form = ctx.modal.querySelector('[data-price-order-form], #specialistOrderForm');
        Object.assign(ctx, {
            orderForm: form,
            modalCard: ctx.modal.querySelector('[data-price-order-modal-card], .bg-white'),
            closeButton: ctx.modal.querySelector('[data-price-modal-close], #closeOrderModal'),
            cancelButton: ctx.modal.querySelector('[data-price-modal-cancel], #cancelOrderModal'),
            addMoreButton: ctx.modal.querySelector('[data-price-order-add-more]'),
            clearButton: ctx.modal.querySelector('[data-price-order-clear]'),
            orderList: ctx.modal.querySelector('[data-price-order-list]'),
            orderCount: ctx.modal.querySelector('[data-price-order-count]'),
            selectedSpecialist: ctx.modal.querySelector('[data-price-selected-specialist], #selectedSpecialist'),
            selectedRate: ctx.modal.querySelector('[data-price-selected-rate], #selectedRate'),
            hiddenSpecialist: form?.querySelector('[data-price-order-specialist], #orderSpecialist'),
            hiddenLevel: form?.querySelector('[data-price-order-level], #orderLevel'),
            hiddenRate: form?.querySelector('[data-price-order-rate], #orderRate'),
            hiddenAmount: form?.querySelector('[data-price-order-amount], #orderAmount'),
            hiddenWorkers: form?.querySelector('[data-price-order-workers], #orderWorkersJson'),
            hiddenTeamPreset: form?.querySelector('[data-price-order-team-preset], #orderTeamPreset'),
            hiddenMonthlyBudget: form?.querySelector('[data-price-order-monthly-budget], #orderMonthlyBudget'),
            monthlyBudgetText: form?.querySelector('[data-price-monthly-budget]'),
            submitButton: form?.querySelector('button[type="submit"]'),
            durationSelect: form?.querySelector('#orderDuration'),
            workloadSelect: form?.querySelector('#orderWorkload'),
            endDateWrap: form?.querySelector('[data-price-end-date-wrap]'),
            endDateInput: form?.querySelector('[data-price-end-date], #orderEndDate'),
        });

        ctx.showModal = () => {
            if (ctx.orderItems.length === 0) return;
            ctx.modal.classList.remove('opacity-0', 'pointer-events-none');
            ctx.modalCard?.classList.remove('scale-95');
            document.body.classList.add('overflow-hidden');
        };
        ctx.closeModal = () => {
            ctx.modal.classList.add('opacity-0', 'pointer-events-none');
            ctx.modalCard?.classList.add('scale-95');
            document.body.classList.remove('overflow-hidden');
        };
        ctx.syncEndDateVisibility = () => {
            const exactDate = ctx.durationSelect?.value === 'exact-date';
            ctx.endDateWrap?.classList.toggle('hidden', !exactDate);
            if (!ctx.endDateInput) return;
            ctx.endDateInput.required = exactDate;
            if (!exactDate) {
                ctx.endDateInput.value = '';
                ctx.endDateInput.setCustomValidity('');
            }
        };
        ctx.validateDuration = () => {
            if (!ctx.durationSelect || ctx.durationSelect.value !== 'exact-date' || !ctx.endDateInput) return true;
            const valid = ctx.endDateInput.value !== '';
            ctx.endDateInput.setCustomValidity(valid ? '' : 'Укажите дату окончания работ.');
            if (!valid) {
                ctx.endDateWrap?.classList.remove('hidden');
                ctx.endDateInput.reportValidity();
            }
            return valid;
        };
        ctx.openModal = (card) => {
            ctx.addOrderItemFromCard(card);
            ctx.showModal();
        };

        document.body.addEventListener('click', (event) => {
            const button = event.target.closest(ctx.selectors.orderButton);
            if (!button || !ctx.root.contains(button)) return;
            const card = button.closest(ctx.selectors.card);
            if (card) ctx.openModal(card);
        });
        ctx.orderList?.addEventListener('click', (event) => {
            const control = event.target.closest('[data-price-worker-action]');
            if (!control) return;
            const key = control.dataset.key || '';
            const item = ctx.orderItems.find((entry) => entry.key === key);
            if (!item) return;
            ctx.setActiveTeamPreset('');
            if (control.dataset.priceWorkerAction === 'increment') item.quantity = Math.min(item.quantity + 1, 99);
            if (control.dataset.priceWorkerAction === 'decrement') item.quantity -= 1;
            if (control.dataset.priceWorkerAction === 'remove' || item.quantity <= 0) {
                ctx.orderItems = ctx.orderItems.filter((entry) => entry.key !== key);
            }
            ctx.renderOrderItems();
        });

        ctx.addMoreButton?.addEventListener('click', () => {
            ctx.closeModal();
            ctx.root.scrollIntoView({ block: 'start' });
        });
        ctx.clearButton?.addEventListener('click', () => {
            ctx.orderItems = [];
            ctx.setActiveTeamPreset('');
            ctx.renderOrderItems();
        });
        ctx.teamSummaryOpen?.addEventListener('click', ctx.showModal);
        ctx.teamSummaryClear?.addEventListener('click', () => {
            ctx.orderItems = [];
            ctx.setActiveTeamPreset('');
            ctx.renderOrderItems();
        });
        ctx.closeButton?.addEventListener('click', ctx.closeModal);
        ctx.cancelButton?.addEventListener('click', ctx.closeModal);
        ctx.modal.addEventListener('click', (event) => {
            if (event.target === ctx.modal) ctx.closeModal();
        });
        ctx.durationSelect?.addEventListener('change', ctx.syncEndDateVisibility);
        ctx.workloadSelect?.addEventListener('change', ctx.renderOrderItems);
        ctx.endDateInput?.addEventListener('input', () => ctx.endDateInput.setCustomValidity(''));
        ctx.orderForm?.addEventListener('submit', (event) => {
            if (!ctx.validateDuration()) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }
            ctx.updateHiddenFields();
        });
        ctx.orderForm?.addEventListener('reset', () => {
            setTimeout(() => {
                ctx.orderItems = [];
                ctx.setActiveTeamPreset('');
                ctx.syncEndDateVisibility();
                ctx.renderOrderItems();
            }, 0);
        });
    };
})();
