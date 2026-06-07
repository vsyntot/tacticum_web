<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div id="calculator">
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <?php
                    $APPLICATION->IncludeComponent(
                        "tacticum:chat.surface",
                        "",
                        [
                            "VARIANT" => "light",
                            "SURFACE" => "price",
                            "ROOT_CLASS" => "ai-chat-container shadow-lg",
                            "TITLE" => "AI-калькулятор Tacticum",
                            "INTRO" => "Здравствуйте! Опишите задачу, текущий этап и ограничения. Я помогу наметить состав команды, роли и ориентир бюджета, а точный план уточнит специалист Tacticum.",
                            "PLACEHOLDER" => "Опишите вашу задачу...",
                            "QUICK_REPLIES" => [
                                "Предпроектная оценка Platform",
                                "Пилот Agents",
                                "Процесс Dev",
                                "Запуск Forum",
                            ],
                        ],
                        false
                    );
                    ?>
                </div>

                <div class="w-full md:w-1/2">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/ai.jpg"
                         width="608" height="512" alt="AI-калькулятор" loading="lazy" decoding="async" class="w-full h-auto rounded-xl shadow-lg object-cover object-top">
                </div>
            </div>
        </div>
    </section>
</div>
