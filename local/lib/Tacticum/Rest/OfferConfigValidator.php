<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class OfferConfigValidator
{
    public static function validate(callable $checkIblock, callable $addError): void
    {
        $offer = Config::section('offer');
        $source = $offer['taxonomy_source'] ?? 'fallback';
        if (!is_string($source) || !in_array($source, ['fallback', 'auto', 'bitrix'], true)) {
            $addError('offer.taxonomy_source', 'invalid_value');
            return;
        }
        if (($offer['taxonomy_cache_ttl'] ?? null) !== null && !is_numeric($offer['taxonomy_cache_ttl'])) {
            $addError('offer.taxonomy_cache_ttl', 'invalid_type');
        }
        if (($offer['allow_taxonomy_fallback'] ?? true) !== true && $source === 'fallback') {
            $addError('offer.allow_taxonomy_fallback', 'fallback_source_disabled');
        }
        if ($source === 'bitrix') {
            $checkIblock('offer_taxonomy_terms');
        }
    }
}
