<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div id="calculator-root">
    <section id="calculator" class="py-20 bg-gradient-to-r from-secondary to-primary text-white" data-home-block="calculator-preview">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">
                        Хотите понять бюджет до старта разработки?
                    </h2>
                    <p class="text-lg mb-8 text-blue-100">
                        AI-калькулятор собирает вводные и готовит предварительный артефакт: диапазон бюджета,
                        сроки, роли в команде, ключевые риски и следующий шаг для точной сметы.
                    </p>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 mb-6">
                        <div class="flex flex-col h-[400px]">
                            <div class="flex-1 overflow-y-auto mb-4 space-y-4" id="chatMessages" role="log" aria-live="polite" aria-label="Сообщения AI-калькулятора">
                                <div class="bg-white/5 rounded-lg p-3">
                                    <p class="text-sm text-white/70 mb-1">AI-ассистент:</p>
                                    <p class="text-white">
                                        Здравствуйте! Я помогу оценить ваш AI-проект. Расскажите,
                                        какую задачу вы хотите решить?
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" id="userMessage" aria-label="Сообщение для AI-калькулятора" placeholder="Введите сообщение..." class="flex-1 bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/30"/>
                                <button id="sendMessage" class="bg-white text-primary w-12 h-12 rounded-full flex items-center justify-center hover:bg-white/90 transition-colors" aria-label="Отправить сообщение AI-калькулятору">
                                    <i class="ri-send-plane-fill" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="w-full md:w-1/2">
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6 shadow-lg">
                        <h3 class="text-xl font-bold mb-6">Примеры оценок проектов</h3>
                        <div class="space-y-6">
                            <div class="bg-white/10 rounded-lg p-4">
                                <h4 class="font-bold mb-2">Система предиктивного обслуживания оборудования</h4>
                                <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                    <div>
                                        <p class="text-white/60">Отрасль:</p>
                                        <p>Производство</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сложность:</p>
                                        <p>Средняя</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сроки:</p>
                                        <p>3-4 месяца</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Команда:</p>
                                        <p>5 специалистов</p>
                                    </div>
                                </div>
                                <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden" role="progressbar" aria-label="Ориентировочная сложность проекта" aria-valuemin="0" aria-valuemax="100" aria-valuenow="65">
                                    <div class="bg-primary h-full tacticum-progress-bar--65"></div>
                                </div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-4">
                                <h4 class="font-bold mb-2">Чат-бот с AI для клиентской поддержки</h4>
                                <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                    <div>
                                        <p class="text-white/60">Отрасль:</p>
                                        <p>Электронная коммерция</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сложность:</p>
                                        <p>Низкая</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сроки:</p>
                                        <p>1-2 месяца</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Команда:</p>
                                        <p>3 специалиста</p>
                                    </div>
                                </div>
                                <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden" role="progressbar" aria-label="Ориентировочная сложность проекта" aria-valuemin="0" aria-valuemax="100" aria-valuenow="35">
                                    <div class="bg-primary h-full tacticum-progress-bar--35"></div>
                                </div>
                            </div>
                            <div class="bg-white/10 rounded-lg p-4">
                                <h4 class="font-bold mb-2">
                                    Система компьютерного зрения для контроля качества
                                </h4>
                                <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                    <div>
                                        <p class="text-white/60">Отрасль:</p>
                                        <p>Автомобилестроение</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сложность:</p>
                                        <p>Высокая</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Сроки:</p>
                                        <p>5-6 месяцев</p>
                                    </div>
                                    <div>
                                        <p class="text-white/60">Команда:</p>
                                        <p>7 специалистов</p>
                                    </div>
                                </div>
                                <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden" role="progressbar" aria-label="Ориентировочная сложность проекта" aria-valuemin="0" aria-valuemax="100" aria-valuenow="85">
                                    <div class="bg-primary h-full tacticum-progress-bar--85"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
