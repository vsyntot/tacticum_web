<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_KEY' => [
            'PARENT' => 'BASE',
            'NAME' => 'Iblock config key',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'Iblock ID fallback',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'NEWS_LIST_TEMPLATE' => [
            'PARENT' => 'BASE',
            'NAME' => 'bitrix:news.list template',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'IBLOCK_TYPE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Iblock type',
            'TYPE' => 'STRING',
            'DEFAULT' => 'company',
        ],
        'NEWS_COUNT' => [
            'PARENT' => 'BASE',
            'NAME' => 'Items count',
            'TYPE' => 'STRING',
            'DEFAULT' => '3',
        ],
        'FIELD_CODE' => [
            'PARENT' => 'DATA_SOURCE',
            'NAME' => 'Field codes',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'Y',
            'DEFAULT' => '',
        ],
        'PROPERTY_CODE' => [
            'PARENT' => 'DATA_SOURCE',
            'NAME' => 'Property codes',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'Y',
            'DEFAULT' => '',
        ],
        'SORT_BY1' => [
            'PARENT' => 'DATA_SOURCE',
            'NAME' => 'Primary sort field',
            'TYPE' => 'STRING',
            'DEFAULT' => 'SORT',
        ],
        'SORT_ORDER1' => [
            'PARENT' => 'DATA_SOURCE',
            'NAME' => 'Primary sort order',
            'TYPE' => 'LIST',
            'VALUES' => ['ASC' => 'ASC', 'DESC' => 'DESC', 'RAND' => 'RAND'],
            'DEFAULT' => 'ASC',
        ],
        'SORT_BY2' => [
            'PARENT' => 'DATA_SOURCE',
            'NAME' => 'Secondary sort field',
            'TYPE' => 'STRING',
            'DEFAULT' => 'ID',
        ],
        'SORT_ORDER2' => [
            'PARENT' => 'DATA_SOURCE',
            'NAME' => 'Secondary sort order',
            'TYPE' => 'LIST',
            'VALUES' => ['ASC' => 'ASC', 'DESC' => 'DESC'],
            'DEFAULT' => 'DESC',
        ],
        'DISPLAY_BOTTOM_PAGER' => [
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => 'Display bottom pager',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
    ],
];
