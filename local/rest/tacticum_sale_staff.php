<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');
tacticum_rest_send_noindex_header();

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_sale_staff');
tacticum_rest_require_method('POST');

$data = tacticum_rest_read_json_body();
tacticum_rest_check_csrf($data, true);

$client_name = trim((string)($data['name'] ?? ''));
$company = trim((string)($data['company'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$task = trim((string)($data['message'] ?? $data['description'] ?? $data['task'] ?? ''));
$startDate = trim((string)($data['startDate'] ?? $data['start_date'] ?? ''));
$endDate = trim((string)($data['endDate'] ?? $data['end_date'] ?? ''));
$duration = trim((string)($data['duration'] ?? 'flexible'));
if ($duration === '') {
    $duration = 'flexible';
}
$workload = trim((string)($data['workload'] ?? 'flexible'));
if ($workload === '') {
    $workload = 'flexible';
}
$teamPreset = trim((string)($data['team_preset'] ?? ''));
$monthlyBudgetEstimate = trim((string)($data['monthly_budget_estimate'] ?? ''));
$monthlyBudgetEstimateClean = preg_replace('/[^\d.,]/', '', $monthlyBudgetEstimate);
if ($monthlyBudgetEstimateClean !== '') {
    $monthlyBudgetEstimate = $monthlyBudgetEstimateClean;
}
$specialist = trim((string)($data['specialist'] ?? ''));
$rate = trim((string)($data['rate'] ?? ''));
$level = trim((string)($data['level'] ?? ''));
$page_url = trim((string)($data['page_url'] ?? ($_SERVER['HTTP_REFERER'] ?? '')));
$group_id = trim((string)($data['group_id'] ?? ''));
$form_id = trim((string)($data['form_id'] ?? ''));
$amount_raw = $data['amount_of_workers'] ?? $data['amount'] ?? 1;
$amount_of_workers = is_numeric($amount_raw) && (int)$amount_raw > 0 ? (int)$amount_raw : 1;

$phone_normalized = tacticum_rest_normalize_phone($phone);
$cost_per_hour = preg_replace('/[^\d.,]/', '', $rate);
if ($cost_per_hour === '') {
    $cost_per_hour = $rate;
}

$errors = [];
$raw_workers = [];
$workers_json = $data['workers_json'] ?? null;
if (isset($data['workers']) && is_array($data['workers'])) {
    $raw_workers = $data['workers'];
} elseif (is_string($workers_json) && trim($workers_json) !== '') {
    $decoded_workers = json_decode($workers_json, true);
    if (is_array($decoded_workers)) {
        $raw_workers = isset($decoded_workers['role']) || isset($decoded_workers['specialist'])
            ? [$decoded_workers]
            : $decoded_workers;
    } else {
        $errors[] = 'workers_json';
    }
}

if (empty($raw_workers) && $specialist !== '') {
    $raw_workers = [
        [
            'role' => $specialist,
            'level' => $level,
            'cost_per_hour' => $cost_per_hour,
            'amount_of_workers' => $amount_of_workers,
        ],
    ];
}

if (count($raw_workers) > 20) {
    $errors[] = 'workers';
}

$workers = [];
$total_workers = 0;
foreach ($raw_workers as $index => $worker) {
    if (!is_array($worker)) {
        $errors[] = 'workers';
        continue;
    }

    $worker_role = trim((string)($worker['role'] ?? $worker['specialist'] ?? $worker['name'] ?? ''));
    $worker_level = trim((string)($worker['level'] ?? ''));
    $worker_rate = trim((string)($worker['cost_per_hour'] ?? $worker['rate'] ?? ''));
    $worker_rate_clean = preg_replace('/[^\d.,]/', '', $worker_rate);
    if ($worker_rate_clean !== '') {
        $worker_rate = $worker_rate_clean;
    }
    $worker_amount_raw = $worker['amount_of_workers'] ?? $worker['amount'] ?? $worker['quantity'] ?? 1;
    $worker_amount = is_numeric($worker_amount_raw) && (int)$worker_amount_raw > 0 ? (int)$worker_amount_raw : 1;

    if ($worker_role === '') {
        $errors[] = 'workers[' . $index . '].role';
        continue;
    }
    if (mb_strlen($worker_role) > 200) {
        $errors[] = 'workers[' . $index . '].role';
    }
    if ($worker_level !== '' && mb_strlen($worker_level) > 100) {
        $errors[] = 'workers[' . $index . '].level';
    }
    if ($worker_rate !== '' && mb_strlen($worker_rate) > 50) {
        $errors[] = 'workers[' . $index . '].cost_per_hour';
    }
    if ($worker_amount > 100) {
        $errors[] = 'workers[' . $index . '].amount_of_workers';
    }

    $total_workers += $worker_amount;
    $workers[] = [
        'role' => $worker_role,
        'level' => $worker_level,
        'cost_per_hour' => $worker_rate,
        'amount_of_workers' => $worker_amount,
    ];
}

if ($client_name === '') {
    $errors[] = 'name';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'email';
}
if ($phone === '' || !tacticum_rest_is_valid_phone($phone_normalized)) {
    $errors[] = 'phone';
}
if ($task === '') {
    $errors[] = 'message';
}
if (empty($workers)) {
    $errors[] = 'workers';
}
if ($client_name !== '' && mb_strlen($client_name) > 200) {
    $errors[] = 'name';
}
if ($company !== '' && mb_strlen($company) > 200) {
    $errors[] = 'company';
}
if ($task !== '' && mb_strlen($task) > 2000) {
    $errors[] = 'message';
}
if ($startDate !== '' && mb_strlen($startDate) > 50) {
    $errors[] = 'startDate';
}
if ($endDate !== '' && mb_strlen($endDate) > 50) {
    $errors[] = 'endDate';
}
if ($duration === 'exact-date' && $endDate === '') {
    $errors[] = 'endDate';
}
if ($duration !== '' && mb_strlen($duration) > 50) {
    $errors[] = 'duration';
}
if ($workload !== '' && mb_strlen($workload) > 50) {
    $errors[] = 'workload';
}
if ($teamPreset !== '' && mb_strlen($teamPreset) > 100) {
    $errors[] = 'team_preset';
}
if ($monthlyBudgetEstimate !== '' && mb_strlen($monthlyBudgetEstimate) > 50) {
    $errors[] = 'monthly_budget_estimate';
}
if ($page_url !== '' && mb_strlen($page_url) > 1000) {
    $errors[] = 'page_url';
}
if ($group_id !== '' && mb_strlen($group_id) > 64) {
    $errors[] = 'group_id';
}
if ($form_id !== '' && mb_strlen($form_id) > 100) {
    $errors[] = 'form_id';
}
if ($amount_of_workers > 100) {
    $errors[] = 'amount_of_workers';
}
if ($total_workers > 100) {
    $errors[] = 'workers';
}
if (!empty($errors)) {
    tacticum_rest_error(400, 'validation_error', 'Некорректные или обязательные поля: ' . implode(', ', array_unique($errors)) . '.');
}

$durationLabels = [
    'flexible' => 'срок обсуждается',
    '2-weeks' => 'короткий спринт: до 2 недель',
    '1-month' => '1 месяц',
    '2-3-months' => '2–3 месяца',
    '3-6-months' => '3–6 месяцев',
    '6-plus-months' => 'дольше 6 месяцев',
    'exact-date' => 'до конкретной даты',
];
$durationLabel = $durationLabels[$duration] ?? $duration;
$workloadLabels = [
    'flexible' => 'обсуждается',
    'part-time' => 'part-time',
    'full-time' => 'full-time',
];
$workloadLabel = $workloadLabels[$workload] ?? $workload;
$teamPresetLabels = [
    'mvp' => 'MVP',
    'discovery' => 'Discovery',
    'support' => 'Support',
    'qa-burst' => 'QA burst',
];
$teamPresetLabel = $teamPresetLabels[$teamPreset] ?? $teamPreset;

$specialist_summary = implode('; ', array_map(static function (array $worker): string {
    $line = $worker['role'];
    if ($worker['level'] !== '') {
        $line .= ' (' . $worker['level'] . ')';
    }
    $line .= ' x' . $worker['amount_of_workers'];
    return $line;
}, $workers));

$taskParts = [
    count($workers) > 1 || $total_workers > 1 ? 'Заявка на команду специалистов' : 'Заявка на специалиста',
    'Состав: ' . $specialist_summary,
    'Общее количество: ' . $total_workers,
];
if ($teamPresetLabel !== '') {
    $taskParts[] = 'Пресет команды: ' . $teamPresetLabel;
}
$total_hourly_rate = 0;
foreach ($workers as $worker) {
    $numeric_rate = (float)str_replace(',', '.', (string)$worker['cost_per_hour']);
    if ($numeric_rate > 0) {
        $total_hourly_rate += $numeric_rate * (int)$worker['amount_of_workers'];
    }
}
if ($total_hourly_rate > 0) {
    $taskParts[] = 'Ориентировочная суммарная ставка: ' . number_format($total_hourly_rate, 0, ',', ' ') . ' руб/час';
}
$numeric_monthly_budget = (float)str_replace(',', '.', $monthlyBudgetEstimate);
if ($numeric_monthly_budget > 0) {
    $taskParts[] = 'Ориентировочный бюджет: ' . number_format($numeric_monthly_budget, 0, ',', ' ') . ' руб/мес';
}
foreach ($workers as $worker) {
    $workerLine = '- ' . $worker['role'] . ($worker['level'] !== '' ? ' (' . $worker['level'] . ')' : '') . ': ' . $worker['amount_of_workers'] . ' чел.';
    if ($worker['cost_per_hour'] !== '') {
        $workerLine .= ', ' . $worker['cost_per_hour'] . ' руб/час';
    }
    $taskParts[] = $workerLine;
}
if ($startDate !== '') {
    $taskParts[] = 'Дата начала: ' . $startDate;
}
if ($durationLabel !== '') {
    $taskParts[] = 'Срок работы: ' . $durationLabel;
}
if ($endDate !== '') {
    $taskParts[] = 'Дата окончания: ' . $endDate;
}
if ($workloadLabel !== '') {
    $taskParts[] = 'Загрузка: ' . $workloadLabel;
}
$taskParts[] = 'Описание задачи: ' . $task;
if ($page_url !== '') {
    $taskParts[] = 'Страница: ' . $page_url;
}
if ($form_id !== '') {
    $taskParts[] = 'Form ID: ' . $form_id;
}

$hotSalePayload = [
    'name' => $client_name,
    'company' => $company,
    'email' => $email,
    'phone' => $phone_normalized,
    'task' => implode("\n", $taskParts),
];

if ($group_id !== '') {
    $hotSalePayload['group_id'] = $group_id;
}
if ($page_url !== '') {
    $hotSalePayload['page_url'] = $page_url;
}

$base_url = tacticum_rest_get_required_https_ai_url('AI_SERVICE_BASE_URL');
$endpoint_path = tacticum_rest_get_ai_endpoint_path('staff_sale', '/tacticum/v1/chat_agent/sale');
$endpoint_url = tacticum_rest_build_url($base_url, $endpoint_path);

$result = tacticum_rest_post_json_retry_without_group_id($endpoint_url, $hotSalePayload, 'tacticum_sale_staff_hot_sale');
$http_status = (int)$result['http_status'];

tacticum_rest_fail_on_curl_error($result, 'tacticum_sale_staff_hot_sale', 'Ошибка отправки во внешний сервис.');

if ($http_status < 200 || $http_status >= 300) {
    tacticum_rest_error(502, 'upstream_error', 'Ошибка отправки во внешний сервис.');
}

tacticum_rest_response(true, 'ok', null);
