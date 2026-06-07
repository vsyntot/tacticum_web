<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?php if ((int)$offerCatalog['total_pages'] > 1):?>
    <nav class="flex flex-wrap items-center justify-center gap-2 mt-12" aria-label="Навигация по страницам расчетов">
        <?php if ((int)$offerCatalog['page'] > 1):?>
            <a href="<?=htmlspecialcharsbx($offerCatalogHref($offerFilters, ['page' => (int)$offerCatalog['page'] - 1]))?>"
               class="inline-flex items-center gap-2 rounded-button border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors"
               data-offer-catalog-link>
                <i class="ri-arrow-left-line"></i>
                Назад
            </a>
        <?php endif;?>
        <?php foreach ($offerCatalog['pagination'] as $pageItem):?>
            <?php if ($pageItem === 'ellipsis'):?>
                <span class="px-2 text-gray-400">...</span>
            <?php elseif ((int)$pageItem === (int)$offerCatalog['page']):?>
                <span class="inline-flex min-w-10 items-center justify-center rounded-button bg-primary px-4 py-2 text-sm font-medium text-white"><?=number_format((int)$pageItem, 0, '', ' ')?></span>
            <?php else:?>
                <a href="<?=htmlspecialcharsbx($offerCatalogHref($offerFilters, ['page' => (int)$pageItem]))?>"
                   class="inline-flex min-w-10 items-center justify-center rounded-button border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors"
                   data-offer-catalog-link><?=number_format((int)$pageItem, 0, '', ' ')?></a>
            <?php endif;?>
        <?php endforeach;?>
        <?php if ((int)$offerCatalog['page'] < (int)$offerCatalog['total_pages']):?>
            <a href="<?=htmlspecialcharsbx($offerCatalogHref($offerFilters, ['page' => (int)$offerCatalog['page'] + 1]))?>"
               class="inline-flex items-center gap-2 rounded-button border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-secondary hover:border-primary hover:text-primary transition-colors"
               data-offer-catalog-link>
                Далее
                <i class="ri-arrow-right-line"></i>
            </a>
        <?php endif;?>
    </nav>
<?php endif;?>
    </div>
</section>
