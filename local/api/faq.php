<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/rest/rest_helpers.php");

header('Content-Type: application/json; charset=UTF-8');

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('faq');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    tacticum_rest_error(405, 'method_not_allowed', 'Метод запроса не поддерживается.');
}

if (!CModule::IncludeModule("iblock")) {
    tacticum_rest_error(500, 'iblock_missing', 'Модуль инфоблоков не установлен');
}

$iblockId = tacticum_rest_get_iblock_id('faq', 10);

$arFilter = [
    'IBLOCK_ID' => $iblockId,
    'ACTIVE' => 'Y'
];

$arSelect = ['ID', 'NAME', 'DETAIL_TEXT'];

$res = CIBlockElement::GetList(['SORT'=>'ASC'], $arFilter, false, false, $arSelect);

$items = [];

$parser = new CTextParser();

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();

    $question = $parser->clearAllTags($fields['NAME']);
    $answer = $parser->clearAllTags($fields['DETAIL_TEXT']);

    $items[] = [
        'question' => $question,
        'answer'   => $answer,
    ];
}

tacticum_rest_response(true, 'ok', null, ['items' => $items]);
