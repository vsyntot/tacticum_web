<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:faq.section",
    "",
    [
        "IBLOCK_ID" => tacticum_iblock_id('faq'),
        "SECTION_KEY" => "calculator",
    ],
    false
);
?>
