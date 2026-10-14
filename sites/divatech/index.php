<?php

declare(strict_types=1);

define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('DIVA Technologies');
$APPLICATION->SetPageProperty('description', 'IT experts for your projects. Development and implementation of IT products with DIVA Technologies.');
$APPLICATION->IncludeComponent('tacticum:divatech.page', '', [], false);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
