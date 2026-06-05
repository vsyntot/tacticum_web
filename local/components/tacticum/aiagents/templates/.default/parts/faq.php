<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => $arResult['FAQ_IBLOCK_ID'],
        "SECTION_KEY" => $arResult['FAQ_SECTION_KEY'],
        "PARENT_SECTION" => $arResult['FAQ_PARENT_SECTION'],
        "SECTION_CLASS" => "py-16 bg-gray-50",
    ],
    $component
);
?>
