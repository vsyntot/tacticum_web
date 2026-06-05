<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'PRODUCT_CODE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Product code',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'CANONICAL_PATH' => [
            'PARENT' => 'BASE',
            'NAME' => 'Canonical path',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'APPLICATION_CATEGORY' => [
            'PARENT' => 'BASE',
            'NAME' => 'SoftwareApplication category',
            'TYPE' => 'STRING',
            'DEFAULT' => 'BusinessApplication',
        ],
        'SCHEMA_DESCRIPTION' => [
            'PARENT' => 'BASE',
            'NAME' => 'Schema description',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'APPLY_SEO_DEFAULTS' => [
            'PARENT' => 'BASE',
            'NAME' => 'Apply SEO defaults',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
        'PREPARE_ONLY' => [
            'PARENT' => 'BASE',
            'NAME' => 'Prepare data and SEO without rendering',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
        ],
    ],
];
