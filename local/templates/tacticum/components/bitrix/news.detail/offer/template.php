<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$summaryText = tacticum_sanitize_iblock_html((string)($arResult["PROPERTIES"]["SUMMARY"]["VALUE"]["TEXT"] ?? ""));
$summaryPlain = trim(strip_tags($summaryText));
$decodeList = static fn($items) => array_filter(
    array_map(static fn($value) => tacticum_decode_iblock_text((string)$value), (array)$items),
    "strlen"
);
$goals = $decodeList($arResult["PROPERTIES"]["GOALS"]["VALUE"] ?? []);
$functionalRequirements = $decodeList($arResult["PROPERTIES"]["FUNCTIONAL_REQUIREMENTS"]["VALUE"] ?? []);
$nonfunctionalRequirements = $decodeList($arResult["PROPERTIES"]["NONFUNCTIONAL_REQUIREMENTS"]["VALUE"] ?? []);
$teamMembers = $decodeList($arResult["PROPERTIES"]["TEAM"]["VALUE"] ?? []);
$stackItems = $decodeList($arResult["PROPERTIES"]["STACK"]["VALUE"] ?? []);
$budgetRaw = tacticum_decode_iblock_text((string)($arResult["PROPERTIES"]["BUDGET"]["VALUE"] ?? ""));
$timelineRaw = tacticum_decode_iblock_text((string)($arResult["PROPERTIES"]["TIMELINE"]["VALUE"] ?? ""));
$budget = htmlspecialcharsbx($budgetRaw);
$timeline = htmlspecialcharsbx($timelineRaw);
$offerResponse = function_exists('tacticum_offer_catalog_response')
    ? tacticum_offer_catalog_response((array)($arResult['PROPERTIES'] ?? []))
    : [];
$offerSector = tacticum_decode_iblock_text((string)($offerResponse['sector'] ?? ($arResult['SECTION_NAME'] ?? '')));
$offerScenario = tacticum_decode_iblock_text((string)($offerResponse['scenario'] ?? ''));
$offerH1 = trim(tacticum_decode_iblock_text((string)($arResult["PROPERTIES"]["H1"]["VALUE"] ?? "")));
if ($offerH1 === "") {
    $offerH1 = trim(tacticum_decode_iblock_text((string)($arResult["PROPERTIES"]["TITLE"]["VALUE"] ?? "")));
}
if ($offerH1 === "") {
    $offerH1 = "Предварительная оценка вашего проекта";
}
$projectInfoLines = array_filter([
    $summaryPlain !== "" ? "Краткое описание: {$summaryPlain}" : "",
    !empty($goals) ? "Цели MVP: " . implode(", ", $goals) : "",
    !empty($functionalRequirements) ? "Функциональные требования: " . implode(", ", $functionalRequirements) : "",
    !empty($nonfunctionalRequirements) ? "Нефункциональные требования: " . implode(", ", $nonfunctionalRequirements) : "",
    !empty($teamMembers) ? "Команда: " . implode(", ", $teamMembers) : "",
    !empty($stackItems) ? "Стек: " . implode(", ", $stackItems) : "",
    $budgetRaw !== "" ? "Бюджет: {$budgetRaw}" : "",
    $timelineRaw !== "" ? "Срок: {$timelineRaw}" : "",
], "strlen");
$projectInfo = htmlspecialcharsbx(implode("\n", $projectInfoLines));

foreach ([
    'summary-estimate',
    'product-context',
    'risks',
    'cta',
    'reasons',
    'faq',
] as $offerDetailPart) {
    require __DIR__ . '/parts/' . $offerDetailPart . '.php';
}
