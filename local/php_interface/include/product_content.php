<?php

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_content_codes')) {
    function tacticum_product_content_codes(): array
    {
        return [
            'platform' => 'platform.php',
            'agents' => 'agents.php',
            'dev' => 'dev.php',
            'forum' => 'forum.php',
        ];
    }
}

if (!function_exists('tacticum_product_content_source')) {
    function tacticum_product_content_source(): string
    {
        $productsConfig = function_exists('tacticum_rest_get_config_section')
            ? tacticum_rest_get_config_section('products')
            : [];

        $source = strtolower(trim((string)($productsConfig['source'] ?? 'bitrix')));
        if (!in_array($source, ['auto', 'bitrix', 'fallback'], true)) {
            return 'bitrix';
        }

        if ($source !== 'bitrix' && !tacticum_product_content_fallback_allowed()) {
            return 'bitrix';
        }

        return $source;
    }
}

if (!function_exists('tacticum_product_content_configured_source')) {
    function tacticum_product_content_configured_source(): string
    {
        $productsConfig = function_exists('tacticum_rest_get_config_section')
            ? tacticum_rest_get_config_section('products')
            : [];

        $source = strtolower(trim((string)($productsConfig['source'] ?? 'bitrix')));

        return in_array($source, ['auto', 'bitrix', 'fallback'], true) ? $source : 'bitrix';
    }
}

if (!function_exists('tacticum_product_content_fallback_allowed')) {
    function tacticum_product_content_fallback_allowed(): bool
    {
        $productsConfig = function_exists('tacticum_rest_get_config_section')
            ? tacticum_rest_get_config_section('products')
            : [];
        $value = $productsConfig['allow_fallback'] ?? false;

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'y'], true);
        }

        return false;
    }
}

if (!function_exists('tacticum_product_content_cache_ttl')) {
    function tacticum_product_content_cache_ttl(): int
    {
        $productsConfig = function_exists('tacticum_rest_get_config_section')
            ? tacticum_rest_get_config_section('products')
            : [];
        $ttl = $productsConfig['cache_ttl'] ?? null;

        if ($ttl === null && function_exists('tacticum_api_cache_ttl')) {
            $ttl = tacticum_api_cache_ttl('products');
        }
        if ($ttl === null) {
            $ttl = 300;
        }

        $ttl = (int)$ttl;

        return $ttl < 0 ? 0 : $ttl;
    }
}

if (!function_exists('tacticum_product_content_cache_dir')) {
    function tacticum_product_content_cache_dir(): string
    {
        return '/tacticum/product_content';
    }
}

if (!function_exists('tacticum_product_content_schema_version')) {
    function tacticum_product_content_schema_version(): string
    {
        return 'v1';
    }
}

if (!function_exists('tacticum_product_content_cache_key')) {
    function tacticum_product_content_cache_key(string $productCode): string
    {
        $iblockIds = [];
        foreach (['products', 'product_blocks', 'product_use_cases'] as $key) {
            $iblockIds[$key] = function_exists('tacticum_rest_get_iblock_id')
                ? tacticum_rest_get_iblock_id($key)
                : 0;
        }

        $cacheIdentity = [
            'schema_version' => tacticum_product_content_schema_version(),
            'source_mode' => tacticum_product_content_source(),
            'configured_source' => function_exists('tacticum_product_content_configured_source')
                ? tacticum_product_content_configured_source()
                : tacticum_product_content_source(),
            'fallback_allowed' => function_exists('tacticum_product_content_fallback_allowed')
                && tacticum_product_content_fallback_allowed(),
            'iblock_ids' => $iblockIds,
        ];

        return 'product_content_' . $productCode . '_' . md5(serialize($cacheIdentity));
    }
}

