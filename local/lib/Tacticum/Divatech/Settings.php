<?php

declare(strict_types=1);

namespace Tacticum\Divatech;

use Tacticum\Rest\Config;

final class Settings
{
    public static function all(): array
    {
        return Config::section('divatech');
    }

    public static function formsEnabled(): bool
    {
        $settings = self::all();
        $policy = (string)($settings['privacy_url'] ?? '');
        return ($settings['forms_enabled'] ?? false) === true
            && defined('SITE_ID') && SITE_ID === 's2'
            && Config::iblockId('divatech_leads') > 0
            && filter_var($settings['recipient'] ?? '', FILTER_VALIDATE_EMAIL) !== false
            && filter_var($settings['sender'] ?? '', FILTER_VALIDATE_EMAIL) !== false
            && str_starts_with($policy, 'https://divatech.space/')
            && trim((string)($settings['consent_text'] ?? '')) !== '';
    }
}
