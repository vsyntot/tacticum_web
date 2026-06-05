<?php

declare(strict_types=1);

namespace Tacticum\CalcRequests;

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;

final class Access
{
    public static function check(array $params, int $iblockId = 0): ?array
    {
        $config = Runtime::config();
        $request = Context::getCurrent()->getRequest();
        $ip = (string)$request->getRemoteAddress();

        $allowedIps = $config['allowed_ips'] ?? [];
        if (is_string($allowedIps)) {
            $allowedIps = array_filter(array_map('trim', explode(',', $allowedIps)));
        }
        if (is_array($allowedIps) && $allowedIps !== [] && in_array($ip, $allowedIps, true)) {
            return null;
        }

        $apiKey = (string)($config['api_key'] ?? '');
        $providedKey = (string)($params['api_key'] ?? $request->getHeader('X-Api-Key'));
        if ($apiKey !== '' && $providedKey !== '' && hash_equals($apiKey, $providedKey)) {
            return null;
        }

        return self::userHasAccess($iblockId)
            ? null
            : Response::error('access_denied', 'Недостаточно прав для выполнения запроса.');
    }

    public static function userHasAccess(int $iblockId): bool
    {
        global $USER;

        if (isset($USER) && is_object($USER) && method_exists($USER, 'IsAuthorized') && $USER->IsAuthorized()) {
            if (method_exists($USER, 'IsAdmin') && $USER->IsAdmin()) {
                return true;
            }

            $userId = method_exists($USER, 'GetID') ? (int)$USER->GetID() : 0;
            return self::hasWritePermission($iblockId, $userId);
        }

        $user = CurrentUser::get();
        $userId = method_exists($user, 'getId') ? (int)$user->getId() : 0;
        if ($userId <= 0) {
            return false;
        }

        return method_exists($user, 'isAdmin') && $user->isAdmin()
            ? true
            : self::hasWritePermission($iblockId, $userId);
    }

    private static function hasWritePermission(int $iblockId, int $userId): bool
    {
        return $iblockId > 0
            && $userId > 0
            && Loader::includeModule('iblock')
            && \CIBlock::GetPermission($iblockId, $userId) >= 'W';
    }
}
