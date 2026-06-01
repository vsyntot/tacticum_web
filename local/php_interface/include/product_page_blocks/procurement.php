<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_render_procurement')) {
    function tacticum_product_page_render_procurement(array $procurement): void
    {
        $items = is_array($procurement['items'] ?? null) ? $procurement['items'] : [];

        if (empty($items)) {
            return;
        }

        $eyebrow = tacticum_product_page_string($procurement, 'eyebrow', 'Security / procurement');
        $title = tacticum_product_page_string($procurement, 'title', 'Что вынести на техническое и закупочное обсуждение');
        $text = tacticum_product_page_string($procurement, 'text', 'До промышленного запуска важно отдельно проверить контур данных, интеграции, роли доступа, журналирование и порядок эксплуатации.');
        $noteTitle = tacticum_product_page_string($procurement, 'note_title', 'Что не обещаем без assessment');
        $noteText = tacticum_product_page_string($procurement, 'note_text', 'Deployment-модель, сертификационный статус, SLA, перечень интеграций и регуляторные формулировки фиксируются только после проверки требований и evidence.');
        $ctaText = tacticum_product_page_string($procurement, 'cta_text', 'Запросить архитектурную сессию');
        $ctaHref = tacticum_product_page_string($procurement, 'cta_href', '#contact-form');
        ?>
        <section class="bg-secondary py-16 text-white" data-product-block="procurement">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                    <div>
                        <?php if ($eyebrow !== ''): ?>
                            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-blue-200"><?=tacticum_product_page_html($eyebrow)?></p>
                        <?php endif; ?>
                        <?php if ($title !== ''): ?>
                            <h2 class="mb-4 text-3xl font-bold md:text-4xl"><?=tacticum_product_page_html($title)?></h2>
                        <?php endif; ?>
                        <?php if ($text !== ''): ?>
                            <p class="mb-8 text-lg text-blue-100"><?=tacticum_product_page_html($text)?></p>
                        <?php endif; ?>
                        <div class="rounded-xl border border-white/10 bg-white/5 p-6">
                            <?php if ($noteTitle !== ''): ?>
                                <h3 class="mb-3 text-xl font-bold"><?=tacticum_product_page_html($noteTitle)?></h3>
                            <?php endif; ?>
                            <?php if ($noteText !== ''): ?>
                                <p class="text-blue-100"><?=tacticum_product_page_html($noteText)?></p>
                            <?php endif; ?>
                            <?php if ($ctaText !== ''): ?>
                                <a href="<?=tacticum_product_page_html($ctaHref)?>" class="mt-5 inline-flex items-center justify-center rounded-button bg-white px-6 py-3 font-medium text-primary transition-colors hover:bg-white/90">
                                    <?=tacticum_product_page_html($ctaText)?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($items as $item): ?>
                            <?php
                            if (!is_array($item)) {
                                continue;
                            }

                            $itemIcon = tacticum_product_page_string($item, 'icon', 'ri-shield-check-line');
                            $itemTitle = tacticum_product_page_string($item, 'title');
                            $itemText = tacticum_product_page_string($item, 'text');
                            ?>
                            <article class="rounded-xl border border-white/10 bg-white/5 p-5">
                                <div class="flex gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white/10 text-blue-100">
                                        <i class="<?=tacticum_product_page_html($itemIcon)?> text-2xl"></i>
                                    </div>
                                    <div>
                                        <?php if ($itemTitle !== ''): ?>
                                            <h3 class="mb-2 text-lg font-bold"><?=tacticum_product_page_html($itemTitle)?></h3>
                                        <?php endif; ?>
                                        <?php if ($itemText !== ''): ?>
                                            <p class="text-sm text-blue-100"><?=tacticum_product_page_html($itemText)?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}
