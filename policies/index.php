<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Политика конфиденциальности - Тактикум");
?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:news.detail", 
	"policies",
	[
		"COMPONENT_TEMPLATE" => "policies",
		"IBLOCK_TYPE" => "company",
		"IBLOCK_ID" => "19",
		"ELEMENT_ID" => "515",
		"ELEMENT_CODE" => "",
		"CHECK_DATES" => "Y",
		"FIELD_CODE" => [
			0 => "ID",
			1 => "CODE",
			2 => "NAME",
			3 => "DETAIL_TEXT",
			4 => "",
		],
		"PROPERTY_CODE" => [
			0 => "",
		],
		"IBLOCK_URL" => "",
		"DETAIL_URL" => "",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"SET_TITLE" => "Y",
		"SET_CANONICAL_URL" => "N",
		"SET_BROWSER_TITLE" => "Y",
		"BROWSER_TITLE" => "-",
		"SET_META_KEYWORDS" => "Y",
		"META_KEYWORDS" => "-",
		"SET_META_DESCRIPTION" => "Y",
		"META_DESCRIPTION" => "-",
		"SET_LAST_MODIFIED" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"ADD_SECTIONS_CHAIN" => "N",
		"ADD_ELEMENT_CHAIN" => "N",
		"ACTIVE_DATE_FORMAT" => "d.m.Y",
		"USE_PERMISSIONS" => "N",
		"STRICT_SECTION_CHECK" => "N",
		"PAGER_TEMPLATE" => ".default",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Страница",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SET_STATUS_404" => "N",
		"SHOW_404" => "N",
		"MESSAGE_404" => ""
	],
	false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>