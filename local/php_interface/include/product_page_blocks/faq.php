<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_render_faq')) {
    function tacticum_product_page_render_faq(array $faq): void
    {
        $items = is_array($faq['items'] ?? null) ? $faq['items'] : [];

        if (empty($items)) {
            return;
        }

        $title = tacticum_product_page_string($faq, 'title', 'Часто задаваемые вопросы');
        $text = tacticum_product_page_string($faq, 'text', 'Ответы на вопросы, которые обычно появляются перед пилотом и внедрением.');
        ?>
        <section class="bg-white py-16" data-product-block="faq">
            <div class="container mx-auto px-4">
                <div class="mb-12 text-center">
                    <h2 class="mb-4 text-3xl font-bold text-secondary md:text-4xl"><?=tacticum_product_page_html($title)?></h2>
                    <?php if ($text !== ''): ?>
                        <p class="mx-auto max-w-3xl text-lg text-gray-600"><?=tacticum_product_page_html($text)?></p>
                    <?php endif; ?>
                </div>
                <div class="mx-auto max-w-3xl">
                    <?php foreach ($items as $item): ?>
                        <?php
                        if (!is_array($item)) {
                            continue;
                        }

                        $question = tacticum_product_page_string($item, 'question');
                        $answer = tacticum_product_page_string($item, 'answer');

                        if ($question === '' || $answer === '') {
                            continue;
                        }
                        ?>
                        <div class="faq-item py-4">
                            <button type="button" class="faq-question flex w-full items-center justify-between gap-4 text-left">
                                <span class="text-xl font-medium text-secondary"><?=tacticum_product_page_html($question)?></span>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                    <i class="ri-add-line faq-icon text-primary"></i>
                                </span>
                            </button>
                            <div class="faq-answer mt-2 text-gray-600">
                                <p class="mt-2 leading-relaxed"><?=tacticum_product_page_html($answer)?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
