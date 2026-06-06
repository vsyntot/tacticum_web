<?php

namespace Tacticum\Product;

final class ContentRepository
{
    public static function elementProperties(int $iblockId, int $elementId): array
    {
        if ($iblockId <= 0 || $elementId <= 0 || !class_exists('CIBlockElement')) {
            return [];
        }

        $properties = [];
        $result = \CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc', 'id' => 'asc'], []);
        while ($property = $result->Fetch()) {
            $code = trim((string)($property['CODE'] ?? ''));
            if ($code === '') {
                continue;
            }

            $value = $property['VALUE'] ?? null;
            if (($property['MULTIPLE'] ?? 'N') === 'Y') {
                if (!array_key_exists($code, $properties)) {
                    $properties[$code] = [];
                }
                if ($value !== null && $value !== '') {
                    $properties[$code][] = $value;
                }
                continue;
            }

            $properties[$code] = $value;
        }

        return $properties;
    }

    public static function fetchElementByCode(int $iblockId, string $code): ?array
    {
        if ($iblockId <= 0 || $code === '' || !class_exists('CIBlockElement')) {
            return null;
        }

        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                '=CODE' => $code,
                'ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            ['nTopCount' => 1],
            ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT']
        );
        $element = $result->Fetch();

        return is_array($element) ? $element : null;
    }

    public static function fetchBlocks(int $blocksIblockId, int $productElementId): array
    {
        if ($blocksIblockId <= 0 || $productElementId <= 0 || !class_exists('CIBlockElement')) {
            return [];
        }

        $blocks = [];
        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $blocksIblockId,
                'ACTIVE' => 'Y',
                'PROPERTY_PRODUCT' => $productElementId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'SORT', 'PREVIEW_TEXT', 'DETAIL_TEXT']
        );

        $containers = [];
        $children = [];
        while ($element = $result->Fetch()) {
            $properties = self::elementProperties($blocksIblockId, (int)$element['ID']);
            $parentId = (int)ContentMapper::propertyScalar($properties, 'PARENT_BLOCK');
            if ($parentId > 0) {
                $children[$parentId][] = [
                    'element' => $element,
                    'properties' => $properties,
                ];
                continue;
            }

            $containers[] = [
                'element' => $element,
                'properties' => $properties,
            ];
        }

        foreach ($containers as $container) {
            $element = $container['element'];
            $properties = $container['properties'];
            $id = (int)($element['ID'] ?? 0);
            $type = ContentMapper::propertyScalar($properties, 'BLOCK_TYPE');
            $blocks[] = [
                'type' => $type,
                'key' => ContentMapper::propertyScalar($properties, 'BLOCK_KEY'),
                'payload' => ContentBlockMapper::payload($type, $element, $properties, $children[$id] ?? []),
            ];
        }

        return $blocks;
    }

    public static function fetchUseCases(int $useCasesIblockId, int $productElementId): array
    {
        if ($useCasesIblockId <= 0 || $productElementId <= 0 || !class_exists('CIBlockElement')) {
            return [];
        }

        $items = [];
        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $useCasesIblockId,
                'ACTIVE' => 'Y',
                'PROPERTY_PRODUCT' => $productElementId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'SORT', 'DETAIL_TEXT']
        );

        while ($element = $result->Fetch()) {
            $properties = self::elementProperties($useCasesIblockId, (int)$element['ID']);
            $payload = ContentMapper::jsonDecode($element['DETAIL_TEXT'] ?? '');
            $items[] = [
                'title' => trim((string)($element['NAME'] ?? '')) !== ''
                    ? trim((string)($element['NAME'] ?? ''))
                    : ContentMapper::propertyScalar($payload, 'title'),
                'trigger' => ContentMapper::propertyScalar($properties, 'TRIGGER', ContentMapper::propertyScalar($payload, 'trigger')),
                'owner' => ContentMapper::propertyScalar($properties, 'OWNER', ContentMapper::propertyScalar($payload, 'owner')),
                'pilot_input' => ContentMapper::propertyScalar($properties, 'PILOT_INPUT', ContentMapper::propertyScalar($payload, 'pilot_input')),
                'pilot_output' => ContentMapper::propertyScalar($properties, 'PILOT_OUTPUT', ContentMapper::propertyScalar($payload, 'pilot_output')),
                'limitation' => ContentMapper::propertyScalar($properties, 'LIMITATION', ContentMapper::propertyScalar($payload, 'limitation')),
                'proof_status' => ContentMapper::propertyScalar($properties, 'PROOF_STATUS', ContentMapper::propertyScalar($payload, 'proof_status', 'pilot-artifact')),
                'cta_intent' => ContentMapper::propertyScalar($properties, 'CTA_INTENT', ContentMapper::propertyScalar($payload, 'cta_intent')),
            ];
        }

        return $items;
    }

    public static function fetchProductFaq(int $faqIblockId, int $productElementId): array
    {
        if ($faqIblockId <= 0 || $productElementId <= 0 || !class_exists('CIBlockElement')) {
            return [];
        }

        $items = [];
        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $faqIblockId,
                'ACTIVE' => 'Y',
                'PROPERTY_PRODUCT' => $productElementId,
                'CHECK_PERMISSIONS' => 'N',
            ],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_TEXT', 'PREVIEW_TEXT']
        );

        while ($element = $result->Fetch()) {
            $question = self::plainText((string)($element['NAME'] ?? ''));
            $answer = self::plainText((string)($element['DETAIL_TEXT'] ?? ''));
            if ($answer === '') {
                $answer = self::plainText((string)($element['PREVIEW_TEXT'] ?? ''));
            }
            if ($question === '' || $answer === '') {
                continue;
            }

            $items[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $items;
    }

    private static function plainText(string $value): string
    {
        $value = trim($value);
        if ($value === '' || str_starts_with($value, '{') || str_starts_with($value, '[')) {
            return '';
        }

        return trim((string)preg_replace('/\s+/u', ' ', strip_tags($value)));
    }
}
