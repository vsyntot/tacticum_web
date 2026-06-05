<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_render_comparison')) {
    function tacticum_product_page_render_comparison(array $comparison): void
    {
        $columns = is_array($comparison['columns'] ?? null) ? $comparison['columns'] : [];

        if (empty($columns)) {
            return;
        }

        $eyebrow = tacticum_product_page_string($comparison, 'eyebrow', 'Сравнение');
        $title = tacticum_product_page_string($comparison, 'title', 'Как не перепутать продуктовый вход');
        $text = tacticum_product_page_string($comparison, 'text', 'Сравнение помогает выбрать правильный следующий шаг без спорных обещаний и без смешивания продуктов с услугами внедрения.');
        ?>
        <section class="bg-white py-16" data-product-block="comparison">
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
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <?php foreach ($columns as $column): ?>
                        <?php
                        if (!is_array($column)) {
                            continue;
                        }

                        $columnTitle = tacticum_product_page_string($column, 'title');
                        $columnText = tacticum_product_page_string($column, 'text');
                        $items = is_array($column['items'] ?? null) ? $column['items'] : [];
                        $href = tacticum_product_page_safe_href($column['href'] ?? '', '');
                        $linkText = tacticum_product_page_string($column, 'link_text', 'Подробнее');
                        ?>
                        <article class="rounded-xl border border-gray-200 bg-gray-50 p-6">
                            <?php if ($columnTitle !== ''): ?>
                                <h3 class="mb-3 text-xl font-bold text-secondary"><?=tacticum_product_page_html($columnTitle)?></h3>
                            <?php endif; ?>
                            <?php if ($columnText !== ''): ?>
                                <p class="mb-4 text-gray-600"><?=tacticum_product_page_html($columnText)?></p>
                            <?php endif; ?>
                            <?php if (!empty($items)): ?>
                                <ul class="space-y-2 text-sm text-gray-700">
                                    <?php foreach ($items as $item): ?>
                                        <?php if (!is_scalar($item)) { continue; } ?>
                                        <li class="flex gap-2">
                                            <i class="ri-arrow-right-s-line mt-0.5 text-primary" aria-hidden="true"></i>
                                            <span><?=tacticum_product_page_html($item)?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if ($href !== '' && $linkText !== ''): ?>
                                <a href="<?=tacticum_product_page_html($href)?>" class="mt-5 inline-flex items-center text-sm font-semibold text-primary hover:text-primary/80">
                                    <?=tacticum_product_page_html($linkText)?>
                                    <i class="ri-arrow-right-line ml-1" aria-hidden="true"></i>
                                </a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
