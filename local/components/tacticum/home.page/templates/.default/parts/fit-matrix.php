<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (function_exists('tacticum_page_content_render_if_live') && tacticum_page_content_render_if_live('/', 'fit-matrix')) {
    return;
}
?>

<section class="py-20 bg-gray-50" data-home-block="fit-matrix">
    <div class="container mx-auto px-4">
        <div class="mb-12 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Как выбрать продукт</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Начните с ситуации, а не с названия продукта
            </h2>
            <p class="text-lg text-gray-600">
                Если продуктовый вход пока не очевиден, используйте короткую матрицу. Она разделяет платформенные,
                функциональные, инженерные и клиентские сценарии без обещаний результата до discovery.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-primary hover:shadow-md transition-all" data-home-product-link="platform">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl" aria-hidden="true"></i>
                </div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Platform</p>
                <h3 class="mb-3 text-xl font-bold text-secondary">Единый AI-контур</h3>
                <p class="mb-4 text-gray-600">
                    Выбирайте, если AI-сценариев несколько и нужны общие RAG, модели, инструменты, доступы, audit и контроль стоимости.
                </p>
                <p class="text-sm font-medium text-secondary">Старт: architecture assessment</p>
            </a>
            <a href="/agents/" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-primary hover:shadow-md transition-all" data-home-product-link="agents">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-robot-2-line text-2xl" aria-hidden="true"></i>
                </div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Agents</p>
                <h3 class="mb-3 text-xl font-bold text-secondary">Ассистенты для функций</h3>
                <p class="mb-4 text-gray-600">
                    Подходит для HR, legal, finance, support, IT helpdesk и базы знаний, где есть документы, правила и handoff к человеку.
                </p>
                <p class="text-sm font-medium text-secondary">Старт: выбор 1-2 сценариев</p>
            </a>
            <a href="/dev/" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-primary hover:shadow-md transition-all" data-home-product-link="dev">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-git-branch-line text-2xl" aria-hidden="true"></i>
                </div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Dev</p>
                <h3 class="mb-3 text-xl font-bold text-secondary">AI-assisted workflow</h3>
                <p class="mb-4 text-gray-600">
                    Смотрите Dev, если команда уже использует AI-инструменты и нужно удержать architecture, review, tests и design tokens.
                </p>
                <p class="text-sm font-medium text-secondary">Старт: пилот на одной команде</p>
            </a>
            <a href="/forum/" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:border-primary hover:shadow-md transition-all" data-home-product-link="forum">
                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-flow-chart text-2xl" aria-hidden="true"></i>
                </div>
                <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Forum</p>
                <h3 class="mb-3 text-xl font-bold text-secondary">Клиентские диалоги</h3>
                <p class="mb-4 text-gray-600">
                    Выбирайте Forum для каналов поддержки и продаж, где нужны сценарии, LLM-уточнения, эскалации и журнал диалогов.
                </p>
                <p class="text-sm font-medium text-secondary">Старт: разбор потока обращений</p>
            </a>
        </div>
    </div>
</section>
