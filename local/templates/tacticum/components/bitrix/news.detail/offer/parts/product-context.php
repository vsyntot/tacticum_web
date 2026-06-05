<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<section class="py-12 md:py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Связь с продуктами</p>
            <h2 class="mb-4 text-2xl md:text-3xl font-bold text-secondary">
                Как читать этот пример в продуктовой модели Tacticum
            </h2>
            <p class="text-gray-600">
                Пример расчета показывает scope, команду и риски. На следующем шаге мы уточняем, какой продуктовый
                контур нужен: платформенный слой, ассистент, инженерный workflow или управляемый диалоговый сценарий.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-lg font-bold text-secondary">Platform</h3>
                <p class="text-sm text-gray-600">Если задаче нужен общий AI-контур, RAG, доступы, инструменты и аудит.</p>
            </a>
            <a href="/agents/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-lg font-bold text-secondary">Agents</h3>
                <p class="text-sm text-gray-600">Если задача похожа на ассистента для функции, документов или поддержки.</p>
            </a>
            <a href="/dev/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-git-branch-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-lg font-bold text-secondary">Dev</h3>
                <p class="text-sm text-gray-600">Если оценка относится к AI-assisted workflow инженерной команды.</p>
            </a>
            <a href="/forum/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-flow-chart text-2xl"></i>
                </div>
                <h3 class="mb-2 text-lg font-bold text-secondary">Forum</h3>
                <p class="text-sm text-gray-600">Если речь о клиентских диалогах, сценариях, LLM-обогащении и эскалации.</p>
            </a>
        </div>
    </div>
</section>
