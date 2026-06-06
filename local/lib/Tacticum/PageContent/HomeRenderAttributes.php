<?php

declare(strict_types=1);

namespace Tacticum\PageContent;

final class HomeRenderAttributes
{
    public static function linkDataAttributes(array $section, array $item): string
    {
        if (RenderSupport::text($section['page_key'] ?? '') !== '/') {
            return '';
        }

        $sectionKey = RenderSupport::text($section['section_key'] ?? '');
        if ($sectionKey === 'ecosystem' || $sectionKey === 'fit-matrix') {
            $product = self::productCode($item);
            return $product !== '' ? ' data-home-product-link="' . RenderSupport::h($product) . '"' : '';
        }
        if ($sectionKey === 'commercial') {
            $link = self::commercialLinkCode($item);
            return $link !== '' ? ' data-home-commercial-link="' . RenderSupport::h($link) . '"' : '';
        }

        return '';
    }

    private static function productCode(array $item): string
    {
        $meta = RenderSupport::text($item['meta'] ?? '');
        if (preg_match('/(?:^|;)product=(platform|agents|dev|forum)(?:;|$)/', $meta, $matches) === 1) {
            return $matches[1];
        }

        return match (RenderSupport::href($item['href'] ?? '')) {
            '/platform/' => 'platform',
            '/agents/' => 'agents',
            '/dev/' => 'dev',
            '/forum/' => 'forum',
            default => '',
        };
    }

    private static function commercialLinkCode(array $item): string
    {
        return match (RenderSupport::href($item['href'] ?? '')) {
            '/offer/' => 'offer',
            '/services/' => 'services',
            '/price/' => 'price',
            '/aiagents/' => 'aiagents',
            default => '',
        };
    }
}
