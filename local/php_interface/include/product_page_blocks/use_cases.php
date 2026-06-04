<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_render_use_cases')) {
    function tacticum_product_page_render_use_cases(array $useCases): void
    {
        $items = is_array($useCases['items'] ?? null) ? $useCases['items'] : [];

        if (empty($items)) {
            return;
        }

        $eyebrow = tacticum_product_page_string($useCases, 'eyebrow', 'Use cases');
        $title = tacticum_product_page_string($useCases, 'title', 'Какие сценарии проверять первыми');
        $text = tacticum_product_page_string($useCases, 'text', 'Каждый сценарий лучше начинать как ограниченный пилот: с понятным триггером, владельцем, входными данными, выходным артефактом и явными ограничениями.');
        ?>
        <section class="bg-gray-50 py-16" data-product-block="use-cases">
            <div class="container mx-auto px-4">
                <div class="mb-10 max-w-3xl">
                    <?php if ($eyebrow !== ''): ?>
                        <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-primary"><?=tacticum_product_page_html($eyebrow)?></p>
                    <?php endif; ?>
                    <?php if ($title !== ''): ?>
                        <h2 class="mb-4 text-3xl font-bold text-secondary md:text-4xl"><?=tacticum_product_page_html($title)?></h2>
                    <?php endif; ?>
                    <?php if ($text !== ''): ?>
                        <p class="text-lg text-gray-600"><?=tacticum_product_page_html($text)?></p>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <?php foreach ($items as $item): ?>
                        <?php
                        if (!is_array($item)) {
                            continue;
                        }

                        $itemTitle = tacticum_product_page_string($item, 'title');
                        $trigger = tacticum_product_page_string($item, 'trigger');
                        $owner = tacticum_product_page_string($item, 'owner');
                        $pilotInput = tacticum_product_page_string($item, 'pilot_input');
                        $pilotOutput = tacticum_product_page_string($item, 'pilot_output');
                        $limitation = tacticum_product_page_string($item, 'limitation');
                        ?>
                        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <?php if ($itemTitle !== ''): ?>
                                <h3 class="mb-3 text-xl font-bold text-secondary"><?=tacticum_product_page_html($itemTitle)?></h3>
                            <?php endif; ?>
                            <div class="mb-5">
                                <?php tacticum_product_page_render_proof_status($item, 'pilot-artifact'); ?>
                            </div>
                            <dl class="space-y-4 text-sm">
                                <?php if ($trigger !== ''): ?>
                                    <div>
                                        <dt class="font-semibold text-primary">Триггер</dt>
                                        <dd class="mt-1 text-gray-700"><?=tacticum_product_page_html($trigger)?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if ($owner !== ''): ?>
                                    <div>
                                        <dt class="font-semibold text-primary">Владелец</dt>
                                        <dd class="mt-1 text-gray-700"><?=tacticum_product_page_html($owner)?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if ($pilotInput !== ''): ?>
                                    <div>
                                        <dt class="font-semibold text-primary">Вход пилота</dt>
                                        <dd class="mt-1 text-gray-700"><?=tacticum_product_page_html($pilotInput)?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if ($pilotOutput !== ''): ?>
                                    <div>
                                        <dt class="font-semibold text-primary">Выход пилота</dt>
                                        <dd class="mt-1 text-gray-700"><?=tacticum_product_page_html($pilotOutput)?></dd>
                                    </div>
                                <?php endif; ?>
                                <?php if ($limitation !== ''): ?>
                                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                                        <dt class="font-semibold text-amber-700">Ограничение</dt>
                                        <dd class="mt-1 text-amber-800"><?=tacticum_product_page_html($limitation)?></dd>
                                    </div>
                                <?php endif; ?>
                            </dl>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
