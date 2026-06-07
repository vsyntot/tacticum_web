<?php

declare(strict_types=1);

namespace Tacticum\PageContent;

final class Renderer
{
    public static function render(array $section, array $context = []): bool
    {
        $templateKey = RenderSupport::text($section['template_key'] ?? '');
        if ($templateKey === '') {
            return false;
        }

        return match ($templateKey) {
            'product-card-grid', 'routing-card-grid' => self::renderLinkedCardGrid($section),
            'step-list' => self::renderStepList($section),
            'tech-grid' => self::renderTechGrid($section),
            'feature-card-grid' => self::renderFeatureGrid($section),
            'calculator-chat-outcome' => CalculatorRenderer::renderChatOutcome($section),
            'contact-card-grid' => self::renderContactGrid($section, $context),
            'cta-band' => self::renderCtaBand($section),
            default => false,
        };
    }

    private static function renderLinkedCardGrid(array $section): bool
    {
        $items = RenderSupport::blocks($section);
        if ($items === []) {
            return false;
        }

        $theme = RenderSupport::text($section['theme'] ?? '');
        $isHome = RenderSupport::text($section['page_key'] ?? '') === '/';
        $sectionClass = ($theme === 'gray')
            ? ($isHome ? 'py-20 bg-gray-50' : 'py-16 bg-gray-50')
            : ($isHome ? 'py-20 bg-white' : 'py-16 bg-white');
        $cardClass = $theme === 'gray'
            ? 'rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all'
            : 'rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors';
        $gridClass = count($items) === 3
            ? 'grid grid-cols-1 md:grid-cols-3 gap-6'
            : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6';

        echo RenderSupport::sectionOpen($section, $sectionClass) . PHP_EOL;
        echo '    <div class="container mx-auto px-4">' . PHP_EOL;
        RenderSupport::renderHeading($section, 'mb-10 max-w-3xl');
        echo '        <div class="' . RenderSupport::h($gridClass) . '">' . PHP_EOL;
        foreach ($items as $item) {
            $href = RenderSupport::href($item['href'] ?? '');
            echo '            <a href="' . RenderSupport::h($href !== '' ? $href : '#') . '" class="' . RenderSupport::h($cardClass) . '"' . HomeRenderAttributes::linkDataAttributes($section, $item) . '>' . PHP_EOL;
            RenderSupport::renderIcon((string)($item['icon'] ?? ''), 'mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary', 'text-2xl');
            RenderSupport::renderTitleText($item, 'h3', 'mb-2 text-xl font-bold text-secondary', 'text-gray-600');
            echo '            </a>' . PHP_EOL;
        }
        echo '        </div>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;

        return true;
    }

    private static function renderStepList(array $section): bool
    {
        $items = RenderSupport::blocks($section);
        if ($items === []) {
            return false;
        }

        echo RenderSupport::sectionOpen($section, 'py-16') . PHP_EOL;
        echo '    <div class="container mx-auto px-4">' . PHP_EOL;
        RenderSupport::renderHeading($section, 'text-center mb-16', true);
        $gridClass = count($items) === 3
            ? 'grid grid-cols-1 md:grid-cols-3 gap-4'
            : 'grid grid-cols-1 md:grid-cols-5 gap-4';
        echo '        <div class="' . RenderSupport::h($gridClass) . '">' . PHP_EOL;
        foreach ($items as $index => $item) {
            $value = RenderSupport::text($item['value'] ?? '') ?: (string)($index + 1);
            echo '            <div class="step-item text-center px-4">' . PHP_EOL;
            echo '                <div class="step-number w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4 text-xl font-bold text-secondary">' . RenderSupport::h($value) . '</div>' . PHP_EOL;
            RenderSupport::renderTitleText($item, 'h3', 'text-lg font-bold text-secondary mb-2', 'text-gray-600');
            echo '            </div>' . PHP_EOL;
        }
        echo '        </div>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;

        return true;
    }

    private static function renderTechGrid(array $section): bool
    {
        $items = RenderSupport::blocks($section);
        if ($items === []) {
            return false;
        }

        echo RenderSupport::sectionOpen($section, 'py-16') . PHP_EOL;
        echo '    <div class="container mx-auto px-4">' . PHP_EOL;
        RenderSupport::renderHeading($section, 'text-center mb-16', true);
        echo '        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">' . PHP_EOL;
        foreach ($items as $item) {
            echo '            <div class="bg-white rounded-xl p-6 shadow-sm text-center">' . PHP_EOL;
            RenderSupport::renderIcon((string)($item['icon'] ?? ''), 'w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4', 'text-3xl text-primary');
            RenderSupport::renderTitleText($item, 'h3', 'text-lg font-bold text-secondary mb-2', 'text-gray-600 text-sm');
            echo '            </div>' . PHP_EOL;
        }
        echo '        </div>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;

        return true;
    }

