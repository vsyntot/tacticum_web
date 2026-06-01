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

if (!function_exists('tacticum_product_page_data')) {
    function tacticum_product_page_data(string $productCode): array
    {
        $productFiles = [
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

        return is_array($data) ? $data : [];
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
