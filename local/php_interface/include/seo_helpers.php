<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_json_ld_text')) {
    function tacticum_json_ld_text(string $text): string
    {
        if (function_exists('tacticum_rest_html_to_text')) {
            return trim(tacticum_rest_html_to_text($text));
        }

        if (function_exists('tacticum_decode_iblock_text')) {
            return trim(strip_tags(tacticum_decode_iblock_text($text)));
        }

        return trim(strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
    }
}

if (!function_exists('tacticum_add_json_ld')) {
    function tacticum_add_json_ld(array $data, string $key = ''): void
    {
        global $APPLICATION;

        if (!is_object($APPLICATION) || !method_exists($APPLICATION, 'AddHeadString')) {
            return;
        }

        static $applied = [];

        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        if (!is_string($json) || $json === '') {
            return;
        }

        $hash = $key !== '' ? $key : md5($json);
        if (isset($applied[$hash])) {
            return;
        }
        $applied[$hash] = true;

        $APPLICATION->AddHeadString('<script type="application/ld+json">' . $json . '</script>', true);
    }
}

if (!function_exists('tacticum_default_json_ld_graph')) {
    function tacticum_default_json_ld_graph(string $canonicalUrl, string $canonicalPath, string $title): array
    {
        $templatePath = defined('SITE_TEMPLATE_PATH') ? SITE_TEMPLATE_PATH : '/local/templates/tacticum';
        $graph = [
            [
                '@type' => 'Organization',
                '@id' => tacticum_public_url('/#organization'),
                'name' => 'Tacticum',
                'alternateName' => 'Тактикум',
                'url' => tacticum_public_url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => tacticum_public_url($templatePath . '/images/logo.png'),
                    'width' => 543,
                    'height' => 150,
                ],
                'contactPoint' => [
                    [
                        '@type' => 'ContactPoint',
                        'telephone' => '+7-495-561-20-84',
                        'email' => 'project@tacticum.ru',
                        'contactType' => 'customer support',
                        'areaServed' => 'RU',
                        'availableLanguage' => ['ru'],
                    ],
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => tacticum_public_url('/#website'),
                'url' => tacticum_public_url('/'),
                'name' => 'Tacticum',
                'inLanguage' => 'ru-RU',
                'publisher' => [
                    '@id' => tacticum_public_url('/#organization'),
                ],
            ],
        ];

        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Главная',
                'item' => tacticum_public_url('/'),
            ],
        ];

        if ($canonicalPath !== '/') {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $title,
                'item' => $canonicalUrl,
            ];
        }

        $graph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $canonicalUrl . '#breadcrumb',
            'itemListElement' => $breadcrumbItems,
        ];

        return $graph;
    }
}

if (!function_exists('tacticum_normalize_json_ld_items')) {
    function tacticum_normalize_json_ld_items(array $items): array
    {
        if (isset($items['@type'])) {
            return [$items];
        }

        $normalized = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item['@type'])) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }
}

if (!function_exists('tacticum_add_robots_meta')) {
    function tacticum_add_robots_meta(string $robots): void
    {
        global $APPLICATION;

        $robots = trim($robots);
        if ($robots === '' || !is_object($APPLICATION) || !method_exists($APPLICATION, 'AddHeadString')) {
            return;
        }

        $APPLICATION->AddHeadString(
            '<meta name="robots" content="' . htmlspecialchars($robots, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
            true
        );
    }
}

if (!function_exists('tacticum_faq_json_ld')) {
    function tacticum_faq_json_ld(int $limit = 20): ?array
    {
        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return null;
        }

        $iblockId = tacticum_iblock_id('faq');
        if ($iblockId <= 0) {
            return null;
        }

        $cacheKey = 'tacticum_faq_json_ld_' . $limit . '_' . $iblockId;
        $cacheDir = '/tacticum/seo';
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache(3600, $cacheKey, $cacheDir)) {
            $cached = $cache->getVars();
            return is_array($cached['schema'] ?? null) ? $cached['schema'] : null;
        }

        $entities = [];
        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
            ],
            false,
            ['nTopCount' => $limit],
            [
                'ID',
                'NAME',
                'DETAIL_TEXT',
            ]
        );

        while ($element = $result->Fetch()) {
            $question = tacticum_json_ld_text((string)($element['NAME'] ?? ''));
            $answer = tacticum_json_ld_text((string)($element['DETAIL_TEXT'] ?? ''));
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

        $schema = !empty($entities)
            ? [
                '@type' => 'FAQPage',
                'mainEntity' => $entities,
            ]
            : null;

        if ($cache->startDataCache(3600, $cacheKey, $cacheDir)) {
            $cache->endDataCache(['schema' => $schema]);
        }

        return $schema;
    }
}

