<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

foreach (['hero-entry', 'delivery-layer', 'services-list', 'process', 'cases-list', 'tech', 'cta-faq'] as $part) {
    include __DIR__ . '/parts/' . $part . '.php';
}
