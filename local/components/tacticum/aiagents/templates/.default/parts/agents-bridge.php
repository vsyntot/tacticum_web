<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (function_exists('tacticum_page_content_render_if_live') && tacticum_page_content_render_if_live('/aiagents/', 'agents-bridge')) {
    return;
}
?>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4 md:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_360px] gap-8 lg:gap-12 items-start">
            <div>
                <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Tacticum Agents</p>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    AI-бот как первый сценарий корпоративных ассистентов
                </h2>
                <p class="text-lg text-gray-600 mb-6">
                    Эта страница остается быстрым входом в Telegram-бот прототип. Если задача шире одного бота,
                    переходите к Tacticum Agents: там сценарии ассистентов рассматриваются вместе с RAG, памятью,
                    инструментами, правами доступа и аудитом поверх общей Platform-инфраструктуры.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="/agents/" class="inline-flex items-center justify-center gap-2 rounded-button bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                        <i class="ri-robot-2-line"></i>
                        Смотреть Tacticum Agents
                    </a>
                    <a href="#demo" class="inline-flex items-center justify-center gap-2 rounded-button border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-900 hover:border-primary hover:text-primary transition-colors">
                        <i class="ri-telegram-line"></i>
                        Остаться в Telegram-демо
                    </a>
                </div>
            </div>
            <div class="space-y-3">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="font-bold text-gray-900 mb-2">Быстрый прототип</h3>
                    <p class="text-sm text-gray-600">Проверить диалог, вопросы квалификации и handoff менеджеру.</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="font-bold text-gray-900 mb-2">Agents pilot</h3>
                    <p class="text-sm text-gray-600">Запустить ассистента с документами, правилами доступа и интеграциями.</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="font-bold text-gray-900 mb-2">Platform path</h3>
                    <p class="text-sm text-gray-600">Вынести RAG, память, инструменты и аудит в общий AI-контур.</p>
                </div>
            </div>
        </div>
    </div>
</section>
