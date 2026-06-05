<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_render_product_page')) {
    function tacticum_render_product_page(array $page): void
    {
        global $APPLICATION;

        if (($page['_status'] ?? '') === 'unavailable') {
            tacticum_render_product_page_unavailable($page);
            return;
        }

        $fitGuide = is_array($page['fit_guide'] ?? null) ? $page['fit_guide'] : [];
        $sections = is_array($page['sections'] ?? null) ? $page['sections'] : [];
        $architecture = is_array($page['architecture'] ?? null) ? $page['architecture'] : [];
        $useCases = is_array($page['use_cases'] ?? null) ? $page['use_cases'] : [];
        $procurement = is_array($page['procurement'] ?? null) ? $page['procurement'] : [];
        $comparison = is_array($page['comparison'] ?? null) ? $page['comparison'] : [];
        $rollout = is_array($page['rollout'] ?? null) ? $page['rollout'] : [];
        $proof = is_array($page['proof'] ?? null) ? $page['proof'] : [];
        $faq = is_array($page['faq'] ?? null) ? $page['faq'] : [];
        $APPLICATION->IncludeComponent(
            'tacticum:product.hero',
            '',
            ['PAGE_DATA' => $page],
            false
        );
        ?>

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

        <?php
        $APPLICATION->IncludeComponent(
            'tacticum:product.lead.cta',
            '',
            ['PAGE_DATA' => $page],
            false
        );
        ?>
        <?php
    }
}

if (!function_exists('tacticum_render_product_page_unavailable')) {
    function tacticum_render_product_page_unavailable(array $page): void
    {
        global $APPLICATION;

        $APPLICATION->IncludeComponent(
            'tacticum:product.hero',
            '',
            ['PAGE_DATA' => $page],
            false
        );
        $APPLICATION->IncludeComponent(
            'tacticum:product.lead.cta',
            '',
            [
                'PAGE_DATA' => $page,
                'UNAVAILABLE' => 'Y',
            ],
            false
        );
    }
}
