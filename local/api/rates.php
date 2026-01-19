<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/rest/rest_helpers.php");

header('Content-Type: application/json; charset=UTF-8');

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('rates');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    tacticum_rest_error(405, 'method_not_allowed', 'Метод запроса не поддерживается.');
}

if (!CModule::IncludeModule("iblock")) {
    tacticum_rest_error(500, 'iblock_missing', 'Модуль инфоблоков не установлен');
}

$iblockId = tacticum_rest_get_iblock_id('rates', 11);
$type = 'services';

$arFilter = [
    'IBLOCK_ID' => $iblockId,
    'type' => $type,
    'ACTIVE' => 'Y'
];

$arSelect = ['PROPERTY_PRICE', 'NAME'];

$res = CIBlockElement::GetList(['SORT'=>'ASC'], $arFilter, false, false, $arSelect);

$items = [];

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();

    $price = tacticum_rest_extract_text($fields['PROPERTY_PRICE_VALUE'] ?? '');
    $name = tacticum_rest_extract_text($fields['NAME'] ?? '');

    $price = html_entity_decode($price);
    $name = html_entity_decode($name);

    $items[] = [
        'price' => $price,
        'name'   => $name,
    ];
}

tacticum_rest_response(true, 'ok', null, ['items' => $items]);
