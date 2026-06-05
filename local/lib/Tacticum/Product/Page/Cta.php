<?php

namespace Tacticum\Product\Page;

final class Cta
{
    public static function standardScenarioOptions(): array
    {
        return [
            'pilot' => 'Пилот продукта',
            'architecture-session' => 'Архитектурная сессия',
            'procurement-security' => 'Закупка и безопасность',
            'team-delivery' => 'Команда внедрения',
            'estimate' => 'Оценка сроков и бюджета',
        ];
    }

    public static function leadContext(array $page, array $cta): array
    {
        $rawContext = is_array($cta['lead_context'] ?? null) ? $cta['lead_context'] : [];
        $productCode = Text::contextSlug(
            $page['_product_code'] ?? ($rawContext['lead_product'] ?? ''),
            'product'
        );

        $knownProducts = function_exists('tacticum_product_content_codes')
            ? array_keys(tacticum_product_content_codes())
            : ['platform', 'agents', 'dev', 'forum'];
        if (!in_array($productCode, $knownProducts, true)) {
            $productCode = 'product';
        }

        $formId = Text::contextSlug($cta['form_id'] ?? '', $productCode . '-cta');
        $context = [
            'lead_entry' => Text::contextSlug($rawContext['lead_entry'] ?? '', $productCode),
            'lead_page_role' => 'product-page',
            'lead_product' => $productCode,
            'lead_intent' => Text::contextSlug($rawContext['lead_intent'] ?? '', 'product-discussion'),
            'lead_cta' => Text::contextSlug($rawContext['lead_cta'] ?? '', $formId),
            'lead_next_step' => Text::contextSlug($rawContext['lead_next_step'] ?? '', 'manual-follow-up'),
        ];

        foreach ($context as $key => $value) {
            $context[$key] = mb_substr($value, 0, 80);
        }

        return $context;
    }

    public static function scenarioOptions(array $cta): array
    {
        $standard = self::standardScenarioOptions();
        $labels = [];
        $rawOptions = is_array($cta['scenario_options'] ?? null) ? $cta['scenario_options'] : [];

        foreach ($rawOptions as $option) {
            if (!is_array($option)) {
                continue;
            }

            $value = trim((string)($option['VALUE'] ?? $option['value'] ?? ''));
            $label = trim((string)($option['LABEL'] ?? $option['label'] ?? ''));
            if ($value === '' || $label === '' || !array_key_exists($value, $standard)) {
                continue;
            }

            $labels[$value] = $label;
        }

        foreach ($standard as $value => $label) {
            if (!array_key_exists($value, $labels)) {
                $labels[$value] = $label;
            }
        }

        $options = [];
        foreach ($standard as $value => $defaultLabel) {
            $options[] = [
                'VALUE' => $value,
                'LABEL' => $labels[$value] ?? $defaultLabel,
            ];
        }

        return $options;
    }
}
