<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/json; charset=UTF-8');

$group_id = $_GET['group_id'] ?? '';
if (!$group_id) {
    echo json_encode(['error' => 'Нет group_id']);
    exit;
}

if (!CModule::IncludeModule("iblock")) {
    echo json_encode(['error' => 'Модуль инфоблоков не подключен']);
    exit;
}

$iblockId = 5;
$arFilter = [
    "IBLOCK_ID" => $iblockId,
    "ACTIVE" => "Y",
    "PROPERTY_GROUP_ID" => $group_id
];
$arSelect = ["ID", "NAME", "PROPERTY_GROUP_ID", "PROPERTY_SUMMARY", "PROPERTY_CLIENT_NAME"];
$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

if ($ob = $res->Fetch()) {
    AddMessage2Log(serialize($ob), "sale_prefill");

    echo json_encode([
        'success' => true,
        'group_id' => $ob['PROPERTY_GROUP_ID_VALUE'],
        'summary' => $ob['PROPERTY_SUMMARY_VALUE']['TEXT'],
        'client_name' => $ob['PROPERTY_CLIENT_NAME_VALUE']
    ]);
} else {
    echo json_encode(['error' => 'Данные не найдены']);
}
?>