if (!function_exists('tacticum_product_content_related_iblock_ids')) {
    function tacticum_product_content_related_iblock_ids(): array
    {
        $ids = [];
        foreach (['products', 'product_blocks', 'product_use_cases'] as $key) {
            $id = function_exists('tacticum_rest_get_iblock_id')
                ? tacticum_rest_get_iblock_id($key)
                : 0;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }
}

if (!function_exists('tacticum_product_content_start_tag_cache')) {
    function tacticum_product_content_start_tag_cache(): void
    {
        global $CACHE_MANAGER;

        if (
            defined('BX_COMP_MANAGED_CACHE')
            && is_object($CACHE_MANAGER)
            && method_exists($CACHE_MANAGER, 'StartTagCache')
            && method_exists($CACHE_MANAGER, 'RegisterTag')
        ) {
            $CACHE_MANAGER->StartTagCache(tacticum_product_content_cache_dir());
            foreach (tacticum_product_content_related_iblock_ids() as $iblockId) {
                $CACHE_MANAGER->RegisterTag('iblock_id_' . $iblockId);
            }
        }
    }
}

if (!function_exists('tacticum_product_content_end_tag_cache')) {
    function tacticum_product_content_end_tag_cache(): void
    {
        global $CACHE_MANAGER;

        if (
            defined('BX_COMP_MANAGED_CACHE')
            && is_object($CACHE_MANAGER)
            && method_exists($CACHE_MANAGER, 'EndTagCache')
        ) {
            $CACHE_MANAGER->EndTagCache();
        }
    }
}

if (!function_exists('tacticum_product_content_clear_cache')) {
    function tacticum_product_content_clear_cache(int $iblockId = 0): void
    {
        Cache::createInstance()->cleanDir(tacticum_product_content_cache_dir());

        global $CACHE_MANAGER;
        if (
            defined('BX_COMP_MANAGED_CACHE')
            && is_object($CACHE_MANAGER)
            && method_exists($CACHE_MANAGER, 'ClearByTag')
        ) {
            $tagIblockIds = $iblockId > 0 ? [$iblockId] : tacticum_product_content_related_iblock_ids();
            foreach ($tagIblockIds as $tagIblockId) {
                $CACHE_MANAGER->ClearByTag('iblock_id_' . $tagIblockId);
            }
        }
    }
}

if (!function_exists('tacticum_product_content_clear_cache_on_iblock_change')) {
    function tacticum_product_content_clear_cache_on_iblock_change(array &$fields): void
    {
        $iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
        if ($iblockId > 0 && in_array($iblockId, tacticum_product_content_related_iblock_ids(), true)) {
            tacticum_product_content_clear_cache($iblockId);
        }
    }
}

if (!function_exists('tacticum_product_content_clear_cache_on_element_delete')) {
    function tacticum_product_content_clear_cache_on_element_delete(array $fields): void
    {
        $iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
        if ($iblockId > 0 && in_array($iblockId, tacticum_product_content_related_iblock_ids(), true)) {
            tacticum_product_content_clear_cache($iblockId);
        }
    }
}

if (!function_exists('tacticum_product_content_clear_cache_on_property_change')) {
    function tacticum_product_content_clear_cache_on_property_change(mixed $elementId, mixed $iblockId = 0): void
    {
        $iblockId = (int)$iblockId;
        if ($iblockId > 0 && in_array($iblockId, tacticum_product_content_related_iblock_ids(), true)) {
            tacticum_product_content_clear_cache($iblockId);
        }
    }
}

if (!function_exists('tacticum_register_product_content_cache_handlers')) {
    function tacticum_register_product_content_cache_handlers(): void
    {
        static $registered = false;
        if ($registered || !class_exists('Bitrix\Main\EventManager')) {
            return;
        }
        $registered = true;

        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', 'tacticum_product_content_clear_cache_on_iblock_change');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', 'tacticum_product_content_clear_cache_on_iblock_change');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', 'tacticum_product_content_clear_cache_on_element_delete');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValues', 'tacticum_product_content_clear_cache_on_property_change');
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValuesEx', 'tacticum_product_content_clear_cache_on_property_change');
    }
}

