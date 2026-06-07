<?php

return [
    'iblocks' => [
        'offer' => 5,
        'offer_taxonomy_terms' => 0,
        'vacancies' => 7,
        'clients' => 8,
        'feedback' => 9,
        'faq' => 10,
        'rates' => 11,
        'services' => 12,
        'cases' => 13,
        'team' => 18,
        'policies' => 19,
        'aiagents' => 20,
        'products' => 0,
        'product_blocks' => 0,
        'product_use_cases' => 0,
        'page_sections' => 0,
        'page_blocks' => 0,
        'team_presets' => 0,
        'team_preset_roles' => 0,
    ],
    'base_urls' => [
        'AI_SERVICE_BASE_URL' => 'https://ai.example.com',
        'TELEGRAM_RESOLVER_URL' => 'https://ai.example.com',
    ],
    'api' => [
        'cache_ttl_default' => 300,
        'cache_ttl' => [
            'cases' => 300,
            'faq' => 300,
            'rates' => 300,
            'services' => 300,
        ],
    ],
    'content' => [
        'faq_section_fallback_ids' => [
            'home' => 17,
            'main' => 17,
            'aiagents' => 18,
            'calculator' => 19,
            'offer' => 19,
            'services' => 20,
            'price' => 21,
        ],
    ],
    'products' => [
        'source' => 'bitrix',
        'allow_fallback' => false,
        'cache_ttl' => 300,
    ],
    'page_content' => [
        'source' => 'fallback',
        'live_status' => 'live',
        'allow_fallback' => true,
    ],
    'offer' => [
        'taxonomy_source' => 'fallback',
        'taxonomy_cache_ttl' => 300,
        'allow_taxonomy_fallback' => true,
    ],
    'price' => [
        'team_presets_source' => 'fallback',
        'team_presets_cache_ttl' => 300,
        'allow_team_presets_fallback' => true,
    ],
    'ai' => [
        'endpoint_paths' => [
            'chat_agent_sale' => '/tacticum/v1/chat_agent/sale',
            'staff_sale' => '/tacticum/v1/chat_agent/sale',
        ],
    ],
    'security' => [
        'csp_mode' => 'report-only',
    ],
    'rest' => [
        'allow_no_origin' => false,
        'allowed_origins' => [
            'tacticum.ru',
            '*.tacticum.ru',
        ],
        'allowed_ips' => [],
        'trusted_proxies' => [],
        'rate_limits' => [
            'CONFIG_HEALTH_GET' => ['limit' => 5, 'ttl' => 60],
            'PUBLIC_LEAD_POST' => ['limit' => 20, 'ttl' => 60],
            'PUBLIC_CHAT_POST' => ['limit' => 20, 'ttl' => 60],
            'PUBLIC_STAFF_POST' => ['limit' => 20, 'ttl' => 60],
            'SCOPED_PREFILL_POST' => ['limit' => 20, 'ttl' => 60],
            'PUBLIC_RESOLVER_POST' => ['limit' => 20, 'ttl' => 60],
            'LEGACY_ALIAS_POST' => ['limit' => 20, 'ttl' => 60],
        ],
    ],
];
