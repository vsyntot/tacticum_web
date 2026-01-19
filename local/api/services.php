<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/rest/rest_helpers.php");

header('Content-Type: application/json; charset=UTF-8');

$iblockId = tacticum_api_bootstrap('services');

$arSelect = ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT'];

$res = tacticum_api_fetch_elements($iblockId, $arSelect);

$items = [];

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();
    $props = $ob->GetProperties();

    $name = tacticum_rest_html_to_text($fields['NAME']);
    $preview = tacticum_rest_html_to_text($fields['PREVIEW_TEXT']);
    $detail = tacticum_rest_html_to_text($fields['DETAIL_TEXT']);

    $item = [
        'name' => $name,
        'preview' => $preview,
        'detail' => $detail,
    ];

    foreach ($props as $propCode => $propValue) {
        $item[strtolower($propCode)] = tacticum_api_normalize_property($propValue);
    }

    $items[] = $item;
}

tacticum_rest_response(true, 'ok', null, ['items' => $items]);
