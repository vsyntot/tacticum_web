<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/rest/rest_helpers.php");

header('Content-Type: application/json; charset=UTF-8');

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('services');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    tacticum_rest_error(405, 'method_not_allowed', 'Метод запроса не поддерживается.');
}

if (!CModule::IncludeModule("iblock")) {
    tacticum_rest_error(500, 'iblock_missing', 'Модуль инфоблоков не установлен');
}

$iblockId = 12;
$type = 'services';

$arFilter = [
    'IBLOCK_ID' => $iblockId,
    'type' => $type,
    'ACTIVE' => 'Y'
];

$arSelect = ['PREVIEW_TEXT', 'NAME'];

$res = CIBlockElement::GetList(['SORT'=>'ASC'], $arFilter, false, false, $arSelect);

$items = [];

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();

    $description = isset($fields['PREVIEW_TEXT']['TEXT'])
        ? $fields['PREVIEW_TEXT']['TEXT']
        : $fields['PREVIEW_TEXT'];

    $name = isset($fields['NAME']['TEXT'])
        ? $fields['NAME']['TEXT']
        : $fields['NAME'];

    $description = html_entity_decode($description);
    $name = html_entity_decode($name);

    $items[] = [
        'description' => $description,
        'name'   => $name,
    ];
}

tacticum_rest_response(true, 'ok', null, ['items' => $items]);