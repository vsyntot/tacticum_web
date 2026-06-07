<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?php
$offerFeaturedSectors = \Tacticum\Offer\CatalogFilters::featuredOptions($offerOptions, 'sectors');
$offerFeaturedScenarios = \Tacticum\Offer\CatalogFilters::featuredOptions($offerOptions, 'scenarios');
?>

<section id="offer-catalog" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <?php if (!empty($offerFeaturedSectors) || !empty($offerFeaturedScenarios)):?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
                <?php if (!empty($offerFeaturedSectors)):?>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 md:p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-secondary mb-2">Быстрые входы по отраслям</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            Приоритетные отрасли для быстрой ориентации: сравните бюджет, команду и сроки.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($offerFeaturedSectors as $sectorOption):?>
                                <a href="<?=htmlspecialcharsbx($offerUrl($offerFilters, ['sector' => $sectorOption['key'], 'scenario' => '', 'budget' => '', 'phase' => '', 'q' => '', 'sort' => 'new', 'page' => 1]))?>"
                                   class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm text-gray-700 hover:border-primary hover:text-primary transition-colors">
                                    <?=htmlspecialcharsbx($sectorOption['label'])?>
                                </a>
                            <?php endforeach;?>
                        </div>
                    </div>
                <?php endif;?>
                <?php if (!empty($offerFeaturedScenarios)):?>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 md:p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-secondary mb-2">Быстрые входы по сценариям</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            Частые сценарии для первого сравнения: откройте карточку и отправьте контекст на точную оценку.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($offerFeaturedScenarios as $scenarioOption):?>
                                <a href="<?=htmlspecialcharsbx($offerUrl($offerFilters, ['sector' => '', 'scenario' => $scenarioOption['key'], 'budget' => '', 'phase' => '', 'q' => '', 'sort' => 'new', 'page' => 1]))?>"
                                   class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm text-gray-700 hover:border-primary hover:text-primary transition-colors">
                                    <?=htmlspecialcharsbx($scenarioOption['label'])?>
                                </a>
                            <?php endforeach;?>
                        </div>
                    </div>
                <?php endif;?>
            </div>
        <?php endif;?>
