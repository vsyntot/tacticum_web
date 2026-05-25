<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'VARIANT' => [
            'PARENT' => 'BASE',
            'NAME' => 'Variant',
            'TYPE' => 'LIST',
            'VALUES' => [
                'hero' => 'Hero',
                'light' => 'Light',
            ],
            'DEFAULT' => 'light',
        ],
        'SURFACE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Analytics surface',
            'TYPE' => 'STRING',
            'DEFAULT' => 'calculator',
        ],
        'TITLE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Header title',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'INTRO' => [
            'PARENT' => 'BASE',
            'NAME' => 'Intro message',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'INTRO_ITEMS' => [
            'PARENT' => 'BASE',
            'NAME' => 'Hero intro bullets',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'Y',
            'DEFAULT' => '',
        ],
        'INTRO_OUTRO' => [
            'PARENT' => 'BASE',
            'NAME' => 'Hero intro closing text',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'INITIAL_USER_MESSAGE' => [
            'PARENT' => 'BASE',
            'NAME' => 'Hero initial user message',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'PLACEHOLDER' => [
            'PARENT' => 'BASE',
            'NAME' => 'Input placeholder',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
        'QUICK_REPLIES' => [
            'PARENT' => 'BASE',
            'NAME' => 'Quick replies',
            'TYPE' => 'STRING',
            'MULTIPLE' => 'Y',
            'DEFAULT' => '',
        ],
        'ROOT_CLASS' => [
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => 'Root CSS class',
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ],
    ],
];
