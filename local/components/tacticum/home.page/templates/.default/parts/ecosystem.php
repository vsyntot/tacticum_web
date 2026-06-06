<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (function_exists('tacticum_page_content_render_if_live') && tacticum_page_content_render_if_live('/', 'ecosystem')) {
    return;
}
?>

<section class="py-20 bg-white" data-home-block="ecosystem-map">
    <div class="container mx-auto px-4">
        <div class="mb-12 max-w-3xl">
            <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary">Экосистема</p>
            <h2 class="mb-4 text-3xl md:text-4xl font-bold text-secondary">
                Общее AI-ядро и прикладные продукты поверх него
            </h2>
            <p class="text-lg text-gray-600">
                Продуктовая модель Tacticum строится вокруг одной архитектуры: Platform отвечает за runtime,
                модели, знания, инструменты и контроль, а Agents, Dev и Forum решают прикладные задачи разных команд.
            </p>
        </div>
        <div class="space-y-6">
            <a href="/platform/" class="block rounded-xl border border-primary/20 bg-white p-6 hover:border-primary transition-colors" data-home-product-link="platform">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary">Platform core</p>
                        <h3 class="mb-3 text-2xl font-bold text-secondary">Tacticum Platform</h3>
                        <p class="text-gray-600">
                            Единый слой для LLM Gateway, RAG, памяти, MCP-инструментов, RBAC, аудита,
                            observability и контроля стоимости в корпоративном контуре.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 lg:max-w-md lg:justify-end">
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">LLM Gateway</span>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">RAG / Memory</span>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">MCP Runtime</span>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">RBAC / Audit</span>
                    </div>
                </div>
            </a>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="/agents/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all" data-home-product-link="agents">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="ri-robot-2-line text-2xl" aria-hidden="true"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-secondary">Tacticum Agents</h3>
                    <p class="text-gray-600">
                        Корпоративные ассистенты для HR, юридического, бухгалтерии, поддержки, IT helpdesk и базы знаний.
                    </p>
                </a>
                <a href="/dev/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all" data-home-product-link="dev">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="ri-git-branch-line text-2xl" aria-hidden="true"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-secondary">Tacticum Dev</h3>
                    <p class="text-gray-600">
                        Governance-слой для AI-assisted разработки: профили, знания, design tokens и quality gates.
                    </p>
                </a>
                <a href="/forum/" class="rounded-xl border border-gray-200 bg-white p-6 hover:border-primary hover:shadow-sm transition-all" data-home-product-link="forum">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <i class="ri-flow-chart text-2xl" aria-hidden="true"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-secondary">Tacticum Forum</h3>
                    <p class="text-gray-600">
                        Управляемые клиентские диалоги: сценарные графы, LLM-обогащение, аналитика и журналирование.
                    </p>
                </a>
            </div>
        </div>
    </div>
</section>
