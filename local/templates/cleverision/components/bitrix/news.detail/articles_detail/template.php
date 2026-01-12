<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$arProps = $arResult["PROPERTIES"];
?>
<div class="cmpServiceDetail">
    <div class="container">
        <div class="cmpTitle"><?=$arResult["NAME"]?></div>
        <div class="block-1">
            <div class="row">
                <div class="col">
                    <div class="text">
                        <?=$arResult["DETAIL_TEXT"]?>
                    </div>
                </div>
                <div class="imgCont col-12 col-md order-first order-md-last">
                    <img src="<?=$arResult["DETAIL_PICTURE"]["SRC"]?>" alt="">
                </div>
            </div>
        </div>
        <?
        if (isset($arProps["TEXT_2"]["~VALUE"]["TEXT"])) {
            ?>
            <div class="glSeparator"></div>
            <div class="block-2">
                <?=$arProps["TEXT_2"]["~VALUE"]["TEXT"]?>
            </div>
            <?
        }

        if (isset($arProps["TEXT_3"]["~VALUE"]["TEXT"])) {
            ?>
            <div class="glSeparator"></div>
            <div class="block-3">
                <?=$arProps["TEXT_3"]["~VALUE"]["TEXT"]?>
            </div>
            <?
        }
        ?>
    </div>
</div>

<div class="container">
    <div class="glSeparator"></div>
</div>

<?$APPLICATION->IncludeComponent(
    "bitrix:iblock.element.add.form",
    "message_form",
    array(
        "COMPOSITE_FRAME_MODE" => "A",
        "COMPOSITE_FRAME_TYPE" => "AUTO",
        "CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
        "CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
        "CUSTOM_TITLE_DETAIL_PICTURE" => "",
        "CUSTOM_TITLE_DETAIL_TEXT" => "",
        "CUSTOM_TITLE_IBLOCK_SECTION" => "",
        "CUSTOM_TITLE_NAME" => "",
        "CUSTOM_TITLE_PREVIEW_PICTURE" => "",
        "CUSTOM_TITLE_PREVIEW_TEXT" => "",
        "CUSTOM_TITLE_TAGS" => "",
        "DEFAULT_INPUT_SIZE" => "30",
        "DETAIL_TEXT_USE_HTML_EDITOR" => "N",
        "ELEMENT_ASSOC" => "CREATED_BY",
        "GROUPS" => array(
        ),
        "IBLOCK_ID" => "7",
        "IBLOCK_TYPE" => "form",
        "LEVEL_LAST" => "Y",
        "LIST_URL" => "",
        "MAX_FILE_SIZE" => "0",
        "MAX_LEVELS" => "100000",
        "MAX_USER_ENTRIES" => "100000",
        "PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
        "PROPERTY_CODES" => array(
            0 => "10",
            1 => "11",
            2 => "12",
            3 => "NAME",
            4 => "DETAIL_TEXT",
        ),
        "PROPERTY_CODES_REQUIRED" => array(
            0 => "10",
            1 => "12",
            2 => "DETAIL_TEXT",
        ),
        "RESIZE_IMAGES" => "N",
        "SEF_MODE" => "N",
        "STATUS" => "ANY",
        "STATUS_NEW" => "N",
        "USER_MESSAGE_ADD" => "Сообщение отправлено",
        "USER_MESSAGE_EDIT" => "",
        "USE_CAPTCHA" => "N",
        "COMPONENT_TEMPLATE" => "message_form",
        "AJAX_MODE" => "Y"
    ),
    false
);?>
