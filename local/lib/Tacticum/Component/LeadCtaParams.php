<?php

namespace Tacticum\Component;

final class LeadCtaParams
{
    public static function prepare(array $params): array
    {
        $type = self::type($params);
        $variant = self::variant($params, $type);
        $default = self::defaults($variant)[$type];
        $endpoint = \TacticumComponentParams::string($params, 'ENDPOINT');
        $closeMode = \TacticumComponentParams::string($params, 'CLOSE_MODE');
        $scenarioOptions = self::normalizeScenarioOptions($params['SCENARIO_OPTIONS'] ?? []);
        $leadContext = self::normalizeLeadContext($params['LEAD_CONTEXT'] ?? []);

        $endpoint = $endpoint !== '' && ($endpoint[0] !== '/' || str_starts_with($endpoint, '//')) ? '' : $endpoint;
        $closeMode = $closeMode !== '' && !in_array($closeMode, ['hidden', 'overlay'], true) ? '' : $closeMode;
        if (!empty($scenarioOptions)) {
            unset($leadContext['lead_scenario']);
        }

        return [
            'TYPE' => $type,
            'VARIANT' => $variant,
            'SECTION_ID' => \TacticumComponentParams::string($params, 'SECTION_ID', $default['SECTION_ID']),
            'FORM_ID' => \TacticumComponentParams::string($params, 'FORM_ID', $default['FORM_ID']),
            'FORM_HTML_ID' => \TacticumComponentParams::string($params, 'FORM_HTML_ID'),
            'FIELD_PREFIX' => \TacticumComponentParams::string($params, 'FIELD_PREFIX', $default['FIELD_PREFIX']),
            'TITLE' => \TacticumComponentParams::string($params, 'TITLE', $default['TITLE']),
            'TEXT' => \TacticumComponentParams::string($params, 'TEXT', $default['TEXT']),
            'FORM_TITLE' => \TacticumComponentParams::string($params, 'FORM_TITLE', $default['FORM_TITLE']),
            'MESSAGE_LABEL' => \TacticumComponentParams::string($params, 'MESSAGE_LABEL', $default['MESSAGE_LABEL']),
            'MESSAGE_PLACEHOLDER' => \TacticumComponentParams::string($params, 'MESSAGE_PLACEHOLDER', $default['MESSAGE_PLACEHOLDER']),
            'BUTTON_TEXT' => \TacticumComponentParams::string($params, 'BUTTON_TEXT', $default['BUTTON_TEXT']),
            'IMAGE_SRC' => \TacticumComponentParams::string($params, 'IMAGE_SRC', $default['IMAGE_SRC']),
            'IMAGE_ALT' => \TacticumComponentParams::string($params, 'IMAGE_ALT', $default['IMAGE_ALT']),
            'ENDPOINT' => $endpoint,
            'CLOSE_TARGET' => \TacticumComponentParams::string($params, 'CLOSE_TARGET'),
            'CLOSE_MODE' => $closeMode,
            'FEATURES' => self::features($params['FEATURES'] ?? null),
            'LEAD_CONTEXT' => $leadContext,
            'SHOW_QUALIFICATION' => self::showQualification($params, $type),
            'SCENARIO_LABEL' => \TacticumComponentParams::string($params, 'SCENARIO_LABEL', 'Сценарий'),
            'SCENARIO_EMPTY_LABEL' => \TacticumComponentParams::string($params, 'SCENARIO_EMPTY_LABEL', 'Выберите сценарий'),
            'SCENARIO_OPTIONS' => $scenarioOptions,
        ];
    }

    public static function normalizeLeadContext(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $context = [];
        foreach ($value as $rawKey => $rawValue) {
            if (is_array($rawValue)) {
                continue;
            }

            $key = preg_replace('/[^a-z0-9_]+/i', '', trim((string)$rawKey)) ?: '';
            $contextValue = trim((string)$rawValue);
            if ($key !== '' && $contextValue !== '') {
                $context[$key] = mb_substr($contextValue, 0, 180);
            }
        }

        return $context;
    }

    public static function normalizeScenarioOptions(mixed $value): array
    {
        if (!is_array($value)) {
            $value = preg_split('/\r\n|\r|\n|,/', (string)$value) ?: [];
        }

        $options = [];
        foreach ($value as $rawKey => $rawOption) {
            [$optionValue, $optionLabel] = self::scenarioOption($rawKey, $rawOption);
            if ($optionValue !== '' && $optionLabel !== '') {
                $options[$optionValue] = mb_substr($optionLabel, 0, 120);
            }
        }

        $normalized = [];
        foreach ($options as $optionValue => $optionLabel) {
            $normalized[] = [
                'VALUE' => $optionValue,
                'LABEL' => $optionLabel,
            ];
        }

        return $normalized;
    }

