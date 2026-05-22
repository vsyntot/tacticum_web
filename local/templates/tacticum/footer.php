<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<!-- Footer -->
<div id="footer">
    <footer class="bg-secondary text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="flex items-center gap-2 text-2xl font-bold mb-6">
                        <a href="/"><img src="<?=SITE_TEMPLATE_PATH?>/images/logo2.png" width="181" height="50" alt="Tacticum"></a>
                    </div>
                    <p class="text-white/70 mb-6">
                        Помогаем бизнесу внедрять искусственный интеллект для решения
                        реальных задач и достижения измеримых результатов.
                    </p>
                    <div class="flex items-center gap-4">
                        <a href="https://t.me/Tacticum_official_bot" target="_blank" rel="noopener" data-tacticum-tg-resolve class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" aria-label="Telegram"><i class="ri-telegram-fill"></i></a>
                        <?/*<a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors"><i class="ri-vk-fill"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors"><i class="ri-youtube-fill"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors"><i class="ri-linkedin-fill"></i></a>*/?>
                    </div>
                </div>
                <?
                $APPLICATION->IncludeComponent(
                        "bitrix:menu",
                        "bottommenu",
                        [
                                "COMPONENT_TEMPLATE" => ".default",
                                "ROOT_MENU_TYPE" => "bottom",
                                "MENU_CACHE_TYPE" => "A",
                                "MENU_CACHE_TIME" => "3600",
                                "MENU_CACHE_USE_GROUPS" => "Y",
                                "MENU_CACHE_GET_VARS" => [],
                                "MAX_LEVEL" => "1",
                                "CHILD_MENU_TYPE" => "left",
                                "USE_EXT" => "N",
                                "DELAY" => "N",
                                "ALLOW_MULTI_SELECT" => "N"
                        ],
                        false
                );
                ?>
                <div>
                    <h3 class="text-lg font-bold mb-6">Контакты</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <i class="ri-map-pin-line mt-1"></i>
                            <span class="text-white/70">119285, г. Москва, Вн.Тер.г. Муниципальный округ Раменки, Км Мжд Киевское 5-й, д. 1 стр. 1, помещ. 3/3</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="ri-phone-line"></i>
                            <a href="tel:+74955612084" class="text-white/70 hover:text-white transition-colors">+7 (495) 561-20-84</a>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="ri-mail-line"></i>
                            <a href="mailto:project@tacticum.ru" class="text-white/70 hover:text-white transition-colors">project@tacticum.ru</a>
                        </li>
                    </ul>
                    <div class="mt-6 space-y-3">
                        <div class="flex items-start gap-3">
                            <i class="ri-file-list-line mt-1"></i>
                            <div class="text-white/70">
                                <p>ООО "Тактикум"</p>
                                <p>ИНН: 9722028080</p>
                                <p>КПП: 772901001</p>
                                <p>ОГРН: 1227700525942</p>
                                <p>ОКВЭД: 62.01 Разработка компьютерного программного обеспечения</p>
                                <p class="mt-3">
                                    Вид деятельности в области информационных технологий по приказу Минцифры N 449:
                                    1.01 Проектирование, обследование, разработка, адаптация, модификация,
                                    интеграция, внедрение, сопровождение, тестирование и техническая поддержка
                                    программ для ЭВМ, баз данных и визуальных пользовательских интерфейсов
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-white/50 text-sm">&copy; 2022 - <?=date("Y")?> Tacticum. Все права защищены.</p>
                <div class="flex items-center gap-6">
                    <a href="/policies/" target="_blank" rel="noopener" class="text-white/50 text-sm hover:text-white transition-colors">Политика конфиденциальности</a>
                    <?/*<a href="#" class="text-white/50 text-sm hover:text-white transition-colors">Условия использования</a>
                    <a href="#" class="text-white/50 text-sm hover:text-white transition-colors">Карта сайта</a>*/?>
                </div>
            </div>
        </div>
    </footer>
</div>

<?
$APPLICATION->IncludeComponent(
        "bitrix:menu",
        "mobilemenu",
        [
                "COMPONENT_TEMPLATE" => "mobilemenu",
                "ROOT_MENU_TYPE" => "top",
                "MENU_CACHE_TYPE" => "A",
                "MENU_CACHE_TIME" => "3600",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "MENU_CACHE_GET_VARS" => [],
                "MAX_LEVEL" => "2",
                "CHILD_MENU_TYPE" => "top",
                "USE_EXT" => "Y",
                "DELAY" => "N",
                "ALLOW_MULTI_SELECT" => "N"
        ],
        false
);
?>

