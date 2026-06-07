<?php

namespace Tacticum\Product;

final class ContentBlockMapper
{
    public static function payload(string $type, array $element, array $properties, array $children): array
    {
        $legacy = ContentMapper::blockPayload($element);
        $payload = $type === 'hero' ? [] : self::base($element, $properties);

        match ($type) {
            'hero' => $payload = array_merge($payload, self::hero($children)),
            'fit_guide' => $payload = array_merge($payload, self::fitGuide($children)),
            'section' => $payload = array_merge($payload, self::items($children, 'cards')),
            'architecture' => $payload = array_merge($payload, self::items($children, 'layers')),
            'comparison' => $payload = array_merge($payload, self::items($children, 'columns')),
            'procurement' => $payload = array_merge($payload, self::items($children, 'items')),
            'rollout' => $payload = array_merge($payload, self::items($children, 'steps')),
            'proof' => $payload = array_merge($payload, self::items($children, 'items')),
            'faq' => $payload = array_merge($payload, self::faq($children)),
            'cta' => $payload = array_merge($payload, self::cta($properties, $children)),
            default => $payload,
        };

        $payload = self::normalizePublicBlockLabels($type, $payload);
        $payload = self::withoutEmpty($payload);

        return empty($payload) ? $legacy : array_merge($legacy, $payload);
    }

    private static function base(array $element, array $properties): array
    {
        return self::withoutEmpty([
            'eyebrow' => ContentMapper::propertyScalar($properties, 'EYEBROW'),
            'theme' => ContentMapper::propertyScalar($properties, 'THEME'),
            'tone' => ContentMapper::propertyScalar($properties, 'TONE'),
            'columns_class' => ContentMapper::propertyScalar($properties, 'COLUMNS_CLASS'),
            'title' => self::plain((string)($element['NAME'] ?? '')),
            'text' => self::plain((string)($element['DETAIL_TEXT'] ?? '')),
            'note_title' => ContentMapper::propertyScalar($properties, 'NOTE_TITLE'),
            'note_text' => ContentMapper::propertyScalar($properties, 'NOTE_TEXT'),
            'cta_text' => ContentMapper::propertyScalar($properties, 'CTA_TEXT'),
            'cta_href' => ContentMapper::propertyScalar($properties, 'CTA_HREF'),
        ]);
    }

    private static function hero(array $children): array
    {
        $cards = self::childCards($children, ['hero_card']);

        return !empty($cards) ? ['hero_cards' => $cards] : [];
    }

    private static function fitGuide(array $children): array
    {
        $payload = [];
        foreach ($children as $child) {
            $type = ContentMapper::propertyScalar($child['properties'] ?? [], 'ITEM_TYPE');
            if (!in_array($type, ['fits', 'not_fits', 'start'], true)) {
                continue;
            }

            $column = self::normalizeFitGuideColumn($type, self::childCard($child));
            if (!empty($column)) {
                $payload[$type] = $column;
            }
        }

        return $payload;
    }

    private static function items(array $children, string $key): array
    {
        $items = self::childCards($children);

        return !empty($items) ? [$key => $items] : [];
    }

    private static function faq(array $children): array
    {
        $items = [];
        foreach ($children as $child) {
            $card = self::childCard($child);
            if (!empty($card)) {
                $items[] = [
                    'question' => $card['title'] ?? '',
                    'answer' => $card['text'] ?? '',
                ];
            }
        }

        return !empty($items) ? ['items' => $items] : [];
    }