    private static function type(array $params): string
    {
        $type = \TacticumComponentParams::string($params, 'TYPE');
        $type = $type !== '' ? $type : \TacticumComponentParams::string($params, 'CTA_TYPE');
        $variantParam = \TacticumComponentParams::string($params, 'VARIANT');
        if ($type === '' && in_array($variantParam, ['personal-offer', 'project-discussion'], true)) {
            $type = $variantParam;
        }

        return in_array($type, ['personal-offer', 'project-discussion'], true) ? $type : 'personal-offer';
    }

    private static function variant(array $params, string $type): string
    {
        $variant = \TacticumComponentParams::string($params, 'VISUAL_VARIANT');
        $variant = $variant !== '' ? $variant : \TacticumComponentParams::string($params, 'FORM_VARIANT');
        $variant = $variant !== '' ? $variant : \TacticumComponentParams::string($params, 'STYLE');
        $variantParam = \TacticumComponentParams::string($params, 'VARIANT');
        if ($variant === '' && in_array($variantParam, ['solid', 'glass'], true)) {
            $variant = $variantParam;
        }
        $default = $type === 'project-discussion' ? 'glass' : 'solid';

        return in_array($variant, ['solid', 'glass'], true) ? $variant : $default;
    }

    private static function showQualification(array $params, string $type): bool
    {
        return \TacticumComponentParams::yesNo(
            $params,
            'SHOW_QUALIFICATION',
            $type === 'personal-offer' ? 'Y' : 'N'
        ) === 'Y';
    }

    private static function features(mixed $features): array
    {
        return is_array($features) ? $features : [
            [
                'ICON' => 'ri-medal-line',
                'TITLE' => 'Опыт проектной оценки',
                'TEXT' => 'Опираемся на реализованные проекты, отраслевые сценарии и прозрачную декомпозицию работ',
            ],
            [
                'ICON' => 'ri-team-line',
                'TITLE' => 'Команда под задачу',
                'TEXT' => 'Подбираем роли, уровни и загрузку под конкретный этап: discovery, MVP, интеграции или support',
            ],
            [
                'ICON' => 'ri-rocket-line',
                'TITLE' => 'Понятный следующий шаг',
                'TEXT' => 'После заявки уточняем вводные и предлагаем формат: расчет, консультация, команда или прототип',
            ],
        ];
    }

    private static function scenarioOption(mixed $rawKey, mixed $rawOption): array
    {
        $optionValue = '';
        $optionLabel = '';

        if (is_array($rawOption)) {
            $optionValue = trim((string)($rawOption['VALUE'] ?? $rawOption['value'] ?? $rawKey));
            $optionLabel = trim((string)($rawOption['LABEL'] ?? $rawOption['label'] ?? $optionValue));
        } elseif (is_scalar($rawOption)) {
            $optionText = trim((string)$rawOption);
            if ($optionText === '') { return ['', '']; }

            $parts = array_map('trim', explode('|', $optionText, 2));
            $optionValue = $parts[0] ?? '';
            $optionLabel = $parts[1] ?? $optionValue;
        }

        $optionValue = preg_replace('/[^a-z0-9_.-]+/i', '', $optionValue) ?: '';

        return [$optionValue, trim($optionLabel)];
    }

    private static function defaults(string $variant): array
    {
        return [
            'personal-offer' => [
                'SECTION_ID' => 'contact-form',
                'FORM_ID' => 'contact-cta',
                'FIELD_PREFIX' => 'cta',
                'TITLE' => 'Получите персональное предложение',
                'TEXT' => 'Оставьте заявку, и мы уточним задачу, ограничения, сроки и следующий шаг: предварительную оценку, discovery или подбор команды.',
                'FORM_TITLE' => '',
                'MESSAGE_LABEL' => $variant === 'glass' ? 'Опишите ваш проект или интересующее предложение' : 'Опишите проект или интересующее предложение',
                'MESSAGE_PLACEHOLDER' => 'Кратко опишите задачу, сроки и ожидаемый результат',
                'BUTTON_TEXT' => 'Получить персональное предложение',
                'IMAGE_SRC' => '',
                'IMAGE_ALT' => '',
            ],
            'project-discussion' => [
                'SECTION_ID' => 'contact-form',
                'FORM_ID' => 'project-cta',
                'FIELD_PREFIX' => 'project',
                'TITLE' => 'Готовы обсудить ваш проект?',
                'TEXT' => 'Заполните форму, и мы свяжемся для уточнения задачи, формата работ и ближайшего полезного шага.',
                'FORM_TITLE' => 'Оставить заявку',
                'MESSAGE_LABEL' => 'Опишите ваш проект',
                'MESSAGE_PLACEHOLDER' => '',
                'BUTTON_TEXT' => 'Запросить расчет',
                'IMAGE_SRC' => '',
                'IMAGE_ALT' => '',
            ],
        ];
    }
}
