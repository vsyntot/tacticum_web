<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<section id='CTA' class="py-12 md:py-16 bg-gradient-to-b from-white to-indigo-50/50">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto bg-white rounded-xl p-8 shadow-lg">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-6">
                Получить персональную оценку по похожей задаче
            </h2>
            <p class="text-center text-gray-700 mb-8">
                Пример выше помогает сориентироваться, но точная оценка зависит от ваших данных, интеграций,
                требований безопасности и сроков. Отправьте заявку, и мы уточним следующий шаг.
            </p>

            <form id="applicationForm" class="space-y-6" data-tacticum-form data-form-id="offer-cta">
                <input type="hidden" name="lead_entry" value="offer-detail">
                <input type="hidden" name="lead_page_role" value="offer-example-detail">
                <input type="hidden" name="lead_intent" value="personalize-similar-estimate">
                <input type="hidden" name="lead_product" value="ecosystem">
                <input type="hidden" name="lead_cta" value="offer-cta">
                <input type="hidden" name="lead_next_step" value="offer-estimate-review">
                <input type="hidden" name="lead_offer_code" value="<?=htmlspecialcharsbx((string)($arResult['CODE'] ?? ''))?>">
                <input type="hidden" name="lead_offer_title" value="<?=htmlspecialcharsbx($offerH1)?>">
                <?php if ($offerSector !== ''):?>
                    <input type="hidden" name="lead_industry" value="<?=htmlspecialcharsbx($offerSector)?>">
                <?php endif;?>
                <?php if ($offerScenario !== ''):?>
                    <input type="hidden" name="lead_scenario" value="<?=htmlspecialcharsbx($offerScenario)?>">
                <?php endif;?>
                <?php if ($budgetRaw !== ''):?>
                    <input type="hidden" name="lead_budget" value="<?=htmlspecialcharsbx($budgetRaw)?>">
                <?php endif;?>
                <?php if ($timelineRaw !== ''):?>
                    <input type="hidden" name="lead_timeline" value="<?=htmlspecialcharsbx($timelineRaw)?>">
                <?php endif;?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Имя</label>
                        <input type="text" id="name" name="name" required placeholder="Укажите как к Вам обращаться"
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                    <div>
                        <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Компания</label>
                        <input type="text" id="company" name="company" placeholder="Укажите название Вашей компании"
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Укажите Вашу почту"
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
                        <input type="tel" id="phone" name="phone" required placeholder="Укажите Ваш контактный номер"
                               class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50" />
                    </div>
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Что нужно уточнить под ваш проект</label>
                    <textarea id="message" name="message" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary/50"
                              placeholder="Опишите отличия от примера: отрасль, данные, интеграции, сроки, ограничения"></textarea>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="consent" name="consent" data-tacticum-consent required checked>
                    <label for="consent" class="text-sm text-gray-600">
                        Я согласен на обработку персональных данных и принимаю
                        <a href="/policies/" target="_blank" rel="noopener" class="text-primary hover:underline">условия использования</a>
                    </label>
                </div>

                <div class="flex justify-center">
                    <button type="submit"
                            class="px-8 py-3 bg-primary text-white !rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap shadow-lg shadow-primary/30 text-lg">
                        Уточнить оценку
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
