<?php

declare(strict_types=1);

namespace Tacticum\CalcRequests;

final class Service
{
    public static function list(array $params): array
    {
        $iblockId = Runtime::offerIblockId();
        if ($iblockId <= 0) {
            return Response::error('iblock_not_configured', 'Инфоблок заявок не настроен.');
        }

        $accessError = Access::check($params, $iblockId);
        return $accessError ?? Repository::list($iblockId);
    }

    public static function add(array $params): array
    {
        $iblockId = Runtime::offerIblockId();
        if ($iblockId <= 0) {
            return Response::error('iblock_not_configured', 'Инфоблок заявок не настроен.');
        }

        $accessError = Access::check($params, $iblockId);
        if ($accessError !== null) {
            return $accessError;
        }

        $validation = Validator::validate($params);
        if (!empty($validation['errors'])) {
            return Response::error(
                'validation_error',
                'Некорректные данные запроса.',
                ['fields' => $validation['errors']]
            );
        }

        return Repository::add($validation['data'], $iblockId);
    }
}
