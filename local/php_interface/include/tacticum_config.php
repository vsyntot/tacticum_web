<?php

return [
    'iblocks' => [
        'cases' => 13,
        'faq' => 10,
        'rates' => 11,
        'services' => 12,
        'offer' => 5,
    ],
    'base_urls' => [
        'AI_SERVICE_BASE_URL' => 'http://5.35.90.193:8000',
        'TELEGRAM_RESOLVER_URL' => 'http://5.35.90.193:8000',
    ],
    'rest' => [
        'allow_no_origin' => false,
        'allowed_origins' => [
            'tacticum.ru',
            '*.tacticum.ru',
        ],
        'allowed_ips' => [
            '77.37.238.241',
            '109.120.158.169',
            '5.35.90.193',
        ],
        'trusted_proxies' => [],
    ],
];
