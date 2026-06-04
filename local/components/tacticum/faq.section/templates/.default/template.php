<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

if (($arResult['FAQ_SECTION_STATUS'] ?? '') === 'missing') {
    ?>
    <section class="hidden" data-faq-section-key="<?=htmlspecialcharsbx((string)($arResult['FAQ_SECTION_KEY'] ?? ''))?>" data-faq-section-status="missing"></section>
    <?php
    return;
}

$APPLICATION->IncludeComponent(
    'bitrix:news.list',
    'faq',
    $arResult['NEWS_LIST_PARAMS'],
    $component
);
