#!/usr/bin/env php
<?php
declare(strict_types=1);

function tacticum_config_runtime_check_usage(): string
{
    return <<<TEXT
Usage:
  php tools/config-runtime-check.php [--json] [--document-root=/path/to/site]

Validates the runtime tacticum_config.php through the same helpers used by
/local/rest/health_config.php and prints a safe config summary without secret values.

TEXT;
}

function tacticum_config_runtime_check_options(array $argv): array
{
    $options = [
        'document_root' => dirname(__DIR__),
        'json' => false,
        'help' => false,
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--help' || $argument === '-h') {
            $options['help'] = true;
            continue;
        }
        if ($argument === '--json') {
            $options['json'] = true;
            continue;
        }
        if (str_starts_with($argument, '--document-root=')) {
            $options['document_root'] = substr($argument, strlen('--document-root='));
            continue;
        }

        throw new InvalidArgumentException('Unknown argument: ' . $argument);
    }

    $documentRoot = realpath((string)$options['document_root']);
    if ($documentRoot === false) {
        throw new RuntimeException('Document root does not exist: ' . (string)$options['document_root']);
    }
    $options['document_root'] = $documentRoot;

    return $options;
}

function tacticum_config_runtime_check_line(string $message): void
{
    echo $message . PHP_EOL;
}

function tacticum_config_runtime_check_value_source(array $config, string $section, string $key): string
{
    return array_key_exists($section, $config)
        && is_array($config[$section])
        && array_key_exists($key, $config[$section])
            ? 'explicit'
            : 'default';
}

function tacticum_config_runtime_check_endpoint_path(array $config, string $key, string $default): array
{
    $ai = is_array($config['ai'] ?? null) ? $config['ai'] : [];
    $endpointPaths = is_array($ai['endpoint_paths'] ?? null) ? $ai['endpoint_paths'] : [];
    $explicit = array_key_exists($key, $endpointPaths);

    return [
        'path' => tacticum_rest_get_ai_endpoint_path($key, $default),
        'source' => $explicit ? 'explicit' : 'default',
    ];
}

function tacticum_config_runtime_check_url_status(string $key): array
{
    $value = trim(tacticum_rest_get_ai_setting($key));
    if ($value === '') {
        return [
            'status' => 'missing',
            'scheme' => '',
            'host' => '',
        ];
    }

    $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
    $host = (string)parse_url($value, PHP_URL_HOST);

    return [
        'status' => $scheme === 'https' && $host !== '' ? 'present_https' : 'invalid',
        'scheme' => $scheme,
        'host' => $host,
    ];
}

function tacticum_config_runtime_check_summary(string $documentRoot): array
{
    $config = tacticum_rest_get_config();
    $scopes = ['api', 'ai', 'telegram', 'offer', 'content', 'products', 'rest', 'security'];
    $errors = tacticum_rest_validate_config($scopes);
    $productsConfig = tacticum_rest_get_config_section('products');
    $securityConfig = tacticum_rest_get_config_section('security');
    $contentConfig = tacticum_rest_get_config_section('content');
    $restConfig = tacticum_rest_get_config_section('rest');
    $rateLimits = is_array($restConfig['rate_limits'] ?? null)
        ? $restConfig['rate_limits']
        : [];
    $rateLimitClasses = function_exists('tacticum_rest_rate_limit_classes')
        ? tacticum_rest_rate_limit_classes()
        : [];

    $iblocks = [];
    foreach ([
        'offer',
        'vacancies',
        'feedback',
        'faq',
        'rates',
        'services',
        'cases',
        'team',
        'policies',
        'aiagents',
        'products',
        'product_blocks',
        'product_use_cases',
    ] as $key) {
        $iblocks[$key] = tacticum_rest_get_iblock_id($key);
    }

    $faqFallbacks = is_array($contentConfig['faq_section_fallback_ids'] ?? null)
        ? $contentConfig['faq_section_fallback_ids']
        : [];

    return [
        'document_root' => $documentRoot,
        'config_file' => $documentRoot . '/local/php_interface/include/tacticum_config.php',
        'scopes' => $scopes,
        'valid' => empty($errors),
        'errors' => $errors,
        'iblocks' => $iblocks,
        'products' => [
            'source' => (string)($productsConfig['source'] ?? 'bitrix'),
            'allow_fallback' => (bool)($productsConfig['allow_fallback'] ?? false),
            'cache_ttl' => isset($productsConfig['cache_ttl']) ? (int)$productsConfig['cache_ttl'] : null,
            'schema_version' => function_exists('tacticum_product_content_schema_version')
                ? tacticum_product_content_schema_version()
                : 'unknown',
            'source_config' => tacticum_config_runtime_check_value_source($config, 'products', 'source'),
            'allow_fallback_config' => tacticum_config_runtime_check_value_source($config, 'products', 'allow_fallback'),
            'cache_ttl_config' => tacticum_config_runtime_check_value_source($config, 'products', 'cache_ttl'),
        ],
        'ai' => [
            'base_urls' => [
                'AI_SERVICE_BASE_URL' => tacticum_config_runtime_check_url_status('AI_SERVICE_BASE_URL'),
                'TELEGRAM_RESOLVER_URL' => tacticum_config_runtime_check_url_status('TELEGRAM_RESOLVER_URL'),
            ],
            'endpoint_paths' => [
                'chat_agent_sale' => tacticum_config_runtime_check_endpoint_path($config, 'chat_agent_sale', '/tacticum/v1/chat_agent/sale'),
                'staff_sale' => tacticum_config_runtime_check_endpoint_path($config, 'staff_sale', '/tacticum/v1/chat_agent/sale'),
            ],
        ],
        'security' => [
            'csp_mode' => strtolower(trim((string)($securityConfig['csp_mode'] ?? 'report-only'))),
            'csp_mode_config' => tacticum_config_runtime_check_value_source($config, 'security', 'csp_mode'),
        ],
        'content' => [
            'faq_section_fallback_ids_count' => count($faqFallbacks),
            'faq_section_fallback_ids_config' => array_key_exists('content', $config) ? 'explicit' : 'default',
        ],
        'rest' => [
            'allow_no_origin' => (bool)($restConfig['allow_no_origin'] ?? false),
            'allowed_origins_count' => is_array($restConfig['allowed_origins'] ?? null) ? count($restConfig['allowed_origins']) : 0,
            'allowed_ips_count' => is_array($restConfig['allowed_ips'] ?? null) ? count($restConfig['allowed_ips']) : 0,
            'trusted_proxies_count' => is_array($restConfig['trusted_proxies'] ?? null) ? count($restConfig['trusted_proxies']) : 0,
            'rate_limit_classes_count' => count($rateLimitClasses),
            'rate_limits_override_count' => count($rateLimits),
            'rate_limits_config' => (
                isset($config['rest'])
                && is_array($config['rest'])
                && array_key_exists('rate_limits', $config['rest'])
            ) ? 'explicit' : 'default',
        ],
    ];
}

