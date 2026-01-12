<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-4">Ставки специалистов по категориям</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Выберите нужную категорию специалистов или воспользуйтесь фильтром для поиска конкретной позиции
            </p>
        </div>
        <!-- Filter Tabs -->
        <div class="flex flex-wrap justify-center gap-4 mb-12">
            <button data-category="all"
                    class="filter-tab bg-primary text-white px-6 py-3 rounded-button hover:bg-primary/90 transition-colors whitespace-nowrap">
                Все специалисты
            </button>
            <?php foreach ($arResult['GROUPED_SECTIONS'] as $section): ?>
                <button data-category="<?= htmlspecialchars($section['NAME']) ?>"
                        class="filter-tab bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-button hover:bg-gray-50 transition-colors whitespace-nowrap">
                    <?= htmlspecialchars($section['NAME']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Only Search -->
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="ri-search-line text-gray-400"></i>
                </div>
                <input type="text" placeholder="Поиск по специальности..."
                       id="specialist-search"
                       class="search-input w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/50 text-gray-700">
            </div>
        </div>

        <?php
        $icons = [
            'Аналитика' => 'ri-file-chart-line',
            'Разработка' => 'ri-code-s-slash-line',
            'DevOps/Инфраструктура' => 'ri-server-line',
            'Тестирование и качество' => 'ri-bug-line',
            'Прочие специалисты' => 'ri-user-line',
        ];
        ?>

        <?php foreach ($arResult['GROUPED_SECTIONS'] as $section): ?>
            <?php if (empty($section['GROUPED_ITEMS'])) continue; ?>

            <h3 class="text-2xl font-bold text-secondary mb-6 section-title"><?= htmlspecialchars($section['NAME']) ?></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= (count($section['GROUPED_ITEMS']) > 3 ? 4 : 3) ?> gap-8 mb-16">
                <?php foreach ($section['GROUPED_ITEMS'] as $item):
                    $icon = $icons[$section['NAME']] ?? 'ri-user-line';
                    $options = (array)($item['OPTIONS'] ?? []);
                    $levels = $item['LEVELS'] ?? [];
                    $levelKeys = array_keys($levels);

                    // По умолчанию Middle, если есть, иначе первый
                    $defaultLevel = 'Middle';
                    $selectedLevel = in_array($defaultLevel, $levelKeys) ? $defaultLevel : reset($levelKeys);
                    $selectedPrice = $levels[$selectedLevel]['PRICE'] ?? null;
                    $isPopular = ($item['POPULAR']['VALUE_XML_ID'] ?? '') === 'popular' || ($item['POPULAR']['VALUE'] ?? '') === 'Да';
                    ?>
                    <div class="pricing-card <?= $isPopular ? 'featured border-2' : 'border' ?> bg-white rounded-xl p-6 shadow-sm relative"
                         data-name="<?= htmlspecialchars($item['NAME']) ?>"
                         data-category="<?= htmlspecialchars($section['NAME']) ?>"
                         data-popular="<?= $isPopular ? '1' : '0' ?>">
                        <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <i class="<?= $icon ?> text-3xl text-primary"></i>
                        </div>
                        <h3 class="text-xl font-bold text-secondary mb-2"><?= htmlspecialchars($item['NAME']) ?></h3>
                        <?php if (count($levels) > 1): ?>
                            <div class="mb-4">
                                <select class="level-select w-full border border-gray-200 rounded-lg px-3 py-2 focus:outline-none">
                                    <?php foreach ($levelKeys as $levelKey): ?>
                                        <option value="<?= htmlspecialchars($levelKey) ?>"
                                                data-price="<?= htmlspecialchars($levels[$levelKey]['PRICE']) ?>"
                                            <?= $levelKey === $selectedLevel ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($levelKey) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div class="text-2xl font-bold text-primary mb-4 price-value">
                            от <?= number_format((float)str_replace(',', '.', str_replace(' ', '', $selectedPrice)), 0, ',', ' ') ?> ₽
                            <span class="text-sm text-gray-500 font-normal">/час</span>
                        </div>
                        <?php if (!empty($options)): ?>
                            <ul class="space-y-2 mb-6 text-gray-600">
                                <?php foreach ($options as $opt): ?>
                                    <li class="flex items-start gap-2">
                                        <i class="ri-checkbox-circle-line text-green-500 mt-1"></i>
                                        <span><?= htmlspecialchars($opt) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <button class="order-specialist-btn w-full <?= $isPopular ? 'bg-primary text-white' : 'bg-white border border-primary text-primary' ?> px-6 py-3 rounded-button hover:bg-primary hover:text-white transition-colors whitespace-nowrap">
                            Заказать специалиста
                        </button>
                        <script type="application/json" class="level-prices-json">
                            <?= json_encode(array_map(fn($arr) => $arr['PRICE'], $levels), JSON_UNESCAPED_UNICODE) ?>
                        </script>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
