<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Platform - платформа для корпоративных AI-продуктов");
$APPLICATION->SetPageProperty("description", "Tacticum Platform - единое инфраструктурное ядро для корпоративных AI-продуктов: LLM-шлюз, RAG, память, MCP-инструменты, права доступа, аудит и контроль стоимости.");
$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");

$tacticumProductPage = tacticum_product_page_data('platform');

tacticum_apply_seo_defaults('/platform/', [
    'schema' => tacticum_product_page_schema(
        $tacticumProductPage,
        '/platform/',
        'BusinessApplication',
        'Платформенное ядро для корпоративных AI-продуктов: runtime, LLM Gateway, RAG, память, MCP-инструменты, доступы, аудит и наблюдаемость.'
    ),
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");

tacticum_render_product_page($tacticumProductPage);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
