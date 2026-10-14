<?php

declare(strict_types=1);

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss('/local/templates/divatech/style.css');
Asset::getInstance()->addJs('/local/templates/divatech/script.js');
// Forms contain a session token. Never let composite/CDN caches share this page.
if (class_exists(\Bitrix\Main\Composite\Engine::class)) {
    \Bitrix\Main\Composite\Engine::setEnable(false);
}
header('Cache-Control: private, no-store');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php $APPLICATION->ShowTitle(); ?></title>
    <?php $APPLICATION->ShowHead(); ?>
    <?php if (!defined('ERROR_404')): ?>
    <link rel="canonical" href="https://divatech.space/">
    <meta property="og:title" content="DIVA Technologies">
    <meta property="og:description" content="Source your future. IT experts for your projects.">
    <meta property="og:url" content="https://divatech.space/">
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://divatech.space/local/templates/divatech/assets/hero.jpg">
    <?php endif; ?>
    <link rel="icon" href="/favicon.ico">
</head>
<body>
<?php $APPLICATION->ShowPanel(); ?>
<a class="diva-skip" href="#main">Skip to content</a>
<header class="diva-header">
    <a href="/" aria-label="DIVA Technologies home"><img src="/local/templates/divatech/assets/logo.svg" width="100" height="45" alt="DIVA Technologies"></a>
    <button class="diva-menu-toggle" type="button" aria-expanded="false" aria-controls="diva-nav">Menu</button>
    <nav id="diva-nav" aria-label="Main navigation">
        <a href="/#rec497650901">About us</a><a href="/#rec497655335">What do we offer</a><a href="/#rec497691984">What can we do</a><a href="/#rec497697185">Contact us</a>
    </nav>
</header>
