<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:content.list",
    "",
    [
        "NEWS_LIST_TEMPLATE" => "aiagents",
        "IBLOCK_ID" => (string)$arResult['AIAGENTS_IBLOCK_ID'],
        "IBLOCK_TYPE" => "services",
        "NEWS_COUNT" => "3",
        "SORT_BY1" => "RAND",
        "SORT_ORDER1" => "ASC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "PREVIEW_PICTURE", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "PROPERTY_CODE" => ["LINK", "PRODUCT"],
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ],
    $component
);
?>
