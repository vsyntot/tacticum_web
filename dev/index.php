<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Dev - управление AI-assisted разработкой");
$APPLICATION->SetPageProperty("description", "Tacticum Dev помогает инженерным организациям управлять AI-assisted разработкой: профили, knowledge layer, design token compliance, quality gates и traceability.");
$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");

$tacticumProductPageResult = $APPLICATION->IncludeComponent(
    'tacticum:product.page',
    '',
    [
        'PRODUCT_CODE' => 'dev',
        'CANONICAL_PATH' => '/dev/',
        'APPLICATION_CATEGORY' => 'DeveloperApplication',
        'SCHEMA_DESCRIPTION' => 'Governance-слой для AI-assisted разработки: профили, knowledge layer, design token rules, workflow gates, quality gates и traceability.',
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
        'PRODUCT_CODE' => 'dev',
        'CANONICAL_PATH' => '/dev/',
        'APPLY_SEO_DEFAULTS' => 'N',
        'PAGE_DATA' => $tacticumProductPage,
    ],
    false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