if (!function_exists('tacticum_product_content_json_decode')) {
    function tacticum_product_content_json_decode(mixed $value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}

if (!function_exists('tacticum_product_content_property_scalar')) {
    function tacticum_product_content_property_scalar(array $properties, string $code, string $default = ''): string
    {
        $value = $properties[$code] ?? $default;
        if (is_array($value)) {
            $value = reset($value);
        }

        return is_scalar($value) ? trim((string)$value) : $default;
    }
}

if (!function_exists('tacticum_product_content_element_properties')) {
    function tacticum_product_content_element_properties(int $iblockId, int $elementId): array
    {
        if ($iblockId <= 0 || $elementId <= 0 || !class_exists('CIBlockElement')) {
            return [];
        }

        $properties = [];
        $result = CIBlockElement::GetProperty($iblockId, $elementId, ['sort' => 'asc', 'id' => 'asc'], []);
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
}

if (!function_exists('tacticum_product_content_fetch_element_by_code')) {
    function tacticum_product_content_fetch_element_by_code(int $iblockId, string $code): ?array
    {
        if ($iblockId <= 0 || $code === '' || !class_exists('CIBlockElement')) {
            return null;
        }

        $result = CIBlockElement::GetList(
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
}

if (!function_exists('tacticum_product_content_block_payload')) {
    function tacticum_product_content_block_payload(array $element): array
    {
        $payload = tacticum_product_content_json_decode($element['DETAIL_TEXT'] ?? '');
        if (!empty($payload)) {
            return $payload;
        }

        $payload = tacticum_product_content_json_decode($element['PREVIEW_TEXT'] ?? '');

        return $payload;
    }
}

if (!function_exists('tacticum_product_content_apply_block')) {
    function tacticum_product_content_apply_block(array &$page, string $type, array $payload): void
    {
        if (empty($payload)) {
            return;
        }

        match ($type) {
            'hero' => $page = array_merge($page, $payload),
            'fit_guide' => $page['fit_guide'] = $payload,
            'section' => $page['sections'][] = $payload,
            'architecture' => $page['architecture'] = $payload,
            'use_cases' => $page['use_cases'] = array_merge($payload, is_array($page['use_cases'] ?? null) ? $page['use_cases'] : []),
            'comparison' => $page['comparison'] = $payload,
            'procurement' => $page['procurement'] = $payload,
            'rollout' => $page['rollout'] = $payload,
            'proof' => $page['proof'] = $payload,
            'faq' => $page['faq'] = $payload,
            'cta' => $page['cta'] = array_merge(is_array($page['cta'] ?? null) ? $page['cta'] : [], $payload),
            default => null,
        };
    }
}

if (!function_exists('tacticum_product_content_fetch_blocks')) {
    function tacticum_product_content_fetch_blocks(int $blocksIblockId, int $productElementId): array
    {
        if ($blocksIblockId <= 0 || $productElementId <= 0 || !class_exists('CIBlockElement')) {
            return [];
        }

        $blocks = [];
        $result = CIBlockElement::GetList(
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

        while ($element = $result->Fetch()) {
            $properties = tacticum_product_content_element_properties($blocksIblockId, (int)$element['ID']);
            $blocks[] = [
                'type' => tacticum_product_content_property_scalar($properties, 'BLOCK_TYPE'),
                'key' => tacticum_product_content_property_scalar($properties, 'BLOCK_KEY'),
                'payload' => tacticum_product_content_block_payload($element),
            ];
        }

        return $blocks;
    }
}

if (!function_exists('tacticum_product_content_fetch_use_cases')) {
    function tacticum_product_content_fetch_use_cases(int $useCasesIblockId, int $productElementId): array
    {
        if ($useCasesIblockId <= 0 || $productElementId <= 0 || !class_exists('CIBlockElement')) {
            return [];
        }

        $items = [];
        $result = CIBlockElement::GetList(
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
            $properties = tacticum_product_content_element_properties($useCasesIblockId, (int)$element['ID']);
            $payload = tacticum_product_content_json_decode($element['DETAIL_TEXT'] ?? '');
            $items[] = [
                'title' => trim((string)($element['NAME'] ?? '')) !== ''
                    ? trim((string)($element['NAME'] ?? ''))
                    : tacticum_product_content_property_scalar($payload, 'title'),
                'trigger' => tacticum_product_content_property_scalar($payload, 'trigger', tacticum_product_content_property_scalar($properties, 'TRIGGER')),
                'owner' => tacticum_product_content_property_scalar($payload, 'owner', tacticum_product_content_property_scalar($properties, 'OWNER')),
                'pilot_input' => tacticum_product_content_property_scalar($payload, 'pilot_input', tacticum_product_content_property_scalar($properties, 'PILOT_INPUT')),
                'pilot_output' => tacticum_product_content_property_scalar($payload, 'pilot_output', tacticum_product_content_property_scalar($properties, 'PILOT_OUTPUT')),
                'limitation' => tacticum_product_content_property_scalar($payload, 'limitation', tacticum_product_content_property_scalar($properties, 'LIMITATION')),
                'proof_status' => tacticum_product_content_property_scalar($payload, 'proof_status', tacticum_product_content_property_scalar($properties, 'PROOF_STATUS', 'pilot-artifact')),
                'cta_intent' => tacticum_product_content_property_scalar($payload, 'cta_intent', tacticum_product_content_property_scalar($properties, 'CTA_INTENT')),
            ];
        }

        return $items;
    }
}

if (!function_exists('tacticum_product_content_is_minimum_renderable')) {
    function tacticum_product_content_is_minimum_renderable(array $page): bool
    {
        $title = trim((string)($page['title'] ?? ''));
        $lead = trim((string)($page['lead'] ?? ''));
        $cta = is_array($page['cta'] ?? null) ? $page['cta'] : [];

        return $title !== '' && $lead !== '' && !empty($cta);
    }
}

if (!function_exists('tacticum_product_content_completeness_diagnostics')) {
    function tacticum_product_content_completeness_diagnostics(array $page): array
    {
        $required = [
            'fit_guide',
            'use_cases',
            'comparison',
            'procurement',
            'rollout',
            'proof',
            'faq',
            'architecture',
        ];
        $missing = [];

        foreach ($required as $key) {
            if (empty($page[$key]) || !is_array($page[$key])) {
                $missing[] = $key;
            }
        }

        return [
            'minimum_renderable' => tacticum_product_content_is_minimum_renderable($page),
            'missing_to_be_blocks' => $missing,
        ];
    }
}

if (!function_exists('tacticum_product_content_bitrix_data_uncached')) {
    function tacticum_product_content_bitrix_data_uncached(string $productCode): array
    {
        if (!isset(tacticum_product_content_codes()[$productCode])) {
            return [];
        }

        if (!Loader::includeModule('iblock')) {
            return [];
        }

        $productsIblockId = function_exists('tacticum_rest_get_iblock_id')
            ? tacticum_rest_get_iblock_id('products')
            : 0;
        if ($productsIblockId <= 0) {
            return [];
        }

        $product = tacticum_product_content_fetch_element_by_code($productsIblockId, $productCode);
        if ($product === null) {
            return [];
        }

        $productId = (int)$product['ID'];
        $properties = tacticum_product_content_element_properties($productsIblockId, $productId);
        $page = [
            'eyebrow' => tacticum_product_content_property_scalar($properties, 'EYEBROW', (string)($product['NAME'] ?? '')),
            'title' => tacticum_product_content_property_scalar($properties, 'PRODUCT_TITLE', (string)($product['DETAIL_TEXT'] ?? '')),
            'lead' => trim((string)($product['PREVIEW_TEXT'] ?? '')) !== ''
                ? trim((string)($product['PREVIEW_TEXT'] ?? ''))
                : tacticum_product_content_property_scalar($properties, 'PRODUCT_LEAD'),
            'primary_cta_text' => tacticum_product_content_property_scalar($properties, 'PRIMARY_CTA_TEXT', 'Обсудить пилот'),
            'secondary_cta_text' => tacticum_product_content_property_scalar($properties, 'SECONDARY_CTA_TEXT', 'Как внедряем'),
            'secondary_cta_href' => tacticum_product_content_property_scalar($properties, 'SECONDARY_CTA_HREF', '/services/'),
            'badges' => tacticum_product_content_json_decode(tacticum_product_content_property_scalar($properties, 'BADGES_JSON')),
            'hero_cards' => tacticum_product_content_json_decode(tacticum_product_content_property_scalar($properties, 'HERO_CARDS_JSON')),
            'sections' => [],
            'cta' => tacticum_product_content_json_decode(tacticum_product_content_property_scalar($properties, 'CTA_JSON')),
            '_source' => 'bitrix',
            '_product_code' => $productCode,
            '_product_element_id' => $productId,
        ];

        $blocksIblockId = function_exists('tacticum_rest_get_iblock_id')
            ? tacticum_rest_get_iblock_id('product_blocks')
            : 0;
        foreach (tacticum_product_content_fetch_blocks($blocksIblockId, $productId) as $block) {
            tacticum_product_content_apply_block($page, (string)$block['type'], is_array($block['payload']) ? $block['payload'] : []);
        }

        $useCasesIblockId = function_exists('tacticum_rest_get_iblock_id')
            ? tacticum_rest_get_iblock_id('product_use_cases')
            : 0;
        $useCases = tacticum_product_content_fetch_use_cases($useCasesIblockId, $productId);
        if (!empty($useCases)) {
            $page['use_cases'] = is_array($page['use_cases'] ?? null) ? $page['use_cases'] : [];
            $page['use_cases']['items'] = $useCases;
        }

        $page['_diagnostics'] = tacticum_product_content_completeness_diagnostics($page);

        return $page;
    }
}

if (!function_exists('tacticum_product_content_bitrix_data')) {
    function tacticum_product_content_bitrix_data(string $productCode): array
    {
        $ttl = tacticum_product_content_cache_ttl();
        if ($ttl <= 0) {
            return tacticum_product_content_bitrix_data_uncached($productCode);
        }

        $cache = Cache::createInstance();
        $cacheKey = tacticum_product_content_cache_key($productCode);
        $cacheDir = tacticum_product_content_cache_dir();

        if ($cache->initCache($ttl, $cacheKey, $cacheDir)) {
            $cached = $cache->getVars();
            if (is_array($cached) && isset($cached['page']) && is_array($cached['page'])) {
                return $cached['page'];
            }
        }

        $page = tacticum_product_content_bitrix_data_uncached($productCode);
        if (!is_array($page)) {
            $page = [];
        }

        if ($cache->startDataCache($ttl, $cacheKey, $cacheDir)) {
            tacticum_product_content_start_tag_cache();
            tacticum_product_content_end_tag_cache();
            $cache->endDataCache(['page' => $page]);
        }

        return $page;
    }
}
