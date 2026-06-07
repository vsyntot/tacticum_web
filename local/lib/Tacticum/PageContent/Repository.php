<?php

declare(strict_types=1);

namespace Tacticum\PageContent;

use Bitrix\Main\Loader;
use Tacticum\Content\PublicCopyNormalizer;
use Tacticum\Rest\Config;

final class Repository
{
    public static function fetchSection(string $pageKey, string $sectionKey, string $migrationStatus = 'live'): ?array
    {
        $pageKey = trim($pageKey);
        $sectionKey = trim($sectionKey);
        $migrationStatus = trim($migrationStatus);
        if ($pageKey === '' || $sectionKey === '' || $migrationStatus === '') {
            return null;
        }

        $sectionsIblockId = Config::iblockId('page_sections');
        $blocksIblockId = Config::iblockId('page_blocks');
        if ($sectionsIblockId <= 0 || $blocksIblockId <= 0 || !Loader::includeModule('iblock')) {
            return null;
        }

        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $sectionsIblockId,
                'ACTIVE' => 'Y',
                '=PROPERTY_PAGE_KEY' => $pageKey,
                '=PROPERTY_SECTION_KEY' => $sectionKey,
                '=PROPERTY_MIGRATION_STATUS' => $migrationStatus,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            ['nTopCount' => 1],
            ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'SORT', 'PREVIEW_TEXT', 'DETAIL_TEXT']
        );
        $element = $result->Fetch();
        if (!is_array($element)) {
            return null;
        }

        $sectionId = (int)($element['ID'] ?? 0);
        $properties = self::elementProperties($sectionsIblockId, $sectionId);

        return PublicCopyNormalizer::normalizeArray([
            'id' => $sectionId,
            'code' => trim((string)($element['CODE'] ?? '')),
            'sort' => (int)($element['SORT'] ?? 500),
            'page_key' => self::propertyScalar($properties, 'PAGE_KEY', $pageKey),
            'section_key' => self::propertyScalar($properties, 'SECTION_KEY', $sectionKey),
            'template_key' => self::propertyScalar($properties, 'TEMPLATE_KEY'),
            'migration_status' => self::propertyScalar($properties, 'MIGRATION_STATUS', $migrationStatus),
            'eyebrow' => self::propertyScalar($properties, 'EYEBROW'),
            'title' => self::propertyScalar($properties, 'TITLE', trim((string)($element['NAME'] ?? ''))),
            'text' => self::propertyScalar($properties, 'TEXT', trim((string)($element['PREVIEW_TEXT'] ?? ''))),
            'theme' => self::propertyScalar($properties, 'THEME'),
            'tone' => self::propertyScalar($properties, 'TONE'),
            'cta_text' => self::propertyScalar($properties, 'CTA_TEXT'),
            'cta_href' => self::propertyScalar($properties, 'CTA_HREF'),
            'fallback_partial' => self::propertyScalar($properties, 'FALLBACK_PARTIAL'),
            'owner_scope' => self::propertyScalar($properties, 'OWNER_SCOPE'),
            'blocks' => self::fetchBlocks($blocksIblockId, $sectionId),
        ]);
    }

    private static function fetchBlocks(int $blocksIblockId, int $sectionId): array
    {
        if ($blocksIblockId <= 0 || $sectionId <= 0 || !class_exists('\CIBlockElement')) {
            return [];
        }

        $items = [];
        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $blocksIblockId,
                'ACTIVE' => 'Y',
                '=PROPERTY_SECTION' => $sectionId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'SORT', 'PREVIEW_TEXT', 'DETAIL_TEXT']
        );

        while ($element = $result->Fetch()) {
            $properties = self::elementProperties($blocksIblockId, (int)($element['ID'] ?? 0));
            $items[] = [
                'id' => (int)($element['ID'] ?? 0),
                'code' => trim((string)($element['CODE'] ?? '')),
                'sort' => (int)($element['SORT'] ?? 500),
                'block_key' => self::propertyScalar($properties, 'BLOCK_KEY'),
                'item_type' => self::propertyScalar($properties, 'ITEM_TYPE'),
                'title' => self::propertyScalar($properties, 'TITLE', trim((string)($element['NAME'] ?? ''))),
                'text' => self::propertyScalar($properties, 'TEXT', trim((string)($element['PREVIEW_TEXT'] ?? ''))),
                'icon' => self::propertyScalar($properties, 'ICON'),
                'href' => self::propertyScalar($properties, 'HREF'),
                'meta' => self::propertyScalar($properties, 'META'),
                'value' => self::propertyScalar($properties, 'VALUE'),
                'label' => self::propertyScalar($properties, 'LABEL'),
                'tone' => self::propertyScalar($properties, 'TONE'),
                'proof_status' => self::propertyScalar($properties, 'PROOF_STATUS'),
            ];
        }

        return $items;
    }

    private static function elementProperties(int $iblockId, int $elementId): array
    {
        if ($iblockId <= 0 || $elementId <= 0 || !class_exists('\CIBlockElement')) {
            return [];
        }

        $properties = [];
        $result = \CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc', 'id' => 'asc'], []);
        while ($property = $result->Fetch()) {
            $code = trim((string)($property['CODE'] ?? ''));
            if ($code === '') {
                continue;
            }
            $properties[$code] = $property['VALUE'] ?? '';
        }

        return $properties;
    }

    private static function propertyScalar(array $properties, string $code, string $default = ''): string
    {
        $value = $properties[$code] ?? $default;
        if (is_array($value)) {
            $value = reset($value);
        }

        return trim((string)$value);
    }
}
