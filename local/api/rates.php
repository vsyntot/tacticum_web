<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=UTF-8');

if (!CModule::IncludeModule("iblock")) {
    echo json_encode(['error' => 'Модуль инфоблоков не установлен']);
    exit;
}

$iblockId = 11;  # ставки
$type = 'services';

$arFilter = [
    'IBLOCK_ID' => $iblockId,
    'type' => $type,
    'ACTIVE' => 'Y'
];

$arSelect = ['PROPERTY_PRICE', 'NAME'];  # 'PROPERTY_*' не работает 'ID', 'NAME', 'CODE', 'DATE_CREATE', 'PREVIEW_TEXT', 'DETAIL_TEXT',

$res = CIBlockElement::GetList(['SORT'=>'ASC'], $arFilter, false, false, $arSelect);

$items = [];

while ($ob = $res->GetNextElement()) {
    $fields = $ob->GetFields();

    // Если вопрос/ответ — HTML, берем 'TEXT', иначе просто VALUE
    $price = isset($fields['PROPERTY_PRICE_VALUE']['TEXT'])
        ? $fields['PROPERTY_PRICE_VALUE']['TEXT']
        : $fields['PROPERTY_PRICE_VALUE'];

    $name = isset($fields['NAME']['TEXT'])
        ? $fields['NAME']['TEXT']
        : $fields['NAME'];

    // Очищаем спецсимволы, если нужно (например, &quot; → ")
    $price = html_entity_decode($price);
    $name = html_entity_decode($name);

    $items[] = [
        'price' => $price,
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
