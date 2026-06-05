<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq,charts,chat");
$APPLICATION->SetTitle("Команда под AI- и IT-задачу: роли, ставки и быстрый старт - Тактикум");
$APPLICATION->SetPageProperty("description", "Соберите управляемую AI- или IT-команду под задачу: роли, уровни специалистов, ставки, пресеты команды и заявка на старт работ.");
tacticum_apply_seo_defaults('/price/', [
    'image' => SITE_TEMPLATE_PATH . '/images/price_hero_bg.jpg',
    'image_width' => 1536,
    'image_height' => 592,
    'schema' => [
        '@type' => 'Service',
        '@id' => tacticum_public_url('/price/#staff-service'),
        'name' => 'Подбор IT-специалистов и AI-команд',
        'serviceType' => 'IT staff augmentation and AI delivery teams',
        'provider' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
        'areaServed' => 'RU',
        'url' => tacticum_public_url('/price/'),
    ],
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:price.page",
    "",
    [
        "FAQ_IBLOCK_ID" => tacticum_iblock_id('faq'),
    ],
    false
);
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
