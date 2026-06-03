<?php

return [
    'iblocks' => [
        'offer' => 5,
        'vacancies' => 7,
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
        'source' => 'auto',
        'cache_ttl' => 300,
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
    ],
];
