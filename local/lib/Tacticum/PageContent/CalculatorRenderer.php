<?php

declare(strict_types=1);

namespace Tacticum\PageContent;

final class CalculatorRenderer
{
    public static function renderChatOutcome(array $section): bool
    {
        $items = RenderSupport::blocks($section);
        if ($items === []) {
            return false;
        }

        global $APPLICATION;
        if (!is_object($APPLICATION) || !method_exists($APPLICATION, 'IncludeComponent')) {
            return false;
        }

        echo RenderSupport::sectionOpen($section, 'py-32 bg-white') . PHP_EOL;
        echo '    <div class="container mx-auto px-4">' . PHP_EOL;
        self::renderHero();
        echo '        <div class="flex flex-col lg:flex-row items-center gap-12">' . PHP_EOL;
        echo '            <div class="w-full lg:w-1/2">' . PHP_EOL;
        self::renderChatSurface();
        echo '            </div>' . PHP_EOL;
        echo '            <div class="w-full lg:w-1/2">' . PHP_EOL;
        self::renderOutcomePanel($section, $items);
        echo '            </div>' . PHP_EOL;
        echo '        </div>' . PHP_EOL;
        echo '    </div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;

        return true;
    }

    private static function renderHero(): void
    {
        echo '        <div class="text-center mb-12">' . PHP_EOL;
        echo '            <h1 class="text-3xl md:text-4xl font-bold text-secondary mb-4">AI-калькулятор для предварительной оценки проекта</h1>' . PHP_EOL;
        echo '            <p class="text-lg text-gray-600 max-w-3xl mx-auto">Опишите задачу и получите черновой артефакт: бюджетный диапазон, сроки, состав команды, ключевые риски и понятный следующий шаг к точной смете.</p>' . PHP_EOL;
        echo '        </div>' . PHP_EOL;
    }

    private static function renderChatSurface(): void
    {
        global $APPLICATION;
        $APPLICATION->IncludeComponent(
            'tacticum:chat.surface',
            '',
            [
                'VARIANT' => 'light',
                'SURFACE' => 'calculator',
                'ROOT_CLASS' => 'ai-chat-container shadow-lg',
                'TITLE' => 'AI-калькулятор Tacticum',
                'INTRO' => 'Здравствуйте! Расскажите о задаче, отрасли, текущих системах и сроке. Я подготовлю предварительную структуру оценки, а команда Tacticum уточнит ее по требованиям.',
                'PLACEHOLDER' => 'Опишите вашу задачу...',
                'QUICK_REPLIES' => [
                    'Platform для RAG и доступа к данным',
                    'Agents для HR или поддержки',
                    'Процесс Dev для инженерной команды',
                    'Forum для клиентских диалогов',
                ],
            ],
            false
        );
    }

    private static function renderOutcomePanel(array $section, array $items): void
    {
        $title = RenderSupport::text($section['title'] ?? '') ?: 'Что вы получите после диалога';

        echo '                <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">' . PHP_EOL;
        echo '                    <h3 class="text-2xl font-bold text-secondary mb-6">' . RenderSupport::h($title) . '</h3>' . PHP_EOL;
        echo '                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">' . PHP_EOL;
        foreach ($items as $item) {
            self::renderOutcomeItem($item);
        }
        echo '                    </div>' . PHP_EOL;
        self::renderOutcomeExample();
        echo '                </div>' . PHP_EOL;
    }

    private static function renderOutcomeItem(array $item): void
    {
        echo '                        <div class="flex items-start gap-4">' . PHP_EOL;
        RenderSupport::renderIcon((string)($item['icon'] ?? ''), 'w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0', 'text-2xl text-primary');
        echo '                            <div>' . PHP_EOL;
        RenderSupport::renderTitleText($item, 'h4', 'text-lg font-semibold text-secondary mb-2', 'text-gray-600');
        echo '                            </div>' . PHP_EOL;
        echo '                        </div>' . PHP_EOL;
    }

    private static function renderOutcomeExample(): void
    {
        echo '                    <div class="mt-8 pt-8 border-t border-gray-200">' . PHP_EOL;
        echo '                        <h4 class="text-lg font-semibold text-secondary mb-4">Пример формата результата</h4>' . PHP_EOL;
        echo '                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">' . PHP_EOL;
        foreach ([['Команда', '5-6 ролей'], ['Срок', '8-12 недель'], ['Следующий шаг', 'уточнить scope']] as $example) {
            echo '                            <div class="rounded-lg bg-gray-50 p-4">' . PHP_EOL;
            echo '                                <p class="text-sm text-gray-500 mb-1">' . RenderSupport::h($example[0]) . '</p>' . PHP_EOL;
            echo '                                <p class="font-semibold text-secondary">' . RenderSupport::h($example[1]) . '</p>' . PHP_EOL;
            echo '                            </div>' . PHP_EOL;
        }
        echo '                        </div>' . PHP_EOL;
        echo '                        <p class="text-sm text-gray-500 mt-4">Это не финальная смета: точность зависит от требований, данных, интеграций и ограничений вашей инфраструктуры.</p>' . PHP_EOL;
        echo '                    </div>' . PHP_EOL;
    }
}
