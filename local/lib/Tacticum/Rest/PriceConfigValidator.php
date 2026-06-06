<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class PriceConfigValidator
{
    public static function validate(callable $checkIblock, callable $addError): void
    {
        $price = Config::section('price');
        $source = $price['team_presets_source'] ?? 'fallback';
        if (!is_string($source) || !in_array($source, ['fallback', 'auto', 'bitrix'], true)) {
            $addError('price.team_presets_source', 'invalid_value');
            return;
        }

        if (($price['team_presets_cache_ttl'] ?? null) !== null && !is_numeric($price['team_presets_cache_ttl'])) {
            $addError('price.team_presets_cache_ttl', 'invalid_type');
        }
        if (($price['allow_team_presets_fallback'] ?? true) !== true && $source === 'fallback') {
            $addError('price.allow_team_presets_fallback', 'fallback_source_disabled');
        }
        if ($source !== 'bitrix') {
            return;
        }

        foreach (['team_presets', 'team_preset_roles', 'rates'] as $key) {
            $checkIblock($key);
        }
    }
}
