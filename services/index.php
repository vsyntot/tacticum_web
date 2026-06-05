<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");
$APPLICATION->SetTitle("Внедрение AI-решений и автоматизация для бизнеса - Тактикум");
$APPLICATION->SetPageProperty("description", "Tacticum проектирует и внедряет AI- и IT-решения для бизнеса: discovery, архитектура, разработка, интеграции, запуск и развитие продукта.");
tacticum_apply_seo_defaults('/services/', [
    'image' => SITE_TEMPLATE_PATH . '/images/services_hero_bg.jpg',
    'image_width' => 1536,
    'image_height' => 592,
    'schema' => [
        '@type' => 'Service',
        '@id' => tacticum_public_url('/services/#service'),
        'name' => 'Внедрение AI-решений и автоматизация для бизнеса',
        'serviceType' => 'AI consulting, ML development, chatbots and business automation',
        'provider' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
        'areaServed' => 'RU',
        'url' => tacticum_public_url('/services/'),
    ],
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:services.page",
    "",
    [
        "FAQ_IBLOCK_ID" => tacticum_iblock_id('faq'),
    ],
    false
);
?>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