function tacticum_config_runtime_check_print(array $summary): void
{
    tacticum_config_runtime_check_line('Tacticum Runtime Config Check');
    tacticum_config_runtime_check_line('Document root: ' . $summary['document_root']);
    tacticum_config_runtime_check_line('Config file: ' . $summary['config_file']);
    tacticum_config_runtime_check_line('Health scopes: ' . implode(', ', $summary['scopes']));
    tacticum_config_runtime_check_line('Valid: ' . ($summary['valid'] ? 'yes' : 'no'));

    if (!$summary['valid']) {
        tacticum_config_runtime_check_line('');
        tacticum_config_runtime_check_line('Errors:');
        foreach ($summary['errors'] as $error) {
            tacticum_config_runtime_check_line('- ' . ($error['key'] ?? 'unknown') . ': ' . ($error['code'] ?? 'unknown'));
        }
        return;
    }

    tacticum_config_runtime_check_line('');
    tacticum_config_runtime_check_line('Iblocks:');
    foreach ($summary['iblocks'] as $key => $id) {
        tacticum_config_runtime_check_line('- ' . $key . ': #' . $id);
    }

    tacticum_config_runtime_check_line('');
    tacticum_config_runtime_check_line('Products: source=' . $summary['products']['source']
        . ' (' . $summary['products']['source_config'] . ')'
        . ', allow_fallback=' . ($summary['products']['allow_fallback'] ? 'true' : 'false')
        . ' (' . $summary['products']['allow_fallback_config'] . ')'
        . ', cache_ttl=' . (string)$summary['products']['cache_ttl']
        . ' (' . $summary['products']['cache_ttl_config'] . ')'
        . ', schema_version=' . $summary['products']['schema_version']);

    tacticum_config_runtime_check_line('');
    tacticum_config_runtime_check_line('AI base URLs:');
    foreach ($summary['ai']['base_urls'] as $key => $status) {
        tacticum_config_runtime_check_line('- ' . $key . ': ' . $status['status']
            . ($status['host'] !== '' ? ', host=' . $status['host'] : ''));
    }

    tacticum_config_runtime_check_line('');
    tacticum_config_runtime_check_line('AI endpoint paths:');
    foreach ($summary['ai']['endpoint_paths'] as $key => $path) {
        tacticum_config_runtime_check_line('- ' . $key . ': ' . $path['path'] . ' (' . $path['source'] . ')');
    }

    tacticum_config_runtime_check_line('');
    tacticum_config_runtime_check_line('Security: csp_mode=' . $summary['security']['csp_mode']
        . ' (' . $summary['security']['csp_mode_config'] . ')');
    tacticum_config_runtime_check_line('Content: faq_section_fallback_ids_count='
        . $summary['content']['faq_section_fallback_ids_count']
        . ' (' . $summary['content']['faq_section_fallback_ids_config'] . ')');
    tacticum_config_runtime_check_line('REST: allow_no_origin=' . ($summary['rest']['allow_no_origin'] ? 'true' : 'false')
        . ', allowed_origins_count=' . $summary['rest']['allowed_origins_count']
        . ', allowed_ips_count=' . $summary['rest']['allowed_ips_count']
        . ', trusted_proxies_count=' . $summary['rest']['trusted_proxies_count']
        . ', rate_limit_classes_count=' . $summary['rest']['rate_limit_classes_count']
        . ', rate_limits_override_count=' . $summary['rest']['rate_limits_override_count']
        . ' (' . $summary['rest']['rate_limits_config'] . ')');
}

try {
    $options = tacticum_config_runtime_check_options($argv);
    if ($options['help']) {
        echo tacticum_config_runtime_check_usage();
        exit(0);
    }

    $documentRoot = (string)$options['document_root'];
    $prolog = $documentRoot . '/bitrix/modules/main/include/prolog_before.php';
    if (!is_file($prolog)) {
        throw new RuntimeException('Bitrix prolog not found: ' . $prolog);
    }

    $_SERVER['DOCUMENT_ROOT'] = $documentRoot;
    $_SERVER['REQUEST_METHOD'] = 'CLI';
    define('NO_KEEP_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);

    require $prolog;

    if (!function_exists('tacticum_rest_validate_config')) {
        throw new RuntimeException('REST config helper tacticum_rest_validate_config() is unavailable.');
    }

    $summary = tacticum_config_runtime_check_summary($documentRoot);
    if ($options['json']) {
        echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        tacticum_config_runtime_check_print($summary);
    }

    exit($summary['valid'] ? 0 : 1);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
