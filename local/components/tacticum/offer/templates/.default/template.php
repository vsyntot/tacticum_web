<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$mode = (string)($arResult['MODE'] ?? 'list');
$templateDir = __DIR__;

if ($mode === 'detail') {
    include $templateDir . '/detail.php';
} elseif ($mode === 'not_found') {
    include $templateDir . '/not-found.php';
} else {
    include $templateDir . '/list.php';
}
