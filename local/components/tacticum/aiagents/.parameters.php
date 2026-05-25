<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_ID' => ['PARENT' => 'BASE', 'NAME' => 'AI agents iblock ID', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'FAQ_IBLOCK_ID' => ['PARENT' => 'BASE', 'NAME' => 'FAQ iblock ID', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'FAQ_SECTION_KEY' => ['PARENT' => 'BASE', 'NAME' => 'FAQ section key', 'TYPE' => 'STRING', 'DEFAULT' => 'aiagents'],
        'FAQ_PARENT_SECTION' => ['PARENT' => 'BASE', 'NAME' => 'FAQ parent section fallback', 'TYPE' => 'STRING', 'DEFAULT' => ''],
    ],
];
