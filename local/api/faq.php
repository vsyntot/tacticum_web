<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/rest/rest_helpers.php");

header('Content-Type: application/json; charset=UTF-8');

$iblockId = tacticum_api_bootstrap('faq');

$arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_TEXT'];

$res = tacticum_api_fetch_elements($iblockId, $arSelect);

$items = [];

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();
    $props = $ob->GetProperties();

    $question = tacticum_rest_html_to_text($fields['NAME']);
    $answer = tacticum_rest_html_to_text($fields['DETAIL_TEXT']);

    $item = [
        'question' => $question,
        'answer'   => $answer,
    ];

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

    foreach($props as $propCode => $propValue) {
        $item[strtolower($propCode)] = $propValue['VALUE'];
    }

    $items[] = $item;
}

tacticum_rest_response(true, 'ok', null, ['items' => $items]);
