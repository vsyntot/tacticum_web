<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="modal fade glModal" id="modalService" tabindex="-1" role="dialog" aria-hidden="true">
    <?
    $APPLICATION->IncludeComponent(
	"bitrix:iblock.element.add.form",
	"service_modal",
	array(
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
		"CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
		"CUSTOM_TITLE_DETAIL_PICTURE" => "",
		"CUSTOM_TITLE_DETAIL_TEXT" => "Комментарий",
		"CUSTOM_TITLE_IBLOCK_SECTION" => "",
		"CUSTOM_TITLE_NAME" => "",
		"CUSTOM_TITLE_PREVIEW_PICTURE" => "",
		"CUSTOM_TITLE_PREVIEW_TEXT" => "Услуги",
		"CUSTOM_TITLE_TAGS" => "",
		"DEFAULT_INPUT_SIZE" => "30",
		"DETAIL_TEXT_USE_HTML_EDITOR" => "N",
		"ELEMENT_ASSOC" => "CREATED_BY",
		"GROUPS" => array(
            "0" => 2,
		),
		"IBLOCK_ID" => "10",
		"IBLOCK_TYPE" => "form",
		"LEVEL_LAST" => "Y",
		"LIST_URL" => "",
		"MAX_FILE_SIZE" => "0",
		"MAX_LEVELS" => "100000",
		"MAX_USER_ENTRIES" => "100000",
		"PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
		"PROPERTY_CODES" => array(
			0 => "20",
			1 => "21",
			2 => "NAME",
			3 => "DETAIL_TEXT",
			4 => "PREVIEW_TEXT",
		),
		"PROPERTY_CODES_REQUIRED" => array(
			0 => "20",
			1 => "21",
			2 => "DETAIL_TEXT",
		),
		"RESIZE_IMAGES" => "N",
		"SEF_MODE" => "N",
		"STATUS" => "ANY",
		"STATUS_NEW" => "N",
		"USER_MESSAGE_ADD" => "Сообщение отправлено",
		"USER_MESSAGE_EDIT" => "",
		"USE_CAPTCHA" => "N",
		"AJAX_MODE" => "Y",
		"COMPONENT_TEMPLATE" => "service_modal"
	),
	false
);
    ?>
</div>

<div class="modal fade glModal" id="modalCallback" tabindex="-1" role="dialog" aria-hidden="true">
    <?
    $APPLICATION->IncludeComponent(
        "bitrix:iblock.element.add.form",
        "callback_modal",
        array(
            "COMPOSITE_FRAME_MODE" => "A",
            "COMPOSITE_FRAME_TYPE" => "AUTO",
            "CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
            "CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
            "CUSTOM_TITLE_DETAIL_PICTURE" => "",
            "CUSTOM_TITLE_DETAIL_TEXT" => "Комментарий",
            "CUSTOM_TITLE_IBLOCK_SECTION" => "",
            "CUSTOM_TITLE_NAME" => "",
            "CUSTOM_TITLE_PREVIEW_PICTURE" => "",
            "CUSTOM_TITLE_PREVIEW_TEXT" => "",
            "CUSTOM_TITLE_TAGS" => "",
            "DEFAULT_INPUT_SIZE" => "30",
            "DETAIL_TEXT_USE_HTML_EDITOR" => "N",
            "ELEMENT_ASSOC" => "CREATED_BY",
            "GROUPS" => array(
                "0" => 2,
            ),
            "IBLOCK_ID" => "9",
            "IBLOCK_TYPE" => "form",
            "LEVEL_LAST" => "Y",
            "LIST_URL" => "",
            "MAX_FILE_SIZE" => "0",
            "MAX_LEVELS" => "100000",
            "MAX_USER_ENTRIES" => "100000",
            "PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
            "PROPERTY_CODES" => array(
                0 => "17",
                1 => "18",
                2 => "NAME",
                3 => "DETAIL_TEXT",
            ),
            "PROPERTY_CODES_REQUIRED" => array(
                0 => "17",
                1 => "18",
                2 => "DETAIL_TEXT",
            ),
            "RESIZE_IMAGES" => "N",
            "SEF_MODE" => "N",
            "STATUS" => "ANY",
            "STATUS_NEW" => "N",
            "USER_MESSAGE_ADD" => "Сообщение отправлено",
            "USER_MESSAGE_EDIT" => "",
            "USE_CAPTCHA" => "N",
            "AJAX_MODE" => "Y"
        ),
        false
    );
    ?>
</div>

<div class="modal fade glModal" id="modalQuestion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <?
        $APPLICATION->IncludeComponent(
	"bitrix:iblock.element.add.form",
	"question_modal",
	array(
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
		"CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
		"CUSTOM_TITLE_DETAIL_PICTURE" => "",
		"CUSTOM_TITLE_DETAIL_TEXT" => "Комментарий",
		"CUSTOM_TITLE_IBLOCK_SECTION" => "",
		"CUSTOM_TITLE_NAME" => "",
		"CUSTOM_TITLE_PREVIEW_PICTURE" => "",
		"CUSTOM_TITLE_PREVIEW_TEXT" => "",
		"CUSTOM_TITLE_TAGS" => "",
		"DEFAULT_INPUT_SIZE" => "30",
		"DETAIL_TEXT_USE_HTML_EDITOR" => "N",
		"ELEMENT_ASSOC" => "CREATED_BY",
		"GROUPS" => array(
            "0" => 2,
		),
		"IBLOCK_ID" => "8",
		"IBLOCK_TYPE" => "form",
		"LEVEL_LAST" => "Y",
		"LIST_URL" => "",
		"MAX_FILE_SIZE" => "0",
		"MAX_LEVELS" => "100000",
		"MAX_USER_ENTRIES" => "100000",
		"PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
		"PROPERTY_CODES" => array(
			0 => "14",
			1 => "15",
			2 => "NAME",
			3 => "DETAIL_TEXT",
		),
		"PROPERTY_CODES_REQUIRED" => array(
			0 => "14",
			1 => "15",
			2 => "DETAIL_TEXT",
		),
		"RESIZE_IMAGES" => "N",
		"SEF_MODE" => "N",
		"STATUS" => "ANY",
		"STATUS_NEW" => "N",
		"USER_MESSAGE_ADD" => "Сообщение отправлено",
		"USER_MESSAGE_EDIT" => "",
		"USE_CAPTCHA" => "N",
		"AJAX_MODE" => "Y"
	),
	false
);
        ?>
    </div>
</div>

<?
// Установка выбранных услуг в JS переменной
$arSessionServices = $_SESSION["ADDED_SERVICES"];
$arSessionServices = is_array($arSessionServices) ? $arSessionServices : array();

// Т.к. модалки работают через ajax битрикса, логика тут
?>

<script>
    $(function(){
        if (window.main) {
            window.main.serviceList = <?=CUtil::PhpToJSObject(array_values($arSessionServices))?>;
        }

        $( "#modalService" ).on('show.bs.modal', function(){
            const main = window.main;
            if (main && main.serviceList) {
                $('#modalService .selectedServicesInput').val(main.serviceList.join(','));
            }
        });
    })
</script>
