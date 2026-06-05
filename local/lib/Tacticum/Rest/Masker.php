<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class Masker
{
    public static function email(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        [$user, $domain] = explode('@', $email, 2);
        $length = mb_strlen($user);
        $maskedUser = $length <= 2
            ? str_repeat('*', $length)
            : mb_substr($user, 0, 1) . str_repeat('*', $length - 2) . mb_substr($user, -1);

        return $maskedUser . '@' . $domain;
    }

    public static function phone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return $phone;
        }

        return str_repeat('*', max(0, strlen($digits) - 2)) . substr($digits, -2);
    }

    public static function freeText(string $value): string
    {
        $value = trim($value);
        return $value === '' ? '' : '[masked:' . mb_strlen($value) . ']';
    }

    public static function isSensitiveLogKey(string $key): bool
    {
        $key = strtolower($key);
        if (in_array($key, [
            'name',
            'client_name',
            'first_name',
            'last_name',
            'patronymic',
            'company',
            'message',
            'task',
            'description',
            'project',
            'summary',
            'response',
            'user_message',
            'comment',
            'comments',
        ], true)) {
            return true;
        }

        return (bool)preg_match('~(name|message|task|description|summary|comment|response|text)$~', $key);
    }

    public static function pii(array $payload): array
    {
        $masked = [];
        foreach ($payload as $key => $value) {
            $keyString = is_string($key) ? $key : (string)$key;
            if (is_array($value)) {
                $masked[$key] = self::pii($value);
                continue;
            }
            if ($keyString === 'email' && is_string($value)) {
                $masked[$key] = self::email($value);
                continue;
            }
            if ($keyString === 'phone' && is_string($value)) {
                $masked[$key] = self::phone($value);
                continue;
            }
            if (is_string($value)) {
                $masked[$key] = self::isSensitiveLogKey($keyString) ? self::freeText($value) : self::string($value);
                continue;
            }
            $masked[$key] = $value;
        }

        return $masked;
    }

    public static function string(string $value): string
    {
        $value = (string)preg_replace('/([A-Z0-9._%+-]+)@([A-Z0-9.-]+\.[A-Z]{2,})/i', '***@$2', $value);
        return (string)preg_replace('/\+?\d[\d\s().-]{6,}\d/', '***', $value);
    }
}
