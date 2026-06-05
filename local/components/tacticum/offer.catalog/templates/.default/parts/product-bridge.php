<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="mb-10 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Proof layer</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Используйте примеры как мост к продуктовой архитектуре
            </h2>
            <p class="text-lg text-gray-600">
                Каталог расчетов остается proof и estimate layer. Он помогает найти похожую задачу, а затем связать
                ее с подходящим продуктовым входом: Platform, Agents, Dev или Forum.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="/platform/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-stack-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Platform examples</h3>
                <p class="text-gray-600">
                    RAG, LLM Gateway, доступы, интеграции и общий AI-контур для нескольких сценариев.
                </p>
            </a>
            <a href="/agents/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-robot-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Agents examples</h3>
                <p class="text-gray-600">
                    Ассистенты для внутренних функций, базы знаний, поддержки, документов и типовых запросов.
                </p>
            </a>
            <a href="/dev/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-code-box-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Dev examples</h3>
                <p class="text-gray-600">
                    Пилоты для инженерных команд: workflow, knowledge layer, rules и quality gates.
                </p>
            </a>
            <a href="/forum/" class="rounded-xl border border-gray-200 bg-gray-50 p-6 hover:border-primary hover:bg-white transition-colors">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="ri-customer-service-2-line text-2xl"></i>
                </div>
                <h3 class="mb-2 text-xl font-bold text-secondary">Forum examples</h3>
                <p class="text-gray-600">
                    Клиентские диалоги, сценарные графы, LLM-обогащение, аналитика и эскалации.
                </p>
            </a>
        </div>
    </div>
</section>
