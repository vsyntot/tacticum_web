<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

$template = (string)($arResult['NEWS_LIST_TEMPLATE'] ?? '');
if ($template === '') {
    return;
}

$APPLICATION->IncludeComponent(
    'bitrix:news.list',
    $template,
    is_array($arResult['NEWS_LIST_PARAMS'] ?? null) ? $arResult['NEWS_LIST_PARAMS'] : [],
    $component
);
