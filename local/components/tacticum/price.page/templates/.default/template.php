<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

foreach (['hero', 'features', 'workstreams', 'price-list', 'calculator', 'faq-cta'] as $part) {
    include __DIR__ . '/parts/' . $part . '.php';
}
