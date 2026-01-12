<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=UTF-8');

if (!CModule::IncludeModule("iblock")) {
    echo json_encode(['error' => 'Модуль инфоблоков не установлен']);
    exit;
}

$iblockId = 12;  # сервисы
$type = 'services';

$arFilter = [
    'IBLOCK_ID' => $iblockId,
    'type' => $type,
    'ACTIVE' => 'Y'
];

$arSelect = ['PREVIEW_TEXT', 'NAME'];  # 'PROPERTY_*' не работает 'ID', 'NAME', 'CODE', 'DATE_CREATE', 'PREVIEW_TEXT', 'DETAIL_TEXT',

$res = CIBlockElement::GetList(['SORT'=>'ASC'], $arFilter, false, false, $arSelect);

$items = [];

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();

    // Если вопрос/ответ — HTML, берем 'TEXT', иначе просто VALUE
    $description = isset($fields['PREVIEW_TEXT']['TEXT'])
        ? $fields['PREVIEW_TEXT']['TEXT']
        : $fields['PREVIEW_TEXT'];

    $name = isset($fields['NAME']['TEXT'])
        ? $fields['NAME']['TEXT']
        : $fields['NAME'];

    // Очищаем спецсимволы, если нужно (например, &quot; → ")
    $description = html_entity_decode($description);
    $name = html_entity_decode($name);

    $items[] = [
        'description' => $description,
        'name'   => $name,
    ];
}

# while($ob = $res->GetNextElement()){
#    $fields = $ob->GetFields();
#    $props = $ob->GetProperties();
#    $fields['PROPERTIES'] = $props;
#    $items[] = $fields;
#}

echo json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
