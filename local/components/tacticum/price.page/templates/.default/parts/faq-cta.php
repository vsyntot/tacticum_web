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
        "SECTION_KEY" => "price",
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
        "FORM_ID" => "price-cta",
        "FORM_HTML_ID" => "pricing-cta-form",
        "TITLE" => "Нужен состав команды под вашу задачу?",
        "TEXT" => "Опишите, какой результат нужен, текущий этап и желаемый срок. Мы предложим роли, уровень специалистов, формат загрузки и следующий шаг по подключению.",
        "MESSAGE_LABEL" => "Опишите задачу для команды",
        "MESSAGE_PLACEHOLDER" => "Например: нужен MVP личного кабинета с AI-подсказками, старт в июне, команда на 2-3 месяца",
        "BUTTON_TEXT" => "Подобрать команду",
        "LEAD_CONTEXT" => [
            "lead_entry" => "price",
            "lead_page_role" => "team-entry",
            "lead_intent" => "build-managed-team",
            "lead_product" => "ecosystem",
            "lead_scenario" => "product-team",
            "lead_cta" => "price-cta",
            "lead_next_step" => "team-scope-and-staffing",
        ],
    ],
    false
);
?>
