<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

if (!function_exists('tacticum_product_page_render_rollout')) {
    function tacticum_product_page_render_rollout(array $rollout): void
    {
        $steps = is_array($rollout['steps'] ?? null) ? $rollout['steps'] : [];

        if (empty($steps)) {
            return;
        }

        $eyebrow = tacticum_product_page_string($rollout, 'eyebrow', 'Внедрение');
        $title = tacticum_product_page_string($rollout, 'title', 'Как внедряется продукт');
        $text = tacticum_product_page_string($rollout, 'text', 'Начинаем с ограниченного контура, проверяем пользу и ограничения, затем проектируем production-переход.');
        ?>
        <section class="bg-gray-50 py-16" data-product-block="rollout">
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
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <?php foreach ($steps as $index => $step): ?>
                        <?php
                        if (!is_array($step)) {
                            continue;
                        }

                        $stepTitle = tacticum_product_page_string($step, 'title');
                        $stepText = tacticum_product_page_string($step, 'text');
                        $stepMeta = tacticum_product_page_string($step, 'meta', str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT));
                        ?>
                        <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <?php if ($stepMeta !== ''): ?>
                                <p class="mb-4 text-sm font-semibold text-primary"><?=tacticum_product_page_html($stepMeta)?></p>
                            <?php endif; ?>
                            <?php if ($stepTitle !== ''): ?>
                                <h3 class="mb-3 text-xl font-bold text-secondary"><?=tacticum_product_page_html($stepTitle)?></h3>
                            <?php endif; ?>
                            <?php if ($stepText !== ''): ?>
                                <p class="text-gray-600"><?=tacticum_product_page_html($stepText)?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php
    }
}
