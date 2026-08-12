<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'widgetpanel.css',
	'js' => 'widgetpanel.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.loader',
		'im.v2.application.core',
		'im.v2.css.tokens',
		'intranet.ai-chat-panel',
		'ui.page-context',
	],
	'skip_core' => false,
];
