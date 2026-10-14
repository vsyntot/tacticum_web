<?php
declare(strict_types=1);
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { return; }
?>
<form data-divatech-form method="post" action="/local/rest/divatech_lead.php">
    <input type="hidden" name="sessid" value="<?= htmlspecialcharsbx(bitrix_sessid()) ?>">
    <input type="hidden" name="kind" value="<?= htmlspecialcharsbx($formKind) ?>">
    <?php if ($formKind === 'callback'): ?>
    <label>Your phone<input type="tel" name="phone" autocomplete="tel" maxlength="40" required></label>
    <?php else: ?>
    <label>Your email<input type="email" name="email" autocomplete="email" maxlength="254" required></label>
    <label>Your name<input type="text" name="name" autocomplete="name" maxlength="120" required></label>
    <label>Your request<textarea name="message" rows="4" maxlength="4000" required></textarea></label>
    <?php endif; ?>
    <div class="diva-honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
    <label class="diva-consent"><input type="checkbox" name="consent" required><span><?= htmlspecialcharsbx((string)$arResult['SETTINGS']['consent_text']) ?> <a href="<?= htmlspecialcharsbx((string)$arResult['SETTINGS']['privacy_url']) ?>">Privacy policy</a></span></label>
    <button type="submit">Send</button><p role="status" aria-live="polite" data-form-status></p>
    <noscript>Please email <a href="mailto:info@divatech.space">info@divatech.space</a> to send a request.</noscript>
</form>
