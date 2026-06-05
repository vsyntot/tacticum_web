<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div id="price-list">
<?php
$APPLICATION->IncludeComponent(
    "tacticum:content.list",
    "",
    [
        "NEWS_LIST_TEMPLATE" => "price",
        "IBLOCK_KEY" => "rates",
        "IBLOCK_TYPE" => "services",
        "NEWS_COUNT" => "9999",
        "SORT_BY1" => "SORT",
        "SORT_ORDER1" => "ASC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "PROPERTY_CODE" => ["LEVEL", "PRICE", "OPTIONS", "POPULAR"],
        "DISPLAY_BOTTOM_PAGER" => "N",
    ],
    false
);
?>
</div>
