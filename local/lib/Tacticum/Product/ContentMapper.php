<?php

namespace Tacticum\Product;

final class ContentMapper
{
    public static function jsonDecode(mixed $value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function propertyScalar(array $properties, string $code, string $default = ''): string
    {
        $value = $properties[$code] ?? $default;
        if (is_array($value)) {
            $value = reset($value);
        }

        return is_scalar($value) ? trim((string)$value) : $default;
    }

    public static function propertyList(array $properties, string $code): array
    {
        $value = $properties[$code] ?? [];
        if (!is_array($value)) {
            $value = [$value];
        }

        $items = [];
        foreach ($value as $item) {
            if (!is_scalar($item)) {
                continue;
            }

            $item = trim((string)$item);
            if ($item !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }

    public static function blockPayload(array $element): array
    {
        $payload = self::jsonDecode($element['DETAIL_TEXT'] ?? '');
        if (!empty($payload)) {
            return $payload;
        }

        return self::jsonDecode($element['PREVIEW_TEXT'] ?? '');
    }

    public static function applyBlock(array &$page, string $type, array $payload): void
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

    public static function isMinimumRenderable(array $page): bool
    {
        $title = trim((string)($page['title'] ?? ''));
        $lead = trim((string)($page['lead'] ?? ''));
        $cta = is_array($page['cta'] ?? null) ? $page['cta'] : [];

        return $title !== '' && $lead !== '' && !empty($cta);
    }

    public static function completenessDiagnostics(array $page): array
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
            'minimum_renderable' => self::isMinimumRenderable($page),
            'missing_to_be_blocks' => $missing,
        ];
    }
}
