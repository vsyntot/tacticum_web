<?php

declare(strict_types=1);

namespace Tacticum\Rest;

final class LeadPayload
{
    public static function build(array $data, array $server = []): array
    {
        $data = array_map(static fn($value) => is_string($value) ? trim($value) : $value, $data);

        $name = trim((string)($data['name'] ?? ''));
        $company = trim((string)($data['company'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $phone = trim((string)($data['phone'] ?? ''));
        $message = trim((string)($data['message'] ?? $data['task'] ?? $data['description'] ?? $data['project'] ?? ''));
        $pageUrl = trim((string)($data['page_url'] ?? ($server['HTTP_REFERER'] ?? '')));
        $groupId = trim((string)($data['group_id'] ?? ''));
        $leadContext = LeadContext::build($data);
        $phoneNormalized = \tacticum_rest_normalize_phone($phone);

        $missing = self::validationErrors($name, $company, $email, $phoneNormalized, $message, $pageUrl, $groupId);
        if ($missing !== []) {
            \tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: ' . implode(', ', $missing) . '.');
        }

        $payload = [
            'name' => $name,
            'company' => $company,
            'email' => $email,
            'phone' => $phoneNormalized,
            'task' => $message,
            'page_url' => $pageUrl,
        ];

        if ($groupId !== '') {
            $payload['group_id'] = $groupId;
        }

        $formId = trim((string)($data['form_id'] ?? ''));
        if ($formId !== '') {
            $payload['form_id'] = $formId;
        }

        if (!empty($data['specialist']) || !empty($data['rate']) || !empty($data['duration'])) {
            $payload['task'] = self::specialistTask($data, $message);
        }

        if ($leadContext !== '') {
            $payload['task'] .= "\n\n" . $leadContext;
        }

        return $payload;
    }

    public static function respond(bool $success, ?string $error, string $code, array $extra = []): void
    {
        echo json_encode(array_merge([
            'success' => $success,
            'error' => $error,
            'code' => $code,
        ], $extra), JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function validationErrors(
        string $name,
        string $company,
        string $email,
        string $phone,
        string $message,
        string $pageUrl,
        string $groupId
    ): array {
        $missing = [];
        if ($name === '' || mb_strlen($name) > 200) {
            $missing[] = 'name';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $missing[] = 'email';
        }
        if ($phone === '' || !\tacticum_rest_is_valid_phone($phone)) {
            $missing[] = 'phone';
        }
        if ($message === '' || mb_strlen($message) > 2000) {
            $missing[] = 'message';
        }
        if ($pageUrl === '' || mb_strlen($pageUrl) > 1000) {
            $missing[] = 'page_url';
        }
        if ($company !== '' && mb_strlen($company) > 200) {
            $missing[] = 'company';
        }
        if ($groupId !== '' && mb_strlen($groupId) > 64) {
            $missing[] = 'group_id';
        }

        return $missing;
    }

    private static function specialistTask(array $data, string $message): string
    {
        $durationLabels = [
            '1-month' => '1 месяц',
            '3-months' => '3 месяца',
            '6-months' => '6 месяцев',
        ];
        $duration = trim((string)($data['duration'] ?? ''));
        $specialist = trim((string)($data['specialist'] ?? ''));
        $level = trim((string)($data['level'] ?? ''));

        $taskParts = [];
        if ($specialist !== '') {
            $taskParts[] = 'Специалист: ' . $specialist . ($level !== '' ? ' (' . $level . ')' : '');
        }
        if (($rate = (string)($data['rate'] ?? '')) !== '') {
            $taskParts[] = 'Ставка: ' . $rate . ' руб/час';
        }
        if (($startDate = trim((string)($data['startDate'] ?? $data['start_date'] ?? ''))) !== '') {
            $taskParts[] = 'Дата начала: ' . $startDate;
        }
        if (($durationLabels[$duration] ?? $duration) !== '') {
            $taskParts[] = 'Срок работы: ' . ($durationLabels[$duration] ?? $duration);
        }
        $taskParts[] = 'Описание задачи: ' . $message;

        return implode("\n", $taskParts);
    }
}
