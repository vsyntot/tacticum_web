<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

foreach (['company-trust', 'values-team', 'stack-cta', 'career-final'] as $part) {
    include __DIR__ . '/parts/' . $part . '.php';
}
