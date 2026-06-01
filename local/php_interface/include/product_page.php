<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_string')) {
    function tacticum_product_page_string(array $data, string $key, string $default = ''): string
    {
        $value = $data[$key] ?? $default;

        if (is_scalar($value)) {
            return trim((string)$value);
        }

        return $default;
    }
}

if (!function_exists('tacticum_product_page_html')) {
    function tacticum_product_page_html($value): string
    {
        return htmlspecialcharsbx((string)$value);
    }
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
        <section class="py-16 <?=$sectionClass?>">
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
        <section class="bg-secondary py-16 text-white">
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

if (!function_exists('tacticum_render_product_page')) {
    function tacticum_render_product_page(array $page): void
    {
        global $APPLICATION;

        $eyebrow = tacticum_product_page_string($page, 'eyebrow', 'Tacticum product');
        $title = tacticum_product_page_string($page, 'title');
        $lead = tacticum_product_page_string($page, 'lead');
        $primaryCtaText = tacticum_product_page_string($page, 'primary_cta_text', 'Обсудить пилот');
        $secondaryCtaText = tacticum_product_page_string($page, 'secondary_cta_text', 'Смотреть внедрение');
        $secondaryCtaHref = tacticum_product_page_string($page, 'secondary_cta_href', '/services/');
        $badges = is_array($page['badges'] ?? null) ? $page['badges'] : [];
        $heroCards = is_array($page['hero_cards'] ?? null) ? $page['hero_cards'] : [];
        $sections = is_array($page['sections'] ?? null) ? $page['sections'] : [];
        $architecture = is_array($page['architecture'] ?? null) ? $page['architecture'] : [];
        $cta = is_array($page['cta'] ?? null) ? $page['cta'] : [];
        ?>
        <section class="bg-gradient-to-r from-secondary to-primary pt-24 text-white">
            <div class="container mx-auto px-4 py-20">
                <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-[1.08fr_0.92fr]">
                    <div>
                        <p class="mb-4 text-sm font-semibold uppercase tracking-wide text-blue-200"><?=tacticum_product_page_html($eyebrow)?></p>
                        <h1 class="mb-6 text-4xl font-bold leading-tight md:text-5xl"><?=tacticum_product_page_html($title)?></h1>
                        <p class="mb-8 max-w-3xl text-lg text-blue-100 md:text-xl"><?=tacticum_product_page_html($lead)?></p>
                        <div class="mb-8 flex flex-col gap-3 sm:flex-row">
                            <a href="#contact-form" class="inline-flex items-center justify-center rounded-button bg-white px-8 py-3 font-medium text-primary transition-colors hover:bg-white/90">
                                <?=tacticum_product_page_html($primaryCtaText)?>
                            </a>
                            <a href="<?=tacticum_product_page_html($secondaryCtaHref)?>" class="inline-flex items-center justify-center rounded-button border border-white/30 bg-white/10 px-8 py-3 font-medium text-white transition-colors hover:bg-white/20">
                                <?=tacticum_product_page_html($secondaryCtaText)?>
                            </a>
                        </div>
                        <?php tacticum_product_page_render_badges($badges); ?>
                    </div>
                    <?php if (!empty($heroCards)): ?>
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

        <?php foreach ($sections as $section): ?>
            <?php if (is_array($section)) { tacticum_product_page_render_section($section); } ?>
        <?php endforeach; ?>

        <?php tacticum_product_page_render_architecture($architecture); ?>

        <?php
        $APPLICATION->IncludeComponent(
            'tacticum:lead.cta',
            '',
            [
                'TYPE' => 'project-discussion',
                'VISUAL_VARIANT' => 'glass',
                'SECTION_ID' => 'contact-form',
                'FORM_ID' => tacticum_product_page_string($cta, 'form_id', 'product-cta'),
                'FIELD_PREFIX' => tacticum_product_page_string($cta, 'field_prefix', 'product'),
                'TITLE' => tacticum_product_page_string($cta, 'title', 'Обсудим пилот и следующий шаг'),
                'TEXT' => tacticum_product_page_string($cta, 'text', 'Опишите задачу, контур и ожидаемый результат. Мы вернемся с форматом пилота, внедрения или архитектурной консультации.'),
                'FORM_TITLE' => tacticum_product_page_string($cta, 'form_title', 'Заявка на обсуждение'),
                'MESSAGE_LABEL' => tacticum_product_page_string($cta, 'message_label', 'Что хотите проверить или внедрить'),
                'MESSAGE_PLACEHOLDER' => tacticum_product_page_string($cta, 'message_placeholder', 'Кратко опишите задачу, системы, ограничения и желаемый следующий шаг'),
                'BUTTON_TEXT' => tacticum_product_page_string($cta, 'button_text', 'Обсудить пилот'),
                'SHOW_QUALIFICATION' => 'Y',
                'LEAD_CONTEXT' => is_array($cta['lead_context'] ?? null) ? $cta['lead_context'] : [],
            ],
            false
        );
    }
}
