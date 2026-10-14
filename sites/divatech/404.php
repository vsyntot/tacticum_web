<?php

declare(strict_types=1);

define('ERROR_404', 'Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
CHTTP::SetStatus('404 Not Found');
$APPLICATION->SetTitle('Page not found — DIVA Technologies');
$APPLICATION->SetPageProperty('robots', 'noindex, nofollow');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_after.php';
?>
<main id="main" class="diva-not-found"><h1>Page not found</h1><p><a href="/">Back to DIVA Technologies</a></p></main>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
