<?
$GLOBALS['TACTICUM_BODY_CLASS'] = 'bg-gray-50 font-sans';

include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/urlrewrite.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

CHTTP::SetStatus('404 Not Found');
@define('ERROR_404', 'Y');

if (!headers_sent()) {
    header('X-Robots-Tag: noindex, nofollow', true);
}

$APPLICATION->SetTitle('Страница не найдена - Тактикум');
$APPLICATION->SetPageProperty('description', 'Запрошенная страница не найдена. Перейдите на главную страницу, к услугам, тарифам, AI-калькулятору или контактам Tacticum.');
$APPLICATION->AddHeadString('<meta name="robots" content="noindex,nofollow">', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
?>

<main class="pt-24 bg-gray-50">
    <section class="py-20 md:py-28 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-primary mb-4">404</p>
                <h1 class="text-3xl md:text-5xl font-bold text-secondary mb-6">Страница не найдена</h1>
                <p class="text-lg text-gray-600 mb-8">
                    Возможно, ссылка устарела или адрес набран с ошибкой. Выберите один из рабочих разделов сайта.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="/" class="inline-flex w-full sm:w-auto items-center justify-center rounded-button bg-primary px-6 py-3 text-sm font-medium text-white hover:bg-primary/90 transition-colors">
                        На главную
                    </a>
                    <a href="/calculator/" class="inline-flex w-full sm:w-auto items-center justify-center rounded-button border border-primary px-6 py-3 text-sm font-medium text-primary hover:bg-primary hover:text-white transition-colors">
                        Открыть AI-калькулятор
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-5xl mx-auto">
                <a href="/services/" class="block rounded-lg border border-gray-100 bg-white p-6 shadow-sm hover:border-primary/40 transition-colors">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary mb-4">
                        <i class="ri-service-line"></i>
                    </span>
                    <span class="block text-lg font-semibold text-secondary mb-2">Услуги</span>
                    <span class="block text-sm text-gray-600">AI/ML-разработка, автоматизация и внедрение решений.</span>
                </a>
                <a href="/price/" class="block rounded-lg border border-gray-100 bg-white p-6 shadow-sm hover:border-primary/40 transition-colors">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary mb-4">
                        <i class="ri-price-tag-3-line"></i>
                    </span>
                    <span class="block text-lg font-semibold text-secondary mb-2">Тарифы</span>
                    <span class="block text-sm text-gray-600">Ставки специалистов и быстрые пресеты команды.</span>
                </a>
                <a href="/contacts/" class="block rounded-lg border border-gray-100 bg-white p-6 shadow-sm hover:border-primary/40 transition-colors">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary mb-4">
                        <i class="ri-mail-line"></i>
                    </span>
                    <span class="block text-lg font-semibold text-secondary mb-2">Контакты</span>
                    <span class="block text-sm text-gray-600">Свяжитесь с командой, чтобы обсудить проект.</span>
                </a>
            </div>
        </div>
    </section>
</main>

<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');?>
