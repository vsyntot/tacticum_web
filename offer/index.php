<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/offer_page.php");

$offerPageState = tacticum_offer_page_resolve();
tacticum_offer_page_apply_redirects($offerPageState);
tacticum_offer_page_apply_seo($offerPageState);
tacticum_offer_page_apply_template($offerPageState);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

<?
$APPLICATION->IncludeComponent(
    "tacticum:offer",
    "",
    tacticum_offer_page_component_params($offerPageState),
    false
);
?>

<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>
