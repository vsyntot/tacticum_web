<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

$APPLICATION->IncludeComponent(
    'tacticum:offer.catalog',
    '',
    [
        'IBLOCK_ID' => (int)($arResult['IBLOCK_ID'] ?? 0),
        'FILTERS' => is_array($arResult['FILTERS'] ?? null) ? $arResult['FILTERS'] : [],
        'PER_PAGE' => (int)($arResult['PER_PAGE'] ?? 24),
    ],
    $component ?? false
);
