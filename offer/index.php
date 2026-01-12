<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Предложение - Тактикум");
?>

<?
$APPLICATION->IncludeComponent(
	"bitrix:news.detail", 
	"offer", 
	[
		"COMPONENT_TEMPLATE" => "offer",
		"IBLOCK_TYPE" => "client_requests",
		"IBLOCK_ID" => "5",
		"ELEMENT_ID" => $_REQUEST["ID"],
		"ELEMENT_CODE" => "",
		"CHECK_DATES" => "Y",
		"FIELD_CODE" => [
			0 => "ID",
			1 => "CODE",
			2 => "NAME",
			3 => "",
		],
		"PROPERTY_CODE" => [
			0 => "IS_FINAL",
			1 => "GROUP_ID",
			2 => "RESPONSE_ID",
			3 => "RESPONSE",
			4 => "SUMMARY",
			5 => "GOALS",
			6 => "BUSINESS_CONTEXT",
			7 => "FUNCTIONAL_REQUIREMENTS",
			8 => "NONFUNCTIONAL_REQUIREMENTS",
			9 => "TEAM",
			10 => "STACK",
			11 => "BUDGET",
			12 => "TIMELINE",
			13 => "CLIENT_NAME",
			14 => "TITLE",
			15 => "DESCRIPTION",
			16 => "KEYWORDS",
			17 => "H1",
			18 => "",
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