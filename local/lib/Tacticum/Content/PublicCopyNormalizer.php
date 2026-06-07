<?php

declare(strict_types=1);

namespace Tacticum\Content;

final class PublicCopyNormalizer
{
    private const REPLACEMENTS = [
        'Product fit' => 'Когда подходит продукт',
        'Use cases' => 'Сценарии применения',
        'Security / procurement' => 'Безопасность и закупка',
        'Что не обещаем без assessment' => 'Что не обещаем без предварительной проверки',
        'Ожидается готовое обещание deployment-модели без assessment и security-проверки.' => 'Ожидается готовое обещание модели размещения без предпроектной оценки и проверки безопасности.',
        'Что уточняется перед выбором deployment-модели?' => 'Что уточняется перед выбором модели размещения?',
        'Не публикуем обещания про сокращение команды, проценты ускорения, снижение ошибок или универсальные quality gates без пилотной evidence.' => 'Не публикуем обещания про сокращение команды, проценты ускорения, снижение ошибок или универсальные quality gates без пилотных подтверждений.',
        'Delivery layer' => 'Слой внедрения',
        'Product workstreams' => 'Команда под этап внедрения',
        'Product-aware estimate' => 'Оценка с учетом продукта',
        'Vendor trust' => 'Доверие к команде',
        'Proof layer' => 'Слой подтверждений',
        'Platform assessment' => 'Предпроектная оценка Platform',
        'Agents pilot' => 'Пилот Agents',
        'Dev workflow' => 'Процесс Dev',
        'Forum launch' => 'Запуск Forum',
        'Platform team' => 'Команда Platform',
        'Platform examples' => 'Примеры Platform',
        'Agents examples' => 'Примеры Agents',
        'Dev examples' => 'Примеры Dev',
        'Forum examples' => 'Примеры Forum',
    ];

    public static function normalizeArray(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = self::normalizeArray($value);
                continue;
            }
            if (is_string($value)) {
                $payload[$key] = self::normalizeString($value);
            }
        }

        return $payload;
    }

    public static function normalizeString(string $value): string
    {
        return str_replace(array_keys(self::REPLACEMENTS), array_values(self::REPLACEMENTS), $value);
    }
}
