<?php

namespace Tacticum\Seo;

final class Meta
{
    public static function addRobots(string $robots): void
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

    public static function applyDefaults(?string $canonicalPath = null, array $options = []): void
    {
        global $APPLICATION;

        if (!is_object($APPLICATION) || !method_exists($APPLICATION, 'AddHeadString')) {
            return;
        }

        static $applied = [];

        $currentPath = method_exists($APPLICATION, 'GetCurPage') ? (string)$APPLICATION->GetCurPage(false) : '/';
        $canonicalPath = self::normalizeCanonicalPath($canonicalPath ?: $currentPath);
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

        $meta = self::socialMeta($canonicalUrl, $title, $type, $image, $imageWidth, $imageHeight, $imageType, $options);
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

        self::applyJsonLd($canonicalUrl, $canonicalPath, $title, $options);
    }

    private static function normalizeCanonicalPath(string $canonicalPath): string
    {
        if ($canonicalPath === '') {
            $canonicalPath = '/';
        }
        if (filter_var($canonicalPath, FILTER_VALIDATE_URL) === false && $canonicalPath[0] !== '/') {
            $canonicalPath = '/' . $canonicalPath;
        }

        return $canonicalPath;
    }

    private static function socialMeta(
        string $canonicalUrl,
        string $title,
        string $type,
        string $image,
        int $imageWidth,
        int $imageHeight,
        string $imageType,
        array $options
    ): array {
        return [
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
    }

    private static function applyJsonLd(string $canonicalUrl, string $canonicalPath, string $title, array $options): void
    {
        if (($options['json_ld'] ?? true) === false) {
            return;
        }

        $graph = JsonLd::defaultGraph($canonicalUrl, $canonicalPath, $title);
        if (!empty($options['schema']) && is_array($options['schema'])) {
            $graph = array_merge($graph, JsonLd::normalizeItems($options['schema']));
        }

        if (!empty($options['faq_schema'])) {
            $faqSchema = FaqSchema::build();
            if ($faqSchema !== null) {
                $graph[] = $faqSchema;
            }
        }

        JsonLd::add([
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ], 'seo-defaults-' . md5($canonicalUrl));
    }
}