<!-- Попап "Связаться с нами" — обновлённый -->
<div id="tacticum-modal" class="fixed inset-0 z-[999] hidden" role="dialog" aria-modal="true" aria-labelledby="tacticum-modal-title">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>

    <div class="tacticum-contact-modal-shell relative mx-auto my-6 w-[min(92vw,760px)]">
        <div class="tacticum-contact-modal-card bg-white rounded-lg shadow-2xl overflow-hidden">
            <div class="tacticum-contact-modal-header bg-secondary text-white px-6 md:px-7 py-5 flex items-center justify-between">
                <div>
                    <h2 id="tacticum-modal-title" class="text-xl md:text-2xl font-bold">Связаться с нами</h2>
                    <p class="text-white/70 text-sm mt-1">Оценим задачу и подскажем лучший вариант запуска</p>
                </div>
                <button id="tacticum-modal-close" class="tacticum-contact-modal-close shrink-0 w-10 h-10 rounded-lg hover:bg-white/10 flex items-center justify-center" aria-label="Закрыть диалог">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>

            <form id="tacticum-modal-form" class="tacticum-contact-form px-6 md:px-7 py-6" data-tacticum-form data-form-id="contact-modal" data-tacticum-close-target="#tacticum-modal" data-tacticum-close-mode="hidden">
                <div class="tacticum-contact-form__grid grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="tacticum-contact-form__field">
                        <label for="modal-name" class="tacticum-contact-form__label block text-sm text-gray-700 mb-1">Имя <span class="tacticum-contact-form__required text-primary">*</span></label>
                        <input id="modal-name" name="name" type="text" required autocomplete="name" placeholder="Иван"
                               class="tacticum-contact-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3 outline-none" />
                        <p class="mt-1 text-xs text-red-600 hidden" data-error="modal-name">Укажите имя</p>
                    </div>
                    <div class="tacticum-contact-form__field">
                        <label for="modal-company" class="tacticum-contact-form__label block text-sm text-gray-700 mb-1">Компания</label>
                        <input id="modal-company" name="company" type="text" autocomplete="organization" placeholder="Название компании"
                               class="tacticum-contact-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3 outline-none" />
                    </div>
                    <div class="tacticum-contact-form__field">
                        <label for="modal-email" class="tacticum-contact-form__label block text-sm text-gray-700 mb-1">Email <span class="tacticum-contact-form__required text-primary">*</span></label>
                        <input id="modal-email" name="email" type="email" required autocomplete="email" placeholder="mail@example.com"
                               class="tacticum-contact-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3 outline-none" />
                        <p class="mt-1 text-xs text-red-600 hidden" data-error="modal-email">Укажите корректный email</p>
                    </div>
                    <div class="tacticum-contact-form__field">
                        <label for="modal-phone" class="tacticum-contact-form__label block text-sm text-gray-700 mb-1">Телефон <span class="tacticum-contact-form__required text-primary">*</span></label>
                        <input id="modal-phone" name="phone" type="tel" required autocomplete="tel" placeholder="+7 999 000-00-00"
                               class="tacticum-contact-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3 outline-none" />
                        <p class="mt-1 text-xs text-red-600 hidden" data-error="modal-phone">Укажите телефон</p>
                    </div>
                </div>

                <div class="tacticum-contact-form__field">
                    <label for="modal-message" class="tacticum-contact-form__label block text-sm text-gray-700 mb-1">Опишите проект или задачу <span class="tacticum-contact-form__required text-primary">*</span></label>
                    <textarea id="modal-message" name="message" rows="4" required placeholder="Кратко опишите задачу, сроки и ожидаемый результат"
                              class="tacticum-contact-form__control w-full rounded-lg border border-gray-300 bg-white px-4 py-3 outline-none"></textarea>
                </div>

                <div class="tacticum-contact-form__consent flex items-start gap-3">
                    <input id="modal-agreement" type="checkbox" required data-tacticum-consent
                           class="tacticum-contact-form__checkbox mt-1 w-4 h-4">
                    <label for="modal-agreement" class="tacticum-contact-form__consent-text text-sm text-gray-600">
                        Я согласен на обработку персональных данных и принимаю условия
                        <a href="/policies/" target="_blank" rel="noopener" class="text-primary hover:underline">политики конфиденциальности</a>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="tacticum-contact-form__submit w-full bg-primary text-white font-semibold px-6 py-3 rounded-lg hover:bg-primary/90 transition disabled:opacity-60 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 hidden" data-role="spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span data-role="btn-text">Отправить заявку</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

</body>
</html>
