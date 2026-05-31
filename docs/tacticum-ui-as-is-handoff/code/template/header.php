<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use Bitrix\Main\Page\Asset;
$obAsset = Asset::getInstance();
$pageAssets = [];
$pageAssetsProperty = trim((string)$APPLICATION->GetPageProperty('tacticum_page_assets', ''));
if ($pageAssetsProperty !== '') {
    $pageAssets = array_merge(
        $pageAssets,
        array_filter(
            array_map('trim', explode(',', $pageAssetsProperty)),
            static fn(string $asset): bool => $asset !== ''
        )
    );
}
$hasPageAsset = static function (string $asset) use ($pageAssets): bool {
    return in_array($asset, $pageAssets, true) || !empty($pageAssets[$asset]);
};
$bodyClass = trim((string)$APPLICATION->GetPageProperty('tacticum_body_class', 'bg-white font-sans'));
if ($bodyClass === '') {
    $bodyClass = 'bg-white font-sans';
}
if (!headers_sent()) {
    $securityConfig = function_exists('tacticum_rest_get_config_section')
        ? tacticum_rest_get_config_section('security')
        : [];
    $cspMode = strtolower(trim((string)($securityConfig['csp_mode'] ?? 'report-only')));
    $cspHeaderName = $cspMode === 'enforce'
        ? 'Content-Security-Policy'
        : 'Content-Security-Policy-Report-Only';
    header(
        $cspHeaderName . ": default-src 'self'; base-uri 'self'; object-src 'none'; " .
        "script-src 'self' 'unsafe-inline' https://mc.yandex.ru https://api-maps.yandex.ru https://*.yandex.ru https://*.yandex.net; " .
        "style-src 'self' 'unsafe-inline'; " .
        "img-src 'self' data: https://mc.yandex.ru https://*.yandex.ru https://*.yandex.net; " .
        "font-src 'self' data:; " .
        "connect-src 'self' https://mc.yandex.ru https://*.yandex.ru https://*.yandex.net; " .
        "frame-src 'self' https://yandex.ru https://*.yandex.ru"
    );
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/png" sizes="32x32" href="<?=SITE_TEMPLATE_PATH?>/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?=SITE_TEMPLATE_PATH?>/images/favicon-16x16.png">
    <link rel="shortcut icon" href="/favicon.ico">

    <link rel="apple-touch-icon" sizes="180x180" href="<?=SITE_TEMPLATE_PATH?>/images/apple-touch-icon.png">
    <link rel="manifest" href="<?=SITE_TEMPLATE_PATH?>/images/site.webmanifest">

    <?
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/menu.js");
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/analytics.js");
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/metrika.js");
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/forms.js");
    if ($hasPageAsset('chat')) {
        $obAsset->addJs(SITE_TEMPLATE_PATH."/js/chat-agent.js");
    }
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/modal.js");
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/scroll.js");
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/tg-link-resolver.js");
    if ($hasPageAsset('faq')) {
        $obAsset->addJs(SITE_TEMPLATE_PATH."/js/faq.js");
    }
    if ($hasPageAsset('charts')) {
        $obAsset->addJs(SITE_TEMPLATE_PATH."/js/charts.js");
    }
    if ($hasPageAsset('yandex_map')) {
        $obAsset->addJs(SITE_TEMPLATE_PATH."/js/yandex-map.js");
    }
    $obAsset->addCss(SITE_TEMPLATE_PATH."/tailwind.generated.css");
    $obAsset->addCss(SITE_TEMPLATE_PATH."/fonts/remixicon.min.css");
    $obAsset->addCss(SITE_TEMPLATE_PATH."/styles/global.css");
    ?>
    <?$APPLICATION->ShowHead(); ?>

    <title><?$APPLICATION->ShowTitle(); ?></title>
</head>
<body class="<?=htmlspecialchars($bodyClass, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>">
<?$APPLICATION->ShowPanel(); ?>
<noscript><div><img src="https://mc.yandex.ru/watch/103471113" width="1" height="1" class="tacticum-metrika-pixel" alt="" /></div></noscript>
<div id="header">
    <header class="fixed w-full bg-white/95 backdrop-blur-sm shadow-sm z-50">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center">
                <a href="/"><img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" width="181" height="50" alt="Tacticum"></a>
            </div>
            <?
            $APPLICATION->IncludeComponent(
	"bitrix:menu", 
	"topmenu", 
	[
		"COMPONENT_TEMPLATE" => "topmenu",
		"ROOT_MENU_TYPE" => "top",
		"MENU_CACHE_TYPE" => "A",
		"MENU_CACHE_TIME" => "3600",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"MENU_CACHE_GET_VARS" => [
		],
		"MAX_LEVEL" => "2",
		"CHILD_MENU_TYPE" => "left",
		"USE_EXT" => "N",
		"DELAY" => "N",
		"ALLOW_MULTI_SELECT" => "N"
	],
	false
);
            ?>
            <div class="hidden lg:block">
                <button id="contactUsBtn" class="bg-primary text-white px-6 py-2 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
                    Связаться с нами
                </button>
            </div>
            <button type="button"
                    class="lg:hidden w-10 h-10 flex items-center justify-center cursor-pointer"
                    data-tacticum-menu-toggle
                    aria-controls="tacticum-mobile-menu"
                    aria-expanded="false"
                    aria-label="Открыть меню">
                <i class="ri-menu-line text-2xl text-secondary"></i>
            </button>
        </div>
    </header>

</div>
