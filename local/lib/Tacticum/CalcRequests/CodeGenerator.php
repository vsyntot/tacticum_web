<?php

declare(strict_types=1);

namespace Tacticum\CalcRequests;

final class CodeGenerator
{
    public static function isUnique(string $code, int $iblockId): bool
    {
        $res = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, '=CODE' => $code],
            false,
            ['nTopCount' => 1],
            ['ID']
        );

        return !$res->Fetch();
    }

    public static function normalize(string $raw, int $iblockId, int $maxLength = 100): string
    {
        $code = \CUtil::translit($raw, 'ru', [
            'replace_space' => '-',
            'replace_other' => '-',
            'change_case' => 'L',
        ]);
        $code = rtrim(mb_substr(trim($code, '-') ?: 'resp-' . uniqid(), 0, $maxLength), '-');

        $unique = $code;
        $suffix = 1;
        while (!self::isUnique($unique, $iblockId)) {
            $suffixStr = '-' . $suffix;
            $base = mb_substr($code, 0, max(0, $maxLength - mb_strlen($suffixStr)));
            $unique = rtrim($base, '-') . $suffixStr;
            $suffix++;
            if ($suffix > 99) {
                $unique = rtrim($code, '-') . '-' . uniqid();
                break;
            }
        }

        return $unique;
    }

    public static function offerCode(array $params, int $iblockId, int $maxLength = 100): string
    {
        $slug = is_array($params['slug'] ?? null) ? $params['slug'] : [];
        $title = trim((string)($slug['title'] ?? ''));
        $fallbackSlug = trim((string)($slug['slug'] ?? ''));
        $source = $title !== '' ? $title : $fallbackSlug;
        if ($source === '') {
            $source = 'offer';
        }

        $suffix = '-' . date('Ymd-His');
        $base = \CUtil::translit($source, 'ru', [
            'replace_space' => '-',
            'replace_other' => '-',
            'change_case' => 'L',
        ]);
        $base = trim($base, '-') ?: 'offer';
        $base = rtrim(mb_substr($base, 0, max(20, $maxLength - mb_strlen($suffix))), '-');

        return self::normalize($base . $suffix, $iblockId, $maxLength);
    }
}
