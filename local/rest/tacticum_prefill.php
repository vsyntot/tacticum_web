<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');
header('Content-Type: application/json; charset=UTF-8');

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_prefill');
tacticum_rest_check_csrf();

$group_id = $_GET['group_id'] ?? '';
$group_id = trim((string)$group_id);
if ($group_id === '' || mb_strlen($group_id) > 64) {
    tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: group_id.');
}

if (!CModule::IncludeModule("iblock")) {
    tacticum_rest_error(500, 'iblock_missing', 'Модуль инфоблоков не подключен');
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
    tacticum_rest_error(404, 'not_found', 'Данные не найдены');
}
?>
