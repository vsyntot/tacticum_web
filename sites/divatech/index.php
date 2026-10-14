<?php

declare(strict_types=1);

define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
$APPLICATION->SetTitle('DIVA Technologies');
$APPLICATION->SetPageProperty('description', 'IT experts for your projects. Development and implementation of IT products with DIVA Technologies.');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_after.php';
$APPLICATION->IncludeComponent('tacticum:divatech.page', '', [], false);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
