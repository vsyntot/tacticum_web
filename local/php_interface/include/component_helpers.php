<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!class_exists('TacticumComponentParams')) {
    final class TacticumComponentParams
    {
        public static function string(array $params, string $key, string $default = ''): string
        {
            $value = trim((string)($params[$key] ?? $default));

            return $value !== '' ? $value : $default;
        }

        public static function int(array $params, string $key, int $default = 0): int
        {
            return max(0, (int)($params[$key] ?? $default));
        }

        public static function list(array $params, string $key, array $default = []): array
        {
            $value = $params[$key] ?? $default;
            if (!is_array($value)) {
                $value = preg_split('/\r\n|\r|\n|,/', (string)$value) ?: [];
            }

            $items = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    continue;
                }
                $item = trim((string)$item);
                if ($item !== '') {
                    $items[] = $item;
                }
            }

            return array_values($items);
        }

        public static function yesNo(array $params, string $key, string $default = 'N'): string
        {
            return strtoupper(trim((string)($params[$key] ?? $default))) === 'Y' ? 'Y' : 'N';
        }

        public static function token(string $value): string
        {
            return preg_replace('/[^a-z0-9_.-]+/i', '', trim($value)) ?: '';
        }
    }
}
