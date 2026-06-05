<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<section class="hero-bg pt-24" data-home-block="hero">
    <div class="container mx-auto px-4 py-16">
        <div class="flex flex-col md:flex-row items-center gap-12">
            <div class="w-full md:w-1/2 text-white">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Tacticum: платформа и продукты для корпоративного AI
                </h1>
                <p class="text-lg md:text-xl mb-8 text-blue-100">
                    Соединяем Platform, Agents, Dev и Forum с внедрением, оценкой проекта и командой. Можно начать
                    с продуктового пилота, архитектурной сессии, расчета бюджета или подбора delivery-команды.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="/platform/" class="bg-primary text-white px-8 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap text-center">Смотреть Platform</a>
                    <a href="/offer/" class="bg-white/10 backdrop-blur-sm text-white border border-white/30 px-8 py-3 rounded-button hover:bg-white/20 transition-colors whitespace-nowrap text-center">Рассчитать проект</a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-8">
                    <a href="/platform/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors" data-home-product-link="platform">
                        <span class="block text-sm text-blue-100">Ядро экосистемы</span>
                        <span class="block font-semibold">Tacticum Platform</span>
                    </a>
                    <a href="/agents/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors" data-home-product-link="agents">
                        <span class="block text-sm text-blue-100">Бизнес-функции</span>
                        <span class="block font-semibold">Tacticum Agents</span>
                    </a>
                    <a href="/dev/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors" data-home-product-link="dev">
                        <span class="block text-sm text-blue-100">Инженерные команды</span>
                        <span class="block font-semibold">Tacticum Dev</span>
                    </a>
                    <a href="/forum/" class="rounded-lg border border-white/15 bg-white/10 p-4 text-white hover:bg-white/15 transition-colors" data-home-product-link="forum">
                        <span class="block text-sm text-blue-100">Клиентские диалоги</span>
                        <span class="block font-semibold">Tacticum Forum</span>
                    </a>
                </div>
            </div>
            <?php
            $APPLICATION->IncludeComponent(
                "tacticum:chat.surface",
                "",
                [
                    "VARIANT" => "hero",
                    "SURFACE" => "hero",
                    "ROOT_CLASS" => "w-full md:w-1/2 relative",
                    "TITLE" => "AI-ассистент Tacticum",
                    "PLACEHOLDER" => "Введите ваш вопрос...",
                    "INITIAL_USER_MESSAGE" => "Как понять, какой продукт Tacticum подходит для нашей задачи?",
                    "INTRO" => "Обычно выбор начинается с того, где находится задача:",
                    "INTRO_ITEMS" => [
                        "Platform - если нужен единый AI-контур, RAG, инструменты, доступы и аудит",
                        "Agents - если нужны ассистенты для HR, юридического, поддержки или базы знаний",
                        "Dev - если нужно управлять AI-assisted разработкой в инженерной команде",
                        "Forum - если важны управляемые клиентские диалоги и сценарии с LLM",
                    ],
                    "INTRO_OUTRO" => "Если продуктовый вход пока не очевиден, можно начать с расчета, discovery или короткой архитектурной сессии.",
                ],
                false
            );
            ?>
        </div>
    </div>
</section>
