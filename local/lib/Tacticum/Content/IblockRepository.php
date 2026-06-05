<?php

declare(strict_types=1);

namespace Tacticum\Content;

use Bitrix\Main\Loader;

final class IblockRepository
{
    public static function activeElementResult(
        int $iblockId,
        array $select,
        array $filter = [],
        array $order = ['SORT' => 'ASC']
    ) {
        $filter = array_merge(['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'], $filter);

        return \CIBlockElement::GetList($order, $filter, false, false, $select);
    }

    public static function activeElementsWithPropertiesAndSections(
        int $iblockId,
        array $select,
        array $filter = [],
        array $order = ['SORT' => 'ASC']
    ): array {
        if ($iblockId <= 0 || !Loader::includeModule('iblock')) {
            return [];
        }

        $result = self::activeElementResult($iblockId, $select, $filter, $order);
        $items = [];
        while ($element = $result->GetNextElement()) {
            $fields = $element->GetFields();
            $items[] = [
                'fields' => $fields,
                'properties' => $element->GetProperties(),
                'sections' => self::elementSectionNames((int)($fields['ID'] ?? 0)),
            ];
        }

        return $items;
    }

    public static function firstActiveElementId(int $iblockId): int
    {
        if ($iblockId <= 0 || !Loader::includeModule('iblock')) {
            return 0;
        }

        $result = self::activeElementResult(
            $iblockId,
            ['ID'],
            [],
            ['SORT' => 'ASC', 'ID' => 'ASC']
        );
        $element = $result->Fetch();

        return $element ? (int)$element['ID'] : 0;
    }

    public static function sectionIdByCodes(int $iblockId, array $codes): string
    {
        if ($iblockId <= 0 || $codes === [] || !Loader::includeModule('iblock')) {
            return '';
        }

        $sectionResult = \CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                '=CODE' => array_values($codes),
            ],
            false,
            ['ID', 'CODE']
        );
        $section = $sectionResult->Fetch();

        return $section ? (string)(int)$section['ID'] : '';
    }

    public static function activeSectionsByIds(array $sectionIds, array $select = ['ID', 'NAME', 'SORT']): array
    {
        $sectionIds = array_values(array_unique(array_filter(array_map('intval', $sectionIds))));
        if ($sectionIds === [] || !Loader::includeModule('iblock')) {
            return [];
        }

        $sectionResult = \CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            ['ID' => $sectionIds, 'ACTIVE' => 'Y'],
            false,
            $select
        );
        $sections = [];
        while ($section = $sectionResult->Fetch()) {
            $id = (int)($section['ID'] ?? 0);
            if ($id > 0) {
                $sections[$id] = $section;
            }
        }

        return $sections;
    }

    public static function elementSections(
        int $elementId,
        array $select = ['ID', 'NAME', 'CODE', 'IBLOCK_ID']
    ): array {
        if ($elementId <= 0 || !class_exists('\CIBlockElement')) {
            return [];
        }

        $sectionLinks = \CIBlockElement::GetElementGroups($elementId, true, $select);
        $sections = [];
        while ($section = $sectionLinks->Fetch()) {
            $sections[] = $section;
        }

        return $sections;
    }

    public static function elementSectionNames(int $elementId): array
    {
        $sections = [];
        foreach (self::elementSections($elementId) as $section) {
            $sections[] = (string)($section['NAME'] ?? '');
        }

        return array_values(array_filter($sections, 'strlen'));
    }
}
