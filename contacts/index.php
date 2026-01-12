<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Контакты - Тактикум");
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

<!-- Contact Form Section -->
<div id="contact-form">
    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-secondary to-primary text-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="w-full md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">Получите персональное предложение</h2>
                    <p class="text-lg mb-8 text-blue-100">
                        Оставьте заявку, и наш менеджер свяжется с вами в течение 2 часов, чтобы обсудить детали и
                        подготовить индивидуальное предложение с учетом всех доступных скидок и акций.
                    </p>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="relative">
                                <input type="text" id="cta-name" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-name" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Имя</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="cta-company" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-company" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Компания</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="cta-email" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-email" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Email</label>
                            </div>
                            <div class="relative">
                                <input type="text" id="cta-phone" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30">
                                <label for="cta-phone" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Телефон</label>
                            </div>
                        </div>
                        <div class="relative mb-6">
                            <textarea id="cta-message" rows="4" placeholder=" " class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-3 text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-white/30"></textarea>
                            <label for="cta-message" class="absolute left-4 top-3 text-white/60 transition-transform origin-left">Опишите ваш проект или интересующее предложение</label>
                        </div>
                        <div class="flex items-start gap-2 mb-6">
                            <input type="checkbox" id="cta-agreement" class="mt-1 appearance-none w-4 h-4 border border-white/30 rounded bg-white/5 checked:bg-primary checked:border-0 relative">
                            <label for="cta-agreement" class="text-sm text-white/70">Я согласен на обработку персональных данных и принимаю условия <a href="#" class="underline hover:text-white">политики конфиденциальности</a></label>
                        </div>
                        <button class="w-full bg-white text-primary font-medium px-6 py-3 rounded-button hover:bg-white/90 transition-colors whitespace-nowrap">Получить персональное предложение</button>
                    </div>
                </div>
                <div class="w-full md:w-1/2">
                    <img src="<?=SITE_TEMPLATE_PATH?>/images/specialoffer.jpg" alt="Персональное предложение" class="w-full h-auto rounded-xl shadow-lg object-cover object-top">
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Map Section -->
<section id="map" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold text-secondary mb-8 text-center">Как нас найти</h2>
            <div class="rounded-2xl overflow-hidden shadow-md h-[450px] map-container">
                <script type="text/javascript" charset="utf-8" async
                        src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Ab1c999cbf0d3fb4a40fc7947d626e2e8f0ea5dfd4bdf88ff616e51b72b494676&amp;width=100%25&amp;height=450&amp;lang=ru_RU&amp;scroll=true"></script>
            </div>
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