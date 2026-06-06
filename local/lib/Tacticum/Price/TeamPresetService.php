<?php

declare(strict_types=1);

namespace Tacticum\Price;

use Tacticum\Rest\Config;

final class TeamPresetService
{
    private static ?array $presets = null;

    public static function presets(): array
    {
        if (self::$presets !== null) {
            return self::$presets;
        }

        $config = Config::section('price');
        $source = (string)($config['team_presets_source'] ?? 'fallback');
        $ttl = isset($config['team_presets_cache_ttl']) ? (int)$config['team_presets_cache_ttl'] : 300;
        $allowFallback = (bool)($config['allow_team_presets_fallback'] ?? true);
        $bitrixPresets = [];

        if ($source === 'bitrix' || $source === 'auto') {
            $bitrixPresets = TeamPresetRepository::activePresets($ttl);
        }

        if ($source === 'bitrix') {
            self::$presets = self::normalizeList($bitrixPresets);
            return self::$presets;
        }

        if ($source === 'auto' && $bitrixPresets !== []) {
            self::$presets = self::normalizeList($bitrixPresets);
            return self::$presets;
        }

        self::$presets = $allowFallback ? self::normalizeList(TeamPresetFallback::all()) : [];
        return self::$presets;
    }

    public static function payload(): array
    {
        $presets = self::presets();
        $source = $presets[0]['source'] ?? 'none';

        return [
            'schema' => 'tacticum.price.team_presets.v1',
            'source' => $source,
            'presets' => $presets,
        ];
    }

    public static function indexed(): array
    {
        $indexed = [];
        foreach (self::presets() as $preset) {
            $code = (string)($preset['code'] ?? '');
            if ($code !== '') {
                $indexed[$code] = $preset;
            }
        }

        return $indexed;
    }

    public static function find(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        return self::indexed()[$code] ?? null;
    }

    private static function normalizeList(array $presets): array
    {
        $result = [];
        foreach ($presets as $preset) {
            $code = trim((string)($preset['code'] ?? ''));
            if ($code === '') {
                continue;
            }

            $roles = [];
            foreach ((array)($preset['roles'] ?? []) as $role) {
                if (!is_array($role)) {
                    continue;
                }
                $roles[] = self::normalizeRole($role);
            }
            if ($roles === []) {
                continue;
            }

            $result[] = [
                'code' => $code,
                'label' => trim((string)($preset['label'] ?? $code)),
                'description' => trim((string)($preset['description'] ?? '')),
                'scenario' => trim((string)($preset['scenario'] ?? '')),
                'defaultWorkload' => trim((string)($preset['defaultWorkload'] ?? '')),
                'recommendedDuration' => trim((string)($preset['recommendedDuration'] ?? '')),
                'version' => trim((string)($preset['version'] ?? '')),
                'analyticsCode' => trim((string)($preset['analyticsCode'] ?? $code)),
                'source' => trim((string)($preset['source'] ?? 'unknown')),
                'roles' => $roles,
            ];
        }

        return $result;
    }

    private static function normalizeRole(array $role): array
    {
        $rateIds = array_values(array_unique(array_filter(array_map(
            static fn(mixed $value): int => (int)$value,
            (array)($role['rateIds'] ?? [])
        ), static fn(int $value): bool => $value > 0)));
        $rateElementId = (int)($role['rateElementId'] ?? 0);
        if ($rateElementId > 0 && !in_array($rateElementId, $rateIds, true)) {
            array_unshift($rateIds, $rateElementId);
        }

        return [
            'roleKey' => trim((string)($role['roleKey'] ?? '')),
            'rateElementId' => $rateElementId,
            'rateIds' => $rateIds,
            'preferredLevels' => self::stringList($role['preferredLevels'] ?? ['Middle', 'Senior', 'Junior', 'Lead']),
            'quantity' => max(1, min((int)($role['quantity'] ?? 1), 99)),
            'required' => (bool)($role['required'] ?? true),
            'keywords' => self::stringList($role['keywords'] ?? []),
        ];
    }

    private static function stringList(mixed $value): array
    {
        return array_values(array_filter(array_map(
            static fn(mixed $item): string => trim((string)$item),
            (array)$value
        ), static fn(string $item): bool => $item !== ''));
    }
}
