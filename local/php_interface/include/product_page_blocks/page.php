<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
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
        $fitGuide = is_array($page['fit_guide'] ?? null) ? $page['fit_guide'] : [];
        $sections = is_array($page['sections'] ?? null) ? $page['sections'] : [];
        $architecture = is_array($page['architecture'] ?? null) ? $page['architecture'] : [];
        $useCases = is_array($page['use_cases'] ?? null) ? $page['use_cases'] : [];
        $procurement = is_array($page['procurement'] ?? null) ? $page['procurement'] : [];
        $comparison = is_array($page['comparison'] ?? null) ? $page['comparison'] : [];
        $rollout = is_array($page['rollout'] ?? null) ? $page['rollout'] : [];
        $proof = is_array($page['proof'] ?? null) ? $page['proof'] : [];
        $faq = is_array($page['faq'] ?? null) ? $page['faq'] : [];
        $cta = is_array($page['cta'] ?? null) ? $page['cta'] : [];
        $source = tacticum_product_page_string($page, '_source', 'unknown');
        if (!in_array($source, ['bitrix', 'fallback'], true)) {
            $source = 'unknown';
        }
        ?>
        <section class="bg-gradient-to-r from-secondary to-primary pt-24 text-white" data-product-block="hero" data-product-source="<?=tacticum_product_page_html($source)?>">
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

        <?php tacticum_product_page_render_fit_guide($fitGuide); ?>

        <?php foreach ($sections as $section): ?>
            <?php if (is_array($section)) { tacticum_product_page_render_section($section); } ?>
        <?php endforeach; ?>

        <?php tacticum_product_page_render_architecture($architecture); ?>

        <?php tacticum_product_page_render_use_cases($useCases); ?>

        <?php tacticum_product_page_render_comparison($comparison); ?>

        <?php tacticum_product_page_render_procurement($procurement); ?>

        <?php tacticum_product_page_render_rollout($rollout); ?>

        <?php tacticum_product_page_render_proof($proof); ?>

        <?php tacticum_product_page_render_faq($faq); ?>

        <div data-product-block="lead-cta">
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
                    'SCENARIO_LABEL' => tacticum_product_page_string($cta, 'scenario_label', 'Сценарий'),
                    'SCENARIO_EMPTY_LABEL' => tacticum_product_page_string($cta, 'scenario_empty_label', 'Выберите сценарий'),
                    'SCENARIO_OPTIONS' => is_array($cta['scenario_options'] ?? null) ? $cta['scenario_options'] : [],
                    'LEAD_CONTEXT' => is_array($cta['lead_context'] ?? null) ? $cta['lead_context'] : [],
                ],
                false
            );
            ?>
        </div>
        <?php
    }
}
