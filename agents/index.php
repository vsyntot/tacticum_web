<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Agents - корпоративные AI-ассистенты для бизнес-функций");
$APPLICATION->SetPageProperty("description", "Tacticum Agents помогает запускать корпоративных AI-ассистентов для HR, юридического, бухгалтерского, клиентского и внутреннего IT-контуров поверх общей AI-платформы.");
$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");

$tacticumProductPage = tacticum_product_page_data('agents');

tacticum_apply_seo_defaults('/agents/', [
    'schema' => tacticum_product_page_schema(
        $tacticumProductPage,
        '/agents/',
        'BusinessApplication',
        'Продуктовый слой для корпоративных AI-ассистентов в HR, legal, finance, support, IT helpdesk и базе знаний поверх общей Tacticum Platform.'
    ),
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");

tacticum_render_product_page($tacticumProductPage);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
