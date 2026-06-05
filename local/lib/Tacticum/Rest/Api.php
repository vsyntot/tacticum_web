<?php

declare(strict_types=1);

namespace Tacticum\Rest;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Tacticum\Content\IblockRepository;

final class Api
{
    public static function normalizeProperty(array $property): array
    {
        $propertyType = (string)($property['PROPERTY_TYPE'] ?? '');
        $userType = (string)($property['USER_TYPE'] ?? '');
        $type = $userType === '' ? $propertyType : ($propertyType === '' ? $userType : $propertyType . ':' . $userType);
        $multiple = ($property['MULTIPLE'] ?? 'N') === 'Y';
        $value = $property['VALUE'] ?? null;

        if ($multiple) {
            $values = [];
            if (is_array($value)) {
                $values = array_values($value);
            } elseif ($value !== null && $value !== '') {
                $values = [$value];
            }

            return ['type' => $type, 'multiple' => true, 'values' => $values];
        }

        return ['type' => $type, 'multiple' => false, 'value' => $value];
    }

    public static function bootstrap(string $action): int
    {
        Security::validateOrigin();
        RateLimiter::hit($action);
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            Response::error(405, 'method_not_allowed', 'Метод запроса не поддерживается.');
        }
        if (!Loader::includeModule('iblock')) {
            Response::error(500, 'iblock_missing', 'Модуль инфоблоков не установлен.');
        }

        $iblockId = Config::iblockId($action);
        if ($iblockId <= 0) {
            Response::error(500, 'iblock_not_configured', 'Инфоблок не настроен.');
        }

        return $iblockId;
    }

    public static function cacheTtl(string $action, int $default = 300): int
    {
        $api = Config::section('api');
        $ttl = $api['cache_ttl_default'] ?? $default;
        if (isset($api['cache_ttl']) && is_array($api['cache_ttl']) && array_key_exists($action, $api['cache_ttl'])) {
            $ttl = $api['cache_ttl'][$action];
        }

        $ttl = (int)$ttl;
        return $ttl < 0 ? 0 : $ttl;
    }

    public static function cachedPayload(string $action, int $iblockId, callable $builder, array $cacheContext = []): array
    {
        $ttl = self::cacheTtl($action);
        if ($ttl <= 0) {
            $payload = $builder();
            return is_array($payload) ? $payload : [];
        }

        $cache = Cache::createInstance();
        $cacheKey = 'tacticum_api_' . $action . '_' . md5($iblockId . '|' . serialize($cacheContext));
        $cacheDir = '/tacticum/api';
        if ($cache->initCache($ttl, $cacheKey, $cacheDir)) {
            $vars = $cache->getVars();
            if (is_array($vars) && isset($vars['payload']) && is_array($vars['payload'])) {
                return $vars['payload'];
            }
        }

        $payload = $builder();
        if (!is_array($payload)) {
            $payload = [];
        }
        if ($cache->startDataCache($ttl, $cacheKey, $cacheDir)) {
            $cache->endDataCache(['payload' => $payload]);
        }

        return $payload;
    }

    public static function fetchElements(int $iblockId, array $select, array $filter = [], array $order = ['SORT' => 'ASC'])
    {
        return IblockRepository::activeElementResult($iblockId, $select, $filter, $order);
    }

    public static function fetchContentItems(
        int $iblockId,
        array $select,
        array $filter = [],
        array $order = ['SORT' => 'ASC']
    ): array {
        return IblockRepository::activeElementsWithPropertiesAndSections($iblockId, $select, $filter, $order);
    }
}
