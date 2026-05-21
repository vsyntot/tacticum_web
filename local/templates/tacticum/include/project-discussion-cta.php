<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$projectDiscussionCtaConfig = is_array($tacticumProjectDiscussionCta ?? null) ? $tacticumProjectDiscussionCta : [];

$sectionId = trim((string)($projectDiscussionCtaConfig["section_id"] ?? "contact-form"));
$formId = trim((string)($projectDiscussionCtaConfig["form_id"] ?? "project-cta"));
$formHtmlId = trim((string)($projectDiscussionCtaConfig["form_html_id"] ?? ""));
$fieldPrefix = trim((string)($projectDiscussionCtaConfig["field_prefix"] ?? "project"));

if ($formId === "") {
    $formId = "project-cta";
}

if ($fieldPrefix === "") {
    $fieldPrefix = "project";
}

$nameId = $fieldPrefix . "-name";
$companyId = $fieldPrefix . "-company";
$emailId = $fieldPrefix . "-email";
$phoneId = $fieldPrefix . "-phone";
$messageId = $fieldPrefix . "-message";
$agreementId = $fieldPrefix . "-agreement";

unset($tacticumProjectDiscussionCta);
?>

<!-- Contact Form Section -->
<div<?php if ($sectionId !== ""): ?> id="<?=htmlspecialcharsbx($sectionId)?>"<?php endif; ?>>
    <section class="py-16 bg-gradient-to-r from-secondary to-primary text-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">Готовы обсудить ваш проект?</h2>
                    <p class="text-lg mb-8 text-blue-100">
                        Заполните форму, и наши специалисты свяжутся с вами в течение 24 часов для обсуждения деталей и
                        подготовки индивидуального предложения.
                    </p>
                    <div class="space-y-6 mb-8">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i class="ri-medal-line text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Опыт и экспертиза</h3>
                                <p class="text-blue-100">Более 50 успешно реализованных AI-проектов в различных отраслях</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i class="ri-team-line text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Команда профессионалов</h3>
                                <p class="text-blue-100">Сертифицированные специалисты с опытом работы в ведущих IT-компаниях</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i class="ri-rocket-line text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Быстрый старт</h3>
                                <p class="text-blue-100">Начинаем работу над проектом в течение 3–5 дней после согласования</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Форма заявки -->
                <div class="w-full md:w-1/2">
                    <form<?php if ($formHtmlId !== ""): ?> id="<?=htmlspecialcharsbx($formHtmlId)?>"<?php endif; ?> class="bg-white/10 backdrop-blur-sm rounded-xl p-6" data-tacticum-form data-form-id="<?=htmlspecialcharsbx($formId)?>">
                        <h3 class="text-xl font-bold mb-6">Оставить заявку</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="relative">
                                <input type="text" id="<?=htmlspecialcharsbx($nameId)?>" name="name" required placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="<?=htmlspecialcharsbx($nameId)?>" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Имя</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="<?=htmlspecialcharsbx($companyId)?>" name="company" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="<?=htmlspecialcharsbx($companyId)?>" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Компания</label>
                            </div>
                            <div class="relative">
                                <input type="email" id="<?=htmlspecialcharsbx($emailId)?>" name="email" required placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="<?=htmlspecialcharsbx($emailId)?>" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Email</label>
                            </div>
                            <div class="relative">
                                <input type="tel" id="<?=htmlspecialcharsbx($phoneId)?>" name="phone" required placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="<?=htmlspecialcharsbx($phoneId)?>" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Телефон</label>
                            </div>
                        </div>

                        <div class="relative mb-6">
                            <textarea id="<?=htmlspecialcharsbx($messageId)?>" name="message" rows="4" required placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30"></textarea>
                            <label for="<?=htmlspecialcharsbx($messageId)?>" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Опишите ваш проект</label>
                        </div>

                        <div class="flex items-start gap-2 mb-6">
                            <input type="checkbox" id="<?=htmlspecialcharsbx($agreementId)?>" data-tacticum-consent required class="mt-1 appearance-none w-4 h-4 border border-white/30 rounded bg-white/5 checked:bg-primary checked:border-0 relative">
                            <label for="<?=htmlspecialcharsbx($agreementId)?>" class="text-sm text-white/70">
                                Я согласен на обработку персональных данных и принимаю условия
                                <a href="/policies/" target="_blank" rel="noopener" class="underline hover:text-white">политики конфиденциальности</a>
                            </label>
                        </div>

                        <button type="submit" class="w-full bg-white text-primary font-medium px-6 py-3 rounded-button hover:bg-white/90 transition-colors whitespace-nowrap">
                            Запросить расчет
                        </button>
                    </form>
                </div>
                <!-- /Форма заявки -->
            </div>
        </div>
    </section>
</div>
<?php
unset(
    $projectDiscussionCtaConfig,
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
