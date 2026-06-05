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
        "TYPE" => "personal-offer",
        "FORM_ID" => "contacts-cta",
        "FORM_HTML_ID" => "contacts-cta-form",
        "VARIANT" => "solid",
        "TITLE" => "Расскажите, какой следующий шаг вам нужен",
        "TEXT" => "Можно начать с консультации, предварительной оценки, подбора команды или прототипа AI-бота. Опишите задачу, а мы направим обращение к нужному специалисту.",
        "MESSAGE_LABEL" => "Ваш вопрос или задача",
        "MESSAGE_PLACEHOLDER" => "Например: нужна оценка AI-проекта, команда на MVP или консультация по Telegram-боту",
        "BUTTON_TEXT" => "Отправить обращение",
        "LEAD_CONTEXT" => [
            "lead_entry" => "contacts",
            "lead_page_role" => "contact-entry",
            "lead_intent" => "route-request-to-next-step",
            "lead_product" => "ecosystem",
            "lead_scenario" => "contact-routing",
            "lead_cta" => "contacts-cta",
            "lead_next_step" => "request-routing",
        ],
    ],
    false
);
?>
