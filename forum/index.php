<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Forum - сценарии и LLM для клиентских коммуникаций");
$APPLICATION->SetPageProperty("description", "Tacticum Forum - диалоговая платформа для клиентских коммуникаций: сценарные графы, LLM-обогащение, аналитика воронок, A/B-проверки и журнал диалогов.");
$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");

$tacticumProductPage = tacticum_product_page_data('forum');

tacticum_apply_seo_defaults('/forum/', [
    'schema' => tacticum_product_page_schema(
        $tacticumProductPage,
        '/forum/',
        'BusinessApplication',
        'Диалоговая платформа для клиентских коммуникаций: сценарные графы, LLM-обогащение, funnel analytics, A/B-проверки и журнал диалогов.'
    ),
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");

tacticum_render_product_page($tacticumProductPage);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
