<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?php
$offerFeaturedSectors = \Tacticum\Offer\CatalogFilters::featuredOptions($offerOptions, 'sectors');
$offerFeaturedScenarios = \Tacticum\Offer\CatalogFilters::featuredOptions($offerOptions, 'scenarios');
?>

<section id="offer-catalog"
         class="offer-catalog-shell py-16 bg-gray-50"
         tabindex="-1"
         aria-label="Каталог примеров расчетов"
         data-offer-catalog-root>
    <div class="container mx-auto px-4">
        <div class="sr-only" aria-live="polite" aria-atomic="true" data-offer-catalog-live></div>
        <?php if (!empty($offerFeaturedSectors) || !empty($offerFeaturedScenarios)):?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
                <?php if (!empty($offerFeaturedSectors)):?>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 md:p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-secondary mb-2">Быстрые входы по отраслям</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            Выберите одну приоритетную отрасль: быстрый вход сбросит остальные фильтры.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($offerFeaturedSectors as $sectorOption):?>
                                <?php
                                $isActiveSector = (string)$offerFilters['sector'] === (string)$sectorOption['key'];
                                $sectorClass = $isActiveSector
                                    ? 'border-primary bg-primary text-white shadow-sm'
                                    : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-primary hover:text-primary';
                                ?>
                                <a href="<?=htmlspecialcharsbx($offerCatalogHref($offerFilters, ['sector' => $sectorOption['key'], 'scenario' => '', 'budget' => '', 'phase' => '', 'q' => '', 'sort' => 'new', 'page' => 1]))?>"
                                   class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm transition-colors <?=$sectorClass?>"
                                   data-offer-catalog-link<?=$isActiveSector ? ' aria-current="true"' : ''?>>
                                    <?php if ($isActiveSector):?>
                                        <i class="ri-check-line" aria-hidden="true"></i>
                                        <span class="sr-only">Выбрано: </span>
                                    <?php endif;?>
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
                            Выберите один сценарий для первого сравнения: быстрый вход сбросит остальные фильтры.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($offerFeaturedScenarios as $scenarioOption):?>
                                <?php
                                $isActiveScenario = (string)$offerFilters['scenario'] === (string)$scenarioOption['key'];
                                $scenarioClass = $isActiveScenario
                                    ? 'border-primary bg-primary text-white shadow-sm'
                                    : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-primary hover:text-primary';
                                ?>
                                <a href="<?=htmlspecialcharsbx($offerCatalogHref($offerFilters, ['sector' => '', 'scenario' => $scenarioOption['key'], 'budget' => '', 'phase' => '', 'q' => '', 'sort' => 'new', 'page' => 1]))?>"
                                   class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm transition-colors <?=$scenarioClass?>"
                                   data-offer-catalog-link<?=$isActiveScenario ? ' aria-current="true"' : ''?>>
                                    <?php if ($isActiveScenario):?>
                                        <i class="ri-check-line" aria-hidden="true"></i>
                                        <span class="sr-only">Выбрано: </span>
                                    <?php endif;?>
                                    <?=htmlspecialcharsbx($scenarioOption['label'])?>
                                </a>
                            <?php endforeach;?>
                        </div>
                    </div>
                <?php endif;?>
            </div>
        <?php endif;?>
