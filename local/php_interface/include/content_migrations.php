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
        [],
        [
            'IBLOCK_ID' => $iblockId,
            'ID' => 515,
        ],
        false,
        ['nTopCount' => 1],
        ['ID', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE']
    );
    $element = $res->Fetch();
    if (!$element) {
        return;
    }

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
        AddMessage2Log(
            'Policy content migration failed: preg_replace returned non-string result.',
            'tacticum_content_migration_error'
        );
        return;
    }

    if ($updatedText === $detailText) {
        if (preg_match('~\[указать|info@tacticum\.ru~iu', $detailText) === 1) {
            AddMessage2Log(
                'Policy content migration did not match unresolved placeholders.',
                'tacticum_content_migration_error'
            );
            return;
        }

        Option::set('tacticum', $optionName, 'Y');
        return;
    }

    $elementApi = new CIBlockElement();
    $updated = $elementApi->Update((int)$element['ID'], [
        'DETAIL_TEXT' => $updatedText,
        'DETAIL_TEXT_TYPE' => (string)($element['DETAIL_TEXT_TYPE'] ?? 'html') ?: 'html',
    ]);

    if (!$updated) {
        AddMessage2Log(
            'Policy content migration failed: ' . (string)$elementApi->LAST_ERROR,
            'tacticum_content_migration_error'
        );
        return;
    }

    if (method_exists('CIBlock', 'clearIblockTagCache')) {
        CIBlock::clearIblockTagCache($iblockId);
    }

    Option::set('tacticum', $optionName, 'Y');
    AddMessage2Log(
        'Policy content migration applied: element_id=' . (int)$element['ID'],
        'tacticum_content_migration'
    );
}

tacticum_content_migration_fix_policy_contacts();
