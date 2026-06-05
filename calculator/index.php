<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq,chat");
$APPLICATION->SetTitle("AI-калькулятор проекта: бюджет, сроки, команда и риски - Тактикум");
$APPLICATION->SetPageProperty("description", "AI-калькулятор Tacticum помогает получить предварительный артефакт оценки проекта: бюджетный диапазон, сроки, состав команды, риски и следующий шаг.");
tacticum_apply_seo_defaults('/calculator/', [
    'image' => SITE_TEMPLATE_PATH . '/images/calculator_hero_bg.jpg',
    'image_width' => 1536,
    'image_height' => 592,
    'schema' => [
        '@type' => 'WebApplication',
        '@id' => tacticum_public_url('/calculator/#ai-calculator'),
        'name' => 'AI-калькулятор Tacticum',
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Web',
        'url' => tacticum_public_url('/calculator/'),
        'provider' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
    ],
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:calculator.page",
    "",
    [
        "FAQ_IBLOCK_ID" => tacticum_iblock_id('faq'),
    ],
    false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
