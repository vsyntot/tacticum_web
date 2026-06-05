<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<section class="pb-16">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl p-8 shadow-sm">
            <div class="flex items-start gap-4 mb-6">
                <div class="w-14 h-14 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                    <i class="ri-file-list-line text-2xl text-primary"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-secondary mb-2">Реквизиты и ИТ-деятельность</h2>
                    <p class="text-gray-600">
                        Информация об организации и виде деятельности в области информационных технологий.
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-lg font-bold text-secondary mb-4">Организация</h3>
                    <dl class="space-y-3 text-gray-600">
                        <div>
                            <dt class="font-medium text-secondary">Наименование</dt>
                            <dd>ООО "Тактикум"</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-secondary">ИНН</dt>
                            <dd>9722028080</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-secondary">КПП</dt>
                            <dd>772901001</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-secondary">ОГРН</dt>
                            <dd>1227700525942</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-secondary">ОКВЭД</dt>
                            <dd>62.01 Разработка компьютерного программного обеспечения</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-secondary mb-4">Вид деятельности в области информационных технологий</h3>
                    <p class="text-gray-600 leading-relaxed">
                        По приказу Минцифры N 449: 1.01 Проектирование, обследование, разработка, адаптация,
                        модификация, интеграция, внедрение, сопровождение, тестирование и техническая поддержка
                        программ для ЭВМ, баз данных и визуальных пользовательских интерфейсов.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="map" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-secondary mb-8 text-center">Как нас найти</h2>
            <div class="rounded-t-2xl overflow-hidden shadow-md h-[450px] bg-gray-100">
                <iframe
                    src="<?=htmlspecialcharsbx($arResult['OFFICE_MAP_WIDGET_URL'])?>"
                    width="100%"
                    height="450"
                    frameborder="0"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Яндекс Карта: Тактикум"
                    class="block h-full w-full border-0"></iframe>
            </div>
            <div class="bg-white p-6 rounded-b-2xl shadow-md -mt-2 relative z-10">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="md:basis-1/2">
                        <h3 class="text-xl font-bold text-secondary mb-2">На карте</h3>
                        <p class="text-gray-700 font-medium mb-2">
                            <?=htmlspecialcharsbx($arResult['OFFICE_PLACE_NAME'])?>
                        </p>
                        <p class="text-gray-600 mb-3">
                            Офис находится в <?=htmlspecialcharsbx($arResult['OFFICE_LANDMARK_NAME'])?>. Для маршрута открывайте карточку
                            <?=htmlspecialcharsbx($arResult['OFFICE_PLACE_NAME'])?> в Яндекс Картах.
                        </p>
                        <p class="text-gray-600">
                            Юридический адрес: <?=htmlspecialcharsbx($arResult['OFFICE_ADDRESS'])?>
                        </p>
                        <a href="<?=htmlspecialcharsbx($arResult['OFFICE_MAP_URL'])?>" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-2 text-primary hover:text-primary/80 transition-colors">
                            Открыть в Яндекс Картах
                            <i class="ri-external-link-line"></i>
                        </a>
                    </div>
                    <div class="md:basis-1/2">
                        <h3 class="text-xl font-bold text-secondary mb-2">Часы работы</h3>
                        <p class="text-gray-600">Пн-Пт: 9:00 - 18:00</p>
                        <p class="text-gray-600">Сб-Вс: Выходной</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
