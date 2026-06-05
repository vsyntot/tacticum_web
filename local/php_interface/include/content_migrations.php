<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

function tacticum_content_migration_fix_policy_contacts(): void
{
    $optionName = 'content_migration_20260521_policy_contacts';
    if (Option::get('tacticum', $optionName, 'N') === 'Y') {
        return;
    }

    if (!Loader::includeModule('iblock')) {
        return;
    }

    $iblockId = function_exists('tacticum_rest_get_iblock_id')
        ? tacticum_rest_get_iblock_id('policies')
        : 0;
    if ($iblockId <= 0) {
        return;
    }

    $res = CIBlockElement::GetList(
        ['SORT' => 'ASC', 'ID' => 'ASC'],
        ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE']
    );
    $elementApi = new CIBlockElement();
    $seenElement = false;
    $updatedAny = false;
    $needsRetry = false;

    while ($element = $res->Fetch()) {
        $seenElement = true;
        $detailText = (string)($element['DETAIL_TEXT'] ?? '');
        $updatedText = preg_replace(
            [
                '~ОГРН:\s*\[указать\]~u',
                '~Адрес:\s*\[указать[^\]]*\]~u',
                '~info@tacticum\.ru~iu',
            ],
            [
                'ОГРН: 1227700525942',
                'Адрес: 119285, г. Москва, Вн.Тер.г. Муниципальный округ Раменки, Км Мжд Киевское 5-й, д. 1 стр. 1, помещ. 3/3',
                'project@tacticum.ru',
            ],
            $detailText
        );

        if (!is_string($updatedText)) {
            $needsRetry = true;
            continue;
        }

        if ($updatedText === $detailText) {
            if (preg_match('~\[указать|info@tacticum\.ru~iu', $detailText) === 1) {
                $needsRetry = true;
            }
            continue;
        }

        $updated = $elementApi->Update((int)$element['ID'], [
            'DETAIL_TEXT' => $updatedText,
            'DETAIL_TEXT_TYPE' => (string)($element['DETAIL_TEXT_TYPE'] ?? 'html') ?: 'html',
        ]);

        if (!$updated) {
            $needsRetry = true;
            continue;
        }

        $updatedAny = true;
    }

    if (!$seenElement || $needsRetry) {
        return;
    }

    if ($updatedAny && method_exists('CIBlock', 'clearIblockTagCache')) {
        CIBlock::clearIblockTagCache($iblockId);
    }

    Option::set('tacticum', $optionName, 'Y');
}

if (defined('TACTICUM_RUN_CONTENT_MIGRATIONS') && TACTICUM_RUN_CONTENT_MIGRATIONS === true) {
    tacticum_content_migration_fix_policy_contacts();
}
