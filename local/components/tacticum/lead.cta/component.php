<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!class_exists('TacticumComponentParams')) {
    $helperPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/component_helpers.php';
    if (file_exists($helperPath)) {
        require_once $helperPath;
    }
}

$type = TacticumComponentParams::string($arParams, 'TYPE');
if ($type === '') {
    $type = TacticumComponentParams::string($arParams, 'CTA_TYPE');
}

$variantParam = TacticumComponentParams::string($arParams, 'VARIANT');
if ($type === '' && in_array($variantParam, ['personal-offer', 'project-discussion'], true)) {
    $type = $variantParam;
    $variantParam = '';
}

if (!in_array($type, ['personal-offer', 'project-discussion'], true)) {
    $type = 'personal-offer';
}

$variant = TacticumComponentParams::string($arParams, 'VISUAL_VARIANT');
if ($variant === '') {
    $variant = TacticumComponentParams::string($arParams, 'FORM_VARIANT');
}
if ($variant === '') {
    $variant = TacticumComponentParams::string($arParams, 'STYLE');
}
if ($variant === '' && in_array($variantParam, ['solid', 'glass'], true)) {
    $variant = $variantParam;
}
if ($variant === '') {
    $variant = $type === 'project-discussion' ? 'glass' : 'solid';
}

if (!in_array($variant, ['solid', 'glass'], true)) {
    $variant = $type === 'project-discussion' ? 'glass' : 'solid';
}

$normalizeLeadContext = static function ($value): array {
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
        if ($key === '' || $contextValue === '') {
            continue;
        }

        $context[$key] = mb_substr($contextValue, 0, 180);
    }

    return $context;
};

$normalizeScenarioOptions = static function ($value): array {
    if (!is_array($value)) {
        $value = preg_split('/\r\n|\r|\n|,/', (string)$value) ?: [];
    }

    $options = [];
    foreach ($value as $rawKey => $rawOption) {
        $optionValue = '';
        $optionLabel = '';

        if (is_array($rawOption)) {
            $optionValue = trim((string)($rawOption['VALUE'] ?? $rawOption['value'] ?? $rawKey));
            $optionLabel = trim((string)($rawOption['LABEL'] ?? $rawOption['label'] ?? $optionValue));
        } elseif (is_scalar($rawOption)) {
            $optionText = trim((string)$rawOption);
            if ($optionText === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $optionText, 2));
            $optionValue = $parts[0] ?? '';
            $optionLabel = $parts[1] ?? $optionValue;
        }

        $optionValue = preg_replace('/[^a-z0-9_.-]+/i', '', $optionValue) ?: '';
        $optionLabel = trim($optionLabel);
        if ($optionValue === '' || $optionLabel === '') {
            continue;
        }

        $options[$optionValue] = mb_substr($optionLabel, 0, 120);
    }

    $normalized = [];
    foreach ($options as $optionValue => $optionLabel) {
        $normalized[] = [
            'VALUE' => $optionValue,
            'LABEL' => $optionLabel,
        ];
    }

    return $normalized;
};

$defaults = [
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

$default = $defaults[$type];
$formId = TacticumComponentParams::string($arParams, 'FORM_ID', $default['FORM_ID']);
$fieldPrefix = TacticumComponentParams::string($arParams, 'FIELD_PREFIX', $default['FIELD_PREFIX']);
$endpoint = TacticumComponentParams::string($arParams, 'ENDPOINT');
$closeTarget = TacticumComponentParams::string($arParams, 'CLOSE_TARGET');
$closeMode = TacticumComponentParams::string($arParams, 'CLOSE_MODE');

if ($endpoint !== '' && ($endpoint[0] !== '/' || str_starts_with($endpoint, '//'))) {
    $endpoint = '';
}

if ($closeMode !== '' && !in_array($closeMode, ['hidden', 'overlay'], true)) {
    $closeMode = '';
}

$features = $arParams['FEATURES'] ?? null;
if (!is_array($features)) {
    $features = [
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

$showQualification = TacticumComponentParams::yesNo(
    $arParams,
    'SHOW_QUALIFICATION',
    $type === 'personal-offer' ? 'Y' : 'N'
) === 'Y';
$scenarioOptions = $normalizeScenarioOptions($arParams['SCENARIO_OPTIONS'] ?? []);
$leadContext = $normalizeLeadContext($arParams['LEAD_CONTEXT'] ?? []);
if (!empty($scenarioOptions)) {
    unset($leadContext['lead_scenario']);
}

$arResult = [
    'TYPE' => $type,
    'VARIANT' => $variant,
    'SECTION_ID' => TacticumComponentParams::string($arParams, 'SECTION_ID', $default['SECTION_ID']),
    'FORM_ID' => $formId,
    'FORM_HTML_ID' => TacticumComponentParams::string($arParams, 'FORM_HTML_ID'),
    'FIELD_PREFIX' => $fieldPrefix,
    'TITLE' => TacticumComponentParams::string($arParams, 'TITLE', $default['TITLE']),
    'TEXT' => TacticumComponentParams::string($arParams, 'TEXT', $default['TEXT']),
    'FORM_TITLE' => TacticumComponentParams::string($arParams, 'FORM_TITLE', $default['FORM_TITLE']),
    'MESSAGE_LABEL' => TacticumComponentParams::string($arParams, 'MESSAGE_LABEL', $default['MESSAGE_LABEL']),
    'MESSAGE_PLACEHOLDER' => TacticumComponentParams::string($arParams, 'MESSAGE_PLACEHOLDER', $default['MESSAGE_PLACEHOLDER']),
    'BUTTON_TEXT' => TacticumComponentParams::string($arParams, 'BUTTON_TEXT', $default['BUTTON_TEXT']),
    'IMAGE_SRC' => TacticumComponentParams::string($arParams, 'IMAGE_SRC', $default['IMAGE_SRC']),
    'IMAGE_ALT' => TacticumComponentParams::string($arParams, 'IMAGE_ALT', $default['IMAGE_ALT']),
    'ENDPOINT' => $endpoint,
    'CLOSE_TARGET' => $closeTarget,
    'CLOSE_MODE' => $closeMode,
    'FEATURES' => $features,
    'LEAD_CONTEXT' => $leadContext,
    'SHOW_QUALIFICATION' => $showQualification,
    'SCENARIO_LABEL' => TacticumComponentParams::string($arParams, 'SCENARIO_LABEL', 'Сценарий'),
    'SCENARIO_EMPTY_LABEL' => TacticumComponentParams::string($arParams, 'SCENARIO_EMPTY_LABEL', 'Выберите сценарий'),
    'SCENARIO_OPTIONS' => $scenarioOptions,
];

$this->IncludeComponentTemplate();
