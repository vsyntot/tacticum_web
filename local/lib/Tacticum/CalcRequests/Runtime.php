<?php

declare(strict_types=1);

namespace Tacticum\CalcRequests;

use Bitrix\Main\Config\Configuration;

final class Runtime
{
    public static function config(): array
    {
        $config = Configuration::getValue('tacticum_calcrequests');
        if (!is_array($config)) {
            $config = [];
        }

        $fallback = Configuration::getValue('tacticum_rest');
        return is_array($fallback) ? array_merge($fallback, $config) : $config;
    }

    public static function offerIblockId(): int
    {
        if (function_exists('tacticum_rest_get_iblock_id')) {
            return (int)tacticum_rest_get_iblock_id('offer');
        }

        return 0;
    }
}
