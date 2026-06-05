<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Delivery layer</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Внедрение как путь от продукта к рабочему процессу
            </h2>
            <p class="text-lg text-gray-600">
                Продуктовая линейка отвечает на вопрос, что именно запускать. Внедрение отвечает на вопрос,
                как безопасно довести это до данных, интеграций, пользователей и production-контроля.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Platform assessment</h3>
                <p class="text-gray-600">
                    Проверяем, нужен ли общий AI-контур: модели, RAG, инструменты, доступы, аудит и эксплуатация.
                </p>
            </a>
            <a href="/agents/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Agents pilot</h3>
                <p class="text-gray-600">
                    Выбираем 1-2 ассистента, готовим документы и сценарии, подключаем безопасный handoff к команде.
                </p>
            </a>
            <a href="/dev/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-code-box-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Dev workflow</h3>
                <p class="text-gray-600">
                    Описываем профиль команды, knowledge layer, design token rules и quality gates для AI-разработки.
                </p>
            </a>
            <a href="/forum/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-customer-service-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Forum launch</h3>
                <p class="text-gray-600">
                    Разбираем поток обращений, проектируем сценарный граф, LLM-обогащение, аналитику и журнал диалогов.
                </p>
            </a>
        </div>
    </div>
</section>
