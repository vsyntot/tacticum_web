<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$officeAddress = '119285, г. Москва, Вн.Тер.г. Муниципальный округ Раменки, Км Мжд Киевское 5-й, д. 1 стр. 1, помещ. 3/3';
$officePlaceName = 'Тактикум';
$officeLandmarkName = 'БЦ Victory Park';
$officeMapObjectId = '243968538014';
$officeLatitude = '55.723957';
$officeLongitude = '37.503747';
$officeMapPoint = rawurlencode($officeLongitude . ',' . $officeLatitude);
$officeMapZoom = '17.13';
$officeMapUrl = 'https://yandex.ru/maps/org/taktikum/' . $officeMapObjectId . '/?ll=' . $officeMapPoint . '&z=' . $officeMapZoom;
$officeMapWidgetUrl = 'https://yandex.ru/map-widget/v1/?ll=' . $officeMapPoint . '&mode=search&oid=' . $officeMapObjectId . '&ol=biz&z=' . $officeMapZoom;

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

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_after.php");
?>

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

<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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

            <div
                class="bg-white rounded-xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full">
                <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6">
                    <i class="ri-map-pin-line text-3xl text-primary"></i>
                </div>
                <h3 class="text-xl font-bold text-secondary mb-3">Офис</h3>
                <p class="text-gray-700 font-medium mb-3">
                    <?=htmlspecialcharsbx($officePlaceName)?>, <?=htmlspecialcharsbx($officeLandmarkName)?>
                </p>
                <p class="text-gray-600 mb-6">Юридический адрес: <?=htmlspecialcharsbx($officeAddress)?></p>
                <div class="mt-auto">
                    <a href="#map" class="text-lg font-medium text-primary hover:text-primary/80 transition-colors flex items-center gap-2">Показать на карте<i class="ri-arrow-down-line"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$APPLICATION->IncludeComponent(
    "tacticum:lead.cta",
    "",
    [
        "TYPE" => "personal-offer",
        "FORM_ID" => "contacts-cta",
        "FORM_HTML_ID" => "contacts-cta-form",
        "VARIANT" => "glass",
        "TITLE" => "Расскажите, какой следующий шаг вам нужен",
        "TEXT" => "Можно начать с консультации, предварительной оценки, подбора команды или прототипа AI-бота. Опишите задачу, а мы направим обращение к нужному специалисту.",
        "MESSAGE_LABEL" => "Ваш вопрос или задача",
        "MESSAGE_PLACEHOLDER" => "Например: нужна оценка AI-проекта, команда на MVP или консультация по Telegram-боту",
        "BUTTON_TEXT" => "Отправить обращение",
        "LEAD_CONTEXT" => [
            "lead_entry" => "contacts",
            "lead_page_role" => "contact-entry",
            "lead_intent" => "route-request-to-next-step",
            "lead_cta" => "contacts-cta",
            "lead_next_step" => "request-routing",
        ],
    ],
    false
);
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
                    src="<?=htmlspecialcharsbx($officeMapWidgetUrl)?>"
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
                            <?=htmlspecialcharsbx($officePlaceName)?>
                        </p>
                        <p class="text-gray-600 mb-3">
                            Офис находится в <?=htmlspecialcharsbx($officeLandmarkName)?>. Для маршрута открывайте карточку
                            <?=htmlspecialcharsbx($officePlaceName)?> в Яндекс Картах.
                        </p>
                        <p class="text-gray-600">
                            Юридический адрес: <?=htmlspecialcharsbx($officeAddress)?>
                        </p>
                        <a href="<?=htmlspecialcharsbx($officeMapUrl)?>" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-2 text-primary hover:text-primary/80 transition-colors">
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

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
