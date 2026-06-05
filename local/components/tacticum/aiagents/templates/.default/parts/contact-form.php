<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<section id="contact" class="py-20">
    <div class="container mx-auto px-4 md:px-6">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-sm p-8 md:p-12">
            <h2 class="text-3xl font-bold text-center mb-8">Нужен бот-прототип под ваш процесс?</h2>

            <form id="contactFormInline" data-tacticum-form data-form-id="aiagents-inline">
                <input type="hidden" name="lead_entry" value="aiagents">
                <input type="hidden" name="lead_page_role" value="telegram-bot-entry">
                <input type="hidden" name="lead_intent" value="request-ai-bot-prototype">
                <input type="hidden" name="lead_product" value="agents">
                <input type="hidden" name="lead_cta" value="aiagents-inline">
                <input type="hidden" name="lead_next_step" value="bot-prototype-review">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="nameInline" class="block text-sm font-medium text-gray-700 mb-1">Имя *</label>
                        <input type="text" id="nameInline" name="name" required class="w-full px-4 py-3 border border-gray-300 !rounded-button">
                    </div>
                    <div>
                        <label for="companyInline" class="block text-sm font-medium text-gray-700 mb-1">Компания</label>
                        <input type="text" id="companyInline" name="company" class="w-full px-4 py-3 border border-gray-300 !rounded-button">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="phoneInline" class="block text-sm font-medium text-gray-700 mb-1">Телефон *</label>
                        <input type="tel" id="phoneInline" name="phone" required class="w-full px-4 py-3 border border-gray-300 !rounded-button">
                    </div>
                    <div>
                        <label for="emailInline" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" id="emailInline" name="email" required class="w-full px-4 py-3 border border-gray-300 !rounded-button">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="projectInline" class="block text-sm font-medium text-gray-700 mb-1">Какой сценарий должен закрывать бот</label>
                    <textarea id="projectInline" name="message" required rows="4" class="w-full px-4 py-3 border border-gray-300 !rounded-button"></textarea>
                </div>

                <div class="flex items-start gap-2 mb-8">
                    <input type="checkbox" id="agreementInline" data-tacticum-consent required class="mt-1">
                    <label for="agreementInline" class="text-sm text-gray-600">
                        Я согласен на обработку персональных данных и принимаю условия
                        <a href="/policies/" target="_blank" rel="noopener" class="underline">политики конфиденциальности</a>
                    </label>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-3 !rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
                    Запросить бот-прототип
                </button>
            </form>
        </div>
    </div>
</section>
