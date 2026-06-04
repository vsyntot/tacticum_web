<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");
$APPLICATION->SetPageProperty("tacticum_body_class", "bg-white font-sans tacticum-aiagents-page");
$APPLICATION->SetTitle("Telegram-бот прототип для B2B-сценариев - Тактикум");
$APPLICATION->SetPageProperty("description", "Service route Tacticum для быстрого Telegram-бот прототипа: проверьте диалог, квалификацию лида и следующий шаг перед внедрением Tacticum Agents.");
tacticum_apply_seo_defaults('/aiagents/', [
    'image' => SITE_TEMPLATE_PATH . '/images/aibot_hero_bg_big.png',
    'image_width' => 1536,
    'image_height' => 1024,
    'image_type' => 'image/png',
    'schema' => [
        '@type' => 'Service',
        '@id' => tacticum_public_url('/aiagents/#ai-bot-service'),
        'name' => 'Telegram-бот прототип для B2B-сценариев',
        'serviceType' => 'Telegram bot prototype and service route',
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
