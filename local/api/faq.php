<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=UTF-8');

if (!CModule::IncludeModule("iblock")) {
    echo json_encode(['error' => 'Модуль инфоблоков не установлен']);
    exit;
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

echo json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
