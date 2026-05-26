<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$isGlassVariant = $arResult['VARIANT'] === 'glass';
$isProjectDiscussion = $arResult['TYPE'] === 'project-discussion';
$labelAfterControl = false;
$hasPersonalOfferImage = !$isProjectDiscussion && $arResult['IMAGE_SRC'] !== '';
$hasSideColumn = $isProjectDiscussion || $hasPersonalOfferImage;
$contentWrapperClass = $hasSideColumn
    ? 'flex flex-col md:flex-row items-center gap-12'
    : 'max-w-3xl mx-auto';
$contentColumnClass = $hasSideColumn ? 'w-full md:w-1/2' : 'w-full';

$formClass = $isGlassVariant
    ? 'tacticum-lead-cta-form tacticum-lead-cta-form--glass tacticum-lead-cta-form--select-labels-static bg-white/10 backdrop-blur-sm rounded-xl p-6' . ($isProjectDiscussion ? '' : ' mb-6')
    : 'tacticum-lead-cta-form tacticum-lead-cta-form--select-labels-static tacticum-personal-offer-form bg-white text-secondary rounded-lg p-6 mb-6 shadow-lg border border-white/20';
$gridClass = $isGlassVariant
    ? 'grid grid-cols-1 md:grid-cols-2 gap-6 mb-6'
    : 'tacticum-personal-offer-form__grid grid grid-cols-1 md:grid-cols-2 gap-4 mb-6';
$fieldClass = $isGlassVariant ? 'relative' : 'tacticum-personal-offer-form__field';
$labelClass = $isGlassVariant
    ? 'block text-sm font-medium text-blue-100 mb-2'
    : 'tacticum-personal-offer-form__label block text-sm font-medium text-gray-700 mb-1';
$controlClass = $isGlassVariant
    ? 'w-full bg-white text-secondary border border-white/30 rounded-lg px-4 py-3 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-white/40 focus:border-white'
    : 'tacticum-personal-offer-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3';
$selectLabelClass = $labelClass;
$selectControlClass = $isGlassVariant
    ? 'w-full bg-white text-secondary border border-white/30 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-white/40 focus:border-white'
    : $controlClass;
$messageClass = $isGlassVariant ? 'mb-6' : 'tacticum-personal-offer-form__message mb-6';
$consentClass = $isGlassVariant
    ? 'flex items-start gap-2 mb-6'
    : 'tacticum-personal-offer-form__consent flex items-start gap-3 rounded-lg bg-gray-50 p-3 mb-6';
$checkboxClass = $isGlassVariant
    ? 'mt-1 appearance-none w-4 h-4 border border-white/30 rounded bg-white/5 checked:bg-primary checked:border-0 relative'
    : 'tacticum-personal-offer-form__checkbox mt-1 w-4 h-4';
$consentLabelClass = $isGlassVariant
    ? 'text-sm text-white/70'
    : 'tacticum-personal-offer-form__consent-text text-sm leading-5 text-gray-600';
$consentLinkClass = $isGlassVariant ? 'underline hover:text-white' : 'text-primary underline';
$submitClass = $isGlassVariant
    ? 'w-full bg-white text-primary font-medium px-6 py-3 rounded-button hover:bg-white/90 transition-colors whitespace-nowrap'
    : 'tacticum-personal-offer-form__submit w-full bg-primary text-white font-medium px-6 py-3 rounded-lg transition-colors whitespace-nowrap';
$requiredMark = $isGlassVariant ? '' : ' <span class="tacticum-personal-offer-form__required text-primary">*</span>';
$namePlaceholder = 'Иван';
$companyPlaceholder = 'Название компании';
$emailPlaceholder = 'mail@example.com';
$phonePlaceholder = '+7 999 000-00-00';
$messagePlaceholder = $arResult['MESSAGE_PLACEHOLDER'];

$nameId = $arResult['FIELD_PREFIX'] . '-name';
$companyId = $arResult['FIELD_PREFIX'] . '-company';
$emailId = $arResult['FIELD_PREFIX'] . '-email';
$phoneId = $arResult['FIELD_PREFIX'] . '-phone';
$messageId = $arResult['FIELD_PREFIX'] . '-message';
$budgetId = $arResult['FIELD_PREFIX'] . '-budget';
$timelineId = $arResult['FIELD_PREFIX'] . '-timeline';
$agreementId = $arResult['FIELD_PREFIX'] . '-agreement';
?>

<!-- Contact Form Section -->
<div<?php if ($arResult['SECTION_ID'] !== ''): ?> id="<?=htmlspecialcharsbx($arResult['SECTION_ID'])?>"<?php endif; ?>>
    <section class="py-16 bg-gradient-to-r from-secondary to-primary text-white">
        <div class="container mx-auto px-4">
            <div class="<?=htmlspecialcharsbx($contentWrapperClass)?>">
                <div class="<?=htmlspecialcharsbx($contentColumnClass)?>">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6"><?=htmlspecialcharsbx($arResult['TITLE'])?></h2>
                    <p class="text-lg mb-8 text-blue-100">
                        <?=htmlspecialcharsbx($arResult['TEXT'])?>
                    </p>

                    <?php if ($isProjectDiscussion && !empty($arResult['FEATURES'])): ?>
                        <div class="space-y-6 mb-8">
                            <?php foreach ($arResult['FEATURES'] as $feature): ?>
                                <?php
                                if (!is_array($feature)) {
                                    continue;
                                }

                                $featureIcon = trim((string)($feature['ICON'] ?? ''));
                                $featureTitle = trim((string)($feature['TITLE'] ?? ''));
                                $featureText = trim((string)($feature['TEXT'] ?? ''));
                                ?>
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                        <?php if ($featureIcon !== ''): ?>
                                            <i class="<?=htmlspecialcharsbx($featureIcon)?> text-2xl"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <?php if ($featureTitle !== ''): ?>
                                            <h3 class="text-xl font-bold mb-2"><?=htmlspecialcharsbx($featureTitle)?></h3>
                                        <?php endif; ?>
                                        <?php if ($featureText !== ''): ?>
                                            <p class="text-blue-100"><?=htmlspecialcharsbx($featureText)?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$isProjectDiscussion): ?>
                        <?php include __DIR__ . '/form.php'; ?>
                    <?php endif; ?>
                </div>

                <?php if ($hasSideColumn): ?>
                <div class="w-full md:w-1/2">
                    <?php if ($isProjectDiscussion): ?>
                        <?php include __DIR__ . '/form.php'; ?>
                    <?php elseif ($hasPersonalOfferImage): ?>
                        <img src="<?=htmlspecialcharsbx($arResult['IMAGE_SRC'])?>"
                             alt="<?=htmlspecialcharsbx($arResult['IMAGE_ALT'])?>"
                             class="w-full h-auto rounded-xl shadow-lg object-cover object-top">
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
