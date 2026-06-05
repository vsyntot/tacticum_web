<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

$page = is_array($arResult['PAGE'] ?? null) ? $arResult['PAGE'] : [];
$cta = is_array($arResult['CTA'] ?? null) ? $arResult['CTA'] : [];
$unavailable = (bool)($arResult['UNAVAILABLE'] ?? false);
$defaults = $unavailable
    ? [
        'form_id' => 'product-unavailable',
        'title' => 'Уточнить продуктовый сценарий',
        'text' => 'Напишите, какой продукт или сценарий вам нужен. Мы ответим без публикации неподтвержденных материалов на сайте.',
        'form_title' => 'Заявка на уточнение',
        'button_text' => 'Отправить запрос',
    ]
    : [
        'form_id' => 'product-cta',
        'title' => 'Обсудим пилот и следующий шаг',
        'text' => 'Опишите задачу, контур и ожидаемый результат. Мы вернемся с форматом пилота, внедрения или архитектурной консультации.',
        'form_title' => 'Заявка на обсуждение',
        'button_text' => 'Обсудить пилот',
    ];
?>
<div data-product-block="lead-cta">
    <?php
    $APPLICATION->IncludeComponent(
        'tacticum:lead.cta',
        '',
        [
            'TYPE' => 'project-discussion',
            'VISUAL_VARIANT' => 'glass',
            'SECTION_ID' => 'contact-form',
            'FORM_ID' => tacticum_product_page_string($cta, 'form_id', $defaults['form_id']),
            'FIELD_PREFIX' => tacticum_product_page_string($cta, 'field_prefix', 'product'),
            'TITLE' => tacticum_product_page_string($cta, 'title', $defaults['title']),
            'TEXT' => tacticum_product_page_string($cta, 'text', $defaults['text']),
            'FORM_TITLE' => tacticum_product_page_string($cta, 'form_title', $defaults['form_title']),
            'MESSAGE_LABEL' => tacticum_product_page_string($cta, 'message_label', 'Что хотите проверить или внедрить'),
            'MESSAGE_PLACEHOLDER' => tacticum_product_page_string($cta, 'message_placeholder', 'Кратко опишите задачу, системы, ограничения и желаемый следующий шаг'),
            'BUTTON_TEXT' => tacticum_product_page_string($cta, 'button_text', $defaults['button_text']),
            'SHOW_QUALIFICATION' => 'Y',
            'SCENARIO_LABEL' => tacticum_product_page_string($cta, 'scenario_label', 'Сценарий'),
            'SCENARIO_EMPTY_LABEL' => tacticum_product_page_string($cta, 'scenario_empty_label', 'Выберите сценарий'),
            'SCENARIO_OPTIONS' => tacticum_product_page_cta_scenario_options($cta),
            'LEAD_CONTEXT' => tacticum_product_page_cta_lead_context($page, $cta),
        ],
        $component
    );
    ?>
</div>
