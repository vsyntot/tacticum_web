<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Agents - корпоративные AI-ассистенты для бизнес-функций");
$APPLICATION->SetPageProperty("description", "Tacticum Agents помогает запускать корпоративных AI-ассистентов для HR, юридического, бухгалтерского, клиентского и внутреннего IT-контуров поверх общей AI-платформы.");
$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");

$tacticumProductPageResult = $APPLICATION->IncludeComponent(
    'tacticum:product.page',
    '',
    [
        'PRODUCT_CODE' => 'agents',
        'CANONICAL_PATH' => '/agents/',
        'APPLICATION_CATEGORY' => 'BusinessApplication',
        'SCHEMA_DESCRIPTION' => 'Продуктовый слой для корпоративных AI-ассистентов в HR, legal, finance, support, IT helpdesk и базе знаний поверх общей Tacticum Platform.',
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
        'PRODUCT_CODE' => 'agents',
        'CANONICAL_PATH' => '/agents/',
        'APPLY_SEO_DEFAULTS' => 'N',
        'PAGE_DATA' => $tacticumProductPage,
    ],
    false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
