<?php

namespace Tacticum\Product;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;

final class ContentService
{
    public static function bitrixDataUncached(string $productCode): array
    {
        if (!isset(ContentRuntime::codes()[$productCode]) || !Loader::includeModule('iblock')) {
            return [];
        }

        $productsIblockId = function_exists('tacticum_rest_get_iblock_id')
            ? tacticum_rest_get_iblock_id('products')
            : 0;
        if ($productsIblockId <= 0) {
            return [];
        }

        $product = ContentRepository::fetchElementByCode($productsIblockId, $productCode);
        if ($product === null) {
            return [];
        }

        $productId = (int)$product['ID'];
        $properties = ContentRepository::elementProperties($productsIblockId, $productId);
        $badges = ContentMapper::propertyList($properties, 'BADGE');
        if (empty($badges)) {
            $badges = ContentMapper::jsonDecode(ContentMapper::propertyScalar($properties, 'BADGES_JSON'));
        }
        $cta = self::productCta($properties, $productCode);
        if (empty($cta)) {
            $cta = ContentMapper::jsonDecode(ContentMapper::propertyScalar($properties, 'CTA_JSON'));
        }

        $page = [
            'eyebrow' => ContentMapper::propertyScalar($properties, 'EYEBROW', (string)($product['NAME'] ?? '')),
            'title' => ContentMapper::propertyScalar($properties, 'PRODUCT_TITLE', (string)($product['DETAIL_TEXT'] ?? '')),
            'lead' => trim((string)($product['PREVIEW_TEXT'] ?? '')) !== ''
                ? trim((string)($product['PREVIEW_TEXT'] ?? ''))
                : ContentMapper::propertyScalar($properties, 'PRODUCT_LEAD'),
            'primary_cta_text' => ContentMapper::propertyScalar($properties, 'PRIMARY_CTA_TEXT', 'Обсудить пилот'),
            'secondary_cta_text' => ContentMapper::propertyScalar($properties, 'SECONDARY_CTA_TEXT', 'Как внедряем'),
            'secondary_cta_href' => ContentMapper::propertyScalar($properties, 'SECONDARY_CTA_HREF', '/services/'),
            'badges' => $badges,
            'hero_cards' => ContentMapper::jsonDecode(ContentMapper::propertyScalar($properties, 'HERO_CARDS_JSON')),
            'sections' => [],
            'cta' => $cta,
            '_source' => 'bitrix',
            '_product_code' => $productCode,
            '_product_element_id' => $productId,
        ];

        $blocksIblockId = function_exists('tacticum_rest_get_iblock_id')
            ? tacticum_rest_get_iblock_id('product_blocks')
            : 0;
        foreach (ContentRepository::fetchBlocks($blocksIblockId, $productId) as $block) {
            ContentMapper::applyBlock($page, (string)$block['type'], is_array($block['payload']) ? $block['payload'] : []);
        }

        $useCasesIblockId = function_exists('tacticum_rest_get_iblock_id')
            ? tacticum_rest_get_iblock_id('product_use_cases')
            : 0;
        $useCases = ContentRepository::fetchUseCases($useCasesIblockId, $productId);
        if (!empty($useCases)) {
            $page['use_cases'] = is_array($page['use_cases'] ?? null) ? $page['use_cases'] : [];
            $page['use_cases']['items'] = $useCases;
        }

        $page['_diagnostics'] = ContentMapper::completenessDiagnostics($page);

        return $page;
    }

    private static function productCta(array $properties, string $productCode): array
    {
        $hasCtaData = false;
        foreach ([
            'CTA_FORM_ID',
            'CTA_FIELD_PREFIX',
            'CTA_TITLE',
            'CTA_TEXT',
            'CTA_FORM_TITLE',
            'CTA_BUTTON_TEXT',
            'CTA_SCENARIO_LABEL',
            'CTA_SCENARIO_EMPTY_LABEL',
            'CTA_LEAD_ENTRY',
            'CTA_LEAD_PAGE_ROLE',
            'CTA_LEAD_PRODUCT',
            'CTA_LEAD_INTENT',
            'CTA_LEAD_CTA',
            'CTA_LEAD_NEXT_STEP',
        ] as $code) {
            if (ContentMapper::propertyScalar($properties, $code) !== '') {
                $hasCtaData = true;
                break;
            }
        }

        if (!$hasCtaData) {
            return [];
        }

        $leadProduct = ContentMapper::propertyScalar($properties, 'CTA_LEAD_PRODUCT', $productCode);
        $cta = [
            'form_id' => ContentMapper::propertyScalar($properties, 'CTA_FORM_ID'),
            'field_prefix' => ContentMapper::propertyScalar($properties, 'CTA_FIELD_PREFIX'),
            'title' => ContentMapper::propertyScalar($properties, 'CTA_TITLE'),
            'text' => ContentMapper::propertyScalar($properties, 'CTA_TEXT'),
            'form_title' => ContentMapper::propertyScalar($properties, 'CTA_FORM_TITLE'),
            'button_text' => ContentMapper::propertyScalar($properties, 'CTA_BUTTON_TEXT'),
            'scenario_label' => ContentMapper::propertyScalar($properties, 'CTA_SCENARIO_LABEL'),
            'scenario_empty_label' => ContentMapper::propertyScalar($properties, 'CTA_SCENARIO_EMPTY_LABEL'),
            'lead_context' => [
                'lead_entry' => ContentMapper::propertyScalar($properties, 'CTA_LEAD_ENTRY'),
                'lead_page_role' => ContentMapper::propertyScalar($properties, 'CTA_LEAD_PAGE_ROLE'),
                'lead_product' => $leadProduct,
                'lead_intent' => ContentMapper::propertyScalar($properties, 'CTA_LEAD_INTENT'),
                'lead_cta' => ContentMapper::propertyScalar($properties, 'CTA_LEAD_CTA'),
                'lead_next_step' => ContentMapper::propertyScalar($properties, 'CTA_LEAD_NEXT_STEP'),
            ],
        ];

        $cta = self::withoutEmpty($cta);
        if (isset($cta['lead_context']) && is_array($cta['lead_context'])) {
            $cta['lead_context'] = self::withoutEmpty($cta['lead_context']);
        }
        if (empty($cta['lead_context'])) {
            unset($cta['lead_context']);
        }

        return $cta;
    }

    private static function withoutEmpty(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if ($value === '' || $value === [] || $value === null) {
                unset($payload[$key]);
            }
        }

        return $payload;
    }

    public static function bitrixData(string $productCode): array
    {
        $ttl = ContentRuntime::cacheTtl();
        if ($ttl <= 0) {
            return self::bitrixDataUncached($productCode);
        }

        $cache = Cache::createInstance();
        $cacheKey = ContentRuntime::cacheKey($productCode);
        $cacheDir = ContentRuntime::cacheDir();
        if ($cache->initCache($ttl, $cacheKey, $cacheDir)) {
            $cached = $cache->getVars();
            if (is_array($cached) && isset($cached['page']) && is_array($cached['page'])) {
                return $cached['page'];
            }
        }

        $page = self::bitrixDataUncached($productCode);
        if (!is_array($page)) {
            $page = [];
        }

        if ($cache->startDataCache($ttl, $cacheKey, $cacheDir)) {
            ContentRuntime::startTagCache();
            ContentRuntime::endTagCache();
            $cache->endDataCache(['page' => $page]);
        }

        return $page;
    }
}
