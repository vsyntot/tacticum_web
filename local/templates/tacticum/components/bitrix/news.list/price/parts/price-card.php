<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$icon = $icons[$section['NAME']] ?? 'ri-user-line';
$options = (array)($item['OPTIONS'] ?? []);
$levels = $item['LEVELS'] ?? [];
$levelKeys = array_keys($levels);
$rateIds = array_values(array_filter(array_map('intval', (array)($item['RATE_IDS'] ?? []))));
$rateCodes = array_values(array_filter(array_map('strval', (array)($item['RATE_CODES'] ?? []))));

$defaultLevel = 'Middle';
$selectedLevel = in_array($defaultLevel, $levelKeys) ? $defaultLevel : reset($levelKeys);
$selectedPrice = $levels[$selectedLevel]['PRICE'] ?? null;
$selectedPriceNum = (float)str_replace(',', '.', str_replace(' ', '', (string)$selectedPrice));
$selectedPriceFormatted = number_format($selectedPriceNum, 0, ',', ' ');
$isPopular = ($item['POPULAR']['VALUE_XML_ID'] ?? '') === 'popular' || ($item['POPULAR']['VALUE'] ?? '') === 'Да';
?>

<div class="pricing-card <?= $isPopular ? 'featured border-2' : 'border' ?> bg-white rounded-xl p-6 shadow-sm relative"
     data-price-card
     data-name="<?= tacticum_escape_iblock_text((string)$item['NAME']) ?>"
     data-category="<?= tacticum_escape_iblock_text((string)$section['NAME']) ?>"
     data-rate-ids="<?= htmlspecialcharsbx(implode(',', $rateIds)) ?>"
     data-rate-codes="<?= htmlspecialcharsbx(implode(',', $rateCodes)) ?>"
     data-level="<?= tacticum_escape_iblock_text((string)$selectedLevel) ?>"
     data-price="<?= htmlspecialcharsbx((string)$selectedPriceNum) ?>"
     data-popular="<?= $isPopular ? '1' : '0' ?>">
    <div class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
        <i class="<?= $icon ?> text-3xl text-primary"></i>
    </div>
    <h3 class="text-xl font-bold text-secondary mb-2"><?= tacticum_escape_iblock_text((string)$item['NAME']) ?></h3>
    <?php if (count($levels) > 1): ?>
        <div class="mb-4">
            <p class="text-sm font-medium text-gray-500 mb-2">Уровень</p>
            <div class="price-level-group flex flex-wrap gap-2" data-price-level-options role="group" aria-label="Уровень специалиста">
                <?php foreach ($levelKeys as $levelKey): ?>
                    <button type="button"
                            data-price-level-option
                            data-level="<?= tacticum_escape_iblock_text((string)$levelKey) ?>"
                            data-price="<?= htmlspecialcharsbx((string)($levels[$levelKey]['PRICE'] ?? '')) ?>"
                            data-active="<?= $levelKey === $selectedLevel ? 'true' : 'false' ?>"
                            aria-pressed="<?= $levelKey === $selectedLevel ? 'true' : 'false' ?>"
                            class="price-level-option inline-flex min-h-10 min-w-[5.5rem] flex-1 items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:border-primary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary/30 transition-colors">
                        <?= tacticum_escape_iblock_text((string)$levelKey) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php elseif (!empty($selectedLevel)): ?>
        <div class="mb-4">
            <span class="inline-flex rounded-lg bg-gray-50 px-3 py-2 text-sm font-medium text-gray-600">
                <?= tacticum_escape_iblock_text((string)$selectedLevel) ?>
            </span>
        </div>
    <?php endif; ?>
    <div class="text-2xl font-bold text-primary mb-4 price-value" data-price-value>
        от <?= $selectedPriceFormatted ?> ₽
        <span class="text-sm text-gray-500 font-normal">/час</span>
    </div>
    <?php if (!empty($options)): ?>
        <ul class="space-y-2 mb-6 text-gray-600">
            <?php foreach ($options as $opt): ?>
                <li class="flex items-start gap-2">
                    <i class="ri-checkbox-circle-line text-green-500 mt-1"></i>
                    <span><?= tacticum_escape_iblock_text((string)$opt) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <button type="button"
            data-price-order
            class="order-specialist-btn w-full <?= $isPopular ? 'bg-primary text-white' : 'bg-white border border-primary text-primary' ?> px-6 py-3 rounded-button hover:bg-primary hover:text-white transition-colors whitespace-nowrap">
        Добавить в заявку
    </button>
    <script type="application/json" class="level-prices-json" data-price-levels>
        <?= json_encode(array_map(fn($arr) => $arr['PRICE'], $levels), JSON_UNESCAPED_UNICODE) ?>
    </script>
</div>
