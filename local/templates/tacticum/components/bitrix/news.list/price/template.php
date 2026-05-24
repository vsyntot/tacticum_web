<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<section class="py-12" data-price-list>
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Ставки специалистов по категориям</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Выберите нужную категорию специалистов или воспользуйтесь фильтром для поиска конкретной позиции
            </p>
        </div>
        <!-- Filter Tabs -->
        <div class="flex flex-wrap justify-center gap-4 mb-12" data-price-filter-tabs>
            <button type="button"
                    data-price-filter-tab
                    data-category="all"
                    data-active="true"
                    aria-pressed="true"
                    class="filter-tab bg-primary text-white px-6 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
                Все специалисты
            </button>
            <?php foreach ($arResult['GROUPED_SECTIONS'] as $section): ?>
                <button type="button"
                        data-price-filter-tab
                        data-category="<?= tacticum_escape_iblock_text((string)$section['NAME']) ?>"
                        data-active="false"
                        aria-pressed="false"
                        class="filter-tab bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-button hover:bg-gray-50 transition-colors whitespace-nowrap">
                    <?= tacticum_escape_iblock_text((string)$section['NAME']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Only Search -->
        <div class="flex flex-col md:flex-row gap-4 mb-4">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="ri-search-line text-gray-400"></i>
                </div>
                <input type="text" placeholder="Поиск по специальности..."
                       id="specialist-search"
                       data-price-search
                       class="search-input w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 text-gray-700">
            </div>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-8 text-sm">
            <p class="text-gray-500" data-price-results-summary>Показаны все специалисты</p>
            <button type="button"
                    data-price-reset
                    class="hidden self-start sm:self-auto text-primary font-medium hover:underline">
                Сбросить фильтры
            </button>
        </div>
        <div class="hidden rounded-xl border border-gray-200 bg-white p-6 mb-10 text-center" data-price-empty>
            <p class="text-lg font-semibold text-secondary">Специалисты не найдены</p>
            <p class="text-gray-500 mt-2">Попробуйте изменить категорию или поисковый запрос.</p>
            <button type="button"
                    data-price-reset
                    class="mt-4 inline-flex items-center justify-center rounded-button bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                Показать всех специалистов
            </button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 md:p-6 mb-8" data-price-team-presets>
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="max-w-2xl">
                    <h3 class="text-xl font-bold text-secondary">Быстрые пресеты команды</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Подберите стартовый состав под тип задачи, а затем уточните уровни и количество специалистов.
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:min-w-[640px]">
                    <button type="button"
                            data-price-team-preset="mvp"
                            class="text-left rounded-lg border border-gray-200 bg-white p-4 hover:border-primary hover:shadow-sm transition-all">
                        <span class="block font-semibold text-secondary">MVP</span>
                        <span class="block text-sm text-gray-500 mt-1">Аналитика, дизайн, разработка, QA</span>
                    </button>
                    <button type="button"
                            data-price-team-preset="discovery"
                            class="text-left rounded-lg border border-gray-200 bg-white p-4 hover:border-primary hover:shadow-sm transition-all">
                        <span class="block font-semibold text-secondary">Discovery</span>
                        <span class="block text-sm text-gray-500 mt-1">Аналитик, архитектор, UX/UI</span>
                    </button>
                    <button type="button"
                            data-price-team-preset="support"
                            class="text-left rounded-lg border border-gray-200 bg-white p-4 hover:border-primary hover:shadow-sm transition-all">
                        <span class="block font-semibold text-secondary">Support</span>
                        <span class="block text-sm text-gray-500 mt-1">Backend, DevOps, QA</span>
                    </button>
                    <button type="button"
                            data-price-team-preset="qa-burst"
                            class="text-left rounded-lg border border-gray-200 bg-white p-4 hover:border-primary hover:shadow-sm transition-all">
                        <span class="block font-semibold text-secondary">QA burst</span>
                        <span class="block text-sm text-gray-500 mt-1">Усиление тестирования перед релизом</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="hidden sticky top-24 z-20 rounded-xl border border-primary/20 bg-white p-4 md:p-5 mb-8 shadow-lg shadow-primary/10" data-price-team-summary aria-live="polite">
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)_auto] gap-5 xl:items-center">
                <div class="min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                        <p class="text-sm font-medium text-primary">Команда в заявке</p>
                        <p class="inline-flex w-fit rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary" data-price-team-summary-text>Состав не выбран</p>
                    </div>
                    <p class="text-sm text-gray-500 mt-2" data-price-team-summary-preset></p>
                    <div class="tacticum-team-summary-list mt-3" data-price-team-summary-list role="list"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-lg bg-gray-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Ставка</p>
                        <p class="font-semibold text-secondary mt-1" data-price-team-summary-rate>—</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Оценка в месяц</p>
                        <p class="font-semibold text-secondary mt-1" data-price-team-summary-budget>Зависит от загрузки</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row xl:flex-col gap-3 xl:shrink-0">
                    <button type="button"
                            data-price-team-summary-open
                            class="inline-flex items-center justify-center rounded-button bg-primary px-5 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors whitespace-nowrap">
                        Уточнить состав
                    </button>
                    <button type="button"
                            data-price-team-summary-clear
                            class="inline-flex items-center justify-center rounded-button border border-gray-200 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:border-red-200 hover:text-red-600 transition-colors whitespace-nowrap">
                        Сбросить
                    </button>
                </div>
            </div>
        </div>

        <?php
        $icons = [
            'Аналитика' => 'ri-file-chart-line',
            'Разработка' => 'ri-code-s-slash-line',
            'DevOps/Инфраструктура' => 'ri-server-line',
            'Тестирование и качество' => 'ri-bug-line',
            'Прочие специалисты' => 'ri-user-line',
        ];
        ?>

        <?php foreach ($arResult['GROUPED_SECTIONS'] as $section): ?>
            <?php if (empty($section['GROUPED_ITEMS'])) continue; ?>

            <div data-price-section data-category="<?= tacticum_escape_iblock_text((string)$section['NAME']) ?>">
                <h3 class="text-2xl font-bold text-secondary mb-6 section-title" data-price-section-title><?= tacticum_escape_iblock_text((string)$section['NAME']) ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= (count($section['GROUPED_ITEMS']) > 3 ? 4 : 3) ?> gap-8 mb-16" data-price-section-grid>
                    <?php foreach ($section['GROUPED_ITEMS'] as $item):
                        $icon = $icons[$section['NAME']] ?? 'ri-user-line';
                        $options = (array)($item['OPTIONS'] ?? []);
                        $levels = $item['LEVELS'] ?? [];
                        $levelKeys = array_keys($levels);

                        // По умолчанию Middle, если есть, иначе первый
                        $defaultLevel = 'Middle';
                        $selectedLevel = in_array($defaultLevel, $levelKeys) ? $defaultLevel : reset($levelKeys);
                        $selectedPrice = $levels[$selectedLevel]['PRICE'] ?? null;
                        $selectedPriceNum = (float)str_replace(',', '.', str_replace(' ', '', (string)$selectedPrice));
                        $selectedPriceFormatted = number_format($selectedPriceNum, 0, ',', ' ');
                        $isPopular = ($item['POPULAR']['VALUE_XML_ID'] ?? '') === 'popular' || ($item['POPULAR']['VALUE'] ?? '') === 'Да';
                        ?>
                        <div class="pricing-card <?= $isPopular ? 'featured border-2' : 'border' ?> bg-white rounded-xl p-6 shadow-sm relative"
                             data-price-card
                             data-name="<?= tacticum_escape_iblock_text((string)$item['NAME']) ?>"
                             data-category="<?= tacticum_escape_iblock_text((string)$section['NAME']) ?>"
                             data-level="<?= tacticum_escape_iblock_text((string)$selectedLevel) ?>"
                             data-price="<?= htmlspecialcharsbx((string)$selectedPriceNum) ?>"
                             data-popular="<?= $isPopular ? '1' : '0' ?>">
                            <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                                <i class="<?= $icon ?> text-3xl text-primary"></i>
                            </div>
                            <h3 class="text-xl font-bold text-secondary mb-2"><?= tacticum_escape_iblock_text((string)$item['NAME']) ?></h3>
                            <?php if (count($levels) > 1): ?>
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-500 mb-2">Уровень</p>
                                    <div class="price-level-group flex flex-wrap gap-2" data-price-level-options role="group" aria-label="Уровень специалиста">
                                        <?php foreach ($levelKeys as $levelKey): ?>
                                            <button type="button"
                                                    data-price-level-option
                                                    data-level="<?= tacticum_escape_iblock_text((string)$levelKey) ?>"
                                                    data-price="<?= htmlspecialcharsbx((string)($levels[$levelKey]['PRICE'] ?? '')) ?>"
                                                    data-active="<?= $levelKey === $selectedLevel ? 'true' : 'false' ?>"
                                                    aria-pressed="<?= $levelKey === $selectedLevel ? 'true' : 'false' ?>"
                                                    class="price-level-option inline-flex min-h-10 min-w-[5.5rem] flex-1 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 transition-colors">
                                                <?= tacticum_escape_iblock_text((string)$levelKey) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php elseif (!empty($selectedLevel)): ?>
                                <div class="mb-4">
                                    <span class="inline-flex rounded-lg bg-gray-50 px-3 py-2 text-sm font-medium text-gray-600">
                                        <?= tacticum_escape_iblock_text((string)$selectedLevel) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="text-2xl font-bold text-primary mb-4 price-value" data-price-value>
                                от <?= $selectedPriceFormatted ?> ₽
                                <span class="text-sm text-gray-500 font-normal">/час</span>
                            </div>
                            <?php if (!empty($options)): ?>
                                <ul class="space-y-2 mb-6 text-gray-600">
                                    <?php foreach ($options as $opt): ?>
                                        <li class="flex items-start gap-2">
                                            <i class="ri-checkbox-circle-line text-green-500 mt-1"></i>
                                            <span><?= tacticum_escape_iblock_text((string)$opt) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <button type="button"
                                    data-price-order
                                    class="order-specialist-btn w-full <?= $isPopular ? 'bg-primary text-white' : 'bg-white border border-primary text-primary' ?> px-6 py-3 rounded-button hover:bg-primary hover:text-white transition-colors whitespace-nowrap">
                                Добавить в заявку
                            </button>
                            <script type="application/json" class="level-prices-json" data-price-levels>
                                <?= json_encode(array_map(fn($arr) => $arr['PRICE'], $levels), JSON_UNESCAPED_UNICODE) ?>
                            </script>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div id="specialistOrderModal"
     data-price-order-modal
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300 px-4 py-6">
    <div class="bg-white rounded-xl max-w-4xl w-full mx-auto transform scale-95 transition-transform duration-300 max-h-[92vh] overflow-hidden flex flex-col"
         data-price-order-modal-card>
        <div class="flex justify-between items-start gap-4 px-6 py-5 border-b border-gray-100 bg-white">
            <div>
                <h3 class="text-2xl font-bold text-secondary">Заказать специалистов</h3>
                <p class="text-sm text-gray-500 mt-1">Состав заявки можно изменить перед отправкой.</p>
            </div>
            <button id="closeOrderModal" type="button"
                    data-price-modal-close
                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100"
                    aria-label="Закрыть">
                <i class="ri-close-line text-xl text-gray-500"></i>
            </button>
        </div>

        <form id="specialistOrderForm"
              class="flex flex-col min-h-0"
              data-price-order-form
              data-tacticum-form
              data-form-id="price-specialist"
              data-endpoint="/local/rest/tacticum_sale_staff.php"
              data-tacticum-close-target="#specialistOrderModal"
              data-tacticum-close-mode="overlay">
            <div class="overflow-y-auto px-6 py-6 space-y-6">
                <section class="p-4 bg-primary/5 rounded-lg border border-primary/10" data-price-order-summary>
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div>
                            <p class="text-primary font-medium">Состав заявки</p>
                            <p class="text-sm text-gray-500 mt-1" data-price-order-count>1 специалист</p>
                        </div>
                        <button type="button"
                                data-price-order-add-more
                                class="inline-flex items-center justify-center px-4 py-2 rounded-button border border-primary text-primary text-sm font-medium hover:bg-primary hover:text-white transition-colors whitespace-nowrap">
                            Добавить ещё
                        </button>
                    </div>
                    <div class="space-y-3 mt-4" data-price-order-list></div>
                    <div class="mt-4 pt-4 border-t border-primary/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p id="selectedRate" class="text-sm font-medium text-secondary" data-price-selected-rate>Суммарная ставка: —</p>
                            <p class="text-xs text-gray-500 mt-1" data-price-monthly-budget>Оценка месячного бюджета появится после выбора загрузки.</p>
                        </div>
                        <button type="button"
                                data-price-order-clear
                                class="text-sm text-gray-500 hover:text-red-600 transition-colors">
                            Очистить состав
                        </button>
                    </div>
                    <p id="selectedSpecialist" class="sr-only" data-price-selected-specialist>Не выбран</p>
                </section>

                <section class="space-y-4">
                    <h4 class="text-lg font-semibold text-secondary">Контакты</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="block">
                            <span class="block text-sm font-medium text-gray-600 mb-2">Имя</span>
                            <input type="text" id="orderName" name="name" required
                                   class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                        </label>
                        <label class="block">
                            <span class="block text-sm font-medium text-gray-600 mb-2">Email</span>
                            <input type="email" id="orderEmail" name="email" required
                                   class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                        </label>
                        <label class="block">
                            <span class="block text-sm font-medium text-gray-600 mb-2">Телефон</span>
                            <input type="tel" id="orderPhone" name="phone" required
                                   class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                        </label>
                        <label class="block">
                            <span class="block text-sm font-medium text-gray-600 mb-2">Компания</span>
                            <input type="text" id="orderCompany" name="company"
                                   class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                        </label>
                    </div>
                </section>

                <section class="space-y-4">
                    <h4 class="text-lg font-semibold text-secondary">Параметры работы</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="block">
                            <span class="block text-sm font-medium text-gray-600 mb-2">Дата старта</span>
                            <input type="date" id="orderStartDate" name="startDate"
                                   class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                        </label>
                        <label class="block">
                            <span class="block text-sm font-medium text-gray-600 mb-2">Срок работы</span>
                            <select id="orderDuration" name="duration"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 appearance-none bg-white">
                                <option value="flexible">Срок обсуждается</option>
                                <option value="2-weeks">Короткий спринт: до 2 недель</option>
                                <option value="1-month">1 месяц</option>
                                <option value="2-3-months">2–3 месяца</option>
                                <option value="3-6-months">3–6 месяцев</option>
                                <option value="6-plus-months">Дольше 6 месяцев</option>
                                <option value="exact-date">До конкретной даты</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="block text-sm font-medium text-gray-600 mb-2">Загрузка</span>
                            <select id="orderWorkload" name="workload"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 appearance-none bg-white">
                                <option value="flexible">Обсуждается</option>
                                <option value="part-time">Part-time</option>
                                <option value="full-time">Full-time</option>
                            </select>
                        </label>
                    </div>
                    <label class="hidden" data-price-end-date-wrap>
                        <span class="block text-sm font-medium text-gray-600 mb-2">Дата окончания работ</span>
                        <input type="date" id="orderEndDate" name="endDate" data-price-end-date
                               class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50">
                    </label>
                </section>

                <label class="block">
                    <span class="block text-sm font-medium text-gray-600 mb-2">Описание задачи</span>
                    <textarea id="orderDescription" name="message" required rows="4"
                              class="w-full border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50"></textarea>
                </label>

                <input type="hidden" id="orderSpecialist" name="specialist" data-price-order-specialist>
                <input type="hidden" id="orderLevel" name="level" data-price-order-level>
                <input type="hidden" id="orderRate" name="rate" data-price-order-rate>
                <input type="hidden" id="orderAmount" name="amount_of_workers" data-price-order-amount>
                <input type="hidden" id="orderWorkersJson" name="workers_json" data-price-order-workers>
                <input type="hidden" id="orderTeamPreset" name="team_preset" data-price-order-team-preset>
                <input type="hidden" id="orderMonthlyBudget" name="monthly_budget_estimate" data-price-order-monthly-budget>

                <div class="flex items-start gap-3">
                    <input type="checkbox" id="orderAgreement" data-tacticum-consent required
                           class="appearance-none mt-1 w-5 h-5 border border-gray-300 rounded bg-white checked:bg-primary checked:border-0 relative">
                    <label for="orderAgreement" class="text-sm text-gray-600 leading-5 pt-0.5">
                        Я согласен на обработку персональных данных и принимаю условия
                        <a href="/policies/" target="_blank" rel="noopener" class="text-primary hover:underline">политики конфиденциальности</a>
                    </label>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 px-6 py-5 border-t border-gray-100 bg-white">
                <button type="submit"
                        class="w-full sm:flex-1 bg-primary text-white px-6 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
                    Отправить заявку
                </button>
                <button type="button" id="cancelOrderModal"
                        data-price-modal-cancel
                        class="w-full sm:flex-1 bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-button hover:bg-gray-50 transition-colors whitespace-nowrap">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>
