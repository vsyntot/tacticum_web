<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class Text
{
    public static function htmlToText(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $html = str_replace(["\\r\\n", "\\n", "\\r", "\\t"], ["\n", "\n", "\n", "\t"], $html);
        $html = (string)preg_replace('~\R~u', "\n", $html);
        for ($i = 0; $i < 5; $i++) {
            $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $html) {
                break;
            }
            $html = $decoded;
        }

        $html = str_replace(["\xC2\xA0", "\xE2\x80\xAF", "\xE2\x80\x89"], ' ', $html);
        $html = (string)preg_replace('~<\s*br\s*/?\s*>~iu', "\n", $html);
        $html = (string)preg_replace('~<\s*(p|div|section|article|blockquote|h[1-6])\b[^>]*>~iu', "\n", $html);
        $html = (string)preg_replace('~</\s*(p|div|section|article|blockquote|h[1-6])\s*>~iu', "\n\n", $html);
        $html = (string)preg_replace('~<\s*li\b[^>]*>~iu', "• ", $html);
        $html = (string)preg_replace('~</\s*li\s*>~iu', "\n", $html);
        $html = (string)preg_replace('~</\s*(ul|ol)\s*>~iu', "\n", $html);

        $text = strip_tags($html);
        $text = str_replace("\t", ' ', $text);
        $text = (string)preg_replace('~[ ]{2,}~u', ' ', $text);
        $text = (string)preg_replace('~ *\n *~u', "\n", $text);
        $text = (string)preg_replace("~\n{3,}~u", "\n\n", $text);
        $text = (string)preg_replace('~\s+([.,;:!?])~u', '$1', $text);
        $text = (string)preg_replace('~\s*—\s*~u', ' — ', $text);
        $text = (string)preg_replace('~\s+\)~u', ')', $text);
        $text = (string)preg_replace('~\s+%~u', '%', $text);
        $text = (string)preg_replace('~[ ]{2,}~u', ' ', $text);
        $text = (string)preg_replace('~ *\n *~u', "\n", $text);

        return trim($text);
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return '';
        }

        return strpos($phone, '+') === 0 ? '+' . $digits : (string)$digits;
    }

    public static function isValidPhone(string $phone): bool
    {
        $normalized = self::normalizePhone($phone);
        return $normalized !== '' && (bool)preg_match('/^\+?\d{7,15}$/', $normalized);
    }
}
