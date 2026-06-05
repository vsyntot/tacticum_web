<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Forum - сценарии и LLM для клиентских коммуникаций");
$APPLICATION->SetPageProperty("description", "Tacticum Forum - диалоговая платформа для клиентских коммуникаций: сценарные графы, LLM-обогащение, аналитика воронок, A/B-проверки и журнал диалогов.");
$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");

$tacticumProductPageResult = $APPLICATION->IncludeComponent(
    'tacticum:product.page',
    '',
    [
        'PRODUCT_CODE' => 'forum',
        'CANONICAL_PATH' => '/forum/',
        'APPLICATION_CATEGORY' => 'BusinessApplication',
        'SCHEMA_DESCRIPTION' => 'Диалоговая платформа для клиентских коммуникаций: сценарные графы, LLM-обогащение, funnel analytics, A/B-проверки и журнал диалогов.',
        'PREPARE_ONLY' => 'Y',
    ],
    false
);
$tacticumProductPage = is_array($tacticumProductPageResult) && is_array($tacticumProductPageResult['PAGE'] ?? null)
    ? $tacticumProductPageResult['PAGE']
    : [];

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");

$APPLICATION->IncludeComponent(
    'tacticum:product.page',
    '',
    [
        'PRODUCT_CODE' => 'forum',
        'CANONICAL_PATH' => '/forum/',
        'APPLY_SEO_DEFAULTS' => 'N',
        'PAGE_DATA' => $tacticumProductPage,
    ],
    false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
