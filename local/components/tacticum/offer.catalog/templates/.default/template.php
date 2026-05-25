<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$offerCatalog = $arResult['CATALOG'] ?? [
    'filters' => [],
    'items' => [],
    'total' => 0,
    'all_total' => 0,
    'page' => 1,
    'total_pages' => 1,
    'per_page' => 24,
    'options' => ['sectors' => [], 'scenarios' => [], 'phases' => [], 'budgets' => []],
    'has_filters' => false,
    'pagination' => [],
    'stats' => ['sectors' => 0, 'scenarios' => 0, 'budget_min' => 0, 'budget_max' => 0],
];
$offerFilters = array_merge(
    ['q' => '', 'sector' => '', 'scenario' => '', 'budget' => '', 'phase' => '', 'sort' => 'new', 'page' => 1],
    is_array($offerCatalog['filters'] ?? null) ? $offerCatalog['filters'] : []
);
$offerOptions = array_merge(
    ['sectors' => [], 'scenarios' => [], 'phases' => [], 'budgets' => []],
    is_array($offerCatalog['options'] ?? null) ? $offerCatalog['options'] : []
);
$offerStats = array_merge(
    ['sectors' => 0, 'scenarios' => 0, 'budget_min' => 0, 'budget_max' => 0],
    is_array($offerCatalog['stats'] ?? null) ? $offerCatalog['stats'] : []
);
$offerMoney = static fn($amount) => $amount > 0 ? number_format((int)$amount, 0, '', ' ') . ' руб.' : 'по запросу';
$offerSelected = static fn($current, $value) => (string)$current === (string)$value ? ' selected' : '';
$offerUrl = static fn(array $filters, array $overrides = []) => function_exists('tacticum_offer_catalog_url')
    ? tacticum_offer_catalog_url($filters, $overrides)
    : '/offer/';
