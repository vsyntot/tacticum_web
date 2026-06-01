<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_render_architecture')) {
    function tacticum_product_page_render_architecture(array $architecture): void
    {
        if (empty($architecture)) {
            return;
        }

        $title = tacticum_product_page_string($architecture, 'title', 'Как это устроено');
        $text = tacticum_product_page_string($architecture, 'text');
        $layers = is_array($architecture['layers'] ?? null) ? $architecture['layers'] : [];
        ?>
        <section class="bg-secondary py-16 text-white" data-product-block="architecture">
            <div class="container mx-auto px-4">
                <div class="mb-10 max-w-3xl">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-blue-200">Архитектура</p>
                    <h2 class="mb-4 text-3xl font-bold md:text-4xl"><?=tacticum_product_page_html($title)?></h2>
                    <?php if ($text !== ''): ?>
                        <p class="text-lg text-blue-100"><?=tacticum_product_page_html($text)?></p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($layers)): ?>
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($layers as $layer): ?>
                            <?php
                            if (!is_array($layer)) {
                                continue;
                            }

                            $layerTitle = tacticum_product_page_string($layer, 'title');
                            $layerText = tacticum_product_page_string($layer, 'text');
                            $items = is_array($layer['items'] ?? null) ? $layer['items'] : [];
                            ?>
                            <div class="rounded-xl border border-white/10 bg-white/5 p-6">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div class="max-w-xl">
                                        <h3 class="text-xl font-bold"><?=tacticum_product_page_html($layerTitle)?></h3>
                                        <?php if ($layerText !== ''): ?>
                                            <p class="mt-2 text-blue-100"><?=tacticum_product_page_html($layerText)?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($items)): ?>
                                        <div class="flex flex-wrap gap-2 md:max-w-xl md:justify-end">
                                            <?php foreach ($items as $item): ?>
                                                <?php if (!is_scalar($item)) { continue; } ?>
                                                <span class="rounded-full bg-white/10 px-3 py-1 text-sm text-blue-100"><?=tacticum_product_page_html($item)?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }
}
