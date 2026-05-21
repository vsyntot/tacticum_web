<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$personalOfferCtaConfig = is_array($tacticumPersonalOfferCta ?? null) ? $tacticumPersonalOfferCta : [];

$sectionId = trim((string)($personalOfferCtaConfig["section_id"] ?? "contact-form"));
$formId = trim((string)($personalOfferCtaConfig["form_id"] ?? "contact-cta"));
$formHtmlId = trim((string)($personalOfferCtaConfig["form_html_id"] ?? ""));
$fieldPrefix = trim((string)($personalOfferCtaConfig["field_prefix"] ?? "cta"));
$formVariant = trim((string)($personalOfferCtaConfig["variant"] ?? "solid"));

if ($formId === "") {
    $formId = "contact-cta";
}

if ($fieldPrefix === "") {
    $fieldPrefix = "cta";
}

if (!in_array($formVariant, ["solid", "glass"], true)) {
    $formVariant = "solid";
}

$isGlassVariant = $formVariant === "glass";

$formClass = $isGlassVariant
    ? "bg-white/10 backdrop-blur-sm rounded-xl p-6 mb-6"
    : "tacticum-personal-offer-form bg-white text-secondary rounded-lg p-6 mb-6 shadow-lg border border-white/20";
$gridClass = $isGlassVariant
    ? "grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"
    : "tacticum-personal-offer-form__grid grid grid-cols-1 md:grid-cols-2 gap-4 mb-6";
$fieldClass = $isGlassVariant ? "relative" : "tacticum-personal-offer-form__field";
$labelClass = $isGlassVariant
    ? "absolute left-4 top-3 text-white/60 transition-transform origin-left"
    : "tacticum-personal-offer-form__label block text-sm font-medium text-gray-700 mb-1";
$controlClass = $isGlassVariant
    ? "w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30"
    : "tacticum-personal-offer-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3";
$messageClass = $isGlassVariant ? "relative mb-6" : "tacticum-personal-offer-form__message mb-6";
$messageLabel = $isGlassVariant
    ? "Опишите ваш проект или интересующее предложение"
    : "Опишите проект или интересующее предложение";
$consentClass = $isGlassVariant
    ? "flex items-start gap-2 mb-6"
    : "tacticum-personal-offer-form__consent flex items-start gap-3 rounded-lg bg-gray-50 p-3 mb-6";
$checkboxClass = $isGlassVariant
    ? "mt-1 appearance-none w-4 h-4 border border-white/30 rounded bg-white/5 checked:bg-primary checked:border-0 relative"
    : "tacticum-personal-offer-form__checkbox mt-1 w-4 h-4";
$consentLabelClass = $isGlassVariant
    ? "text-sm text-white/70"
    : "tacticum-personal-offer-form__consent-text text-sm leading-5 text-gray-600";
$consentLinkClass = $isGlassVariant
    ? "underline hover:text-white"
    : "text-primary underline";
$submitClass = $isGlassVariant
    ? "w-full bg-white text-primary font-medium px-6 py-3 rounded-button hover:bg-white/90 transition-colors whitespace-nowrap"
    : "tacticum-personal-offer-form__submit w-full bg-primary text-white font-medium px-6 py-3 rounded-lg transition-colors whitespace-nowrap";
$requiredMark = $isGlassVariant ? "" : ' <span class="tacticum-personal-offer-form__required text-primary">*</span>';
$namePlaceholder = $isGlassVariant ? " " : "Иван";
$companyPlaceholder = $isGlassVariant ? " " : "Название компании";
$emailPlaceholder = $isGlassVariant ? " " : "mail@example.com";
$phonePlaceholder = $isGlassVariant ? " " : "+7 999 000-00-00";
$messagePlaceholder = $isGlassVariant ? " " : "Кратко опишите задачу, сроки и ожидаемый результат";

$nameId = $fieldPrefix . "-name";
$companyId = $fieldPrefix . "-company";
$emailId = $fieldPrefix . "-email";
$phoneId = $fieldPrefix . "-phone";
$messageId = $fieldPrefix . "-message";
$agreementId = $fieldPrefix . "-agreement";

unset($tacticumPersonalOfferCta);
?>

