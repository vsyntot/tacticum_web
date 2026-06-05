<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<section id="demo" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6">
        <h2 class="text-3xl font-bold text-center mb-6">Проверьте прототип в Telegram</h2>
        <p class="text-xl text-gray-600 text-center mb-12 max-w-3xl mx-auto">Демо поможет увидеть механику диалога до того, как вы будете планировать интеграции и разработку</p>

        <div class="flex flex-col md:flex-row items-center justify-center gap-12">
            <div class="w-full md:w-1/2 max-w-md">
                <div class="bg-gray-100 p-4 rounded-2xl shadow-sm">
                    <div class="bg-white rounded-xl p-4 shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white mr-3">
                                <i class="ri-robot-line"></i>
                            </div>
                            <div>
                                <div class="font-semibold">AI-бот для квалификации</div>
                                <div class="text-xs text-gray-500">Онлайн</div>
                            </div>
                        </div>

                        <div class="space-y-4 mb-4">
                            <div class="bg-gray-100 p-3 rounded-lg rounded-tl-none max-w-[80%]">
                                <p class="text-sm">Здравствуйте! Я помогу проверить сценарий AI-бота. Чем занимается ваша компания и какой лид нужно квалифицировать?</p>
                            </div>

                            <div class="bg-primary/10 p-3 rounded-lg rounded-tr-none max-w-[80%] ml-auto">
                                <p class="text-sm">Мы продаем B2B-сервис для логистики и хотим быстрее обрабатывать входящие заявки</p>
                            </div>

                            <div class="bg-gray-100 p-3 rounded-lg rounded-tl-none max-w-[80%]">
                                <p class="text-sm">Какие критерии важны для квалификации: размер компании, регион, срок запуска, бюджет или текущая система?</p>
                            </div>

                            <div class="bg-primary/10 p-3 rounded-lg rounded-tr-none max-w-[80%] ml-auto">
                                <p class="text-sm">Нужны регион, объем заявок, текущая CRM и срок внедрения</p>
                            </div>

                            <div class="bg-gray-100 p-3 rounded-lg rounded-tl-none max-w-[80%]">
                                <p class="text-sm">Понял. Я соберу черновой сценарий, а команда Tacticum поможет уточнить интеграции и передачу лида менеджеру.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-1/2 max-w-md flex flex-col items-center">
                <h3 class="text-2xl font-semibold mb-6">Что можно проверить в демо:</h3>
                <ul class="space-y-4 mb-8 w-full">
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>как бот объясняет ваши услуги и задает уточняющие вопросы</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>какие критерии квалификации нужны до передачи менеджеру</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>какие данные стоит собирать в Telegram-сценарии</span>
                    </li>
                    <li class="flex items-start">
                        <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center text-primary mr-3 mt-1">
                            <i class="ri-check-line"></i>
                        </div>
                        <span>какие интеграции понадобятся для production-запуска</span>
                    </li>
                </ul>

                <a href="https://t.me/tacticum_father_bot" target="_blank" rel="noopener" data-tacticum-tg-resolve class="bg-primary text-white py-3 px-8 !rounded-button flex items-center justify-center hover:bg-primary/90 transition-colors whitespace-nowrap">
                    <i class="ri-telegram-line mr-2"></i>
                    Открыть демо в Telegram
                </a>
            </div>
        </div>
    </div>
</section>
