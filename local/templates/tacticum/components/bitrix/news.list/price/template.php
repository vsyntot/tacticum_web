<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$priceTemplateFolder = (string)($templateFolder ?? '/local/templates/tacticum/components/bitrix/news.list/price');
foreach ([
    'price-configurator-utils.js',
    'price-configurator-fallback.js',
    'price-configurator-catalog.js',
    'price-configurator-filters.js',
    'price-configurator-order-state.js',
    'price-configurator-order-render.js',
    'price-configurator-modal.js',
] as $priceScript) {
    $this->addExternalJs($priceTemplateFolder . '/' . $priceScript);
}

$icons = [
    'Аналитика' => 'ri-file-chart-line',
    'Разработка' => 'ri-code-s-slash-line',
    'DevOps/Инфраструктура' => 'ri-server-line',
    'Тестирование и качество' => 'ri-bug-line',
    'Прочие специалисты' => 'ri-user-line',
];

include __DIR__ . '/parts/catalog.php';
include __DIR__ . '/parts/order-modal.php';
