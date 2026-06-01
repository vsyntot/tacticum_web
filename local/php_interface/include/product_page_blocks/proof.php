<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_render_proof')) {
    function tacticum_product_page_render_proof(array $proof): void
    {
        $items = is_array($proof['items'] ?? null) ? $proof['items'] : [];

        if (empty($items)) {
            return;
        }

        $eyebrow = tacticum_product_page_string($proof, 'eyebrow', 'Проверка');
        $title = tacticum_product_page_string($proof, 'title', 'Что подтверждаем на пилоте');
        $text = tacticum_product_page_string($proof, 'text', 'До появления публичных метрик фиксируем проверяемые артефакты: что измеряем, кто владелец и какие ограничения остаются после пилота.');
        ?>
        <section class="bg-white py-16" data-product-block="proof">
            <div class="container mx-auto px-4">
                <div class="mb-10 max-w-3xl">
                    <?php if ($eyebrow !== ''): ?>
                        <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary"><?=tacticum_product_page_html($eyebrow)?></p>
                    <?php endif; ?>
                    <?php if ($title !== ''): ?>
                        <h2 class="mb-4 text-3xl font-bold text-secondary md:text-4xl"><?=tacticum_product_page_html($title)?></h2>
                    <?php endif; ?>
                    <?php if ($text !== ''): ?>
                        <p class="text-lg text-gray-600"><?=tacticum_product_page_html($text)?></p>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <?php foreach ($items as $item): ?>
                        <?php
                        if (!is_array($item)) {
                            continue;
                        }

                        $itemMeta = tacticum_product_page_string($item, 'meta', 'Пилот');
                        $itemTitle = tacticum_product_page_string($item, 'title');
                        $itemText = tacticum_product_page_string($item, 'text');
                        ?>
                        <article class="rounded-xl border border-gray-200 bg-gray-50 p-6">
                            <?php if ($itemMeta !== ''): ?>
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-primary"><?=tacticum_product_page_html($itemMeta)?></p>
                            <?php endif; ?>
                            <?php if ($itemTitle !== ''): ?>
                                <h3 class="mb-3 text-xl font-bold text-secondary"><?=tacticum_product_page_html($itemTitle)?></h3>
                            <?php endif; ?>
                            <?php if ($itemText !== ''): ?>
                                <p class="text-gray-600"><?=tacticum_product_page_html($itemText)?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
