<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetPageProperty("tacticum_page_assets", "faq,chat");
$APPLICATION->SetTitle("Тактикум - экосистема корпоративных AI-продуктов");
$APPLICATION->SetPageProperty("description", "Tacticum развивает корпоративную AI-экосистему: Platform, Agents, Dev и Forum, а также помогает оценить, внедрить и запустить AI-решения в бизнес-процессах.");
tacticum_apply_seo_defaults('/', [
    'faq_schema' => true,
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:home.page",
    "",
    [
        "FAQ_IBLOCK_ID" => tacticum_iblock_id('faq'),
    ],
    false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
