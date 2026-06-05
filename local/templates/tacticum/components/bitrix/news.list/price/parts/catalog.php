<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<section class="py-12" data-price-list>
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Роли и ставки для вашей команды</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Выберите специалистов вручную или начните с пресета команды. Итоговая заявка сохранит состав,
                уровни, загрузку и бюджетный ориентир.
            </p>
        </div>

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
                        Начните с типового состава под этап работ, затем уточните уровни, количество и загрузку.
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:min-w-[640px]">
                    <?php foreach ([
                        'mvp' => ['MVP', 'Аналитика, дизайн, разработка, QA'],
                        'discovery' => ['Discovery', 'Аналитик, архитектор, UX/UI'],
                        'support' => ['Support', 'Backend, DevOps, QA'],
                        'qa-burst' => ['QA burst', 'Усиление тестирования перед релизом'],
                    ] as $presetCode => [$presetTitle, $presetText]): ?>
                        <button type="button"
                                data-price-team-preset="<?= htmlspecialcharsbx($presetCode) ?>"
                                class="text-left rounded-lg border border-gray-200 bg-white p-4 hover:border-primary hover:shadow-sm transition-all">
                            <span class="block font-semibold text-secondary"><?= htmlspecialcharsbx($presetTitle) ?></span>
                            <span class="block text-sm text-gray-500 mt-1"><?= htmlspecialcharsbx($presetText) ?></span>
                        </button>
                    <?php endforeach; ?>
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

        <?php foreach ($arResult['GROUPED_SECTIONS'] as $section): ?>
            <?php if (empty($section['GROUPED_ITEMS'])) continue; ?>
            <div data-price-section data-category="<?= tacticum_escape_iblock_text((string)$section['NAME']) ?>">
                <h3 class="text-2xl font-bold text-secondary mb-6 section-title" data-price-section-title><?= tacticum_escape_iblock_text((string)$section['NAME']) ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= (count($section['GROUPED_ITEMS']) > 3 ? 4 : 3) ?> gap-8 mb-16" data-price-section-grid>
                    <?php foreach ($section['GROUPED_ITEMS'] as $item): ?>
                        <?php include __DIR__ . '/price-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
