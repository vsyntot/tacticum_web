<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$page = is_array($arResult['PAGE'] ?? null) ? $arResult['PAGE'] : [];
$unavailable = (bool)($arResult['UNAVAILABLE'] ?? false);
$eyebrow = tacticum_product_page_string($page, 'eyebrow', 'Tacticum product');
$title = tacticum_product_page_string(
    $page,
    'title',
    $unavailable ? 'Материалы продукта обновляются' : ''
);
$lead = tacticum_product_page_string($page, 'lead');
$primaryCtaText = tacticum_product_page_string(
    $page,
    'primary_cta_text',
    $unavailable ? 'Связаться с командой' : 'Обсудить пилот'
);
$secondaryCtaText = tacticum_product_page_string(
    $page,
    'secondary_cta_text',
    $unavailable ? 'Все услуги' : 'Смотреть внедрение'
);
$secondaryCtaHref = tacticum_product_page_safe_href($page['secondary_cta_href'] ?? '', '/services/');
$productCode = tacticum_product_page_context_slug($page['_product_code'] ?? '', 'product');
$source = tacticum_product_page_string($page, '_source', $unavailable ? 'bitrix' : 'unknown');
if (!$unavailable && !in_array($source, ['bitrix', 'fallback'], true)) {
    $source = 'unknown';
}
$heroCards = is_array($page['hero_cards'] ?? null) ? $page['hero_cards'] : [];
$badges = is_array($page['badges'] ?? null) ? $page['badges'] : [];
?>
<section class="bg-gradient-to-r from-secondary to-primary pt-24 text-white" data-product-block="hero" data-product-source="<?=tacticum_product_page_html($source)?>" data-product-code="<?=tacticum_product_page_html($productCode)?>"<?php if ($unavailable): ?> data-product-status="unavailable"<?php endif; ?>>
    <div class="container mx-auto px-4 py-20">
        <div class="<?=$unavailable ? 'max-w-3xl' : 'grid grid-cols-1 items-center gap-10 lg:grid-cols-[1.08fr_0.92fr]'?>">
            <div>
                <p class="mb-4 text-sm font-semibold uppercase tracking-wide text-blue-200"><?=tacticum_product_page_html($eyebrow)?></p>
                <h1 class="mb-6 text-4xl font-bold leading-tight md:text-5xl"><?=tacticum_product_page_html($title)?></h1>
                <p class="<?=$unavailable ? 'mb-8 text-lg text-blue-100 md:text-xl' : 'mb-8 max-w-3xl text-lg text-blue-100 md:text-xl'?>"><?=tacticum_product_page_html($lead)?></p>
                <div class="<?=$unavailable ? 'flex flex-col gap-3 sm:flex-row' : 'mb-8 flex flex-col gap-3 sm:flex-row'?>">
                    <a href="#contact-form" class="inline-flex items-center justify-center rounded-button bg-white px-8 py-3 font-medium text-primary transition-colors hover:bg-white/90">
                        <?=tacticum_product_page_html($primaryCtaText)?>
                    </a>
                    <a href="<?=tacticum_product_page_html($secondaryCtaHref)?>" class="inline-flex items-center justify-center rounded-button border border-white/30 bg-white/10 px-8 py-3 font-medium text-white transition-colors hover:bg-white/20">
                        <?=tacticum_product_page_html($secondaryCtaText)?>
                    </a>
                </div>
                <?php if (!$unavailable) { tacticum_product_page_render_badges($badges); } ?>
            </div>
            <?php if (!$unavailable && !empty($heroCards)): ?>
                <div class="space-y-4">
                    <?php foreach ($heroCards as $card): ?>
                        <?php
                        if (!is_array($card)) {
                            continue;
                        }

                        $cardTitle = tacticum_product_page_string($card, 'title');
                        $cardText = tacticum_product_page_string($card, 'text');
                        ?>
                        <div class="rounded-xl border border-white/10 bg-white/10 p-5 shadow-sm backdrop-blur-sm">
                            <h2 class="font-bold"><?=tacticum_product_page_html($cardTitle)?></h2>
                            <p class="mt-1 text-sm text-blue-100"><?=tacticum_product_page_html($cardText)?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
