<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/rest/rest_helpers.php");

header('Content-Type: application/json; charset=UTF-8');
tacticum_rest_send_noindex_header();

$iblockId = tacticum_api_bootstrap('services');

$payload = tacticum_api_cached_payload('services', $iblockId, static function () use ($iblockId): array {
    $arSelect = ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT'];

    $items = [];
    foreach (tacticum_api_fetch_content_items($iblockId, $arSelect) as $row) {
        $fields = is_array($row['fields'] ?? null) ? $row['fields'] : [];
        $props = is_array($row['properties'] ?? null) ? $row['properties'] : [];

        $name = tacticum_rest_html_to_text((string)($fields['NAME'] ?? ''));
        $preview = tacticum_rest_html_to_text((string)($fields['PREVIEW_TEXT'] ?? ''));
        $detail = tacticum_rest_html_to_text((string)($fields['DETAIL_TEXT'] ?? ''));

        $item = [
            'name' => $name,
            'preview' => $preview,
            'detail' => $detail,
        ];

        foreach ($props as $propCode => $propValue) {
            $item[strtolower($propCode)] = tacticum_api_normalize_property($propValue);
        }

        $items[] = $item;
    }

    return ['items' => $items];
});

tacticum_rest_response(true, 'ok', null, $payload);
