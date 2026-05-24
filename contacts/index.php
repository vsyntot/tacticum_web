<?
$GLOBALS['TACTICUM_PAGE_ASSETS'] = ['yandex_map'];
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Контакты Tacticum - AI-разработка и консалтинг");
$APPLICATION->SetPageProperty("description", "Контакты Tacticum: телефон, email, адрес офиса и форма заявки на консультацию по AI-проекту.");
tacticum_apply_seo_defaults('/contacts/', [
    'schema' => [
        '@type' => 'ContactPage',
        '@id' => tacticum_public_url('/contacts/#contact-page'),
        'name' => 'Контакты Tacticum',
        'url' => tacticum_public_url('/contacts/'),
        'mainEntity' => [
            '@id' => tacticum_public_url('/#organization'),
        ],
    ],
]);
?>

<!-- Page Title Section -->
<section class="pt-32 pb-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-secondary mb-4">Контакты</h1>
            <p class="text-lg text-gray-600">
                Свяжитесь с нами для обсуждения вашего проекта или получения
                дополнительной информации о наших услугах
            </p>
        </div>
    </div>
</section>

<!-- Contact Info Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Contact Card -->
            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-phone-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Телефон</h3>
                <p class="text-gray-600 mb-6">
                    Мы доступны с понедельника по пятницу с 9:00 до 18:00 по
                    московскому времени
                </p>
                <div class="mt-auto">
                    <a href="tel:+74955612084" class="text-lg font-medium text-primary hover:text-primary/80 transition-colors flex items-center gap-2">+7 (495) 561-20-84<i class="ri-external-link-line"></i></a>
                </div>
            </div>

            <!-- Email Card -->
            <div class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-mail-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Email</h3>
                <p class="text-gray-600 mb-6">Отправьте нам email, и мы ответим вам в течение 24 часов в рабочие дни</p>
                <div class="mt-auto">
                    <a href="mailto:project@tacticum.ru" class="text-lg font-medium text-primary hover:text-primary/80 transition-colors flex items-center gap-2">project@tacticum.ru<i class="ri-external-link-line"></i></a>
                </div>
            </div>

            <!-- Address Card -->
            <div
                class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-map-pin-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Адрес</h3>
                <p class="text-gray-600 mb-6">119285, г. Москва, Вн.Тер.г. Муниципальный округ Раменки, Км Мжд Киевское 5-й, д. 1 стр. 1 , помещ. 3/3</p>
                <div class="mt-auto">
                    <a href="#map" class="text-lg font-medium text-primary hover:text-primary/80 transition-colors flex items-center gap-2">Показать на карте<i class="ri-arrow-down-line"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Legal Info Section -->
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

<?php
$tacticumPersonalOfferCta = [
    "form_id" => "contacts-cta",
    "form_html_id" => "contacts-cta-form",
    "variant" => "glass",
];
include $_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/include/personal-offer-cta.php";
?>

<!-- Map Section -->
<section id="map" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-secondary mb-8 text-center">Как нас найти</h2>
            <div class="rounded-2xl overflow-hidden shadow-md h-[450px] map-container"
                 data-yandex-constructor-map
                 data-yandex-constructor-src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ab1c999cbf0d3fb4a40fc7947d626e2e8f0ea5dfd4bdf88ff616e51b72b494676&amp;width=100%25&amp;height=450&amp;lang=ru_RU&amp;scroll=true"></div>
            <div class="bg-white p-6 rounded-b-2xl shadow-md -mt-2 relative z-10">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="md:basis-1/2">
                        <h3 class="text-xl font-bold text-secondary mb-2">Наш офис</h3>
                        <p class="text-gray-600">
                            119285, г. Москва, Вн.Тер.г. Муниципальный округ Раменки, Км Мжд Киевское 5-й, д. 1 стр. 1 , помещ. 3/3
                        </p>
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

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
