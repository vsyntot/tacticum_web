<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("О компании Tacticum - команда корпоративных AI-продуктов");
$APPLICATION->SetPageProperty("description", "О компании Tacticum: команда, подход и опыт разработки корпоративных AI-продуктов, внедрения AI-решений, автоматизации и интеграций.");
tacticum_apply_seo_defaults('/about/', [
    'image' => SITE_TEMPLATE_PATH . '/images/about_hero_bg.jpg',
    'image_width' => 1536,
    'image_height' => 800,
    'schema' => [
        '@type' => 'AboutPage',
        '@id' => tacticum_public_url('/about/#about-page'),
        'name' => 'О компании Tacticum',
        'url' => tacticum_public_url('/about/'),
        'mainEntity' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
    ],
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:about.page",
    "",
    [],
    false
);
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
