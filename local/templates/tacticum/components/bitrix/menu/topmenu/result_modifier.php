<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/menu_helpers.php';

$arResult['MENU_TREE'] = tacticum_build_menu_tree($arResult);
?>
