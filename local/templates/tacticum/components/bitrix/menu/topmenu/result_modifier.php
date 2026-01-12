<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// Собираем список главных пунктов меню
$mainMenuTitles = [];
foreach ($arResult as $item) {
    if ($item['DEPTH_LEVEL'] == 1) {
        $mainMenuTitles[] = $item['TEXT'];
    }
}

// Функция для построения дерева меню
function buildMenuTree($menu, $mainMenuTitles) {
    $tree = [];
    $parents = [];

    foreach ($menu as $key => &$item) {
        $item['CHILDREN'] = [];
        $parents[$item['DEPTH_LEVEL']][$key] = &$item;

        if ($item['DEPTH_LEVEL'] == 1) {
            $tree[$key] = &$item;
        } else {
            $parentLevel = $item['DEPTH_LEVEL'] - 1;
            end($parents[$parentLevel]);
            $parentKey = key($parents[$parentLevel]);
            if (isset($parents[$parentLevel][$parentKey])) {
                $parents[$parentLevel][$parentKey]['CHILDREN'][] = &$item;
            }
        }
    }

    // Фильтруем дубли у каждого CHILDREN сразу
    foreach ($tree as &$node) {
        if (!empty($node['CHILDREN'])) {
            $node['CHILDREN'] = array_filter(
                $node['CHILDREN'],
                function($child) use ($mainMenuTitles) {
                    return !in_array($child["TEXT"], $mainMenuTitles);
                }
            );
        }
    }

    return $tree;
}

$arResult['MENU_TREE'] = buildMenuTree($arResult, $mainMenuTitles);
?>
