<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');
header('Content-Type: application/json; charset=UTF-8');

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_prefill');
tacticum_rest_check_csrf(null, true);

$group_id = $_GET['group_id'] ?? '';
$group_id = trim((string)$group_id);
if ($group_id === '' || mb_strlen($group_id) > 64) {
    tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: group_id.');
}

if (!Bitrix\Main\Loader::includeModule('iblock')) {
    tacticum_rest_error(500, 'iblock_missing', 'Модуль инфоблоков не подключен');
}

$iblockId = tacticum_rest_get_iblock_id('offer');
if ($iblockId <= 0) {
    tacticum_rest_error(500, 'iblock_not_configured', 'Инфоблок заявок не настроен.');
}

$arFilter = [
    "IBLOCK_ID" => $iblockId,
    "ACTIVE" => "Y",
    "PROPERTY_GROUP_ID" => $group_id
];
$arSelect = ["ID", "NAME", "PROPERTY_GROUP_ID", "PROPERTY_SUMMARY", "PROPERTY_CLIENT_NAME"];
$res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

if ($ob = $res->Fetch()) {
    $summary = (string)($ob['PROPERTY_SUMMARY_VALUE']['TEXT'] ?? '');
    $clientName = (string)($ob['PROPERTY_CLIENT_NAME_VALUE'] ?? '');
    AddMessage2Log(serialize(tacticum_rest_mask_pii([
        'id' => $ob['ID'] ?? null,
        'group_id' => $ob['PROPERTY_GROUP_ID_VALUE'] ?? '',
        'summary_length' => mb_strlen($summary),
        'client_name' => $clientName,
    ])), "tacticum_prefill_result");

    echo json_encode([
        'success' => true,
        'group_id' => $ob['PROPERTY_GROUP_ID_VALUE'],
        'summary' => $summary,
        'client_name' => $clientName
    ]);
} else {
    tacticum_rest_error(404, 'not_found', 'Данные не найдены');
}
?>
