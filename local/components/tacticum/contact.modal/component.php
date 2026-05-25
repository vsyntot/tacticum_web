<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$formId = trim((string)($arParams['FORM_ID'] ?? 'contact-modal'));
if ($formId === '') {
    $formId = 'contact-modal';
}

$arResult = [
    'FORM_ID' => preg_replace('/[^a-z0-9_-]+/i', '', $formId) ?: 'contact-modal',
];

$this->IncludeComponentTemplate();
