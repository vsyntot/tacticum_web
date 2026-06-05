<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

global $APPLICATION;

foreach ([
    'hero',
    'agents-bridge',
    'how-it-works',
    'demoagents-list',
    'services',
    'demo',
    'contact-form',
    'faq',
] as $aiagentsPart) {
    require __DIR__ . '/parts/' . $aiagentsPart . '.php';
}
