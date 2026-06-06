<?php

use Tacticum\PageContent\Renderer;
use Tacticum\PageContent\Repository;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

function tacticum_page_content_config(): array
{
    $config = function_exists('tacticum_rest_get_config_section')
        ? tacticum_rest_get_config_section('page_content')
        : [];

    return is_array($config) ? $config : [];
}

function tacticum_page_content_source(): string
{
    $config = tacticum_page_content_config();
    $source = strtolower(trim((string)($config['source'] ?? 'fallback')));

    return in_array($source, ['fallback', 'bitrix'], true) ? $source : 'fallback';
}

function tacticum_page_content_live_status(): string
{
    $config = tacticum_page_content_config();
    $status = trim((string)($config['live_status'] ?? 'live'));

    return $status !== '' ? $status : 'live';
}

function tacticum_page_content_render_if_live(string $pageKey, string $sectionKey, array $context = []): bool
{
    if (tacticum_page_content_source() !== 'bitrix') {
        return false;
    }

    $section = Repository::fetchSection($pageKey, $sectionKey, tacticum_page_content_live_status());
    if (!is_array($section)) {
        return false;
    }

    return Renderer::render($section, $context);
}
