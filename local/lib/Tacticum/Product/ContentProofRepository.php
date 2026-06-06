<?php

namespace Tacticum\Product;

final class ContentProofRepository
{
    private const PUBLIC_PROOF_META = [
        'cases' => 'Кейс',
        'feedback' => 'Отзыв',
        'clients' => 'Клиент',
    ];

    public static function fetchProductProof(array $proofIblockIds, int $productElementId): array
    {
        if ($productElementId <= 0 || !class_exists('CIBlockElement')) {
            return [];
        }

        $items = [];
        foreach (self::PUBLIC_PROOF_META as $iblockKey => $meta) {
            $iblockId = (int)($proofIblockIds[$iblockKey] ?? 0);
            if ($iblockId <= 0 || !self::hasProperty($iblockId, 'PUBLIC_RENDER_APPROVED')) {
                continue;
            }

            $result = \CIBlockElement::GetList(
                ['SORT' => 'ASC', 'ID' => 'ASC'],
                [
                    'IBLOCK_ID' => $iblockId,
                    'ACTIVE' => 'Y',
                    'PROPERTY_PRODUCT' => $productElementId,
                    'PROPERTY_PUBLIC_RENDER_APPROVED' => 'Y',
                    'CHECK_PERMISSIONS' => 'N',
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT']
            );

            while ($element = $result->Fetch()) {
                $properties = ContentRepository::elementProperties($iblockId, (int)($element['ID'] ?? 0));
                $item = self::publicProofItem((string)$iblockKey, (string)$meta, $element, $properties);
                if ($item !== null) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    private static function publicProofItem(string $iblockKey, string $meta, array $element, array $properties): ?array
    {
        $title = self::plainText((string)($element['NAME'] ?? ''));
        $text = self::plainText((string)($element['PREVIEW_TEXT'] ?? ''));
        if ($text === '') {
            $text = self::plainText((string)($element['DETAIL_TEXT'] ?? ''));
        }

        if ($iblockKey === 'feedback') {
            $company = self::plainText(ContentMapper::propertyScalar($properties, 'COMPANY'));
            if ($company !== '') {
                $title = $company;
            }
        }

        if ($title === '' || $text === '') {
            return null;
        }

        return [
            'meta' => $meta,
            'title' => $title,
            'text' => $text,
            'proof_status' => 'public-safe',
        ];
    }

    private static function hasProperty(int $iblockId, string $code): bool
    {
        if ($iblockId <= 0 || !class_exists('CIBlockProperty')) {
            return false;
        }

        $result = \CIBlockProperty::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'CODE' => $code,
                'ACTIVE' => 'Y',
            ]
        );

        return is_array($result->Fetch());
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
