<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class Response
{
    public static function sendNoindexHeader(): void
    {
        if (!headers_sent()) {
            header('X-Robots-Tag: noindex, nofollow', true);
        }
    }

    public static function send(bool $success, string $code, ?string $message, array $extra = [], int $status = 200): void
    {
        http_response_code($status);
        self::sendNoindexHeader();
        $payload = [
            'success' => $success,
            'code' => $code,
        ];

        if ($message !== null) {
            $payload['message'] = $message;
        }
        if (!empty($extra)) {
            $payload = array_merge($payload, $extra);
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(int $status, string $code, string $message, array $extra = []): void
    {
        self::send(false, $code, $message, $extra, $status);
    }

    public static function requireMethod(string $method): void
    {
        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== strtoupper($method)) {
            self::error(405, 'method_not_allowed', 'Метод запроса не поддерживается.');
        }
    }

    public static function readJsonBody(string $message = 'Некорректные данные формы.'): array
    {
        $data = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($data)) {
            self::error(400, 'invalid_json', $message);
        }

        return $data;
    }
}
