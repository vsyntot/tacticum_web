<?php

namespace Tacticum\Offer;

use Bitrix\Main\Loader;

final class CatalogRepository
{
    public static function findElement(int $offerId, string $offerCode): ?array
    {
        if (!Loader::includeModule('iblock')) {
            return null;
        }

        $iblockId = function_exists('tacticum_iblock_id') ? tacticum_iblock_id('offer') : 0;
        if ($iblockId <= 0) {
            return null;
        }

        $filter = ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'];
        if ($offerCode !== '') {
            if (!preg_match('/^[A-Za-z0-9_-]{1,120}$/', $offerCode)) {
                return null;
            }
            $filter['=CODE'] = $offerCode;
        } elseif ($offerId > 0) {
            $filter['=ID'] = $offerId;
        } else {
            return null;
        }

        $result = \CIBlockElement::GetList(
            [],
            $filter,
            false,
            ['nTopCount' => 1],
            ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'DATE_CREATE', 'TIMESTAMP_X', 'PROPERTY_TITLE', 'PROPERTY_DESCRIPTION', 'PROPERTY_H1']
        );
        $element = $result->Fetch();
        if (!$element || trim((string)$element['CODE']) === '') {
            return null;
        }

        $keywords = [];
        $propertyResult = \CIBlockElement::GetProperty($iblockId, (int)$element['ID'], ['sort' => 'asc'], ['CODE' => 'KEYWORDS']);
        while ($property = $propertyResult->Fetch()) {
            $value = trim(CatalogMapper::decodeText((string)($property['VALUE'] ?? '')));
            if ($value !== '') {
                $keywords[] = $value;
            }
        }

        $title = trim(CatalogMapper::decodeText((string)($element['PROPERTY_TITLE_VALUE'] ?? '')))
            ?: trim(CatalogMapper::decodeText((string)($element['NAME'] ?? '')))
            ?: 'Пример расчета проекта - Тактикум';
        $description = trim(CatalogMapper::decodeText((string)($element['PROPERTY_DESCRIPTION_VALUE'] ?? '')))
            ?: 'Пример расчета AI-проекта Tacticum: состав работ, команда, сроки и бюджет.';

        $element['SEO_TITLE'] = $title;
        $element['SEO_DESCRIPTION'] = $description;
        $element['SEO_H1'] = trim(CatalogMapper::decodeText((string)($element['PROPERTY_H1_VALUE'] ?? '')));
        $element['KEYWORDS'] = $keywords;

        return $element;
    }

    public static function items(int $iblockId): array
    {
        if ($iblockId <= 0 || !Loader::includeModule('iblock')) {
            return [];
        }

        $items = [];
        $result = \CIBlockElement::GetList(
            ['ID' => 'DESC'],
            ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            false,
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'DATE_CREATE', 'DATE_ACTIVE_FROM', 'TIMESTAMP_X']
        );
        while ($element = $result->GetNextElement()) {
            $item = CatalogMapper::itemFromElement($element->GetFields(), $element->GetProperties());
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
