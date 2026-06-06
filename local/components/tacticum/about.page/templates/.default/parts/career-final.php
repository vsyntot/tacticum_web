<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<?php
if (function_exists('tacticum_page_content_render_if_live')) {
    tacticum_page_content_render_if_live('/about/', 'career-final');
}

// Fallback body retired after owner-approved page-content fallback retirement.
// Retired page-content fallback: tacticum_page_content_render_if_live('/about/', 'career-final').
?>

<section class="py-20 bg-gradient-to-r from-secondary to-primary text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Готовы начать сотрудничество?</h2>
            <p class="text-lg mb-8 text-blue-100">
                Свяжитесь с нами, чтобы обсудить, как искусственный интеллект и автоматизация могут помочь вашему
                бизнесу достичь новых высот.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a
                        class="inline-block bg-white text-primary px-8 py-3 rounded-button hover:bg-white/90 transition-colors whitespace-nowrap text-center"
                        href="/calculator/">
                    Познакомиться ближе
                </a>
                <a
                        class="inline-block bg-white/10 backdrop-blur-sm text-white border border-white/30 px-8 py-3 rounded-button hover:bg-white/20 transition-colors whitespace-nowrap text-center"
                        href="#contact-form">
                    Связаться с командой
                </a>
            </div>
        </div>
    </div>
</section>
