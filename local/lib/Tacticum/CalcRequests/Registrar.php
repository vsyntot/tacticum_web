<?php

declare(strict_types=1);

namespace Tacticum\CalcRequests;

use Bitrix\Main\EventManager;

final class Registrar
{
    public static function register(): void
    {
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

        EventManager::getInstance()->addEventHandler('rest', 'OnRestServiceBuildDescription', static function (): array {
            return [
                'calcrequests_api' => [
                    'calcrequests_list' => [
                        'callback' => static fn($params): array => Service::list((array)$params),
                    ],
                    'calcrequests_add' => [
                        'callback' => static fn($params): array => Service::add((array)$params),
                    ],
                ],
            ];
        });
    }
}
