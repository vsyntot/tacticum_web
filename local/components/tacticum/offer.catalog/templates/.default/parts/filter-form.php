<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<form method="get"
      action="/offer/#offer-catalog"
      class="bg-white rounded-xl border border-gray-200 p-5 md:p-7 mb-10 shadow-sm"
      data-offer-catalog-form>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-7 gap-4">
        <div class="xl:col-span-2">
            <label for="offer-q" class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
            <input id="offer-q" type="search" name="q" value="<?=htmlspecialcharsbx((string)$offerFilters['q'])?>" placeholder="Например: OCR, ритейл, поддержка" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
        </div>
        <div>
            <label for="offer-sector" class="block text-sm font-medium text-gray-700 mb-2">Отрасль</label>
            <select id="offer-sector" name="sector" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                <option value="">Все отрасли</option>
                <?php foreach ($offerOptions['sectors'] as $option):?>
                    <option value="<?=htmlspecialcharsbx($option['key'])?>"<?=$offerSelected($offerFilters['sector'], $option['key'])?>><?=htmlspecialcharsbx($option['label'])?> · <?=number_format((int)$option['count'], 0, '', ' ')?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div>
            <label for="offer-scenario" class="block text-sm font-medium text-gray-700 mb-2">Тип задачи</label>
            <select id="offer-scenario" name="scenario" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                <option value="">Все задачи</option>
                <?php foreach ($offerOptions['scenarios'] as $option):?>
                    <option value="<?=htmlspecialcharsbx($option['key'])?>"<?=$offerSelected($offerFilters['scenario'], $option['key'])?>><?=htmlspecialcharsbx($option['label'])?> · <?=number_format((int)$option['count'], 0, '', ' ')?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div>
            <label for="offer-budget" class="block text-sm font-medium text-gray-700 mb-2">Бюджет</label>
            <select id="offer-budget" name="budget" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                <option value="">Любой бюджет</option>
                <?php foreach ($offerOptions['budgets'] as $option):?>
                    <option value="<?=htmlspecialcharsbx($option['key'])?>"<?=$offerSelected($offerFilters['budget'], $option['key'])?>><?=htmlspecialcharsbx($option['label'])?> · <?=number_format((int)$option['count'], 0, '', ' ')?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div>
            <label for="offer-phase" class="block text-sm font-medium text-gray-700 mb-2">Формат</label>
            <select id="offer-phase" name="phase" class="w-full rounded border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40">
                <option value="">Все форматы</option>
                <?php foreach ($offerOptions['phases'] as $option):?>
                    <option value="<?=htmlspecialcharsbx($option['key'])?>"<?=$offerSelected($offerFilters['phase'], $option['key'])?>><?=htmlspecialcharsbx($option['label'])?> · <?=number_format((int)$option['count'], 0, '', ' ')?></option>
                <?php endforeach;?>
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
    <?php if (!empty($offerAppliedFilters)):?>
        <div class="mt-5 rounded-lg border border-primary/20 bg-primary/5 p-4" aria-label="Примененные фильтры">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-secondary">Выбрано:</span>
                    <?php foreach ($offerAppliedFilters as $appliedFilter):?>
                        <?php
                        $appliedText = trim((string)$appliedFilter['label']) . ': ' . trim((string)$appliedFilter['value']);
                        ?>
                        <a href="<?=htmlspecialcharsbx((string)$appliedFilter['href'])?>"
                           class="inline-flex items-center gap-1.5 rounded-full border border-primary/30 bg-white px-3 py-1.5 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors"
                           data-offer-catalog-link>
                            <span><?=htmlspecialcharsbx($appliedText)?></span>
                            <i class="ri-close-line" aria-hidden="true"></i>
                            <span class="sr-only">Убрать фильтр <?=htmlspecialcharsbx($appliedText)?></span>
                        </a>
                    <?php endforeach;?>
                </div>
                <a href="/offer/#offer-catalog"
                   class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary/80 transition-colors"
                   data-offer-catalog-link>
                    <i class="ri-refresh-line" aria-hidden="true"></i>
                    Сбросить всё
                </a>
            </div>
        </div>
    <?php endif;?>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6">
        <div class="text-sm text-gray-600"
             tabindex="-1"
             aria-live="polite"
             aria-atomic="true"
             data-offer-catalog-status>
            <?php if ($offerCatalog['total'] > 0):?>
                Показано <?=number_format($offerResultFrom, 0, '', ' ')?>-<?=number_format($offerResultTo, 0, '', ' ')?> из <?=number_format((int)$offerCatalog['total'], 0, '', ' ')?>
            <?php else:?>
                Ничего не найдено
            <?php endif;?>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <?php if ($offerCatalog['has_filters']):?>
                <a href="/offer/#offer-catalog"
                   class="inline-flex items-center justify-center gap-2 rounded-button border border-gray-300 px-5 py-3 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors"
                   data-offer-catalog-link>
                    <i class="ri-close-line"></i>
                    Сбросить
                </a>
            <?php endif;?>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-button bg-primary px-5 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                <i class="ri-filter-3-line"></i>
                Показать
            </button>
        </div>
    </div>
</form>
