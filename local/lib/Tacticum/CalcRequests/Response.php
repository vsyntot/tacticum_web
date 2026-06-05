<?php

declare(strict_types=1);

namespace Tacticum\CalcRequests;

final class Response
{
    public static function error(string $code, string $message, array $extra = []): array
    {
        $payload = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        return $extra !== [] ? array_merge($payload, $extra) : $payload;
    }

    public static function success(array $extra = []): array
    {
        return array_merge(['success' => true], $extra);
    }
}
