<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:content.list",
    "",
    [
        "NEWS_LIST_TEMPLATE" => "services",
        "IBLOCK_KEY" => "services",
        "IBLOCK_TYPE" => "services",
        "NEWS_COUNT" => "6",
        "SORT_BY1" => "SORT",
        "SORT_ORDER1" => "ASC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "PROPERTY_CODE" => ["OPTIONS", "CLASS", "LINK", "LINKTEXT", "PRODUCT"],
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ],
    false
);
?>
