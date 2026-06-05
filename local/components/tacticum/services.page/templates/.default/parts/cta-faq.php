<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:lead.cta",
    "",
    [
        "TYPE" => "project-discussion",
        "FORM_ID" => "services-cta",
        "FORM_HTML_ID" => "services-cta-form",
        "FIELD_PREFIX" => "services",
        "TITLE" => "Обсудим внедрение AI-решения",
        "TEXT" => "Оставьте задачу, которую нужно автоматизировать или усилить AI. Мы уточним данные, ограничения, интеграции и предложим ближайший рабочий шаг.",
        "FORM_TITLE" => "Заявка на обсуждение внедрения",
        "MESSAGE_LABEL" => "Какая бизнес-задача требует решения",
        "MESSAGE_PLACEHOLDER" => "Например: хотим снизить ручную обработку документов в отделе продаж",
        "BUTTON_TEXT" => "Обсудить внедрение",
        "LEAD_CONTEXT" => [
            "lead_entry" => "services",
            "lead_page_role" => "implementation-entry",
            "lead_intent" => "ai-automation-delivery",
            "lead_product" => "ecosystem",
            "lead_scenario" => "product-delivery",
            "lead_cta" => "services-cta",
            "lead_next_step" => "discovery-call",
        ],
    ],
    false
);
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => $arResult['FAQ_IBLOCK_ID'],
        "SECTION_KEY" => "services",
    ],
    false
);
?>
