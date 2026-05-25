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
        'DETAIL_TEMPLATE' => [
            'PARENT' => 'BASE',
            'NAME' => 'bitrix:news.detail template',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'ELEMENT_CODE' => [
            'PARENT' => 'DATA_SOURCE',
            'NAME' => 'Element code',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'ELEMENT_ID' => [
            'PARENT' => 'DATA_SOURCE',
            'NAME' => 'Element ID fallback',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'IBLOCK_TYPE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Iblock type',
            'TYPE' => 'STRING',
            'DEFAULT' => 'company',
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
    ],
];
