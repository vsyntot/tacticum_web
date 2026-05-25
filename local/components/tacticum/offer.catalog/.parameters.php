<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_ID' => ['PARENT' => 'BASE', 'NAME' => 'Offer iblock ID', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'PER_PAGE' => ['PARENT' => 'BASE', 'NAME' => 'Items per page', 'TYPE' => 'STRING', 'DEFAULT' => '24'],
    ],
];
