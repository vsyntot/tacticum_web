<?php

namespace Tacticum\Product\Page;

final class DataProvider
{
    public static function data(string $productCode): array
    {
        $source = function_exists('tacticum_product_content_source')
            ? tacticum_product_content_source()
            : 'fallback';

        if ($source !== 'fallback' && function_exists('tacticum_product_content_bitrix_data')) {
            $bitrixData = tacticum_product_content_bitrix_data($productCode);
            if (!empty($bitrixData)) {
                $isRenderable = function_exists('tacticum_product_content_is_minimum_renderable')
                    ? tacticum_product_content_is_minimum_renderable($bitrixData)
                    : true;

                if ($isRenderable) {
                    return $bitrixData;
                }

                if ($source === 'bitrix') {
                    return self::unavailableData($productCode, $bitrixData);
                }
            }

            if ($source === 'bitrix') {
                return self::unavailableData($productCode, $bitrixData);
            }
        }

        if ($source === 'bitrix') {
            return self::unavailableData($productCode, []);
        }

        return self::fallbackData($productCode);
    }

    public static function unavailableData(string $productCode, array $bitrixData = []): array
    {
        if (!headers_sent()) {
            http_response_code(503);
        }

        $diagnostics = [];
        if (function_exists('tacticum_product_content_completeness_diagnostics')) {
            $diagnostics = tacticum_product_content_completeness_diagnostics($bitrixData);
        }

        return [
            'eyebrow' => 'Tacticum product',
            'title' => 'Материалы продукта обновляются',
            'lead' => 'Страница временно недоступна: продуктовый контент проверяется в Bitrix. Оставьте заявку, и команда вернется с актуальным описанием сценария.',
            'primary_cta_text' => 'Связаться с командой',
            'secondary_cta_text' => 'Все услуги',
            'secondary_cta_href' => '/services/',
            'badges' => [],
            'hero_cards' => [],
            'sections' => [],
            'cta' => [
                'form_id' => 'product-unavailable',
                'field_prefix' => 'product',
                'title' => 'Уточнить продуктовый сценарий',
                'text' => 'Напишите, какой продукт или сценарий вам нужен. Мы ответим без публикации неподтвержденных материалов на сайте.',
                'form_title' => 'Заявка на уточнение',
                'button_text' => 'Отправить запрос',
                'scenario_label' => 'Сценарий',
                'scenario_empty_label' => 'Выберите сценарий',
                'scenario_options' => [
                    ['VALUE' => 'architecture-session', 'LABEL' => 'Архитектурная сессия'],
                    ['VALUE' => 'pilot', 'LABEL' => 'Пилот'],
                    ['VALUE' => 'estimate', 'LABEL' => 'Оценка внедрения'],
                ],
                'lead_context' => [
                    'lead_entry' => 'product-unavailable',
                    'lead_page_role' => 'product-page',
                    'lead_product' => $productCode,
                    'lead_intent' => 'content-unavailable',
                    'lead_cta' => 'contact-team',
                    'lead_next_step' => 'manual-follow-up',
                ],
            ],
            '_source' => 'bitrix',
            '_status' => 'unavailable',
            '_product_code' => $productCode,
            '_diagnostics' => $diagnostics,
        ];
    }

    public static function fallbackData(string $productCode): array
    {
        $productFiles = function_exists('tacticum_product_content_codes')
            ? tacticum_product_content_codes()
            : [
                'platform' => 'platform.php',
                'agents' => 'agents.php',
                'dev' => 'dev.php',
                'forum' => 'forum.php',
            ];

        if (!isset($productFiles[$productCode])) {
            return [];
        }

        $path = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/product_data/' . $productFiles[$productCode];
        if (!is_file($path)) {
            return [];
        }

        $data = require $path;
        if (!is_array($data)) {
            return [];
        }

        $data['_source'] = 'fallback';
        $data['_product_code'] = $productCode;

        return $data;
    }
}
