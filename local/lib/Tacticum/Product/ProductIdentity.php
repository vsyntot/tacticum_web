<?php

namespace Tacticum\Product;

final class ProductIdentity
{
    private const PRODUCTS = [
        'platform' => [
            'fallback' => 'platform.php',
            'path' => '/platform/',
        ],
        'agents' => [
            'fallback' => 'agents.php',
            'path' => '/agents/',
        ],
        'adp' => [
            'fallback' => 'adp.php',
            'path' => '/adp/',
        ],
        'forum' => [
            'fallback' => 'forum.php',
            'path' => '/forum/',
        ],
    ];

    private const LEGACY_CODES = [
        'dev' => 'adp',
    ];

    public static function codes(): array
    {
        $codes = [];
        foreach (self::PRODUCTS as $code => $product) {
            $codes[$code] = $product['fallback'];
        }

        return $codes;
    }

    public static function canonicalCode(string $code): string
    {
        $normalized = strtolower(trim($code));

        return self::LEGACY_CODES[$normalized] ?? $normalized;
    }

    public static function isKnownCode(string $code): bool
    {
        return array_key_exists(self::canonicalCode($code), self::PRODUCTS);
    }

    public static function legacyCodes(string $canonicalCode): array
    {
        $canonicalCode = self::canonicalCode($canonicalCode);
        $legacyCodes = [];

        foreach (self::LEGACY_CODES as $legacyCode => $targetCode) {
            if ($targetCode === $canonicalCode) {
                $legacyCodes[] = $legacyCode;
            }
        }

        return $legacyCodes;
    }

    public static function fallbackFile(string $code): string
    {
        $canonicalCode = self::canonicalCode($code);

        return self::PRODUCTS[$canonicalCode]['fallback'] ?? '';
    }

    public static function path(string $code): string
    {
        $canonicalCode = self::canonicalCode($code);

        return self::PRODUCTS[$canonicalCode]['path'] ?? '/';
    }
}
