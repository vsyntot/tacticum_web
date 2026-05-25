<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'MODE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Render mode',
            'TYPE' => 'LIST',
            'VALUES' => ['list' => 'List', 'detail' => 'Detail', 'not_found' => 'Not found'],
            'DEFAULT' => 'list',
        ],
        'IBLOCK_ID' => ['PARENT' => 'BASE', 'NAME' => 'Offer iblock ID', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'PER_PAGE' => ['PARENT' => 'BASE', 'NAME' => 'Items per page', 'TYPE' => 'STRING', 'DEFAULT' => '24'],
    ],
];
