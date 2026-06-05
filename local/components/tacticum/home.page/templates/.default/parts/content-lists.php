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
        "SORT_ORDER1" => "ASC",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ],
    false
);
?>

<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <?php
        $APPLICATION->IncludeComponent(
            "tacticum:content.list",
            "",
            [
                "NEWS_LIST_TEMPLATE" => "feedback",
                "IBLOCK_KEY" => "feedback",
                "IBLOCK_TYPE" => "company",
                "NEWS_COUNT" => "3",
                "SORT_BY1" => "SORT",
                "SORT_ORDER1" => "ASC",
                "FIELD_CODE" => ["ID", "CODE", "NAME", "SORT", "DETAIL_TEXT", "IBLOCK_TYPE_ID", "IBLOCK_ID"],
                "PROPERTY_CODE" => ["NAME", "COMPANY", "POSITION", "RATING"],
                "DISPLAY_BOTTOM_PAGER" => "Y",
            ],
            false
        );
        ?>
    </div>
</section>
