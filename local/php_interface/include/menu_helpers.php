<?php

if (!function_exists('tacticum_build_menu_tree')) {
    function tacticum_build_menu_tree(array $menu): array
    {
        $tree = [];
        $stack = [];

        foreach ($menu as $sourceItem) {
            if (!is_array($sourceItem)) {
                continue;
            }

            $level = max(1, (int)($sourceItem['DEPTH_LEVEL'] ?? 1));
            $item = $sourceItem;
            $item['CHILDREN'] = [];

            if ($level === 1 || !isset($stack[$level - 1])) {
                $tree[] = $item;
                $lastKey = array_key_last($tree);
                $stack = [1 => &$tree[$lastKey]];
                continue;
            }

            $stack[$level - 1]['CHILDREN'][] = $item;
            $lastChildKey = array_key_last($stack[$level - 1]['CHILDREN']);
            $stack[$level] = &$stack[$level - 1]['CHILDREN'][$lastChildKey];

            foreach (array_keys($stack) as $stackLevel) {
                if ($stackLevel > $level) {
                    unset($stack[$stackLevel]);
                }
            }
        }

        return $tree;
    }
}
