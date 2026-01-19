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

$iblockId = tacticum_rest_get_iblock_id('services');

$arFilter = [
    'IBLOCK_ID' => $iblockId,
    'ACTIVE' => 'Y'
];

$arSelect = ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT'];

$res = CIBlockElement::GetList(['SORT'=>'ASC'], $arFilter, false, false, $arSelect);

$items = [];

$parser = new CTextParser();

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();
    $props = $ob->GetProperties();

    $name = $parser->clearAllTags($fields['NAME']);
    $preview = tacticum_rest_html_to_text($fields['PREVIEW_TEXT']);
    $detail = tacticum_rest_html_to_text($fields['DETAIL_TEXT']);

    $item = [
        'name' => $name,
        'preview' => $preview,
        'detail' => $detail,
    ];

    foreach($props as $propCode => $propValue) {
        $item[strtolower($propCode)] = $propValue['VALUE'];
    }

    $items[] = $item;
}

tacticum_rest_response(true, 'ok', null, ['items' => $items]);
