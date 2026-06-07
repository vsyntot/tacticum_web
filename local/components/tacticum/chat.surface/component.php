<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$variant = strtolower(trim((string)($arParams['VARIANT'] ?? 'light')));
if (!in_array($variant, ['hero', 'light'], true)) {
    $variant = 'light';
}

$normalizeText = static function ($value): string {
    if (is_array($value)) {
        $value = reset($value);
    }

    $text = trim((string)$value);
    if ($text !== '' && class_exists('\Tacticum\Content\PublicCopyNormalizer')) {
        $text = \Tacticum\Content\PublicCopyNormalizer::normalizeString($text);
    }

    return $text;
};

$normalizeList = static function ($value) use ($normalizeText): array {
    if (!is_array($value)) {
        $value = preg_split('/\r\n|\r|\n|,/', (string)$value) ?: [];
    }

    $items = [];
    foreach ($value as $item) {
        $item = $normalizeText($item);
        if ($item !== '') {
            $items[] = $item;
        }
    }

    return array_values(array_unique($items));
};

$surface = (string)preg_replace('/[^a-z0-9_-]+/i', '', $normalizeText($arParams['SURFACE'] ?? ''));
if ($surface === '') {
    $surface = $variant === 'hero' ? 'hero' : 'calculator';
}

$title = $normalizeText($arParams['TITLE'] ?? '');
if ($title === '') {
    $title = $variant === 'hero' ? 'AI-ассистент Tacticum' : 'AI-калькулятор Tacticum';
}

$intro = $normalizeText($arParams['INTRO'] ?? '');
if ($intro === '') {
    $intro = $variant === 'hero'
        ? 'Искусственный интеллект может значительно оптимизировать ваши бизнес-процессы через:'
        : 'Здравствуйте! Я ИИ-ассистент Tacticum. Опишите вашу задачу, и я помогу оценить необходимые ресурсы, состав команды и примерный бюджет.';
}

$placeholder = $normalizeText($arParams['PLACEHOLDER'] ?? '');
if ($placeholder === '') {
    $placeholder = $variant === 'hero' ? 'Введите ваш вопрос...' : 'Опишите вашу задачу...';
}

$quickReplies = $normalizeList($arParams['QUICK_REPLIES'] ?? []);
if ($variant === 'light' && $quickReplies === []) {
    $quickReplies = [
        'Чат-бот',
        'Анализ данных',
        'Интеграция ИИ-агентов',
        'Мобильное приложение',
    ];
}

$heroIntroItems = $normalizeList($arParams['INTRO_ITEMS'] ?? []);
if ($variant === 'hero' && $heroIntroItems === []) {
    $heroIntroItems = [
        'Автоматизацию рутинных задач',
        'Предиктивную аналитику для прогнозирования трендов',
        'Интеллектуальную обработку документов',
        'Оптимизацию цепочек поставок',
        'Персонализацию клиентского опыта',
    ];
}

$heroUserMessage = $normalizeText($arParams['INITIAL_USER_MESSAGE'] ?? '');
if ($variant === 'hero' && $heroUserMessage === '') {
    $heroUserMessage = 'Как искусственный интеллект может помочь оптимизировать наши бизнес-процессы?';
}

$heroIntroOutro = $normalizeText($arParams['INTRO_OUTRO'] ?? '');
if ($variant === 'hero' && $heroIntroOutro === '') {
    $heroIntroOutro = 'Давайте обсудим, какие конкретные процессы в вашей компании требуют оптимизации?';
}

$rootClass = $normalizeText($arParams['ROOT_CLASS'] ?? '');
if ($rootClass === '') {
    $rootClass = $variant === 'hero' ? 'w-full md:w-1/2 relative' : 'ai-chat-container shadow-lg';
}

$arResult = [
    'VARIANT' => $variant,
    'SURFACE' => $surface,
    'TITLE' => $title,
    'INTRO' => $intro,
    'INTRO_ITEMS' => $heroIntroItems,
    'INTRO_OUTRO' => $heroIntroOutro,
    'INITIAL_USER_MESSAGE' => $heroUserMessage,
    'PLACEHOLDER' => $placeholder,
    'QUICK_REPLIES' => $quickReplies,
    'ROOT_CLASS' => $rootClass,
];

$this->IncludeComponentTemplate();
