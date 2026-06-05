<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_render_fit_guide_column')) {
    function tacticum_product_page_render_fit_guide_column(array $column, string $defaultTitle, string $defaultTone): void
    {
        $title = tacticum_product_page_string($column, 'title', $defaultTitle);
        $tone = tacticum_product_page_string($column, 'tone', $defaultTone);
        $items = is_array($column['items'] ?? null) ? $column['items'] : [];

        if (empty($items)) {
            return;
        }

        $toneClasses = [
            'fit' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'caution' => 'border-amber-200 bg-amber-50 text-amber-700',
            'start' => 'border-blue-200 bg-blue-50 text-primary',
        ];
        $iconClasses = [
            'fit' => 'ri-check-line',
            'caution' => 'ri-error-warning-line',
            'start' => 'ri-arrow-right-line',
        ];
        $badgeClass = $toneClasses[$tone] ?? $toneClasses[$defaultTone] ?? $toneClasses['start'];
        $iconClass = $iconClasses[$tone] ?? $iconClasses[$defaultTone] ?? $iconClasses['start'];
        ?>
        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 inline-flex h-11 w-11 items-center justify-center rounded-lg <?=$badgeClass?>">
                <i class="<?=$iconClass?> text-2xl" aria-hidden="true"></i>
            </div>
            <h3 class="mb-4 text-xl font-bold text-secondary"><?=tacticum_product_page_html($title)?></h3>
            <ul class="space-y-3 text-sm text-gray-700">
                <?php foreach ($items as $item): ?>
                    <?php if (!is_scalar($item)) { continue; } ?>
                    <li class="flex gap-2">
                        <i class="ri-checkbox-circle-line mt-0.5 text-primary" aria-hidden="true"></i>
                        <span><?=tacticum_product_page_html($item)?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>
        <?php
    }
}

if (!function_exists('tacticum_product_page_render_fit_guide')) {
    function tacticum_product_page_render_fit_guide(array $fitGuide): void
    {
        $columns = [
            'fits' => ['title' => 'Подходит, если', 'tone' => 'fit'],
            'not_fits' => ['title' => 'Не подходит, если', 'tone' => 'caution'],
            'start' => ['title' => 'С чего начать', 'tone' => 'start'],
        ];
        $hasItems = false;

        foreach (array_keys($columns) as $key) {
            if (!empty($fitGuide[$key]) && is_array($fitGuide[$key]) && !empty($fitGuide[$key]['items'])) {
                $hasItems = true;
                break;
            }
        }

        if (!$hasItems) {
            return;
        }

        $eyebrow = tacticum_product_page_string($fitGuide, 'eyebrow', 'Product fit');
        $title = tacticum_product_page_string($fitGuide, 'title', 'Когда этот продукт подходит');
        $text = tacticum_product_page_string($fitGuide, 'text', 'Короткий фильтр помогает понять, какой продукт смотреть дальше и какой следующий шаг выбирать.');
        ?>
        <section class="bg-white py-16" data-product-block="fit-guide">
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
                    <?php foreach ($columns as $key => $defaults): ?>
                        <?php
                        $column = is_array($fitGuide[$key] ?? null) ? $fitGuide[$key] : [];
                        $column['tone'] = tacticum_product_page_string($column, 'tone', $defaults['tone']);
                        tacticum_product_page_render_fit_guide_column($column, $defaults['title'], $defaults['tone']);
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
