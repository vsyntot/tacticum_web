<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'PARAMETERS' => [
        'TYPE' => [
            'PARENT' => 'BASE',
            'NAME' => 'CTA type',
            'TYPE' => 'LIST',
            'VALUES' => [
                'personal-offer' => 'Personal offer',
                'project-discussion' => 'Project discussion',
            ],
            'DEFAULT' => 'personal-offer',
        ],
        'VARIANT' => [
            'PARENT' => 'BASE',
            'NAME' => 'Visual variant',
            'TYPE' => 'LIST',
            'VALUES' => [
                'solid' => 'Solid',
                'glass' => 'Glass',
            ],
            'DEFAULT' => '',
        ],
        'FORM_ID' => ['PARENT' => 'BASE', 'NAME' => 'Form analytics ID', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'FORM_HTML_ID' => ['PARENT' => 'BASE', 'NAME' => 'Form HTML ID', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'FIELD_PREFIX' => ['PARENT' => 'BASE', 'NAME' => 'Field ID prefix', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'SECTION_ID' => ['PARENT' => 'BASE', 'NAME' => 'Section HTML ID', 'TYPE' => 'STRING', 'DEFAULT' => 'contact-form'],
        'TITLE' => ['PARENT' => 'BASE', 'NAME' => 'Title', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'TEXT' => ['PARENT' => 'BASE', 'NAME' => 'Text', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'BUTTON_TEXT' => ['PARENT' => 'BASE', 'NAME' => 'Button text', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'LEAD_CONTEXT' => ['PARENT' => 'ADDITIONAL_SETTINGS', 'NAME' => 'Hidden lead context', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'SCENARIO_LABEL' => ['PARENT' => 'ADDITIONAL_SETTINGS', 'NAME' => 'Scenario select label', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'SCENARIO_EMPTY_LABEL' => ['PARENT' => 'ADDITIONAL_SETTINGS', 'NAME' => 'Scenario empty label', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'SCENARIO_OPTIONS' => ['PARENT' => 'ADDITIONAL_SETTINGS', 'NAME' => 'Scenario options value|label', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'SHOW_QUALIFICATION' => [
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => 'Show optional qualification fields',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ],
        'IMAGE_SRC' => ['PARENT' => 'ADDITIONAL_SETTINGS', 'NAME' => 'Image src', 'TYPE' => 'STRING', 'DEFAULT' => ''],
        'IMAGE_ALT' => ['PARENT' => 'ADDITIONAL_SETTINGS', 'NAME' => 'Image alt', 'TYPE' => 'STRING', 'DEFAULT' => ''],
    ],
];
