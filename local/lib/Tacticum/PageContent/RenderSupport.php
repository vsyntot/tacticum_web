<?php

declare(strict_types=1);

namespace Tacticum\PageContent;

final class RenderSupport
{
    public static function renderHeading(array $section, string $class, bool $centerText = false): void
    {
        $eyebrow = self::text($section['eyebrow'] ?? '');
        $title = self::text($section['title'] ?? '');
        $text = self::text($section['text'] ?? '');
        if ($eyebrow === '' && $title === '' && $text === '') {
            return;
        }

        echo '        <div class="' . self::h($class) . '">' . PHP_EOL;
        if ($eyebrow !== '') {
            echo '            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">' . self::h($eyebrow) . '</p>' . PHP_EOL;
        }
        if ($title !== '') {
            echo '            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">' . self::h($title) . '</h2>' . PHP_EOL;
        }
        if ($text !== '') {
            $className = $centerText ? 'text-lg text-gray-600 max-w-3xl mx-auto' : 'text-lg text-gray-600';
            echo '            <p class="' . self::h($className) . '">' . self::h($text) . '</p>' . PHP_EOL;
        }
        echo '        </div>' . PHP_EOL;
    }

    public static function sectionOpen(array $section, string $class): string
    {
        $attributes = self::sectionAttributes($section);
        $extra = '';
        foreach ($attributes as $name => $value) {
            $extra .= ' ' . $name . '="' . self::h($value) . '"';
        }

        return '<section class="' . self::h($class) . '"'
            . ' data-page-content-source="bitrix"'
            . ' data-page-content-page="' . self::h(self::text($section['page_key'] ?? '')) . '"'
            . ' data-page-content-section="' . self::h(self::text($section['section_key'] ?? '')) . '"'
            . ' data-page-content-template="' . self::h(self::text($section['template_key'] ?? '')) . '"'
            . $extra
            . '>';
    }

    public static function renderTitleText(array $item, string $titleTag, string $titleClass, string $textClass): void
    {
        $title = self::text($item['title'] ?? '');
        $text = self::text($item['text'] ?? '');
        if ($title !== '') {
            echo '                <' . $titleTag . ' class="' . self::h($titleClass) . '">' . self::h($title) . '</' . $titleTag . '>' . PHP_EOL;
        }
        if ($text !== '') {
            echo '                <p class="' . self::h($textClass) . '">' . self::h($text) . '</p>' . PHP_EOL;
        }
    }

    public static function renderIcon(string $icon, string $wrapperClass, string $iconClass): void
    {
        $icon = self::icon($icon);
        if ($icon === '') {
            return;
        }

        echo '                <div class="' . self::h($wrapperClass) . '">' . PHP_EOL;
        echo '                    <i class="' . self::h($icon . ' ' . $iconClass) . '"></i>' . PHP_EOL;
        echo '                </div>' . PHP_EOL;
    }

    public static function renderOfficeCardBody(array $item, array $context): void
    {
        $title = self::text($item['title'] ?? 'Офис');
        $place = self::text($context['OFFICE_PLACE_NAME'] ?? '');
        $landmark = self::text($context['OFFICE_LANDMARK_NAME'] ?? '');
        $address = self::text($context['OFFICE_ADDRESS'] ?? '');
        $href = self::href($item['href'] ?? '#map') ?: '#map';
        $label = self::text($item['label'] ?? 'Показать на карте');

        echo '                <h3 class="text-xl font-bold text-secondary mb-3">' . self::h($title) . '</h3>' . PHP_EOL;
        if ($place !== '' || $landmark !== '') {
            $placeLine = trim($place . ($place !== '' && $landmark !== '' ? ', ' : '') . $landmark);
            echo '                <p class="text-gray-700 font-medium mb-3">' . self::h($placeLine) . '</p>' . PHP_EOL;
        }
        if ($address !== '') {
            echo '                <p class="text-gray-600 mb-6">Юридический адрес: ' . self::h($address) . '</p>' . PHP_EOL;
        } else {
            $text = self::text($item['text'] ?? '');
            if ($text !== '') {
                echo '                <p class="text-gray-600 mb-6">' . self::h($text) . '</p>' . PHP_EOL;
            }
        }
        if ($label !== '') {
            echo '                <div class="mt-auto">' . PHP_EOL;
            echo '                    <a href="' . self::h($href) . '" class="text-lg font-medium text-primary hover:text-primary/80 transition-colors flex items-center gap-2">' . self::h($label) . '<i class="ri-arrow-down-line"></i></a>' . PHP_EOL;
            echo '                </div>' . PHP_EOL;
        }
    }

    public static function blocks(array $section): array
    {
        $blocks = $section['blocks'] ?? [];

        return is_array($blocks) ? array_values(array_filter($blocks, 'is_array')) : [];
    }

    public static function text(mixed $value): string
    {
        return trim((string)$value);
    }

    public static function href(mixed $value): string
    {
        $href = trim((string)$value);
        if ($href === '') {
            return '';
        }
        if (str_starts_with($href, '/') || str_starts_with($href, '#')) {
            return $href;
        }
        if (preg_match('/^(?:mailto|tel):/i', $href) === 1) {
            return $href;
        }

        return '';
    }

    public static function h(string $value): string
    {
        return function_exists('htmlspecialcharsbx')
            ? htmlspecialcharsbx($value)
            : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function icon(string $value): string
    {
        return preg_match('/^ri-[a-z0-9-]+$/', $value) === 1 ? $value : '';
    }

    private static function sectionAttributes(array $section): array
    {
        if (self::text($section['page_key'] ?? '') !== '/') {
            return [];
        }

        return match (self::text($section['section_key'] ?? '')) {
            'ecosystem' => ['data-home-block' => 'ecosystem-map'],
            'fit-matrix' => ['data-home-block' => 'fit-matrix'],
            'commercial' => ['data-home-block' => 'commercial-next-steps'],
            default => [],
        };
    }
}