$offerResultFrom = $offerCatalog['total'] > 0 ? (($offerCatalog['page'] - 1) * $offerCatalog['per_page'] + 1) : 0;
$offerResultTo = min($offerCatalog['total'], $offerCatalog['page'] * $offerCatalog['per_page']);
?>
<section class="pt-32 pb-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_380px] gap-10 lg:gap-14 items-start">
            <div>
                <h1 class="text-3xl md:text-5xl font-bold text-secondary mb-6">
                    Примеры расчетов AI- и IT-проектов
                </h1>
                <p class="text-lg text-gray-600 max-w-3xl mb-10">
                    Собрали типовые оценки по отраслям, задачам, командам и бюджетам. Используйте их как ориентир,
                    а не как финальную смету: точную оценку нужно уточнять по вашим данным, интеграциям и ограничениям.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="/calculator/" class="inline-flex items-center justify-center gap-2 rounded-button bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                        <i class="ri-calculator-line"></i>
                        Рассчитать свой проект
                    </a>
                    <a href="#offer-catalog" class="inline-flex items-center justify-center gap-2 rounded-button border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors">
                        <i class="ri-list-check-2"></i>
                        Смотреть примеры
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-3xl font-bold text-secondary"><?=number_format((int)$offerCatalog['all_total'], 0, '', ' ')?></p>
                    <p class="text-sm text-gray-600 mt-1">активных расчетов</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-3xl font-bold text-secondary"><?=number_format((int)$offerStats['sectors'], 0, '', ' ')?></p>
                    <p class="text-sm text-gray-600 mt-1">отраслей</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-3xl font-bold text-secondary"><?=number_format((int)$offerStats['scenarios'], 0, '', ' ')?></p>
                    <p class="text-sm text-gray-600 mt-1">типов задач</p>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <p class="text-2xl font-bold text-secondary"><?=htmlspecialcharsbx($offerMoney((int)$offerStats['budget_max']))?></p>
                    <p class="text-sm text-gray-600 mt-1">верхняя оценка</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="offer-catalog" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <?if (!empty($offerOptions['sectors']) || !empty($offerOptions['scenarios'])):?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
                <?if (!empty($offerOptions['sectors'])):?>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 md:p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-secondary mb-2">Быстрые входы по отраслям</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            Быстро сузьте каталог до близкой отрасли, а затем сравните бюджет, команду и сроки.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <?foreach (array_slice($offerOptions['sectors'], 0, 8) as $sectorOption):?>
                                <a href="<?=htmlspecialcharsbx($offerUrl($offerFilters, ['sector' => $sectorOption['key'], 'scenario' => '', 'budget' => '', 'phase' => '', 'q' => '', 'sort' => 'new', 'page' => 1]))?>"
                                   class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm text-gray-700 hover:border-primary hover:text-primary transition-colors">
                                    <?=htmlspecialcharsbx($sectorOption['label'])?>
                                </a>
                            <?endforeach;?>
                        </div>
                    </div>
                <?endif;?>
                <?if (!empty($offerOptions['scenarios'])):?>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 md:p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-secondary mb-2">Быстрые входы по сценариям</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            Сравните похожие задачи, затем откройте карточку и отправьте контекст на точную оценку.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <?foreach (array_slice($offerOptions['scenarios'], 0, 8) as $scenarioOption):?>
                                <a href="<?=htmlspecialcharsbx($offerUrl($offerFilters, ['sector' => '', 'scenario' => $scenarioOption['key'], 'budget' => '', 'phase' => '', 'q' => '', 'sort' => 'new', 'page' => 1]))?>"
                                   class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm text-gray-700 hover:border-primary hover:text-primary transition-colors">
                                    <?=htmlspecialcharsbx($scenarioOption['label'])?>
                                </a>
                            <?endforeach;?>
                        </div>
                    </div>
                <?endif;?>
            </div>
        <?endif;?>

        <form method="get" action="/offer/" class="bg-white rounded-xl border border-gray-200 p-5 md:p-7 mb-10 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-7 gap-4">
                <div class="xl:col-span-2">
                    <label for="offer-q" class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                    <input id="offer-q" type="search" name="q" value="<?=htmlspecialcharsbx((string)$offerFilters['q'])?>" placeholder="Например: OCR, ритейл, поддержка" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
                <div>
                    <label for="offer-sector" class="block text-sm font-medium text-gray-700 mb-2">Отрасль</label>
                    <select id="offer-sector" name="sector" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Все отрасли</option>
                        <?foreach ($offerOptions['sectors'] as $option):?>
                            <option value="<?=htmlspecialcharsbx($option['key'])?>"<?=$offerSelected($offerFilters['sector'], $option['key'])?>><?=htmlspecialcharsbx($option['label'])?> · <?=number_format((int)$option['count'], 0, '', ' ')?></option>
                        <?endforeach;?>
                    </select>
                </div>
                <div>
                    <label for="offer-scenario" class="block text-sm font-medium text-gray-700 mb-2">Тип задачи</label>
                    <select id="offer-scenario" name="scenario" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Все задачи</option>
                        <?foreach ($offerOptions['scenarios'] as $option):?>
                            <option value="<?=htmlspecialcharsbx($option['key'])?>"<?=$offerSelected($offerFilters['scenario'], $option['key'])?>><?=htmlspecialcharsbx($option['label'])?> · <?=number_format((int)$option['count'], 0, '', ' ')?></option>
                        <?endforeach;?>
                    </select>
                </div>
                <div>
                    <label for="offer-budget" class="block text-sm font-medium text-gray-700 mb-2">Бюджет</label>
                    <select id="offer-budget" name="budget" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Любой бюджет</option>
                        <?foreach ($offerOptions['budgets'] as $option):?>
                            <option value="<?=htmlspecialcharsbx($option['key'])?>"<?=$offerSelected($offerFilters['budget'], $option['key'])?>><?=htmlspecialcharsbx($option['label'])?> · <?=number_format((int)$option['count'], 0, '', ' ')?></option>
                        <?endforeach;?>
                    </select>
                </div>
                <div>
                    <label for="offer-phase" class="block text-sm font-medium text-gray-700 mb-2">Формат</label>
                    <select id="offer-phase" name="phase" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="">Все форматы</option>
                        <?foreach ($offerOptions['phases'] as $option):?>
                            <option value="<?=htmlspecialcharsbx($option['key'])?>"<?=$offerSelected($offerFilters['phase'], $option['key'])?>><?=htmlspecialcharsbx($option['label'])?> · <?=number_format((int)$option['count'], 0, '', ' ')?></option>
                        <?endforeach;?>
                    </select>
                </div>
                <div>
                    <label for="offer-sort" class="block text-sm font-medium text-gray-700 mb-2">Сортировка</label>
                    <select id="offer-sort" name="sort" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                        <option value="new"<?=$offerSelected($offerFilters['sort'], 'new')?>>Сначала новые</option>
                        <option value="budget-desc"<?=$offerSelected($offerFilters['sort'], 'budget-desc')?>>Бюджет по убыванию</option>
                        <option value="budget-asc"<?=$offerSelected($offerFilters['sort'], 'budget-asc')?>>Бюджет по возрастанию</option>
                    </select>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6">
                <div class="text-sm text-gray-600">
                    <?if ($offerCatalog['total'] > 0):?>
                        Показано <?=number_format($offerResultFrom, 0, '', ' ')?>-<?=number_format($offerResultTo, 0, '', ' ')?> из <?=number_format((int)$offerCatalog['total'], 0, '', ' ')?>
                    <?else:?>
                        Ничего не найдено
                    <?endif;?>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <?if ($offerCatalog['has_filters']):?>
                        <a href="/offer/" class="inline-flex items-center justify-center gap-2 rounded-button border border-gray-300 px-5 py-3 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors">
                            <i class="ri-close-line"></i>
                            Сбросить
                        </a>
                    <?endif;?>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-button bg-primary px-5 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                        <i class="ri-filter-3-line"></i>
                        Показать
                    </button>
                </div>
            </div>
        </form>

        <?if (!empty($offerCatalog['items'])):?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
                <?foreach ($offerCatalog['items'] as $offerItem):?>
                    <article class="bg-white rounded-xl border border-gray-200 p-6 md:p-7 flex flex-col min-h-[360px] shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary"><?=htmlspecialcharsbx((string)$offerItem['sector'])?></span>
                            <?if (!empty($offerItem['phase'])):?>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"><?=htmlspecialcharsbx((string)$offerItem['phase'])?></span>
                            <?endif;?>
                        </div>
                        <h2 class="text-xl font-bold text-secondary mb-3 leading-tight">
                            <a href="<?=htmlspecialcharsbx((string)$offerItem['url'])?>" class="hover:text-primary transition-colors">
                                <?=htmlspecialcharsbx((string)$offerItem['title'])?>
                            </a>
                        </h2>
                        <?if (!empty($offerItem['summary'])):?>
                            <p class="text-sm text-gray-600 mb-5">
                                <?=htmlspecialcharsbx((string)$offerItem['summary'])?>
                            </p>
                        <?endif;?>
                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                            <div>
                                <dt class="text-xs text-gray-500 mb-1">Бюджет</dt>
                                <dd class="text-sm font-semibold text-secondary"><?=htmlspecialcharsbx((string)($offerItem['budget'] ?: 'по запросу'))?></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 mb-1">Срок</dt>
                                <dd class="text-sm font-semibold text-secondary"><?=htmlspecialcharsbx((string)($offerItem['timeline'] ?: 'по оценке'))?></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 mb-1">Команда</dt>
                                <dd class="text-sm font-semibold text-secondary"><?=number_format((int)$offerItem['team_count'], 0, '', ' ')?> чел.</dd>
                            </div>
                        </dl>
                        <?if (!empty($offerItem['goals'])):?>
                            <ul class="space-y-2 mb-5">
                                <?foreach ($offerItem['goals'] as $goal):?>
                                    <li class="flex items-start gap-2 text-sm text-gray-700">
                                        <i class="ri-check-line text-primary mt-0.5"></i>
                                        <span><?=htmlspecialcharsbx((string)$goal)?></span>
                                    </li>
                                <?endforeach;?>
                            </ul>
                        <?endif;?>
                        <div class="flex flex-wrap gap-2 mt-auto mb-5">
                            <?foreach ($offerItem['stack'] as $stackItem):?>
                                <span class="rounded bg-gray-50 px-2.5 py-1 text-xs text-gray-600"><?=htmlspecialcharsbx((string)$stackItem)?></span>
                            <?endforeach;?>
                        </div>
                        <p class="text-xs text-gray-500 mb-4">
                            Это пример расчета. Точный бюджет зависит от ваших данных, интеграций и требований.
                        </p>
                        <a href="<?=htmlspecialcharsbx((string)$offerItem['url'])?>" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:gap-3 transition-all">
                            Открыть и уточнить
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </article>
                <?endforeach;?>
            </div>
        <?else:?>
            <div class="bg-white rounded-xl border border-gray-200 p-8 md:p-10 text-center shadow-sm">
                <h2 class="text-2xl font-bold text-secondary mb-3">Подходящие расчеты не найдены</h2>
                <p class="text-gray-600 max-w-2xl mx-auto mb-6">
                    Попробуйте изменить фильтры или сформируйте точную оценку под вашу задачу через AI-калькулятор.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <a href="/offer/" class="inline-flex items-center justify-center gap-2 rounded-button border border-gray-300 px-5 py-3 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors">
                        <i class="ri-refresh-line"></i>
                        Сбросить фильтры
                    </a>
                    <a href="/calculator/" class="inline-flex items-center justify-center gap-2 rounded-button bg-primary px-5 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                        <i class="ri-calculator-line"></i>
                        Рассчитать проект
                    </a>
                </div>
            </div>
        <?endif;?>

        <?if ((int)$offerCatalog['total_pages'] > 1):?>
            <nav class="flex flex-wrap items-center justify-center gap-2 mt-12" aria-label="Навигация по страницам расчетов">
                <?if ((int)$offerCatalog['page'] > 1):?>
                    <a href="<?=htmlspecialcharsbx($offerUrl($offerFilters, ['page' => (int)$offerCatalog['page'] - 1]))?>" class="inline-flex items-center gap-2 rounded-button border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors">
                        <i class="ri-arrow-left-line"></i>
                        Назад
                    </a>
                <?endif;?>
                <?foreach ($offerCatalog['pagination'] as $pageItem):?>
                    <?if ($pageItem === 'ellipsis'):?>
                        <span class="px-2 text-gray-400">...</span>
                    <?elseif ((int)$pageItem === (int)$offerCatalog['page']):?>
                        <span class="inline-flex min-w-10 items-center justify-center rounded-button bg-primary px-4 py-2 text-sm font-medium text-white"><?=number_format((int)$pageItem, 0, '', ' ')?></span>
                    <?else:?>
                        <a href="<?=htmlspecialcharsbx($offerUrl($offerFilters, ['page' => (int)$pageItem]))?>" class="inline-flex min-w-10 items-center justify-center rounded-button border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors"><?=number_format((int)$pageItem, 0, '', ' ')?></a>
                    <?endif;?>
                <?endforeach;?>
                <?if ((int)$offerCatalog['page'] < (int)$offerCatalog['total_pages']):?>
                    <a href="<?=htmlspecialcharsbx($offerUrl($offerFilters, ['page' => (int)$offerCatalog['page'] + 1]))?>" class="inline-flex items-center gap-2 rounded-button border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors">
                        Далее
                        <i class="ri-arrow-right-line"></i>
                    </a>
                <?endif;?>
            </nav>
        <?endif;?>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 border border-primary/10 rounded-xl bg-white p-6 md:p-8 shadow-sm">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-secondary mb-3">Нашли похожую задачу?</h2>
                <p class="text-gray-600 max-w-3xl">
                    Откройте карточку, перенесите ее контекст в заявку или начните с AI-калькулятора. Мы уточним
                    отрасль, ограничения, интеграции и подготовим следующий шаг к точной смете.
                </p>
            </div>
            <a href="/calculator/" class="inline-flex items-center justify-center gap-2 rounded-button bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                <i class="ri-calculator-line"></i>
                Начать точную оценку
            </a>
        </div>
    </div>
</section>
