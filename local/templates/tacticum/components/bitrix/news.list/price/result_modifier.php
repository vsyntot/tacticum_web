<?php

use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (empty($arResult['ITEMS'])) {
    return;
}

$grouped = [];
$sectionIds = [];

// Группировка элементов по разделам
foreach ($arResult['ITEMS'] as $item) {
    $sectionId = (int)($item['IBLOCK_SECTION_ID'] ?? 0);
    if ($sectionId > 0) {
        $grouped[$sectionId][] = $item;
        $sectionIds[$sectionId] = true;
    }
}

// Получение информации о разделах
$sections = [];
if (!empty($sectionIds)) {
    $res = CIBlockSection::GetList(
        ['SORT' => 'ASC'],
        ['ID' => array_keys($sectionIds), 'ACTIVE' => 'Y'],
        false,
        ['ID', 'NAME', 'SORT']
    );
    while ($section = $res->Fetch()) {
        $sections[(int)$section['ID']] = [
            'ID' => (int)$section['ID'],
            'NAME' => $section['NAME'],
            'SORT' => (int)$section['SORT'],
        ];
    }
}

// Сортировка разделов по SORT
uasort($sections, fn($a, $b) => $a['SORT'] <=> $b['SORT']);

$arResult['GROUPED_SECTIONS'] = [];
foreach ($sections as $sectionId => $sectionData) {
    $items = $grouped[$sectionId] ?? [];
    $levelOrder = [
        'junior' => 10,
        'middle' => 20,
        'senior' => 30,
        'lead' => 40,
    ];
    $levelRank = static function (string $level) use ($levelOrder): int {
        $normalized = mb_strtolower(trim(tacticum_decode_iblock_text($level)));
        return $levelOrder[$normalized] ?? 100;
    };

    // --- Группировка позиций внутри раздела по названию (NAME) ---
    $groupedItems = [];
    foreach ($items as $item) {
        $name = trim(tacticum_decode_iblock_text((string)$item['NAME']));
        $level = $item['DISPLAY_PROPERTIES']['LEVEL']['VALUE'] ?? null;
        $level = $level !== null ? trim(tacticum_decode_iblock_text((string)$level)) : null;

        // Ключ для группировки: название
        if (!isset($groupedItems[$name])) {
            $groupedItems[$name] = [
                'NAME' => $name,
                'OPTIONS' => array_map(
                    static fn($option) => tacticum_decode_iblock_text((string)$option),
                    (array)($item['DISPLAY_PROPERTIES']['OPTIONS']['DISPLAY_VALUE'] ?? [])
                ),
                'LEVELS' => [],
                'POPULAR' => $item['DISPLAY_PROPERTIES']['POPULAR'] ?? [],
                'ICON' => null, // можно пробросить если нужно
            ];
        }

        if ($level) {
            $groupedItems[$name]['LEVELS'][$level] = [
                'PRICE' => $item['DISPLAY_PROPERTIES']['PRICE']['DISPLAY_VALUE'],
                'PROPS' => $item['DISPLAY_PROPERTIES'],
            ];
        } else {
            // fallback, если LEVEL не заполнен — все равно добавим как отдельный уровень
            $groupedItems[$name]['LEVELS'][''] = [
                'PRICE' => $item['DISPLAY_PROPERTIES']['PRICE']['DISPLAY_VALUE'],
                'PROPS' => $item['DISPLAY_PROPERTIES'],
            ];
        }
    }

    foreach ($groupedItems as &$groupedItem) {
        uksort($groupedItem['LEVELS'], static function ($left, $right) use ($levelRank): int {
            $rankCompare = $levelRank((string)$left) <=> $levelRank((string)$right);
            if ($rankCompare !== 0) {
                return $rankCompare;
            }

            return strnatcasecmp((string)$left, (string)$right);
        });
    }
    unset($groupedItem);

    // Сброс индексов для GROUPED_ITEMS (важно для foreach в шаблоне)
    $arResult['GROUPED_SECTIONS'][] = [
        'ID' => $sectionData['ID'],
        'NAME' => $sectionData['NAME'],
        'SORT' => $sectionData['SORT'],
        'GROUPED_ITEMS' => array_values($groupedItems),
    ];
}
