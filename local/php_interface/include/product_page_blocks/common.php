<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_render_badges')) {
    function tacticum_product_page_render_badges(array $badges): void
    {
        if (empty($badges)) {
            return;
        }
        ?>
        <div class="flex flex-wrap gap-3">
            <?php foreach ($badges as $badge): ?>
                <?php
                if (!is_scalar($badge)) {
                    continue;
                }
                ?>
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-blue-100">
                    <?=tacticum_product_page_html($badge)?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if (!function_exists('tacticum_product_page_proof_statuses')) {
    function tacticum_product_page_proof_statuses(): array
    {
        return [
            'pilot-artifact' => [
                'label' => 'Артефакт пилота',
                'class' => 'border-blue-100 bg-blue-50 text-blue-700',
                'icon' => 'ri-flask-line',
            ],
            'private-evidence' => [
                'label' => 'Доступно по запросу',
                'class' => 'border-violet-100 bg-violet-50 text-violet-700',
                'icon' => 'ri-lock-line',
            ],
            'public-safe' => [
                'label' => 'Публичная выдержка',
                'class' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
                'icon' => 'ri-shield-check-line',
            ],
            'pending' => [
                'label' => 'На проверке',
                'class' => 'border-amber-100 bg-amber-50 text-amber-700',
                'icon' => 'ri-time-line',
            ],
            'blocked' => [
                'label' => 'Не публикуется',
                'class' => 'border-gray-200 bg-gray-100 text-gray-700',
                'icon' => 'ri-forbid-2-line',
            ],
        ];
    }
}

if (!function_exists('tacticum_product_page_proof_status')) {
    function tacticum_product_page_proof_status(array $item, string $default = 'pilot-artifact'): array
    {
        $statuses = tacticum_product_page_proof_statuses();
        $status = strtolower(tacticum_product_page_string($item, 'proof_status', $default));

        if (!array_key_exists($status, $statuses)) {
            $status = 'pending';
        }

        return [
            'value' => $status,
            'label' => $statuses[$status]['label'],
            'class' => $statuses[$status]['class'],
            'icon' => $statuses[$status]['icon'],
        ];
    }
}

if (!function_exists('tacticum_product_page_render_proof_status')) {
    function tacticum_product_page_render_proof_status(array $item, string $default = 'pilot-artifact'): void
    {
        $status = tacticum_product_page_proof_status($item, $default);
        ?>
        <span class="inline-flex w-fit items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold <?=$status['class']?>" data-product-proof-status="<?=tacticum_product_page_html($status['value'])?>">
            <i class="<?=tacticum_product_page_html($status['icon'])?>"></i>
            <?=tacticum_product_page_html($status['label'])?>
        </span>
        <?php
    }
}

if (!function_exists('tacticum_product_page_render_cards')) {
    function tacticum_product_page_render_cards(array $cards, string $columnsClass = 'lg:grid-cols-3'): void
    {
        if (empty($cards)) {
            return;
        }
        ?>
        <div class="grid grid-cols-1 md:grid-cols-2 <?=$columnsClass?> gap-6">
            <?php foreach ($cards as $card): ?>
                <?php
                if (!is_array($card)) {
                    continue;
                }

                $icon = tacticum_product_page_string($card, 'icon');
                $title = tacticum_product_page_string($card, 'title');
                $text = tacticum_product_page_string($card, 'text');
                $meta = tacticum_product_page_string($card, 'meta');
                $items = is_array($card['items'] ?? null) ? $card['items'] : [];
                ?>
                <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <?php if ($icon !== ''): ?>
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <i class="<?=tacticum_product_page_html($icon)?> text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    <?php if ($meta !== ''): ?>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-primary"><?=tacticum_product_page_html($meta)?></p>
                    <?php endif; ?>
                    <?php if ($title !== ''): ?>
                        <h3 class="mb-3 text-xl font-bold text-secondary"><?=tacticum_product_page_html($title)?></h3>
                    <?php endif; ?>
                    <?php if ($text !== ''): ?>
                        <p class="text-gray-600"><?=tacticum_product_page_html($text)?></p>
                    <?php endif; ?>
                    <?php if (!empty($items)): ?>
                        <ul class="mt-4 space-y-2 text-sm text-gray-600">
                            <?php foreach ($items as $item): ?>
                                <?php if (!is_scalar($item)) { continue; } ?>
                                <li class="flex gap-2">
                                    <i class="ri-check-line mt-0.5 text-primary"></i>
                                    <span><?=tacticum_product_page_html($item)?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

if (!function_exists('tacticum_product_page_render_section')) {
    function tacticum_product_page_render_section(array $section): void
    {
        $theme = tacticum_product_page_string($section, 'theme', 'white');
        $eyebrow = tacticum_product_page_string($section, 'eyebrow');
        $title = tacticum_product_page_string($section, 'title');
        $text = tacticum_product_page_string($section, 'text');
        $cards = is_array($section['cards'] ?? null) ? $section['cards'] : [];
        $columnsClass = tacticum_product_page_string($section, 'columns_class', 'lg:grid-cols-3');
        $sectionClass = $theme === 'muted' ? 'bg-gray-50' : 'bg-white';
        ?>
        <section class="py-16 <?=$sectionClass?>" data-product-block="content-section">
            <div class="container mx-auto px-4">
                <?php if ($eyebrow !== '' || $title !== '' || $text !== ''): ?>
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
                <?php endif; ?>
                <?php tacticum_product_page_render_cards($cards, $columnsClass); ?>
            </div>
        </section>
        <?php
    }
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
                <i class="<?=$iconClass?> text-2xl"></i>
            </div>
            <h3 class="mb-4 text-xl font-bold text-secondary"><?=tacticum_product_page_html($title)?></h3>
            <ul class="space-y-3 text-sm text-gray-700">
                <?php foreach ($items as $item): ?>
                    <?php if (!is_scalar($item)) { continue; } ?>
                    <li class="flex gap-2">
                        <i class="ri-checkbox-circle-line mt-0.5 text-primary"></i>
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
