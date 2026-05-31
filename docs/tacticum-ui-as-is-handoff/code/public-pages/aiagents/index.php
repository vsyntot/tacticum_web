<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");
$APPLICATION->SetPageProperty("tacticum_body_class", "bg-white font-sans tacticum-aiagents-page");
$APPLICATION->SetTitle("AI-боты и Telegram-агенты для B2B-сценариев - Тактикум");
$APPLICATION->SetPageProperty("description", "Tacticum помогает быстро проверить и запустить AI-бота в Telegram для продаж, консультаций и лидогенерации: демо, прототип и внедрение.");
tacticum_apply_seo_defaults('/aiagents/', [
    'image' => SITE_TEMPLATE_PATH . '/images/aibot_hero_bg_big.png',
    'image_width' => 1536,
    'image_height' => 1024,
    'image_type' => 'image/png',
    'schema' => [
        '@type' => 'Service',
        '@id' => tacticum_public_url('/aiagents/#ai-bot-service'),
        'name' => 'AI-боты и Telegram-агенты для B2B-сценариев',
        'serviceType' => 'AI agents and Telegram bots',
        'provider' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
        'areaServed' => 'RU',
        'url' => tacticum_public_url('/aiagents/'),
    ],
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:aiagents",
    "",
    [
        "IBLOCK_ID" => tacticum_iblock_id('aiagents'),
        "FAQ_IBLOCK_ID" => tacticum_iblock_id('faq'),
        "FAQ_SECTION_KEY" => "aiagents",
    ],
    false
);
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
