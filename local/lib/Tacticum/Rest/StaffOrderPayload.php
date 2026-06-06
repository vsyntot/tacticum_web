<?php

declare(strict_types=1);

namespace Tacticum\Rest;

use Tacticum\Price\TeamPresetService;

final class StaffOrderPayload
{
    public static function build(array $data, array $server = []): array
    {
        $clientName = trim((string)($data['name'] ?? ''));
        $company = trim((string)($data['company'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $task = trim((string)($data['message'] ?? $data['description'] ?? $data['task'] ?? ''));
        $startDate = trim((string)($data['startDate'] ?? $data['start_date'] ?? ''));
        $endDate = trim((string)($data['endDate'] ?? $data['end_date'] ?? ''));
        $duration = self::defaulted($data['duration'] ?? 'flexible');
        $workload = self::defaulted($data['workload'] ?? 'flexible');
        $teamPreset = trim((string)($data['team_preset'] ?? ''));
        $monthlyBudgetEstimate = self::cleanNumber((string)($data['monthly_budget_estimate'] ?? ''));
        $specialist = trim((string)($data['specialist'] ?? ''));
        $rate = trim((string)($data['rate'] ?? ''));
        $level = trim((string)($data['level'] ?? ''));
        $pageUrl = trim((string)($data['page_url'] ?? ($server['HTTP_REFERER'] ?? '')));
        $groupId = trim((string)($data['group_id'] ?? ''));
        $formId = trim((string)($data['form_id'] ?? ''));
        $amountRaw = $data['amount_of_workers'] ?? $data['amount'] ?? 1;
        $amountOfWorkers = is_numeric($amountRaw) && (int)$amountRaw > 0 ? (int)$amountRaw : 1;
        $phoneNormalized = \tacticum_rest_normalize_phone($phone);
        $costPerHour = self::cleanNumber($rate);
        $teamPresetMeta = self::teamPresetMeta($teamPreset);

        $errors = [];
        $workerData = StaffOrderWorkers::normalize($data, $specialist, $level, $costPerHour, $amountOfWorkers, $errors);
        $workers = $workerData['workers'];
        $totalWorkers = (int)$workerData['total_workers'];
        $errors = array_merge($errors, self::validationErrors(
            $clientName,
            $company,
            $email,
            $phoneNormalized,
            $task,
            $workers,
            $startDate,
            $endDate,
            $duration,
            $workload,
            $teamPreset,
            $monthlyBudgetEstimate,
            $pageUrl,
            $groupId,
            $formId,
            $amountOfWorkers,
            $totalWorkers
        ));

        if ($errors !== []) {
            \tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: ' . implode(', ', array_unique($errors)) . '.');
        }

        $hotSalePayload = [
            'name' => $clientName,
            'company' => $company,
            'email' => $email,
            'phone' => $phoneNormalized,
            'task' => StaffOrderText::task(
                $workers,
                $totalWorkers,
                $teamPreset,
                $teamPresetMeta,
                $monthlyBudgetEstimate,
                $startDate,
                $duration,
                $endDate,
                $workload,
                $task,
                $pageUrl,
                $formId
            ),
        ];

        if ($groupId !== '') {
            $hotSalePayload['group_id'] = $groupId;
        }
        if ($pageUrl !== '') {
            $hotSalePayload['page_url'] = $pageUrl;
        }

        return $hotSalePayload;
    }

    private static function validationErrors(
        string $clientName,
        string $company,
        string $email,
        string $phone,
        string $task,
        array $workers,
        string $startDate,
        string $endDate,
        string $duration,
        string $workload,
        string $teamPreset,
        string $monthlyBudgetEstimate,
        string $pageUrl,
        string $groupId,
        string $formId,
        int $amountOfWorkers,
        int $totalWorkers
    ): array {
        $errors = [];
        if ($clientName === '' || mb_strlen($clientName) > 200) {
            $errors[] = 'name';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'email';
        }
        if ($phone === '' || !\tacticum_rest_is_valid_phone($phone)) {
            $errors[] = 'phone';
        }
        if ($task === '' || mb_strlen($task) > 2000) {
            $errors[] = 'message';
        }
        if ($workers === []) {
            $errors[] = 'workers';
        }

        foreach ([
            'company' => [$company, 200],
            'startDate' => [$startDate, 50],
            'endDate' => [$endDate, 50],
            'duration' => [$duration, 50],
            'workload' => [$workload, 50],
            'team_preset' => [$teamPreset, 100],
            'monthly_budget_estimate' => [$monthlyBudgetEstimate, 50],
            'page_url' => [$pageUrl, 1000],
            'group_id' => [$groupId, 64],
            'form_id' => [$formId, 100],
        ] as $field => [$value, $maxLength]) {
            if ($value !== '' && mb_strlen($value) > $maxLength) {
                $errors[] = $field;
            }
        }

        if ($duration === 'exact-date' && $endDate === '') {
            $errors[] = 'endDate';
        }
        if ($amountOfWorkers > 100) {
            $errors[] = 'amount_of_workers';
        }
        if ($totalWorkers > 100) {
            $errors[] = 'workers';
        }

        return $errors;
    }

    private static function cleanNumber(string $value): string
    {
        $clean = preg_replace('/[^\d.,]/', '', trim($value));
        return $clean !== '' ? $clean : trim($value);
    }

    private static function defaulted(mixed $value): string
    {
        $value = trim((string)$value);
        return $value !== '' ? $value : 'flexible';
    }

    private static function teamPresetMeta(string $teamPreset): array
    {
        $preset = TeamPresetService::find($teamPreset);
        if (!is_array($preset)) {
            return [];
        }

        return [
            'label' => (string)($preset['label'] ?? ''),
            'source' => (string)($preset['source'] ?? ''),
            'version' => (string)($preset['version'] ?? ''),
        ];
    }
}
