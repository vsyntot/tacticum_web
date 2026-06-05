<?php

namespace Tacticum\Seo;

final class JsonLd
{
    public static function text(string $text): string
    {
        if (function_exists('tacticum_rest_html_to_text')) {
            return trim(tacticum_rest_html_to_text($text));
        }

        if (function_exists('tacticum_decode_iblock_text')) {
            return trim(strip_tags(tacticum_decode_iblock_text($text)));
        }

        return trim(strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
    }

    public static function add(array $data, string $key = ''): void
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

    public static function defaultGraph(string $canonicalUrl, string $canonicalPath, string $title): array
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

    public static function normalizeItems(array $items): array
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
