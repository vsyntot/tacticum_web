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

$iblockId = 14;  # faq
$type = 'faq';

$arFilter = [
    'IBLOCK_ID' => $iblockId,
    'type' => $type,
    'ACTIVE' => 'Y'
];

$arSelect = ['PROPERTY_question', 'PROPERTY_answer'];  # 'PROPERTY_*' не работает 'ID', 'NAME', 'CODE', 'DATE_CREATE', 'PREVIEW_TEXT', 'DETAIL_TEXT',

$res = CIBlockElement::GetList(['SORT'=>'ASC'], $arFilter, false, false, $arSelect);

$items = [];

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();

    // Если вопрос/ответ — HTML, берем 'TEXT', иначе просто VALUE
    $question = isset($fields['PROPERTY_QUESTION_VALUE']['TEXT'])
        ? $fields['PROPERTY_QUESTION_VALUE']['TEXT']
        : $fields['PROPERTY_QUESTION_VALUE'];

    $answer = isset($fields['PROPERTY_ANSWER_VALUE']['TEXT'])
        ? $fields['PROPERTY_ANSWER_VALUE']['TEXT']
        : $fields['PROPERTY_ANSWER_VALUE'];

    // Очищаем спецсимволы, если нужно (например, &quot; → ")
    $question = html_entity_decode($question);
    $answer = html_entity_decode($answer);

    $items[] = [
        'question' => $question,
        'answer'   => $answer,
    ];
}

# while($ob = $res->GetNextElement()){
#    $fields = $ob->GetFields();
#    $props = $ob->GetProperties();
#    $fields['PROPERTIES'] = $props;
#    $items[] = $fields;
#}

tacticum_rest_response(true, 'ok', null, ['items' => $items]);
