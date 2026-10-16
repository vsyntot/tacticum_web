<?php
declare(strict_types=1);
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { return; }
?>
<?php $formId = 'diva-' . ($formKind === 'callback' ? 'callback' : 'contact') . '-form'; ?>
<form id="<?= htmlspecialcharsbx($formId) ?>" data-divatech-form method="post" action="/local/rest/divatech_lead.php" data-success-message="Thank you for your request! We will contact you soon." data-error-message="We could not confirm your request. Please try again or email info@divatech.space.">
    <input type="hidden" name="sessid" value="<?= htmlspecialcharsbx(bitrix_sessid()) ?>">
    <input type="hidden" name="kind" value="<?= htmlspecialcharsbx($formKind) ?>">
    <?php if ($formKind === 'callback'): ?>
    <label for="<?= htmlspecialcharsbx($formId) ?>-phone">Your phone<input id="<?= htmlspecialcharsbx($formId) ?>-phone" type="tel" name="phone" autocomplete="tel" maxlength="40" pattern="[+0-9 ()-]{7,40}" placeholder="+90 (000) 000-0000" aria-describedby="<?= htmlspecialcharsbx($formId) ?>-phone-error" required></label>
    <p id="<?= htmlspecialcharsbx($formId) ?>-phone-error" class="diva-field-error" hidden>Please enter a valid phone number.</p>
    <?php else: ?>
    <label for="<?= htmlspecialcharsbx($formId) ?>-email">Your email<input id="<?= htmlspecialcharsbx($formId) ?>-email" type="email" name="email" autocomplete="email" maxlength="254" placeholder="mail@example.com" aria-describedby="<?= htmlspecialcharsbx($formId) ?>-email-error" required></label>
    <p id="<?= htmlspecialcharsbx($formId) ?>-email-error" class="diva-field-error" hidden>Please enter a valid email address.</p>
    <label for="<?= htmlspecialcharsbx($formId) ?>-name">Your name<input id="<?= htmlspecialcharsbx($formId) ?>-name" type="text" name="name" autocomplete="name" maxlength="120" aria-describedby="<?= htmlspecialcharsbx($formId) ?>-name-error" required></label>
    <p id="<?= htmlspecialcharsbx($formId) ?>-name-error" class="diva-field-error" hidden>Please enter your name.</p>
    <label for="<?= htmlspecialcharsbx($formId) ?>-message">Your request<textarea id="<?= htmlspecialcharsbx($formId) ?>-message" name="message" rows="4" maxlength="4000" aria-describedby="<?= htmlspecialcharsbx($formId) ?>-message-error" required></textarea></label>
    <p id="<?= htmlspecialcharsbx($formId) ?>-message-error" class="diva-field-error" hidden>Please describe your request.</p>
    <?php endif; ?>
    <div class="diva-honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
    <label class="diva-consent" for="<?= htmlspecialcharsbx($formId) ?>-consent"><input id="<?= htmlspecialcharsbx($formId) ?>-consent" type="checkbox" name="consent" required><span><?= htmlspecialcharsbx((string)$arResult['SETTINGS']['consent_text']) ?> <a href="<?= htmlspecialcharsbx((string)$arResult['SETTINGS']['privacy_url']) ?>" target="_blank" rel="noopener">Privacy policy</a></span></label>
    <button type="submit"><span class="diva-submit-spinner" data-submit-spinner hidden aria-hidden="true"></span><span data-submit-label>Send</span></button><p class="diva-form-status" role="status" aria-live="polite" data-form-status></p>
    <noscript>Please email <a href="mailto:info@divatech.space">info@divatech.space</a> to send a request.</noscript>
</form>
