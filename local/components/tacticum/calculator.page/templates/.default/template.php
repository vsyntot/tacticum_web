<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;
?>

<?php
if (function_exists('tacticum_page_content_render_if_live')) {
    tacticum_page_content_render_if_live('/calculator/', 'calculator-outcome-cards');
}

// Fallback body retired after owner-approved page-content fallback retirement.
// Retired page-content fallback: tacticum_page_content_render_if_live('/calculator/', 'calculator-outcome-cards').
?>

<?php
if (function_exists('tacticum_page_content_render_if_live')) {
    tacticum_page_content_render_if_live('/calculator/', 'product-aware-estimate-cards');
}

// Fallback body retired after owner-approved page-content fallback retirement.
// Retired page-content fallback: tacticum_page_content_render_if_live('/calculator/', 'product-aware-estimate-cards').
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => $arResult['FAQ_IBLOCK_ID'],
        "SECTION_KEY" => "calculator",
    ],
    false
);
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:lead.cta",
    "",
    [
        "TYPE" => "personal-offer",
        "FORM_ID" => "calculator-cta",
        "TITLE" => "Уточнить предварительную оценку",
        "TEXT" => "Пришлите вводные из калькулятора или краткое описание задачи. Мы проверим гипотезы, зададим уточняющие вопросы и подготовим следующий шаг к точной смете.",
        "MESSAGE_LABEL" => "Вводные для уточнения оценки",
        "MESSAGE_PLACEHOLDER" => "Например: нужен AI-модуль для обработки заявок, CRM уже есть, данные за 2 года",
        "BUTTON_TEXT" => "Уточнить оценку",
        "LEAD_CONTEXT" => [
            "lead_entry" => "calculator",
            "lead_page_role" => "estimate-entry",
            "lead_intent" => "clarify-budget-timeline-team",
            "lead_product" => "ecosystem",
            "lead_scenario" => "product-estimate",
            "lead_cta" => "calculator-cta",
            "lead_next_step" => "estimate-review",
        ],
    ],
    false
);
?>
