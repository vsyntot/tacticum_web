<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (function_exists('tacticum_page_content_render_if_live') && tacticum_page_content_render_if_live('/price/', 'features')) {
    return;
}

// Fallback body retired after owner-approved page-content fallback retirement.
return;
