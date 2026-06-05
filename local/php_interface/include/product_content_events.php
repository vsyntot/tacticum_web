<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_content_events_include_runtime')) {
    function tacticum_product_content_events_include_runtime(): void
    {
        $runtimePath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/product_content.php';
        if (is_file($runtimePath)) {
            require_once $runtimePath;
        }
    }
}

if (!function_exists('tacticum_register_product_content_cache_handlers')) {
    function tacticum_register_product_content_cache_handlers(): void
    {
        static $registered = false;
        if ($registered || !class_exists('Bitrix\Main\EventManager')) {
            return;
        }
        $registered = true;

        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', static function (array &$fields): void {
            tacticum_product_content_events_include_runtime();
            if (function_exists('tacticum_product_content_clear_cache_on_iblock_change')) {
                tacticum_product_content_clear_cache_on_iblock_change($fields);
            }
        });
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', static function (array &$fields): void {
            tacticum_product_content_events_include_runtime();
            if (function_exists('tacticum_product_content_clear_cache_on_iblock_change')) {
                tacticum_product_content_clear_cache_on_iblock_change($fields);
            }
        });
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', static function (array $fields): void {
            tacticum_product_content_events_include_runtime();
            if (function_exists('tacticum_product_content_clear_cache_on_element_delete')) {
                tacticum_product_content_clear_cache_on_element_delete($fields);
            }
        });
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValues', static function (mixed $elementId, mixed $iblockId = 0): void {
            tacticum_product_content_events_include_runtime();
            if (function_exists('tacticum_product_content_clear_cache_on_property_change')) {
                tacticum_product_content_clear_cache_on_property_change($elementId, $iblockId);
            }
        });
        $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementSetPropertyValuesEx', static function (mixed $elementId, mixed $iblockId = 0): void {
            tacticum_product_content_events_include_runtime();
            if (function_exists('tacticum_product_content_clear_cache_on_property_change')) {
                tacticum_product_content_clear_cache_on_property_change($elementId, $iblockId);
            }
        });
    }
}
