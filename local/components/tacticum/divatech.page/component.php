<?php

declare(strict_types=1);

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

$arResult = ['FORMS_ENABLED' => \Tacticum\Divatech\Settings::formsEnabled(), 'SETTINGS' => \Tacticum\Divatech\Settings::all()];
$this->IncludeComponentTemplate();
