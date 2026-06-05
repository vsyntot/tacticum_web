<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?php if (!empty($offerCatalog['items'])):?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
        <?php foreach ($offerCatalog['items'] as $offerItem):?>
            <article class="bg-white rounded-xl border border-gray-200 p-6 md:p-7 flex flex-col min-h-[360px] shadow-sm hover:shadow-md transition-shadow">
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary"><?=htmlspecialcharsbx((string)$offerItem['sector'])?></span>
                    <?php if (!empty($offerItem['phase'])):?>
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"><?=htmlspecialcharsbx((string)$offerItem['phase'])?></span>
                    <?php endif;?>
                </div>
                <h2 class="text-xl font-bold text-secondary mb-3 leading-tight">
                    <a href="<?=htmlspecialcharsbx((string)$offerItem['url'])?>" class="hover:text-primary transition-colors">
                        <?=htmlspecialcharsbx((string)$offerItem['title'])?>
                    </a>
                </h2>
                <?php if (!empty($offerItem['summary'])):?>
                    <p class="text-sm text-gray-600 mb-5">
                        <?=htmlspecialcharsbx((string)$offerItem['summary'])?>
                    </p>
                <?php endif;?>
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
                <?php if (!empty($offerItem['goals'])):?>
                    <ul class="space-y-2 mb-5">
                        <?php foreach ($offerItem['goals'] as $goal):?>
                            <li class="flex items-start gap-2 text-sm text-gray-700">
                                <i class="ri-check-line text-primary mt-0.5"></i>
                                <span><?=htmlspecialcharsbx((string)$goal)?></span>
                            </li>
                        <?php endforeach;?>
                    </ul>
                <?php endif;?>
                <div class="flex flex-wrap gap-2 mt-auto mb-5">
                    <?php foreach ($offerItem['stack'] as $stackItem):?>
                        <span class="rounded bg-gray-50 px-2.5 py-1 text-xs text-gray-600"><?=htmlspecialcharsbx((string)$stackItem)?></span>
                    <?php endforeach;?>
                </div>
                <p class="text-xs text-gray-500 mb-4">
                    Это пример расчета. Точный бюджет зависит от ваших данных, интеграций и требований.
                </p>
                <a href="<?=htmlspecialcharsbx((string)$offerItem['url'])?>" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:gap-3 transition-all">
                    Открыть и уточнить
                    <i class="ri-arrow-right-line"></i>
                </a>
            </article>
        <?php endforeach;?>
    </div>
<?php else:?>
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
<?php endif;?>
