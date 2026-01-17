<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/menu_helpers.php';

$mainMenuTitles = [];
foreach ($arResult as $item) {
    if ($item['DEPTH_LEVEL'] == 1) {
        $mainMenuTitles[] = $item['TEXT'];
    }
}

$arResult['MENU_TREE'] = buildMenuTree($arResult, $mainMenuTitles);
?>
