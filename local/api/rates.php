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

$arFilter = [
    'IBLOCK_ID' => $iblockId,
    'ACTIVE' => 'Y'
];

$arSelect = ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME'];

$res = CIBlockElement::GetList(['SORT'=>'ASC'], $arFilter, false, false, $arSelect);

$items = [];

$parser = new CTextParser();

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();
    $props = $ob->GetProperties();

    $name = $parser->clearAllTags($fields['NAME']);
    $item = [];
    $item['name'] = $name;

    foreach($props as $propCode => $propValue) {
        $item[$propCode] = $propValue['VALUE'];
    }

    $sectionLinks = CIBlockElement::GetElementGroups(
        $fields['ID'],
        true,
        ['ID', 'NAME', 'CODE', 'IBLOCK_ID']
    );
    $sections = [];
    $sectionIds = [];
    while ($section = $sectionLinks->Fetch()) {
        $sectionId = (int)$section['ID'];
        $sections[] = [
            'id' => $sectionId,
            'name' => $section['NAME'],
            'code' => $section['CODE'],
        ];
        $sectionIds[] = $sectionId;
    }

    if (!empty($sectionIds)) {
        $activeSections = [];
        $sectionRes = CIBlockSection::GetList(
            [],
            ['ID' => $sectionIds, 'ACTIVE' => 'Y'],
            false,
            ['ID']
        );
        while ($section = $sectionRes->Fetch()) {
            $activeSections[(int)$section['ID']] = true;
        }

        $filteredSections = [];
        foreach ($sections as $section) {
            if (isset($activeSections[$section['id']])) {
                $filteredSections[] = $section;
            }
        }
        $sections = $filteredSections;
    }

    $item['sections'] = $sections;

    $items[] = $item;
}

// Response contract: each item includes name, properties, and sections (array of {id, name, code}).
tacticum_rest_response(true, 'ok', null, ['items' => $items]);
