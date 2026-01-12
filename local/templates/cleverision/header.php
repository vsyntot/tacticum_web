<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use Bitrix\Main\Page\Asset;
$obAsset = Asset::getInstance();

$arSessionServices = $_SESSION["ADDED_SERVICES"];
$arSessionServices = is_array($arSessionServices) ? $arSessionServices : array();
$selectedServices = count($arSessionServices);

$curPage = $APPLICATION->GetCurPage();
?><!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?$obAsset->addCss(SITE_TEMPLATE_PATH."/styles/bootstrap/bootstrap.min.css");?>
        <?$obAsset->addCss(SITE_TEMPLATE_PATH."/styles/styles.css");?>
        <?$obAsset->addJs(SITE_TEMPLATE_PATH."/js/jquery/jquery-3.4.1.min.js")?>
        <?$obAsset->addJs(SITE_TEMPLATE_PATH."/js/bootstrap/bootstrap.min.js")?>
        <?$obAsset->addJs(SITE_TEMPLATE_PATH."/js/bootstrap-notify/bootstrap-notify.min.js")?>
        <?$obAsset->addJs(SITE_TEMPLATE_PATH."/js/main.js")?>
        <?$APPLICATION->ShowHead();?>
        <title><?$APPLICATION->ShowTitle()?></title>
    </head>

    <body>
        <?$APPLICATION->ShowPanel()?>
        <?$APPLICATION->IncludeFile( SITE_TEMPLATE_PATH."/include/handlers.php" );?>
        <?$APPLICATION->IncludeFile( SITE_TEMPLATE_PATH."/include/modal.php" );?>
        <div class="header">
            <div class="infoWrapper">
                <div class="container">
                    <div class="info">
                        <a href="/" class="logo">
                            <div class="icon"></div>
                            <div class="text">РАЦИОНАЛЬНЫЕ<br /> РЕШЕНИЯ</div>
                        </a>
                        <div class="search d-none d-md-block">
                            <div class="searchWrapper">
                                <form method="get" action="/search/">
                                    <input type="text" placeholder="Умный поиск..." name="q">
                                    <input type="submit" class="icon" value="">
                                </form>
                            </div>
                        </div>
                        <div class="phone d-none d-lg-block">
                            <a class="infoArea" href="tel:+79038314339">
                                <div class="icon"></div>
                                <div class="text text-nowrap">8 (903) 831-43-39</div>
                            </a>
                            <div class="actionWrapper">
                                <div class="action" data-toggle="modal" data-target="#modalCallback">обратный звонок</div>
                            </div>
                        </div>
                        <div class="workHours d-none d-xl-block">
                            <div class="infoArea">
                                <div class="icon"></div>
                                <div class="text text-nowrap">9:00 — 18:00 <span class="smaller">(пн.-пт.)</span></div>
                            </div>
                            <div class="actionWrapper">
                                <div class="action" data-toggle="modal" data-target="#modalQuestion">вопрос специалисту</div>
                            </div>
                        </div>
                        <div class="services">
                            <a class="counterArea" href="/cart/">
                                <div class="icon"></div>
                                <span class="text text-nowrap">Список услуг:</span>
                                <span class="counter"><?=$selectedServices?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="menuWrapper">
                <div class="menu">
                    <div class="container">
                        <div class="d-md-none mobileMenuPanel">
                            <div class="row h-100">
                                <div class="col h-100 menuBtn" id="mobileMenuBtn">
                                    <div class="icon"></div>
                                    <span>МЕНЮ</span>
                                </div>
                                <a class="col h-100 searchBtn" href="/search/">
                                    <span>ПОИСК</span>
                                    <div class="icon"></div>
                                </a>
                            </div>
                        </div>
                        <div class="mobileSizer">
                            <table class="itemsWrapper" id="siteMenu">
                                <tr>
                                    <td class="menuItem text-center">
                                        <a href="/about/">О КОМПАНИИ</a>
                                    </td>
                                    <td class="menuItem text-center">
                                        <a href="/services/">КАТАЛОГ УСЛУГ</a>
                                    </td>
                                    <td class="menuItem text-center">
                                        <a href="/projects/">ВЫПОЛНЕННЫЕ РАБОТЫ</a>
                                    </td>
                                    <?/*<td class="menuItem text-center">
                                        <a href="/articles/">ПОЛЕЗНЫЕ МАТЕРИАЛЫ</a>
                                    </td>*/?>
                                    <td class="menuItem text-center">
                                        <a href="/contacts/">КОНТАКТНАЯ ИНФОРМАЦИЯ</a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content">