if (!function_exists('tacticum_apply_seo_defaults')) {
    function tacticum_apply_seo_defaults(?string $canonicalPath = null, array $options = []): void
    {
        global $APPLICATION;

        if (!is_object($APPLICATION) || !method_exists($APPLICATION, 'AddHeadString')) {
            return;
        }

        static $applied = [];

        $currentPath = method_exists($APPLICATION, 'GetCurPage') ? (string)$APPLICATION->GetCurPage(false) : '/';
        $canonicalPath = $canonicalPath ?: $currentPath;
        if ($canonicalPath === '') {
            $canonicalPath = '/';
        }
        if (filter_var($canonicalPath, FILTER_VALIDATE_URL) === false && $canonicalPath[0] !== '/') {
            $canonicalPath = '/' . $canonicalPath;
        }
        $canonicalUrl = tacticum_public_url($canonicalPath);
        if (isset($applied[$canonicalUrl])) {
            return;
        }
        $applied[$canonicalUrl] = true;

        $title = trim((string)(method_exists($APPLICATION, 'GetTitle') ? $APPLICATION->GetTitle(false) : ''));
        if ($title === '') {
            $title = 'Тактикум';
        }

        $description = trim((string)(method_exists($APPLICATION, 'GetPageProperty') ? $APPLICATION->GetPageProperty('description') : ''));
        $type = trim((string)($options['type'] ?? 'website'));
        $templatePath = defined('SITE_TEMPLATE_PATH') ? SITE_TEMPLATE_PATH : '/local/templates/tacticum';
        $image = tacticum_public_url((string)($options['image'] ?? $templatePath . '/images/og-default.jpg'));
        $imageWidth = (int)($options['image_width'] ?? 1200);
        $imageHeight = (int)($options['image_height'] ?? 630);
        $imageType = trim((string)($options['image_type'] ?? 'image/jpeg'));

        $meta = [
            '<link rel="canonical" href="' . htmlspecialchars($canonicalUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
            '<meta property="og:site_name" content="Tacticum">',
            '<meta property="og:locale" content="ru_RU">',
            '<meta property="og:type" content="' . htmlspecialchars($type ?: 'website', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
            '<meta property="og:url" content="' . htmlspecialchars($canonicalUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
            '<meta property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
            '<meta property="og:image" content="' . htmlspecialchars($image, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
            '<meta property="og:image:width" content="' . $imageWidth . '">',
            '<meta property="og:image:height" content="' . $imageHeight . '">',
            '<meta property="og:image:type" content="' . htmlspecialchars($imageType, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
            '<meta name="twitter:card" content="' . htmlspecialchars((string)($options['twitter_card'] ?? 'summary_large_image'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
            '<meta name="twitter:title" content="' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
            '<meta name="twitter:image" content="' . htmlspecialchars($image, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">',
        ];

        if ($description !== '') {
            $meta[] = '<meta property="og:description" content="' . htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
            $meta[] = '<meta name="twitter:description" content="' . htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
        }

        $robots = trim((string)($options['robots'] ?? ''));
        if ($robots !== '') {
            $meta[] = '<meta name="robots" content="' . htmlspecialchars($robots, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
        }

        foreach ($meta as $tag) {
            $APPLICATION->AddHeadString($tag, true);
        }

        if (($options['json_ld'] ?? true) !== false) {
            $graph = tacticum_default_json_ld_graph($canonicalUrl, $canonicalPath, $title);

            if (!empty($options['schema']) && is_array($options['schema'])) {
                $graph = array_merge($graph, tacticum_normalize_json_ld_items($options['schema']));
            }

            if (!empty($options['faq_schema'])) {
                $faqSchema = tacticum_faq_json_ld();
                if ($faqSchema !== null) {
                    $graph[] = $faqSchema;
                }
            }

            tacticum_add_json_ld([
                '@context' => 'https://schema.org',
                '@graph' => $graph,
            ], 'seo-defaults-' . md5($canonicalUrl));
        }
    }
}
