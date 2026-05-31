<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

$template = (string)($arResult['DETAIL_TEMPLATE'] ?? '');
if ($template === '') {
    return;
}

$APPLICATION->IncludeComponent(
    'bitrix:news.detail',
    $template,
    is_array($arResult['DETAIL_PARAMS'] ?? null) ? $arResult['DETAIL_PARAMS'] : [],
    $component
);
