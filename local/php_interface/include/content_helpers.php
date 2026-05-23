<?php

if (!function_exists('tacticum_decode_iblock_text')) {
    function tacticum_decode_iblock_text(string $text, int $maxPasses = 5): string
    {
        $charset = defined('SITE_CHARSET') && SITE_CHARSET !== '' ? SITE_CHARSET : 'UTF-8';
        $text = str_replace(["\\r\\n", "\\n", "\\r", "\\t"], ["\n", "\n", "\n", "\t"], $text);

        for ($i = 0; $i < $maxPasses; $i++) {
            $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, $charset);
            if ($decoded === $text) {
                break;
            }
            $text = $decoded;
        }

        return str_replace(
            ["\xC2\xA0", "\xE2\x80\xAF", "\xE2\x80\x89"],
            ' ',
            $text
        );
    }
}

if (!function_exists('tacticum_escape_iblock_text')) {
    function tacticum_escape_iblock_text(string $text): string
    {
        return htmlspecialcharsbx(tacticum_decode_iblock_text($text));
    }
}

if (!function_exists('tacticum_sanitize_iblock_html')) {
    function tacticum_sanitize_iblock_html(string $html): string
    {
        $sanitizer = new \CBXSanitizer();
        $sanitizer->SetLevel(\CBXSanitizer::SECURE_LEVEL_MIDDLE);

        return $sanitizer->SanitizeHtml(tacticum_decode_iblock_text($html));
    }
}
