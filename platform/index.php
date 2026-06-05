<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Platform - платформа для корпоративных AI-продуктов");
$APPLICATION->SetPageProperty("description", "Tacticum Platform - единое инфраструктурное ядро для корпоративных AI-продуктов: LLM-шлюз, RAG, память, MCP-инструменты, права доступа, аудит и контроль стоимости.");
$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");

$tacticumProductPageResult = $APPLICATION->IncludeComponent(
    'tacticum:product.page',
    '',
    [
        'PRODUCT_CODE' => 'platform',
        'CANONICAL_PATH' => '/platform/',
        'APPLICATION_CATEGORY' => 'BusinessApplication',
        'SCHEMA_DESCRIPTION' => 'Платформенное ядро для корпоративных AI-продуктов: runtime, LLM Gateway, RAG, память, MCP-инструменты, доступы, аудит и наблюдаемость.',
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
        'PRODUCT_CODE' => 'platform',
        'CANONICAL_PATH' => '/platform/',
        'APPLY_SEO_DEFAULTS' => 'N',
        'PAGE_DATA' => $tacticumProductPage,
    ],
    false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
