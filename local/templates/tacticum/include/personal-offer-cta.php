<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$personalOfferCtaConfig = is_array($tacticumPersonalOfferCta ?? null) ? $tacticumPersonalOfferCta : [];

$sectionId = trim((string)($personalOfferCtaConfig["section_id"] ?? "contact-form"));
$formId = trim((string)($personalOfferCtaConfig["form_id"] ?? "contact-cta"));
$formHtmlId = trim((string)($personalOfferCtaConfig["form_html_id"] ?? ""));
$fieldPrefix = trim((string)($personalOfferCtaConfig["field_prefix"] ?? "cta"));

if ($formId === "") {
    $formId = "contact-cta";
}

if ($fieldPrefix === "") {
    $fieldPrefix = "cta";
}

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
                    <form<?php if ($formHtmlId !== ""): ?> id="<?=htmlspecialcharsbx($formHtmlId)?>"<?php endif; ?> class="tacticum-personal-offer-form bg-white text-secondary rounded-lg p-6 mb-6 shadow-lg border border-white/20" data-tacticum-form data-form-id="<?=htmlspecialcharsbx($formId)?>">
                        <div class="tacticum-personal-offer-form__grid grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="tacticum-personal-offer-form__field">
                                <label for="<?=htmlspecialcharsbx($nameId)?>" class="tacticum-personal-offer-form__label block text-sm font-medium text-gray-700 mb-1">Имя <span class="tacticum-personal-offer-form__required text-primary">*</span></label>
                                <input type="text" id="<?=htmlspecialcharsbx($nameId)?>" name="name" required autocomplete="name" placeholder="Иван"
                                       class="tacticum-personal-offer-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3">
                            </div>
                            <div class="tacticum-personal-offer-form__field">
                                <label for="<?=htmlspecialcharsbx($companyId)?>" class="tacticum-personal-offer-form__label block text-sm font-medium text-gray-700 mb-1">Компания</label>
                                <input type="text" id="<?=htmlspecialcharsbx($companyId)?>" name="company" autocomplete="organization" placeholder="Название компании"
                                       class="tacticum-personal-offer-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3">
                            </div>
                            <div class="tacticum-personal-offer-form__field">
                                <label for="<?=htmlspecialcharsbx($emailId)?>" class="tacticum-personal-offer-form__label block text-sm font-medium text-gray-700 mb-1">Email <span class="tacticum-personal-offer-form__required text-primary">*</span></label>
                                <input type="email" id="<?=htmlspecialcharsbx($emailId)?>" name="email" required autocomplete="email" placeholder="mail@example.com"
                                       class="tacticum-personal-offer-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3">
                            </div>
                            <div class="tacticum-personal-offer-form__field">
                                <label for="<?=htmlspecialcharsbx($phoneId)?>" class="tacticum-personal-offer-form__label block text-sm font-medium text-gray-700 mb-1">Телефон <span class="tacticum-personal-offer-form__required text-primary">*</span></label>
                                <input type="tel" id="<?=htmlspecialcharsbx($phoneId)?>" name="phone" required autocomplete="tel" placeholder="+7 999 000-00-00"
                                       class="tacticum-personal-offer-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3">
                            </div>
                        </div>
                        <div class="tacticum-personal-offer-form__message mb-6">
                            <label for="<?=htmlspecialcharsbx($messageId)?>" class="tacticum-personal-offer-form__label block text-sm font-medium text-gray-700 mb-1">Опишите проект или интересующее предложение <span class="tacticum-personal-offer-form__required text-primary">*</span></label>
                            <textarea id="<?=htmlspecialcharsbx($messageId)?>" name="message" rows="4" required placeholder="Кратко опишите задачу, сроки и ожидаемый результат"
                                      class="tacticum-personal-offer-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3"></textarea>
                        </div>
                        <div class="tacticum-personal-offer-form__consent flex items-start gap-3 rounded-lg bg-gray-50 p-3 mb-6">
                            <input type="checkbox" id="<?=htmlspecialcharsbx($agreementId)?>" data-tacticum-consent required
                                   class="tacticum-personal-offer-form__checkbox mt-1 w-4 h-4">
                            <label for="<?=htmlspecialcharsbx($agreementId)?>" class="tacticum-personal-offer-form__consent-text text-sm leading-5 text-gray-600">
                                Я согласен на обработку персональных данных и принимаю условия
                                <a href="/policies/" target="_blank" rel="noopener" class="text-primary underline">политики конфиденциальности</a>
                            </label>
                        </div>
                        <button type="submit" class="tacticum-personal-offer-form__submit w-full bg-primary text-white font-medium px-6 py-3 rounded-lg transition-colors whitespace-nowrap">
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
    $nameId,
    $companyId,
    $emailId,
    $phoneId,
    $messageId,
    $agreementId
);
?>
