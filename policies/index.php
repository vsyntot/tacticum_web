<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Политика конфиденциальности - Тактикум");
$APPLICATION->SetPageProperty("description", "Политика конфиденциальности Tacticum: порядок обработки персональных данных и права пользователей.");
tacticum_apply_seo_defaults('/policies/', [
    'schema' => [
        '@type' => 'WebPage',
        '@id' => tacticum_public_url('/policies/#privacy-policy'),
        'name' => 'Политика конфиденциальности Tacticum',
        'url' => tacticum_public_url('/policies/'),
        'isPartOf' => [
            '@id' => tacticum_public_url('/#website'),
        ],
    ],
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<?
$APPLICATION->IncludeComponent(
    "tacticum:content.detail",
    "",
    [
        "IBLOCK_KEY" => "policies",
        "DETAIL_TEMPLATE" => "policies",
        "FIELD_CODE" => ["ID", "CODE", "NAME", "DETAIL_TEXT"],
        "PROPERTY_CODE" => [],
    ],
    false
);
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
