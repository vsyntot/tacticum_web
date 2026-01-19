<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/rest/rest_helpers.php");

header('Content-Type: application/json; charset=UTF-8');

$iblockId = tacticum_api_bootstrap('cases');

$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT'];

$res = tacticum_api_fetch_elements($iblockId, $arSelect);

$items = [];

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();
    $props = $ob->GetProperties();

    $name = tacticum_rest_html_to_text($fields['NAME']);
    $preview = tacticum_rest_html_to_text($fields['PREVIEW_TEXT']);
    $detail = tacticum_rest_html_to_text($fields['DETAIL_TEXT']);

    $item['name'] = $name;

    $sectionLinks = CIBlockElement::GetElementGroups(
        $fields['ID'],
        true,
        ['ID', 'NAME', 'CODE', 'IBLOCK_ID']
    );
    $sections = [];
    while ($section = $sectionLinks->Fetch()) {
        $sections[] = $section['NAME'];
    }
    $item['sections'] = $sections;
    $item['preview'] = $preview;
    $item['detail'] = $detail;

    foreach($props as $propCode => $propValue) {
        $item[strtolower($propCode)] = $propValue['VALUE'];
    }

    $items[] = $item;
}

tacticum_rest_response(true, 'ok', null, ['items' => $items]);
