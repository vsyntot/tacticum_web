(() => {
    const ns = window.TacticumPriceConfigurator = window.TacticumPriceConfigurator || {};

    ns.createFallbackOrderModal = () => {
        const modal = document.createElement('div');
        modal.id = 'specialistOrderModal';
        modal.setAttribute('data-price-order-modal', '');
        modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300 px-4 py-6';
        modal.innerHTML = `
            <div class="bg-white rounded-xl max-w-4xl w-full mx-auto transform scale-95 transition-transform duration-300 max-h-[92vh] overflow-hidden flex flex-col" data-price-order-modal-card>
                <div class="flex justify-between items-start gap-4 px-6 py-5 border-b border-gray-100 bg-white">
                    <div><h3 class="text-2xl font-bold text-secondary">Подобрать команду под задачу</h3><p class="text-sm text-gray-500 mt-1">Состав заявки можно изменить перед отправкой.</p></div>
                    <button id="closeOrderModal" type="button" data-price-modal-close class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100" aria-label="Закрыть"><i class="ri-close-line text-xl text-gray-500"></i></button>
                </div>
                <form id="specialistOrderForm" class="flex flex-col min-h-0" data-price-order-form data-tacticum-form data-form-id="price-specialist" data-endpoint="/local/rest/tacticum_sale_staff.php" data-tacticum-close-target="#specialistOrderModal" data-tacticum-close-mode="overlay">
                    <div class="overflow-y-auto px-6 py-6 space-y-6">
                        <section class="p-4 bg-primary/5 rounded-lg border border-primary/10" data-price-order-summary>
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div><p class="text-primary font-medium">Состав заявки</p><p class="text-sm text-gray-500 mt-1" data-price-order-count>1 специалист</p></div>
                                <button type="button" data-price-order-add-more class="inline-flex items-center justify-center px-4 py-2 rounded-button border border-primary text-primary text-sm font-medium hover:bg-primary hover:text-white transition-colors whitespace-nowrap">Добавить ещё</button>
                            </div>
                            <div class="space-y-3 mt-4" data-price-order-list></div>
                            <div class="mt-4 pt-4 border-t border-primary/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div><p id="selectedRate" class="text-sm font-medium text-secondary" data-price-selected-rate>Суммарная ставка: —</p><p class="text-xs text-gray-500 mt-1" data-price-monthly-budget>Оценка месячного бюджета появится после выбора загрузки.</p></div>
                                <button type="button" data-price-order-clear class="text-sm text-gray-500 hover:text-red-600 transition-colors">Очистить состав</button>
                            </div>
                            <p id="selectedSpecialist" class="sr-only" data-price-selected-specialist>Не выбран</p>
                        </section>
                        <section class="space-y-4"><h4 class="text-lg font-semibold text-secondary">Контакты</h4><div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Имя</span><input type="text" id="orderName" name="name" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                            <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Email</span><input type="email" id="orderEmail" name="email" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                            <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Телефон</span><input type="tel" id="orderPhone" name="phone" required class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                            <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Компания</span><input type="text" id="orderCompany" name="company" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                        </div></section>
                        <section class="space-y-4"><h4 class="text-lg font-semibold text-secondary">Параметры подключения</h4><div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Дата старта</span><input type="date" id="orderStartDate" name="startDate" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label>
                            <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Срок работы</span><select id="orderDuration" name="duration" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 appearance-none bg-white"><option value="flexible">Срок обсуждается</option><option value="2-weeks">Короткий спринт: до 2 недель</option><option value="1-month">1 месяц</option><option value="2-3-months">2–3 месяца</option><option value="3-6-months">3–6 месяцев</option><option value="6-plus-months">Дольше 6 месяцев</option><option value="exact-date">До конкретной даты</option></select></label>
                            <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Загрузка</span><select id="orderWorkload" name="workload" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 appearance-none bg-white"><option value="flexible">Обсуждается</option><option value="part-time">Part-time</option><option value="full-time">Full-time</option></select></label>
                        </div><label class="hidden" data-price-end-date-wrap><span class="block text-sm font-medium text-gray-600 mb-2">Дата окончания работ</span><input type="date" id="orderEndDate" name="endDate" data-price-end-date class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></label></section>
                        <label class="block"><span class="block text-sm font-medium text-gray-600 mb-2">Какую задачу должна решить команда</span><textarea id="orderDescription" name="message" required rows="4" class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></textarea></label>
                        <input type="hidden" id="orderSpecialist" name="specialist" data-price-order-specialist><input type="hidden" id="orderLevel" name="level" data-price-order-level><input type="hidden" id="orderRate" name="rate" data-price-order-rate><input type="hidden" id="orderAmount" name="amount_of_workers" data-price-order-amount><input type="hidden" id="orderWorkersJson" name="workers_json" data-price-order-workers><input type="hidden" id="orderTeamPreset" name="team_preset" data-price-order-team-preset><input type="hidden" id="orderMonthlyBudget" name="monthly_budget_estimate" data-price-order-monthly-budget>
                        <div class="flex items-start gap-3"><input type="checkbox" id="orderAgreement" data-tacticum-consent required class="appearance-none mt-1 w-5 h-5 border border-gray-300 rounded bg-white checked:bg-primary checked:border-0 relative"><label for="orderAgreement" class="text-sm text-gray-600 leading-5 pt-0.5">Я согласен на обработку персональных данных и принимаю условия <a href="/policies/" target="_blank" rel="noopener" class="text-primary hover:underline">политики конфиденциальности</a></label></div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 px-6 py-5 border-t border-gray-100 bg-white"><button type="submit" class="w-full sm:flex-1 bg-primary text-white px-6 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">Отправить состав команды</button><button type="button" id="cancelOrderModal" data-price-modal-cancel class="w-full sm:flex-1 bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-button hover:bg-gray-50 transition-colors whitespace-nowrap">Отмена</button></div>
                </form>
            </div>`;
        document.body.appendChild(modal);
        return modal;
    };
})();
