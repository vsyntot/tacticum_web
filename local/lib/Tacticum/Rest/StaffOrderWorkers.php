<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class StaffOrderWorkers
{
    public static function normalize(
        array $data,
        string $specialist,
        string $level,
        string $costPerHour,
        int $amountOfWorkers,
        array &$errors
    ): array {
        $rawWorkers = self::rawWorkers($data, $errors);
        if ($rawWorkers === [] && $specialist !== '') {
            $rawWorkers = [[
                'role' => $specialist,
                'level' => $level,
                'cost_per_hour' => $costPerHour,
                'amount_of_workers' => $amountOfWorkers,
            ]];
        }

        if (count($rawWorkers) > 20) {
            $errors[] = 'workers';
        }

        $workers = [];
        $totalWorkers = 0;
        foreach ($rawWorkers as $index => $worker) {
            if (!is_array($worker)) {
                $errors[] = 'workers';
                continue;
            }

            $role = trim((string)($worker['role'] ?? $worker['specialist'] ?? $worker['name'] ?? ''));
            $workerLevel = trim((string)($worker['level'] ?? ''));
            $workerRate = self::cleanNumber((string)($worker['cost_per_hour'] ?? $worker['rate'] ?? ''));
            $amountRaw = $worker['amount_of_workers'] ?? $worker['amount'] ?? $worker['quantity'] ?? 1;
            $amount = is_numeric($amountRaw) && (int)$amountRaw > 0 ? (int)$amountRaw : 1;

            if ($role === '') {
                $errors[] = 'workers[' . $index . '].role';
                continue;
            }
            if (mb_strlen($role) > 200) {
                $errors[] = 'workers[' . $index . '].role';
            }
            if ($workerLevel !== '' && mb_strlen($workerLevel) > 100) {
                $errors[] = 'workers[' . $index . '].level';
            }
            if ($workerRate !== '' && mb_strlen($workerRate) > 50) {
                $errors[] = 'workers[' . $index . '].cost_per_hour';
            }
            if ($amount > 100) {
                $errors[] = 'workers[' . $index . '].amount_of_workers';
            }

            $totalWorkers += $amount;
            $workers[] = [
                'role' => $role,
                'level' => $workerLevel,
                'cost_per_hour' => $workerRate,
                'amount_of_workers' => $amount,
            ];
        }

        return ['workers' => $workers, 'total_workers' => $totalWorkers];
    }

    public static function summary(array $workers): string
    {
        return implode('; ', array_map(static function (array $worker): string {
            $line = $worker['role'];
            if ($worker['level'] !== '') {
                $line .= ' (' . $worker['level'] . ')';
            }

            return $line . ' x' . $worker['amount_of_workers'];
        }, $workers));
    }

    public static function totalHourlyRate(array $workers): float
    {
        $total = 0.0;
        foreach ($workers as $worker) {
            $numericRate = (float)str_replace(',', '.', (string)$worker['cost_per_hour']);
            if ($numericRate > 0) {
                $total += $numericRate * (int)$worker['amount_of_workers'];
            }
        }

        return $total;
    }

    private static function rawWorkers(array $data, array &$errors): array
    {
        if (isset($data['workers']) && is_array($data['workers'])) {
            return $data['workers'];
        }

        $workersJson = $data['workers_json'] ?? null;
        if (!is_string($workersJson) || trim($workersJson) === '') {
            return [];
        }

        $decodedWorkers = json_decode($workersJson, true);
        if (!is_array($decodedWorkers)) {
            $errors[] = 'workers_json';
            return [];
        }

        return isset($decodedWorkers['role']) || isset($decodedWorkers['specialist'])
            ? [$decodedWorkers]
            : $decodedWorkers;
    }

    private static function cleanNumber(string $value): string
    {
        $clean = preg_replace('/[^\d.,]/', '', trim($value));
        return $clean !== '' ? $clean : trim($value);
    }
}