<!-- Contact Form Section -->
<div<?php if ($sectionId !== ""): ?> id="<?=htmlspecialcharsbx($sectionId)?>"<?php endif; ?>>
    <section class="py-16 bg-gradient-to-r from-secondary to-primary text-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">Получите персональное предложение</h2>
                    <p class="text-lg mb-8 text-blue-100">
                        Оставьте заявку, и наш менеджер свяжется с вами в течение 2 часов, чтобы обсудить детали и
                        подготовить индивидуальное предложение с учетом всех доступных скидок и акций.
                    </p>
                    <form<?php if ($formHtmlId !== ""): ?> id="<?=htmlspecialcharsbx($formHtmlId)?>"<?php endif; ?> class="<?=htmlspecialcharsbx($formClass)?>" data-tacticum-form data-form-id="<?=htmlspecialcharsbx($formId)?>">
                        <div class="<?=htmlspecialcharsbx($gridClass)?>">
                            <div class="<?=htmlspecialcharsbx($fieldClass)?>">
                                <label for="<?=htmlspecialcharsbx($nameId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Имя<?=$requiredMark?></label>
                                <input type="text" id="<?=htmlspecialcharsbx($nameId)?>" name="name" required autocomplete="name" placeholder="<?=htmlspecialcharsbx($namePlaceholder)?>"
                                       class="<?=htmlspecialcharsbx($controlClass)?>">
                            </div>
                            <div class="<?=htmlspecialcharsbx($fieldClass)?>">
                                <label for="<?=htmlspecialcharsbx($companyId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Компания</label>
                                <input type="text" id="<?=htmlspecialcharsbx($companyId)?>" name="company" autocomplete="organization" placeholder="<?=htmlspecialcharsbx($companyPlaceholder)?>"
                                       class="<?=htmlspecialcharsbx($controlClass)?>">
                            </div>
                            <div class="<?=htmlspecialcharsbx($fieldClass)?>">
                                <label for="<?=htmlspecialcharsbx($emailId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Email<?=$requiredMark?></label>
                                <input type="email" id="<?=htmlspecialcharsbx($emailId)?>" name="email" required autocomplete="email" placeholder="<?=htmlspecialcharsbx($emailPlaceholder)?>"
                                       class="<?=htmlspecialcharsbx($controlClass)?>">
                            </div>
                            <div class="<?=htmlspecialcharsbx($fieldClass)?>">
                                <label for="<?=htmlspecialcharsbx($phoneId)?>" class="<?=htmlspecialcharsbx($labelClass)?>">Телефон<?=$requiredMark?></label>
                                <input type="tel" id="<?=htmlspecialcharsbx($phoneId)?>" name="phone" required autocomplete="tel" placeholder="<?=htmlspecialcharsbx($phonePlaceholder)?>"
                                       class="<?=htmlspecialcharsbx($controlClass)?>">
                            </div>
                        </div>
                        <div class="<?=htmlspecialcharsbx($messageClass)?>">
                            <label for="<?=htmlspecialcharsbx($messageId)?>" class="<?=htmlspecialcharsbx($labelClass)?>"><?=htmlspecialcharsbx($messageLabel)?><?=$requiredMark?></label>
                            <textarea id="<?=htmlspecialcharsbx($messageId)?>" name="message" rows="4" required placeholder="<?=htmlspecialcharsbx($messagePlaceholder)?>"
                                      class="<?=htmlspecialcharsbx($controlClass)?>"></textarea>
                        </div>
                        <div class="<?=htmlspecialcharsbx($consentClass)?>">
                            <input type="checkbox" id="<?=htmlspecialcharsbx($agreementId)?>" data-tacticum-consent required
                                   class="<?=htmlspecialcharsbx($checkboxClass)?>">
                            <label for="<?=htmlspecialcharsbx($agreementId)?>" class="<?=htmlspecialcharsbx($consentLabelClass)?>">
                                Я согласен на обработку персональных данных и принимаю условия
                                <a href="/policies/" target="_blank" rel="noopener" class="<?=htmlspecialcharsbx($consentLinkClass)?>">политики конфиденциальности</a>
                            </label>
                        </div>
                        <button type="submit" class="<?=htmlspecialcharsbx($submitClass)?>">
                            Получить персональное предложение
                        </button>
                    </form>
                </div>
                <div class="w-full md:w-1/2">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/specialoffer.jpg"
                         alt="Персональное предложение"
                         class="w-full h-auto rounded-xl shadow-lg object-cover object-top">
                </div>
            </div>
        </div>
    </section>
</div>
<?php
unset(
    $personalOfferCtaConfig,
    $sectionId,
    $formId,
    $formHtmlId,
    $fieldPrefix,
    $formVariant,
    $isGlassVariant,
    $formClass,
    $gridClass,
    $fieldClass,
    $labelClass,
    $controlClass,
    $messageClass,
    $messageLabel,
    $consentClass,
    $checkboxClass,
    $consentLabelClass,
    $consentLinkClass,
    $submitClass,
    $requiredMark,
    $namePlaceholder,
    $companyPlaceholder,
    $emailPlaceholder,
    $phonePlaceholder,
    $messagePlaceholder,
    $nameId,
    $companyId,
    $emailId,
    $phoneId,
    $messageId,
    $agreementId
);
?>
