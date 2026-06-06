<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class StaffOrderText
{
    public static function task(
        array $workers,
        int $totalWorkers,
        string $teamPreset,
        array $teamPresetMeta,
        string $monthlyBudgetEstimate,
        string $startDate,
        string $duration,
        string $endDate,
        string $workload,
        string $task,
        string $pageUrl,
        string $formId
    ): string {
        $taskParts = [
            count($workers) > 1 || $totalWorkers > 1 ? 'Заявка на команду специалистов' : 'Заявка на специалиста',
            'Состав: ' . StaffOrderWorkers::summary($workers),
            'Общее количество: ' . $totalWorkers,
        ];

        if (($teamPresetLabel = self::teamPresetLabel($teamPreset, $teamPresetMeta)) !== '') {
            $taskParts[] = 'Пресет команды: ' . $teamPresetLabel;
        }
        if (($teamPresetVersion = trim((string)($teamPresetMeta['version'] ?? ''))) !== '') {
            $taskParts[] = 'Версия пресета: ' . $teamPresetVersion;
        }
        if (($teamPresetSource = trim((string)($teamPresetMeta['source'] ?? ''))) !== '') {
            $taskParts[] = 'Источник пресета: ' . $teamPresetSource;
        }
        if (($totalHourlyRate = StaffOrderWorkers::totalHourlyRate($workers)) > 0) {
            $taskParts[] = 'Ориентировочная суммарная ставка: ' . number_format($totalHourlyRate, 0, ',', ' ') . ' руб/час';
        }
        if (($numericMonthlyBudget = (float)str_replace(',', '.', $monthlyBudgetEstimate)) > 0) {
            $taskParts[] = 'Ориентировочный бюджет: ' . number_format($numericMonthlyBudget, 0, ',', ' ') . ' руб/мес';
        }

        foreach ($workers as $worker) {
            $line = '- ' . $worker['role'] . ($worker['level'] !== '' ? ' (' . $worker['level'] . ')' : '') . ': ' . $worker['amount_of_workers'] . ' чел.';
            $taskParts[] = $worker['cost_per_hour'] !== '' ? $line . ', ' . $worker['cost_per_hour'] . ' руб/час' : $line;
        }

        return implode("\n", array_merge(
            $taskParts,
            self::tail($startDate, $duration, $endDate, $workload, $task, $pageUrl, $formId)
        ));
    }

    private static function tail(
        string $startDate,
        string $duration,
        string $endDate,
        string $workload,
        string $task,
        string $pageUrl,
        string $formId
    ): array {
        $lines = [];
        if ($startDate !== '') {
            $lines[] = 'Дата начала: ' . $startDate;
        }
        if (($durationLabel = self::durationLabels()[$duration] ?? $duration) !== '') {
            $lines[] = 'Срок работы: ' . $durationLabel;
        }
        if ($endDate !== '') {
            $lines[] = 'Дата окончания: ' . $endDate;
        }
        if (($workloadLabel = self::workloadLabels()[$workload] ?? $workload) !== '') {
            $lines[] = 'Загрузка: ' . $workloadLabel;
        }
        $lines[] = 'Описание задачи: ' . $task;
        if ($pageUrl !== '') {
            $lines[] = 'Страница: ' . $pageUrl;
        }
        if ($formId !== '') {
            $lines[] = 'Form ID: ' . $formId;
        }

        return $lines;
    }

    private static function durationLabels(): array
    {
        return [
            'flexible' => 'срок обсуждается',
            '2-weeks' => 'короткий спринт: до 2 недель',
            '1-month' => '1 месяц',
            '2-3-months' => '2–3 месяца',
            '3-6-months' => '3–6 месяцев',
            '6-plus-months' => 'дольше 6 месяцев',
            'exact-date' => 'до конкретной даты',
        ];
    }

    private static function workloadLabels(): array
    {
        return ['flexible' => 'обсуждается', 'part-time' => 'part-time', 'full-time' => 'full-time'];
    }

    private static function teamPresetLabel(string $teamPreset, array $teamPresetMeta): string
    {
        $label = trim((string)($teamPresetMeta['label'] ?? ''));
        if ($label !== '') {
            return $label;
        }

        return ['mvp' => 'MVP', 'discovery' => 'Discovery', 'support' => 'Support', 'qa-burst' => 'QA burst'][$teamPreset] ?? $teamPreset;
    }
}
