<?php

declare(strict_types=1);

define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
$APPLICATION->SetTitle('Privacy policy — DIVA Technologies');
$APPLICATION->SetPageProperty('description', 'Privacy notice for DIVA Technologies contact requests.');
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_after.php';
?>
<main class="diva-container diva-legal" aria-labelledby="diva-privacy-title">
    <h1 id="diva-privacy-title">Privacy policy</h1>
    <p>DIVA Technologies respects your privacy. This notice explains how we use information submitted through the contact and callback forms on this website.</p>
    <h2>Information we collect</h2>
    <p>Depending on the form, we collect your name, email address, phone number, request text, consent record and the date of your request.</p>
    <h2>Why we use it</h2>
    <p>We use this information to respond to your request, arrange a callback and keep a private record of the conversation. We do not publish submitted requests or include their contents in notification emails.</p>
    <h2>How long we keep it</h2>
    <p>We keep a request for up to 12 months after submission and then remove it, unless a longer period is required to establish, exercise or defend a legal claim.</p>
    <h2>Contact</h2>
    <p>For questions about this notice or your request, email <a href="mailto:info@divatech.space">info@divatech.space</a> or call <a href="tel:+905066212712">+90 506 621 27 12</a>.</p>
    <p>Last updated: 16 October 2026.</p>
</main>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
