<?php

namespace Tacticum\Seo;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;

final class FaqSchema
{
    public static function build(int $limit = 20): ?array
    {
        if (!Loader::includeModule('iblock')) {
            return null;
        }

        $iblockId = tacticum_iblock_id('faq');
        if ($iblockId <= 0) {
            return null;
        }

        $cacheKey = 'tacticum_faq_json_ld_' . $limit . '_' . $iblockId;
        $cacheDir = '/tacticum/seo';
        $cache = Cache::createInstance();
        if ($cache->initCache(3600, $cacheKey, $cacheDir)) {
            $cached = $cache->getVars();
            return is_array($cached['schema'] ?? null) ? $cached['schema'] : null;
        }

        $entities = [];
        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
            ],
            false,
            ['nTopCount' => $limit],
            [
                'ID',
                'NAME',
                'DETAIL_TEXT',
            ]
        );

        while ($element = $result->Fetch()) {
            $question = JsonLd::text((string)($element['NAME'] ?? ''));
            $answer = JsonLd::text((string)($element['DETAIL_TEXT'] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }

            $entities[] = [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $answer,
                ],
            ];
        }

        $schema = !empty($entities)
            ? [
                '@type' => 'FAQPage',
                'mainEntity' => $entities,
            ]
            : null;

        if ($cache->startDataCache(3600, $cacheKey, $cacheDir)) {
            $cache->endDataCache(['schema' => $schema]);
        }

        return $schema;
    }
}
