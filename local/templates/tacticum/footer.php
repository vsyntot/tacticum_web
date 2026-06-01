<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<div id="footer">
    <footer class="bg-secondary text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-12">
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
                "CHILD_MENU_TYPE" => "left",
                "USE_EXT" => "N",
                "DELAY" => "N",
                "ALLOW_MULTI_SELECT" => "N"
        ],
        false
);
?>

<?
$APPLICATION->IncludeComponent(
        "tacticum:contact.modal",
        "",
        [
                "FORM_ID" => "contact-modal",
        ],
        false
);
?>

</body>
</html>
