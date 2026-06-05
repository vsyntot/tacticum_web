<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Product workstreams</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Команда под продуктовый пилот или delivery-этап
            </h2>
            <p class="text-lg text-gray-600">
                Страница команды не становится страницей лицензий на продукты. Это по-прежнему способ оценить роли,
                загрузку и старт команды для внедрения Platform, Agents, Dev, Forum или отдельной AI-интеграции.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Platform team</h3>
                <p class="text-gray-600">
                    Архитектор, backend, data/RAG, integration, QA и DevOps для платформенного assessment или пилота.
                </p>
            </a>
            <a href="/agents/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Agents pilot</h3>
                <p class="text-gray-600">
                    Аналитик, prompt/RAG, backend, integration и QA для запуска ассистента в одном подразделении.
                </p>
            </a>
            <a href="/dev/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-code-box-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Dev workflow</h3>
                <p class="text-gray-600">
                    Engineering lead, архитектор, design system owner, QA и DevOps для пилота AI-assisted процесса.
                </p>
            </a>
            <a href="/forum/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-customer-service-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Forum launch</h3>
                <p class="text-gray-600">
                    CX-аналитик, сценарист, backend, integration, QA и PM для первого потока обращений.
                </p>
            </a>
        </div>
    </div>
</section>
