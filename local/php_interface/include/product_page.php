<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_string')) {
    function tacticum_product_page_string(array $data, string $key, string $default = ''): string
    {
        $value = $data[$key] ?? $default;

        if (is_scalar($value)) {
            return trim((string)$value);
        }

        return $default;
    }
}

if (!function_exists('tacticum_product_page_html')) {
    function tacticum_product_page_html($value): string
    {
        return htmlspecialcharsbx((string)$value);
    }
}

if (!function_exists('tacticum_product_page_canonical_path')) {
    function tacticum_product_page_canonical_path(string $canonicalPath): string
    {
        $path = trim($canonicalPath);
        if ($path === '') {
            return '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return str_ends_with($path, '/') ? $path : $path . '/';
    }
}

if (!function_exists('tacticum_product_page_safe_href')) {
    function tacticum_product_page_safe_href($value, string $default = '#'): string
    {
        $href = is_scalar($value) ? trim((string)$value) : '';
        if (tacticum_product_page_is_safe_href($href)) {
            return $href;
        }

        $fallback = trim($default);
        if ($fallback === '') {
            return '';
        }
        if (tacticum_product_page_is_safe_href($fallback)) {
            return $fallback;
        }

        return '#';
    }
}

if (!function_exists('tacticum_product_page_is_safe_href')) {
    function tacticum_product_page_is_safe_href(string $href): bool
    {
        if ($href === '' || preg_match('/[\x00-\x1F\x7F]/', $href)) {
            return false;
        }

        if (str_starts_with($href, 'https://') || str_starts_with($href, '#')) {
            return true;
        }

        return str_starts_with($href, '/')
            && !str_starts_with($href, '//')
            && !str_starts_with($href, '/\\');
    }
}

if (!function_exists('tacticum_product_page_icon_class')) {
    function tacticum_product_page_icon_class($value, string $default = ''): string
    {
        $icon = is_scalar($value) ? trim((string)$value) : '';
        if (preg_match('/^ri-[a-z0-9]+(?:-[a-z0-9]+)*$/', $icon)) {
            return $icon;
        }

        $fallback = trim($default);
        if ($fallback !== '' && preg_match('/^ri-[a-z0-9]+(?:-[a-z0-9]+)*$/', $fallback)) {
            return $fallback;
        }

        return '';
    }
}

if (!function_exists('tacticum_product_page_columns_class')) {
    function tacticum_product_page_columns_class($value, string $default = 'lg:grid-cols-3'): string
    {
        $allowed = ['lg:grid-cols-2', 'lg:grid-cols-3', 'lg:grid-cols-4'];
        $class = is_scalar($value) ? trim((string)$value) : '';
        if (in_array($class, $allowed, true)) {
            return $class;
        }

        $fallback = trim($default);

        return in_array($fallback, $allowed, true) ? $fallback : 'lg:grid-cols-3';
    }
}

if (!function_exists('tacticum_product_page_standard_scenario_options')) {
    function tacticum_product_page_standard_scenario_options(): array
    {
        return [
            'pilot' => 'Пилот продукта',
            'architecture-session' => 'Архитектурная сессия',
            'procurement-security' => 'Закупка и безопасность',
            'team-delivery' => 'Команда внедрения',
            'estimate' => 'Оценка сроков и бюджета',
        ];
    }
}

if (!function_exists('tacticum_product_page_context_slug')) {
    function tacticum_product_page_context_slug($value, string $default = ''): string
    {
        if (!is_scalar($value)) {
            return $default;
        }

        $normalized = strtolower(trim((string)$value));
        $normalized = preg_replace('/[^a-z0-9_.-]+/', '-', $normalized) ?: '';
        $normalized = trim($normalized, '-_.');
        if ($normalized === '') {
            return $default;
        }

        return mb_substr($normalized, 0, 80);
    }
}

if (!function_exists('tacticum_product_page_cta_lead_context')) {
    function tacticum_product_page_cta_lead_context(array $page, array $cta): array
    {
        $rawContext = is_array($cta['lead_context'] ?? null) ? $cta['lead_context'] : [];
        $productCode = tacticum_product_page_context_slug(
            $page['_product_code'] ?? ($rawContext['lead_product'] ?? ''),
            'product'
        );

        $knownProducts = function_exists('tacticum_product_content_codes')
            ? array_keys(tacticum_product_content_codes())
            : ['platform', 'agents', 'dev', 'forum'];
        if (!in_array($productCode, $knownProducts, true)) {
            $productCode = 'product';
        }

        $formId = tacticum_product_page_context_slug($cta['form_id'] ?? '', $productCode . '-cta');
        $context = [
            'lead_entry' => tacticum_product_page_context_slug($rawContext['lead_entry'] ?? '', $productCode),
            'lead_page_role' => 'product-page',
            'lead_product' => $productCode,
            'lead_intent' => tacticum_product_page_context_slug($rawContext['lead_intent'] ?? '', 'product-discussion'),
            'lead_cta' => tacticum_product_page_context_slug($rawContext['lead_cta'] ?? '', $formId),
            'lead_next_step' => tacticum_product_page_context_slug($rawContext['lead_next_step'] ?? '', 'manual-follow-up'),
        ];

        foreach ($context as $key => $value) {
            $context[$key] = mb_substr($value, 0, 80);
        }

        return $context;
    }
}

if (!function_exists('tacticum_product_page_cta_scenario_options')) {
    function tacticum_product_page_cta_scenario_options(array $cta): array
    {
        $standard = tacticum_product_page_standard_scenario_options();
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

if (!function_exists('tacticum_product_page_data')) {
    function tacticum_product_page_data(string $productCode): array
    {
        $source = function_exists('tacticum_product_content_source')
            ? tacticum_product_content_source()
            : 'fallback';

        if ($source !== 'fallback' && function_exists('tacticum_product_content_bitrix_data')) {
            $bitrixData = tacticum_product_content_bitrix_data($productCode);
            if (!empty($bitrixData)) {
                $isRenderable = function_exists('tacticum_product_content_is_minimum_renderable')
                    ? tacticum_product_content_is_minimum_renderable($bitrixData)
                    : true;

                if ($isRenderable) {
                    return $bitrixData;
                }

                if ($source === 'bitrix') {
                    return tacticum_product_page_unavailable_data($productCode, $bitrixData);
                }
            }

            if ($source === 'bitrix') {
                return tacticum_product_page_unavailable_data($productCode, $bitrixData);
            }
        }

        if ($source === 'bitrix') {
            return tacticum_product_page_unavailable_data($productCode, []);
        }

        return tacticum_product_page_fallback_data($productCode);
    }
}

if (!function_exists('tacticum_product_page_unavailable_data')) {
    function tacticum_product_page_unavailable_data(string $productCode, array $bitrixData = []): array
    {
        if (!headers_sent()) {
            http_response_code(503);
        }

        $diagnostics = [];
        if (function_exists('tacticum_product_content_completeness_diagnostics')) {
            $diagnostics = tacticum_product_content_completeness_diagnostics($bitrixData);
        }

        return [
            'eyebrow' => 'Tacticum product',
            'title' => 'Материалы продукта обновляются',
            'lead' => 'Страница временно недоступна: продуктовый контент проверяется в Bitrix. Оставьте заявку, и команда вернется с актуальным описанием сценария.',
            'primary_cta_text' => 'Связаться с командой',
            'secondary_cta_text' => 'Все услуги',
            'secondary_cta_href' => '/services/',
            'badges' => [],
            'hero_cards' => [],
            'sections' => [],
            'cta' => [
                'form_id' => 'product-unavailable',
                'field_prefix' => 'product',
                'title' => 'Уточнить продуктовый сценарий',
                'text' => 'Напишите, какой продукт или сценарий вам нужен. Мы ответим без публикации неподтвержденных материалов на сайте.',
                'form_title' => 'Заявка на уточнение',
                'button_text' => 'Отправить запрос',
                'scenario_label' => 'Сценарий',
                'scenario_empty_label' => 'Выберите сценарий',
                'scenario_options' => [
                    ['VALUE' => 'architecture-session', 'LABEL' => 'Архитектурная сессия'],
                    ['VALUE' => 'pilot', 'LABEL' => 'Пилот'],
                    ['VALUE' => 'estimate', 'LABEL' => 'Оценка внедрения'],
                ],
                'lead_context' => [
                    'lead_entry' => 'product-unavailable',
                    'lead_page_role' => 'product-page',
                    'lead_product' => $productCode,
                    'lead_intent' => 'content-unavailable',
                    'lead_cta' => 'contact-team',
                    'lead_next_step' => 'manual-follow-up',
                ],
            ],
            '_source' => 'bitrix',
            '_status' => 'unavailable',
            '_product_code' => $productCode,
            '_diagnostics' => $diagnostics,
        ];
    }
}

if (!function_exists('tacticum_product_page_fallback_data')) {
    function tacticum_product_page_fallback_data(string $productCode): array
    {
        $productFiles = function_exists('tacticum_product_content_codes')
            ? tacticum_product_content_codes()
            : [
                'platform' => 'platform.php',
                'agents' => 'agents.php',
                'dev' => 'dev.php',
                'forum' => 'forum.php',
            ];

        if (!isset($productFiles[$productCode])) {
            return [];
        }

        $path = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/product_data/' . $productFiles[$productCode];
        if (!is_file($path)) {
            return [];
        }

        $data = require $path;

        if (!is_array($data)) {
            return [];
        }

        $data['_source'] = 'fallback';
        $data['_product_code'] = $productCode;

        return $data;
    }
}

if (!function_exists('tacticum_product_page_schema_text')) {
    function tacticum_product_page_schema_text($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        if (function_exists('tacticum_json_ld_text')) {
            return tacticum_json_ld_text((string)$value);
        }

        return trim(strip_tags(html_entity_decode((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
    }
}

if (!function_exists('tacticum_product_page_software_schema')) {
    function tacticum_product_page_software_schema(
        array $page,
        string $canonicalPath,
        string $applicationCategory = 'BusinessApplication',
        string $description = ''
    ): array {
        $path = tacticum_product_page_canonical_path($canonicalPath);
        $name = tacticum_product_page_string($page, 'eyebrow');
        if ($name === '') {
            $name = tacticum_product_page_string($page, 'title', 'Tacticum product');
        }

        $schemaDescription = tacticum_product_page_schema_text($description);
        if ($schemaDescription === '') {
            $schemaDescription = tacticum_product_page_schema_text(tacticum_product_page_string($page, 'lead'));
        }

        return [
            '@type' => 'SoftwareApplication',
            '@id' => tacticum_public_url($path . '#software'),
            'name' => $name,
            'applicationCategory' => $applicationCategory !== '' ? $applicationCategory : 'BusinessApplication',
            'operatingSystem' => 'Web',
            'url' => tacticum_public_url($path),
            'description' => $schemaDescription,
            'provider' => [
                '@id' => tacticum_public_url('/#organization'),
            ],
            'isPartOf' => [
                '@id' => tacticum_public_url('/#website'),
            ],
        ];
    }
}

if (!function_exists('tacticum_product_page_faq_schema')) {
    function tacticum_product_page_faq_schema(array $page, string $canonicalPath): ?array
    {
        $faq = is_array($page['faq'] ?? null) ? $page['faq'] : [];
        $items = is_array($faq['items'] ?? null) ? $faq['items'] : [];

        if (empty($items)) {
            return null;
        }

        $entities = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $question = tacticum_product_page_schema_text($item['question'] ?? '');
            $answer = tacticum_product_page_schema_text($item['answer'] ?? '');

            if ($question === '' || $answer === '') {
                continue;
            }

            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        if (empty($entities)) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            '@id' => tacticum_public_url(tacticum_product_page_canonical_path($canonicalPath) . '#faq'),
            'mainEntity' => $entities,
        ];
    }
}

if (!function_exists('tacticum_product_page_schema')) {
    function tacticum_product_page_schema(
        array $page,
        string $canonicalPath,
        string $applicationCategory = 'BusinessApplication',
        string $description = ''
    ): array {
        $schema = [
            tacticum_product_page_software_schema($page, $canonicalPath, $applicationCategory, $description),
        ];
        $faqSchema = tacticum_product_page_faq_schema($page, $canonicalPath);
        if ($faqSchema !== null) {
            $schema[] = $faqSchema;
        }

        return $schema;
    }
}

$tacticumProductPageBlockIncludes = [
    '/local/php_interface/include/product_page_blocks/common.php',
    '/local/php_interface/include/product_page_blocks/architecture.php',
    '/local/php_interface/include/product_page_blocks/use_cases.php',
    '/local/php_interface/include/product_page_blocks/procurement.php',
    '/local/php_interface/include/product_page_blocks/comparison.php',
    '/local/php_interface/include/product_page_blocks/rollout.php',
    '/local/php_interface/include/product_page_blocks/proof.php',
    '/local/php_interface/include/product_page_blocks/faq.php',
    '/local/php_interface/include/product_page_blocks/page.php',
];

foreach ($tacticumProductPageBlockIncludes as $relativePath) {
    $path = $_SERVER['DOCUMENT_ROOT'] . $relativePath;
    if (is_file($path)) {
        require_once $path;
    }
}

unset($tacticumProductPageBlockIncludes, $relativePath, $path);