    private static function cta(array $properties, array $children): array
    {
        $payload = self::withoutEmpty([
            'form_id' => ContentMapper::propertyScalar($properties, 'FORM_ID'),
            'field_prefix' => ContentMapper::propertyScalar($properties, 'FIELD_PREFIX'),
            'form_title' => ContentMapper::propertyScalar($properties, 'FORM_TITLE'),
            'button_text' => ContentMapper::propertyScalar($properties, 'BUTTON_TEXT'),
            'scenario_label' => ContentMapper::propertyScalar($properties, 'SCENARIO_LABEL'),
            'scenario_empty_label' => ContentMapper::propertyScalar($properties, 'SCENARIO_EMPTY_LABEL'),
            'lead_context' => self::withoutEmpty([
                'lead_entry' => ContentMapper::propertyScalar($properties, 'LEAD_ENTRY'),
                'lead_page_role' => ContentMapper::propertyScalar($properties, 'LEAD_PAGE_ROLE'),
                'lead_product' => ContentMapper::propertyScalar($properties, 'LEAD_PRODUCT'),
                'lead_intent' => ContentMapper::propertyScalar($properties, 'LEAD_INTENT'),
                'lead_cta' => ContentMapper::propertyScalar($properties, 'LEAD_CTA'),
                'lead_next_step' => ContentMapper::propertyScalar($properties, 'LEAD_NEXT_STEP'),
            ]),
        ]);

        $options = [];
        foreach ($children as $child) {
            $properties = $child['properties'] ?? [];
            $value = ContentMapper::propertyScalar($properties, 'VALUE');
            $label = ContentMapper::propertyScalar($properties, 'LABEL', self::plain((string)($child['element']['NAME'] ?? '')));
            if ($value !== '' && $label !== '') {
                $options[] = ['VALUE' => $value, 'LABEL' => $label];
            }
        }
        if (!empty($options)) {
            $payload['scenario_options'] = $options;
        }

        return $payload;
    }

    private static function childCards(array $children, array $allowedTypes = []): array
    {
        $items = [];
        foreach ($children as $child) {
            $type = ContentMapper::propertyScalar($child['properties'] ?? [], 'ITEM_TYPE');
            if (!empty($allowedTypes) && !in_array($type, $allowedTypes, true)) {
                continue;
            }

            $card = self::childCard($child);
            if (!empty($card)) {
                $items[] = $card;
            }
        }

        return $items;
    }

    private static function childCard(array $child): array
    {
        $element = $child['element'] ?? [];
        $properties = $child['properties'] ?? [];
        $detailText = self::plain((string)($element['DETAIL_TEXT'] ?? ''));

        return self::withoutEmpty([
            'title' => self::plain((string)($element['NAME'] ?? '')),
            'text' => $detailText !== '' ? $detailText : self::plain((string)($element['PREVIEW_TEXT'] ?? '')),
            'icon' => ContentMapper::propertyScalar($properties, 'ICON'),
            'meta' => ContentMapper::propertyScalar($properties, 'META'),
            'href' => ContentMapper::propertyScalar($properties, 'HREF'),
            'tone' => ContentMapper::propertyScalar($properties, 'TONE'),
            'proof_status' => ContentMapper::propertyScalar($properties, 'PROOF_STATUS'),
            'items' => ContentMapper::propertyList($properties, 'ITEMS'),
        ]);
    }

    private static function normalizeFitGuideColumn(string $type, array $column): array
    {
        $technicalTitles = ['fits' => 'fits', 'not_fits' => 'not_fits', 'start' => 'start'];
        $title = self::plain((string)($column['title'] ?? ''));
        if ($title !== '' && isset($technicalTitles[$type]) && strcasecmp($title, $technicalTitles[$type]) === 0) {
            unset($column['title']);
        }

        return $column;
    }

    private static function normalizePublicBlockLabels(string $type, array $payload): array
    {
        $replacements = [
            'fit_guide' => [['eyebrow', 'Product fit', 'Когда подходит продукт']],
            'use_cases' => [['eyebrow', 'Use cases', 'Сценарии применения']],
            'procurement' => [
                ['eyebrow', 'Security / procurement', 'Безопасность и закупка'],
                ['note_title', 'Что не обещаем без assessment', 'Что не обещаем без предварительной проверки'],
            ],
        ];
        foreach ($replacements[$type] ?? [] as [$key, $from, $to]) {
            if (isset($payload[$key]) && is_string($payload[$key]) && strcasecmp(trim($payload[$key]), $from) === 0) {
                $payload[$key] = $to;
            }
        }

        return $payload;
    }

    private static function plain(string $value): string
    {
        $value = trim($value);

        return $value === '' || str_starts_with($value, '{') || str_starts_with($value, '[') ? '' : $value;
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
}
