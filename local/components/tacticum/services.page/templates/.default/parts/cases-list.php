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
        "NEWS_LIST_TEMPLATE" => "cases",
        "IBLOCK_KEY" => "cases",
        "IBLOCK_TYPE" => "company",
        "NEWS_COUNT" => "3",
        "SORT_BY1" => "RAND",
        "SORT_ORDER1" => "DESC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ],
    false
);
?>
