<?php

namespace Tacticum\Offer\Page;

final class Response
{
    public static function applyRedirects(array $state): void
    {
        $redirect = $state['redirect'] ?? null;
        if (!is_array($redirect)) {
            return;
        }

        $url = (string)($redirect['url'] ?? '');
        if ($url === '') {
            return;
        }

        LocalRedirect($url, true, (string)($redirect['status'] ?? '302 Found'));
    }

    public static function applySeo(array $state): void
    {
        global $APPLICATION;

        $mode = (string)($state['mode'] ?? 'list');
        if ($mode === 'not_found') {
            \CHTTP::SetStatus('404 Not Found');
            @define('ERROR_404', 'Y');
            $APPLICATION->SetTitle('Предложение не найдено - Тактикум');
            $APPLICATION->SetPageProperty('description', 'Запрошенный пример расчета не найден или больше недоступен.');
            tacticum_add_robots_meta('noindex,nofollow');
            return;
        }

        if ($mode === 'detail') {
            self::applyDetailSeo($state);
            return;
        }

        self::applyListSeo($state);
    }

    public static function applyTemplate(array $state): void
    {
        global $APPLICATION;

        $APPLICATION->SetPageProperty('tacticum_page_assets', 'faq');
        $APPLICATION->SetPageProperty('tacticum_body_class', 'bg-gray-50');
    }

    public static function componentParams(array $state): array
    {
        return [
            'IBLOCK_ID' => tacticum_iblock_id('offer'),
            'MODE' => (string)($state['mode'] ?? 'list'),
            'FILTERS' => is_array($state['filters'] ?? null) ? $state['filters'] : [],
            'ELEMENT' => is_array($state['element'] ?? null) ? $state['element'] : [],
            'PER_PAGE' => (int)($state['per_page'] ?? 24),
        ];
    }

    private static function applyDetailSeo(array $state): void
    {
        global $APPLICATION;

        $element = is_array($state['element'] ?? null) ? $state['element'] : [];
        $APPLICATION->SetTitle((string)($element['SEO_TITLE'] ?? 'Пример расчета проекта - Тактикум'));
        $APPLICATION->SetPageProperty(
            'description',
            (string)($element['SEO_DESCRIPTION'] ?? 'Пример расчета AI-проекта Tacticum: состав работ, команда, сроки и бюджет.')
        );
        if (!empty($element['KEYWORDS']) && is_array($element['KEYWORDS'])) {
            $APPLICATION->SetPageProperty('keywords', implode(', ', $element['KEYWORDS']));
        }
        tacticum_apply_seo_defaults((string)($state['canonical_path'] ?? '/offer/'), ['type' => 'article']);
    }

    private static function applyListSeo(array $state): void
    {
        global $APPLICATION;

        $APPLICATION->SetTitle('Примеры расчетов AI- и IT-проектов по отраслям - Тактикум');
        $APPLICATION->SetPageProperty(
            'description',
            'Каталог примеров оценки AI- и IT-проектов по отраслям и сценариям: команда, сроки, стек, бюджет и переход к персональной смете.'
        );
        $seoOptions = [
            'type' => 'website',
            'schema' => [
                [
                    '@type' => 'CollectionPage',
                    'name' => 'Примеры расчетов AI- и IT-проектов по отраслям',
                    'description' => 'Каталог примеров оценки AI- и IT-проектов по отраслям, задачам, срокам, бюджету и команде.',
                    'url' => tacticum_public_url('/offer/'),
                ],
            ],
        ];
        $filters = is_array($state['filters'] ?? null) ? $state['filters'] : [];
        if (function_exists('tacticum_offer_catalog_has_filters') && tacticum_offer_catalog_has_filters($filters, true)) {
            $seoOptions['robots'] = 'noindex,follow';
        }
        tacticum_apply_seo_defaults('/offer/', $seoOptions);
    }
}
