<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->SetTitle("Tacticum Dev - управление AI-assisted разработкой");
$APPLICATION->SetPageProperty("description", "Tacticum Dev помогает инженерным организациям управлять AI-assisted разработкой: профили, knowledge layer, design token compliance, quality gates и traceability.");
$APPLICATION->SetPageProperty("tacticum_page_assets", "faq");

$tacticumProductPage = tacticum_product_page_data('dev');

tacticum_apply_seo_defaults('/dev/', [
    'schema' => tacticum_product_page_schema(
        $tacticumProductPage,
        '/dev/',
        'DeveloperApplication',
        'Governance-слой для AI-assisted разработки: профили, knowledge layer, design token rules, workflow gates, quality gates и traceability.'
    ),
]);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");

tacticum_render_product_page($tacticumProductPage);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
