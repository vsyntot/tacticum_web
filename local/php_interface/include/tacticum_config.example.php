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
    ],
    'base_urls' => [
        'AI_SERVICE_BASE_URL' => 'https://ai.example.com',
        'TELEGRAM_RESOLVER_URL' => 'https://ai.example.com',
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
