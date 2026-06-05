<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => $arResult['FAQ_IBLOCK_ID'],
        "SECTION_KEY" => "home",
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
        "FORM_ID" => "home-cta",
        "FORM_HTML_ID" => "cta-form",
        "TITLE" => "Подберем правильный вход в экосистему Tacticum",
        "TEXT" => "Опишите бизнес-задачу, желаемый срок и текущие ограничения. Мы свяжемся, уточним вводные и предложим следующий шаг: продуктовый пилот, discovery, расчет или подбор команды.",
        "MESSAGE_LABEL" => "Кратко опишите задачу",
        "MESSAGE_PLACEHOLDER" => "Например: хотим запустить AI-ассистента, нужен RAG по документам или нужно упорядочить AI-assisted разработку",
        "BUTTON_TEXT" => "Получить следующий шаг",
        "LEAD_CONTEXT" => [
            "lead_entry" => "home",
            "lead_page_role" => "ecosystem-router",
            "lead_intent" => "choose-product-or-commercial-entry",
            "lead_product" => "ecosystem",
            "lead_scenario" => "product-routing",
            "lead_cta" => "home-cta",
            "lead_next_step" => "product-discovery-or-project-estimate",
        ],
    ],
    false
);
?>
