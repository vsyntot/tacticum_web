<?php

if (!function_exists('buildMenuTree')) {
    function buildMenuTree(array $menu, array $mainMenuTitles): array
    {
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

        foreach ($tree as &$node) {
            if (!empty($node['CHILDREN'])) {
                $node['CHILDREN'] = array_filter(
                    $node['CHILDREN'],
                    function ($child) use ($mainMenuTitles) {
                        return !in_array($child['TEXT'], $mainMenuTitles);
                    }
                );
            }
        }

        return $tree;
    }
}
