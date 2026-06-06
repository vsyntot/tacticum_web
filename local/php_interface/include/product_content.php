<?php

use Tacticum\Product\{ContentMapper, ContentRepository, ContentRuntime, ContentService};

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_content_codes')) {
    function tacticum_product_content_codes(): array { return ContentRuntime::codes(); }
}

if (!function_exists('tacticum_product_content_source')) {
    function tacticum_product_content_source(): string { return ContentRuntime::source(); }
}

if (!function_exists('tacticum_product_content_configured_source')) {
    function tacticum_product_content_configured_source(): string { return ContentRuntime::configuredSource(); }
}

if (!function_exists('tacticum_product_content_fallback_allowed')) {
    function tacticum_product_content_fallback_allowed(): bool { return ContentRuntime::fallbackAllowed(); }
}

if (!function_exists('tacticum_product_content_cache_ttl')) {
    function tacticum_product_content_cache_ttl(): int
    {
        return ContentRuntime::cacheTtl();
    }
}

if (!function_exists('tacticum_product_content_cache_dir')) {
    function tacticum_product_content_cache_dir(): string
    {
        return ContentRuntime::cacheDir();
    }
}

if (!function_exists('tacticum_product_content_schema_version')) {
    function tacticum_product_content_schema_version(): string
    {
        return ContentRuntime::schemaVersion();
    }
}

if (!function_exists('tacticum_product_content_cache_key')) {
    function tacticum_product_content_cache_key(string $productCode): string
    {
        return ContentRuntime::cacheKey($productCode);
    }
}

if (!function_exists('tacticum_product_content_related_iblock_ids')) {
    function tacticum_product_content_related_iblock_ids(): array
    {
        return ContentRuntime::relatedIblockIds();
    }
}

if (!function_exists('tacticum_product_content_start_tag_cache')) {
    function tacticum_product_content_start_tag_cache(): void
    {
        ContentRuntime::startTagCache();
    }
}

if (!function_exists('tacticum_product_content_end_tag_cache')) {
    function tacticum_product_content_end_tag_cache(): void
    {
        ContentRuntime::endTagCache();
    }
}

if (!function_exists('tacticum_product_content_clear_cache')) {
    function tacticum_product_content_clear_cache(int $iblockId = 0): void
    {
        ContentRuntime::clearCache($iblockId);
    }
}

if (!function_exists('tacticum_product_content_clear_cache_for_iblock')) {
    function tacticum_product_content_clear_cache_for_iblock(mixed $iblockId): void
    {
        $iblockId = (int)$iblockId;
        if ($iblockId > 0 && in_array($iblockId, tacticum_product_content_related_iblock_ids(), true)) {
            tacticum_product_content_clear_cache($iblockId);
        }
    }
}

if (!function_exists('tacticum_product_content_clear_cache_on_iblock_change')) {
    function tacticum_product_content_clear_cache_on_iblock_change(array &$fields): void
    {
        tacticum_product_content_clear_cache_for_iblock($fields['IBLOCK_ID'] ?? 0);
    }
}

if (!function_exists('tacticum_product_content_clear_cache_on_element_delete')) {
    function tacticum_product_content_clear_cache_on_element_delete(array $fields): void
    {
        tacticum_product_content_clear_cache_for_iblock($fields['IBLOCK_ID'] ?? 0);
    }
}

if (!function_exists('tacticum_product_content_clear_cache_on_property_change')) {
    function tacticum_product_content_clear_cache_on_property_change(mixed $elementId, mixed $iblockId = 0): void
    {
        tacticum_product_content_clear_cache_for_iblock($iblockId);
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
        return ContentMapper::jsonDecode($value);
    }
}

if (!function_exists('tacticum_product_content_property_scalar')) {
    function tacticum_product_content_property_scalar(array $properties, string $code, string $default = ''): string
    {
        return ContentMapper::propertyScalar($properties, $code, $default);
    }
}

if (!function_exists('tacticum_product_content_element_properties')) {
    function tacticum_product_content_element_properties(int $iblockId, int $elementId): array
    {
        return ContentRepository::elementProperties($iblockId, $elementId);
    }
}

if (!function_exists('tacticum_product_content_fetch_element_by_code')) {
    function tacticum_product_content_fetch_element_by_code(int $iblockId, string $code): ?array
    {
        return ContentRepository::fetchElementByCode($iblockId, $code);
    }
}

if (!function_exists('tacticum_product_content_block_payload')) {
    function tacticum_product_content_block_payload(array $element): array
    {
        return ContentMapper::blockPayload($element);
    }
}

if (!function_exists('tacticum_product_content_apply_block')) {
    function tacticum_product_content_apply_block(array &$page, string $type, array $payload): void
    {
        ContentMapper::applyBlock($page, $type, $payload);
    }
}

if (!function_exists('tacticum_product_content_fetch_blocks')) {
    function tacticum_product_content_fetch_blocks(int $blocksIblockId, int $productElementId): array
    {
        return ContentRepository::fetchBlocks($blocksIblockId, $productElementId);
    }
}

if (!function_exists('tacticum_product_content_fetch_use_cases')) {
    function tacticum_product_content_fetch_use_cases(int $useCasesIblockId, int $productElementId): array
    {
        return ContentRepository::fetchUseCases($useCasesIblockId, $productElementId);
    }
}

if (!function_exists('tacticum_product_content_fetch_product_faq')) {
    function tacticum_product_content_fetch_product_faq(int $faqIblockId, int $productElementId): array
    {
        return ContentRepository::fetchProductFaq($faqIblockId, $productElementId);
    }
}

if (!function_exists('tacticum_product_content_is_minimum_renderable')) {
    function tacticum_product_content_is_minimum_renderable(array $page): bool
    {
        return ContentMapper::isMinimumRenderable($page);
    }
}

if (!function_exists('tacticum_product_content_completeness_diagnostics')) {
    function tacticum_product_content_completeness_diagnostics(array $page): array
    {
        return ContentMapper::completenessDiagnostics($page);
    }
}

if (!function_exists('tacticum_product_content_bitrix_data_uncached')) {
    function tacticum_product_content_bitrix_data_uncached(string $productCode): array
    {
        return ContentService::bitrixDataUncached($productCode);
    }
}

if (!function_exists('tacticum_product_content_bitrix_data')) {
    function tacticum_product_content_bitrix_data(string $productCode): array
    {
        return ContentService::bitrixData($productCode);
    }
}
