<?php

declare(strict_types=1);

namespace Tacticum\Price;

final class TeamPresetFallback
{
    public static function all(): array
    {
        return [
            [
                'code' => 'mvp',
                'label' => 'MVP',
                'description' => 'Аналитика, дизайн, разработка, QA',
                'scenario' => 'mvp',
                'defaultWorkload' => 'part-time',
                'recommendedDuration' => '2-3-months',
                'version' => 'legacy-2026-06-06',
                'analyticsCode' => 'mvp',
                'source' => 'fallback',
                'roles' => [
                    ['roleKey' => 'analyst', 'keywords' => ['бизнес-аналит', 'аналитик']],
                    ['roleKey' => 'ux-ui', 'keywords' => ['ux', 'ui', 'дизайн', 'designer']],
                    ['roleKey' => 'frontend', 'keywords' => ['frontend', 'front-end', 'фронтенд']],
                    ['roleKey' => 'backend', 'keywords' => ['backend', 'back-end', 'python', 'php', 'java', 'node', 'разработчик', 'developer']],
                    ['roleKey' => 'qa', 'keywords' => ['qa', 'quality', 'тест', 'тестирование']],
                ],
            ],
            [
                'code' => 'discovery',
                'label' => 'Discovery',
                'description' => 'Аналитик, архитектор, UX/UI',
                'scenario' => 'discovery',
                'defaultWorkload' => 'part-time',
                'recommendedDuration' => '1-month',
                'version' => 'legacy-2026-06-06',
                'analyticsCode' => 'discovery',
                'source' => 'fallback',
                'roles' => [
                    ['roleKey' => 'analyst', 'keywords' => ['бизнес-аналит', 'аналитик']],
                    ['roleKey' => 'architect', 'keywords' => ['архитектор', 'architect', 'tech lead', 'lead']],
                    ['roleKey' => 'ux-ui', 'keywords' => ['ux', 'ui', 'дизайн', 'designer']],
                ],
            ],
            [
                'code' => 'support',
                'label' => 'Support',
                'description' => 'Backend, DevOps, QA',
                'scenario' => 'support',
                'defaultWorkload' => 'part-time',
                'recommendedDuration' => '3-6-months',
                'version' => 'legacy-2026-06-06',
                'analyticsCode' => 'support',
                'source' => 'fallback',
                'roles' => [
                    ['roleKey' => 'backend', 'keywords' => ['backend', 'back-end', 'python', 'php', 'java', 'node', 'разработчик', 'developer']],
                    ['roleKey' => 'devops', 'keywords' => ['devops', 'инфраструктура', 'sre']],
                    ['roleKey' => 'qa', 'keywords' => ['qa', 'quality', 'тест', 'тестирование']],
                ],
            ],
            [
                'code' => 'qa-burst',
                'label' => 'QA burst',
                'description' => 'Усиление тестирования перед релизом',
                'scenario' => 'qa',
                'defaultWorkload' => 'part-time',
                'recommendedDuration' => '2-weeks',
                'version' => 'legacy-2026-06-06',
                'analyticsCode' => 'qa-burst',
                'source' => 'fallback',
                'roles' => [
                    ['roleKey' => 'qa', 'quantity' => 2, 'keywords' => ['qa', 'quality', 'тест', 'тестирование']],
                    ['roleKey' => 'qa-automation', 'keywords' => ['автоматиз', 'automation', 'автотест']],
                ],
            ],
        ];
    }
}
