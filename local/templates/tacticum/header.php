<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use Bitrix\Main\Page\Asset;
$obAsset = Asset::getInstance();
$curPage = $APPLICATION->GetCurPage();
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
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/3.4.16");
    $obAsset->addJs(SITE_TEMPLATE_PATH."/js/init.js");
    $obAsset->addCss(SITE_TEMPLATE_PATH."/fonts/remixicon.min.css");
    if(substr_count($curPage, "aiagents") != 0){
        $obAsset->addCss(SITE_TEMPLATE_PATH."/styles/aiagents.css");
    }
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <?$APPLICATION->ShowHead(); ?>

	<!-- Yandex.Metrika counter -->
	<script type="text/javascript">
		(function(m,e,t,r,i,k,a){
			m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
			m[i].l=1*new Date();
			for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
			k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
		})(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=103471113', 'ym');
	
		ym(103471113, 'init', {ssr:true, webvisor:true, clickmap:true, accurateTrackBounce:true, trackLinks:true});
	</script>
	<noscript><div><img src="https://mc.yandex.ru/watch/103471113" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->

    <title><?$APPLICATION->ShowTitle(); ?></title>
</head>
<body class="<?=(substr_count($curPage, "offer") == 0) ? 'bg-white font-sans' : 'bg-gray-50'?>">
<?$APPLICATION->ShowPanel(); ?>
<!-- Header -->
<div id="header">
    <header class="fixed w-full bg-white/95 backdrop-blur-sm shadow-sm z-50">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center">
                <a href="/"><img src="<?=SITE_TEMPLATE_PATH?>/images/logo.png" width="181" height="50"></a>
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
		"CHILD_MENU_TYPE" => "top",
		"USE_EXT" => "Y",
		"DELAY" => "N",
		"ALLOW_MULTI_SELECT" => "N"
	],
	false
);
            ?>
            <div class="hidden md:block">
                <button id="contactUsBtn" class="bg-primary text-white px-6 py-2 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
                    Связаться с нами
                </button>
            </div>
            <div class="md:hidden w-10 h-10 flex items-center justify-center cursor-pointer">
                <i class="ri-menu-line text-2xl text-secondary"></i>
            </div>
        </div>
    </header>

</div>