<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

foreach (['hero', 'routing', 'cards', 'cta', 'legal-map'] as $part) {
    include __DIR__ . '/parts/' . $part . '.php';
}
