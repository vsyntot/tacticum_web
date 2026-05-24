<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!function_exists('tacticum_offer_sitemap_escape')) {
    function tacticum_offer_sitemap_escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('tacticum_offer_sitemap_lastmod')) {
    function tacticum_offer_sitemap_lastmod(array $element): string
    {
        $raw = trim((string)(($element['TIMESTAMP_X'] ?? '') ?: ($element['DATE_CREATE'] ?? '') ?: ''));
        if ($raw === '' || !function_exists('MakeTimeStamp')) {
            return '';
        }

        $timestamp = (int)MakeTimeStamp($raw);
        if ($timestamp <= 0) {
            return '';
        }

        return date('c', $timestamp);
    }
}

header('Content-Type: application/xml; charset=UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

if (\Bitrix\Main\Loader::includeModule('iblock')) {
    $iblockId = tacticum_iblock_id('offer');
    if ($iblockId > 0) {
        $result = CIBlockElement::GetList(
            ['TIMESTAMP_X' => 'DESC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
                '!CODE' => false,
            ],
            false,
            ['nTopCount' => 50000],
            [
                'ID',
                'CODE',
                'DATE_CREATE',
                'TIMESTAMP_X',
            ]
        );

        $seenUrls = [];
        while ($element = $result->Fetch()) {
            $code = trim((string)($element['CODE'] ?? ''));
            if ($code === '' || !preg_match('/^[A-Za-z0-9_-]{1,120}$/', $code)) {
                continue;
            }

            $url = tacticum_public_url(tacticum_offer_detail_path($code));
            if (isset($seenUrls[$url])) {
                continue;
            }
            $seenUrls[$url] = true;

            $lastmod = tacticum_offer_sitemap_lastmod($element);

            echo "  <url>\n";
            echo '    <loc>' . tacticum_offer_sitemap_escape($url) . "</loc>\n";
            if ($lastmod !== '') {
                echo '    <lastmod>' . tacticum_offer_sitemap_escape($lastmod) . "</lastmod>\n";
            }
            echo "  </url>\n";
        }
    }
}

echo "</urlset>\n";
