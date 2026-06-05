<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/local/rest/rest_helpers.php");

header('Content-Type: application/json; charset=UTF-8');
tacticum_rest_send_noindex_header();

$iblockId = tacticum_api_bootstrap('faq');

$payload = tacticum_api_cached_payload('faq', $iblockId, static function () use ($iblockId): array {
    $arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_TEXT'];

    $items = [];
    foreach (tacticum_api_fetch_content_items($iblockId, $arSelect) as $row) {
        $fields = is_array($row['fields'] ?? null) ? $row['fields'] : [];
        $props = is_array($row['properties'] ?? null) ? $row['properties'] : [];

        $question = tacticum_rest_html_to_text((string)($fields['NAME'] ?? ''));
        $answer = tacticum_rest_html_to_text((string)($fields['DETAIL_TEXT'] ?? ''));

        $item = [
            'question' => $question,
            'answer'   => $answer,
        ];
        $item['sections'] = is_array($row['sections'] ?? null) ? $row['sections'] : [];

        foreach ($props as $propCode => $propValue) {
            $item[strtolower($propCode)] = tacticum_api_normalize_property($propValue);
        }

        $items[] = $item;
    }

    return ['items' => $items];
});

tacticum_rest_response(true, 'ok', null, $payload);
