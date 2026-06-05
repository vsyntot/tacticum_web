<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class LeadContext
{
    public static function build(array $data): string
    {
        $lines = [];
        $profile = self::profile($data);
        $valueLabels = self::valueLabels();
        foreach (self::labels() as $profileKey => $label) {
            $value = $profile[$profileKey] ?? '';
            if ($value !== '') {
                $lines[] = $label . ': ' . ($valueLabels[$profileKey][$value] ?? $value);
            }
        }

        return $lines === [] ? '' : mb_substr("Контекст заявки:\n- " . implode("\n- ", $lines), 0, 900);
    }

    private static function profile(array $data): array
    {
        $fieldMap = [
            'product_interest' => ['lead_product'],
            'use_case_interest' => ['lead_scenario'],
            'deployment_interest' => ['lead_next_step'],
            'funnel_entry' => ['lead_entry'],
            'funnel_stage' => ['lead_page_role'],
            'lead_intent' => ['lead_intent'],
            'cta_id' => ['lead_cta', 'form_id'],
            'budget_band' => ['lead_budget'],
            'timeline_band' => ['lead_timeline'],
            'industry' => ['lead_industry'],
            'offer_code' => ['lead_offer_code'],
            'offer_title' => ['lead_offer_title'],
        ];

        $profile = [];
        foreach ($fieldMap as $profileKey => $sourceKeys) {
            foreach ($sourceKeys as $sourceKey) {
                $value = self::normalize($data[$sourceKey] ?? '');
                if ($value !== '') {
                    $profile[$profileKey] = $value;
                    break;
                }
            }
        }

        return $profile;
    }

    private static function normalize(mixed $value, int $maxLength = 180): string
    {
        if (is_array($value)) {
            return '';
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', (string)$value) ?: '');
        return $normalized === '' ? '' : mb_substr($normalized, 0, $maxLength);
    }

    private static function labels(): array
    {
        return [
            'product_interest' => 'Продуктовый интерес',
            'use_case_interest' => 'Сценарий / use case',
            'deployment_interest' => 'Ожидаемый следующий шаг',
            'funnel_entry' => 'Вход',
            'funnel_stage' => 'Стадия funnel',
            'lead_intent' => 'Интент',
            'cta_id' => 'CTA',
            'budget_band' => 'Бюджетный ориентир',
            'timeline_band' => 'Срок',
            'industry' => 'Отрасль',
            'offer_code' => 'Код примера расчета',
            'offer_title' => 'Пример расчета',
        ];
    }

    private static function valueLabels(): array
    {
        return [
            'budget_band' => [
                'up-to-1m' => 'до 1 млн руб.',
                '1-3m' => '1-3 млн руб.',
                '3-7m' => '3-7 млн руб.',
                '7m-plus' => '7+ млн руб.',
            ],
            'timeline_band' => [
                'asap' => 'нужен быстрый старт',
                '1-2-months' => '1-2 месяца',
                '3-6-months' => '3-6 месяцев',
                '6-plus-months' => 'дольше 6 месяцев',
            ],
            'use_case_interest' => [
                'product-routing' => 'маршрутизация по продуктовой экосистеме',
                'product-delivery' => 'внедрение продуктового сценария',
                'product-estimate' => 'уточнение продуктовой оценки',
                'product-team' => 'подбор команды под продуктовый поток',
                'contact-routing' => 'маршрутизация обращения',
                'pilot' => 'пилот продукта',
                'architecture-session' => 'архитектурная сессия',
                'procurement-security' => 'закупка и безопасность',
                'team-delivery' => 'команда внедрения',
                'estimate' => 'оценка сроков и бюджета',
            ],
        ];
    }
}