    private static function renderFeatureGrid(array $section): bool
    {
        $items = RenderSupport::blocks($section);
        if ($items === []) {
            return false;
        }

        $count = count($items);
        $countClass = in_array($count, [3, 4, 5], true)
            ? ' tacticum-feature-grid--count-' . $count
            : '';

        echo RenderSupport::sectionOpen($section, 'py-16 bg-gray-50') . PHP_EOL;
        echo '    <div class="container mx-auto px-4">' . PHP_EOL;
        RenderSupport::renderHeading($section, 'mx-auto mb-10 max-w-3xl text-center', true);
        echo '        <div class="tacticum-feature-grid' . $countClass . '">' . PHP_EOL;
        foreach ($items as $item) {
            echo '            <div class="tacticum-feature-card">' . PHP_EOL;
            RenderSupport::renderIcon((string)($item['icon'] ?? ''), 'tacticum-feature-card__icon', 'text-2xl');
            RenderSupport::renderTitleText($item, 'h3', 'tacticum-feature-card__title', 'tacticum-feature-card__text');
            echo '            </div>' . PHP_EOL;
        }
        echo '        </div>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;

        return true;
    }

    private static function renderContactGrid(array $section, array $context = []): bool
    {
        $items = RenderSupport::blocks($section);
        if ($items === []) {
            return false;
        }

        echo RenderSupport::sectionOpen($section, 'py-16') . PHP_EOL;
        echo '    <div class="container mx-auto px-4">' . PHP_EOL;
        echo '        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">' . PHP_EOL;
        foreach ($items as $item) {
            echo '            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full">' . PHP_EOL;
            RenderSupport::renderIcon((string)($item['icon'] ?? ''), 'w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6', 'text-3xl text-primary');
            if (str_contains(RenderSupport::text($item['meta'] ?? ''), 'source=contact_config')) {
                RenderSupport::renderOfficeCardBody($item, $context);
                echo '            </div>' . PHP_EOL;
                continue;
            }
            RenderSupport::renderTitleText($item, 'h3', 'text-xl font-bold text-secondary mb-3', 'text-gray-600 mb-6');
            $href = RenderSupport::href($item['href'] ?? '');
            $label = RenderSupport::text($item['label'] ?? '');
            if ($href !== '' && $label !== '') {
                echo '                <div class="mt-auto">' . PHP_EOL;
                echo '                    <a href="' . RenderSupport::h($href) . '" class="text-lg font-medium text-primary hover:text-primary/80 transition-colors flex items-center gap-2">' . RenderSupport::h($label) . '<i class="ri-external-link-line"></i></a>' . PHP_EOL;
                echo '                </div>' . PHP_EOL;
            }
            echo '            </div>' . PHP_EOL;
        }
        echo '        </div>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;

        return true;
    }

    private static function renderCtaBand(array $section): bool
    {
        $title = RenderSupport::text($section['title'] ?? '');
        $text = RenderSupport::text($section['text'] ?? '');
        $ctaText = RenderSupport::text($section['cta_text'] ?? '');
        $ctaHref = RenderSupport::href($section['cta_href'] ?? '');
        if ($title === '' && $text === '') {
            return false;
        }

        echo RenderSupport::sectionOpen($section, 'py-16 bg-gray-50') . PHP_EOL;
        echo '    <div class="container mx-auto px-4">' . PHP_EOL;
        echo '        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 border border-primary/10 rounded-xl bg-white p-6 md:p-8 shadow-sm">' . PHP_EOL;
        echo '            <div>' . PHP_EOL;
        if ($title !== '') {
            echo '                <h2 class="text-2xl md:text-3xl font-bold text-secondary mb-3">' . RenderSupport::h($title) . '</h2>' . PHP_EOL;
        }
        if ($text !== '') {
            echo '                <p class="text-gray-600 max-w-3xl">' . RenderSupport::h($text) . '</p>' . PHP_EOL;
        }
        echo '            </div>' . PHP_EOL;
        if ($ctaText !== '' && $ctaHref !== '') {
            echo '            <a href="' . RenderSupport::h($ctaHref) . '" class="inline-flex items-center justify-center gap-2 rounded-button bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">' . PHP_EOL;
            echo '                <i class="ri-calculator-line"></i>' . PHP_EOL;
            echo '                ' . RenderSupport::h($ctaText) . PHP_EOL;
            echo '            </a>' . PHP_EOL;
        }
        echo '        </div>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;

        return true;
    }

}
