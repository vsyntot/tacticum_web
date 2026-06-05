<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'PAGE_DATA' => [
            'PARENT' => 'BASE',
            'NAME' => 'Prepared product page data',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'UNAVAILABLE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Use unavailable-state CTA copy',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
        ],
    ],
];
