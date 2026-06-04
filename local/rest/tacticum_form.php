<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once(__DIR__ . '/rest_helpers.php');

header('Content-Type: application/json; charset=UTF-8');
tacticum_rest_send_noindex_header();
function tacticum_form_response(bool $success, ?string $error, string $code, array $extra = []): void
{
    $payload = array_merge([
        'success' => $success,
        'error' => $error,
        'code' => $code,
    ], $extra);

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function tacticum_form_normalize_lead_value($value, int $maxLength = 180): string
{
    if (is_array($value)) {
        return '';
    }

    $normalized = trim(preg_replace('/\s+/u', ' ', (string)$value) ?: '');
    if ($normalized === '') {
        return '';
    }

    return mb_substr($normalized, 0, $maxLength);
}

function tacticum_form_lead_value_labels(): array
{
    return [
        'budget_band' => [
            'up-to-1m' => 'до 1 млн руб.',
            '1-3m' => '1-3 млн руб.',
            '3-7m' => '3-7 млн руб.',
            '7m-plus' => '7+ млн руб.',
        ],
        'timeline_band' => [
            'asap' => 'нужен быстрый старт',
            '1-2-months' => '1-2 месяца',
            '3-6-months' => '3-6 месяцев',
            '6-plus-months' => 'дольше 6 месяцев',
        ],
        'use_case_interest' => [
            'product-routing' => 'маршрутизация по продуктовой экосистеме',
            'product-delivery' => 'внедрение продуктового сценария',
            'product-estimate' => 'уточнение продуктовой оценки',
            'product-team' => 'подбор команды под продуктовый поток',
            'contact-routing' => 'маршрутизация обращения',
            'pilot' => 'пилот продукта',
            'architecture-session' => 'архитектурная сессия',
            'procurement-security' => 'закупка и безопасность',
            'team-delivery' => 'команда внедрения',
            'estimate' => 'оценка сроков и бюджета',
        ],
    ];
}

function tacticum_form_build_lead_profile(array $data): array
{
    $fieldMap = [
        'product_interest' => ['lead_product'],
        'use_case_interest' => ['lead_scenario'],
        'deployment_interest' => ['lead_next_step'],
        'funnel_entry' => ['lead_entry'],
        'funnel_stage' => ['lead_page_role'],
        'lead_intent' => ['lead_intent'],
        'cta_id' => ['lead_cta', 'form_id'],
        'budget_band' => ['lead_budget'],
        'timeline_band' => ['lead_timeline'],
        'industry' => ['lead_industry'],
        'offer_code' => ['lead_offer_code'],
        'offer_title' => ['lead_offer_title'],
    ];

    $profile = [];
    foreach ($fieldMap as $profileKey => $sourceKeys) {
        foreach ($sourceKeys as $sourceKey) {
            $value = tacticum_form_normalize_lead_value($data[$sourceKey] ?? '');
            if ($value === '') {
                continue;
            }

            $profile[$profileKey] = $value;
            break;
        }
    }

    return $profile;
}

function tacticum_form_build_lead_context(array $data): string
{
    $labels = [
        'product_interest' => 'Продуктовый интерес',
        'use_case_interest' => 'Сценарий / use case',
        'deployment_interest' => 'Ожидаемый следующий шаг',
        'funnel_entry' => 'Вход',
        'funnel_stage' => 'Стадия funnel',
        'lead_intent' => 'Интент',
        'cta_id' => 'CTA',
        'budget_band' => 'Бюджетный ориентир',
        'timeline_band' => 'Срок',
        'industry' => 'Отрасль',
        'offer_code' => 'Код примера расчета',
        'offer_title' => 'Пример расчета',
    ];
    $valueLabels = tacticum_form_lead_value_labels();
    $leadProfile = tacticum_form_build_lead_profile($data);

    $lines = [];
    foreach ($labels as $profileKey => $label) {
        $value = $leadProfile[$profileKey] ?? '';
        if ($value === '') {
            continue;
        }

        if (isset($valueLabels[$profileKey][$value])) {
            $value = $valueLabels[$profileKey][$value];
        }

        $lines[] = $label . ': ' . $value;
    }

    if ($lines === []) {
        return '';
    }

    return mb_substr("Контекст заявки:\n- " . implode("\n- ", $lines), 0, 900);
}

tacticum_rest_validate_origin();
tacticum_rest_rate_limit('tacticum_form');
tacticum_rest_require_method('POST');

$data = tacticum_rest_read_json_body();
$data = array_map(static fn($value) => is_string($value) ? trim($value) : $value, $data);

tacticum_rest_check_csrf($data, true);

$name = trim((string)($data['name'] ?? ''));
$company = trim((string)($data['company'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$message = trim((string)($data['message'] ?? $data['task'] ?? $data['description'] ?? $data['project'] ?? ''));
$page_url = trim((string)($data['page_url'] ?? ($_SERVER['HTTP_REFERER'] ?? '')));
$group_id = trim((string)($data['group_id'] ?? ''));
$lead_context = tacticum_form_build_lead_context($data);

$phone_normalized = tacticum_rest_normalize_phone($phone);

$missing = [];
if ($name === '') {
    $missing[] = 'name';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $missing[] = 'email';
}
if ($phone === '' || !tacticum_rest_is_valid_phone($phone_normalized)) {
    $missing[] = 'phone';
}
if ($message === '') {
    $missing[] = 'message';
}
if ($page_url === '') {
    $missing[] = 'page_url';
}

if (mb_strlen($name) > 200) {
    $missing[] = 'name';
}
if ($company !== '' && mb_strlen($company) > 200) {
    $missing[] = 'company';
}
if (mb_strlen($message) > 2000) {
    $missing[] = 'message';
}
if ($page_url !== '' && mb_strlen($page_url) > 1000) {
    $missing[] = 'page_url';
}
if ($group_id !== '' && mb_strlen($group_id) > 64) {
    $missing[] = 'group_id';
}

if (!empty($missing)) {
    $fields = implode(', ', $missing);
    tacticum_rest_error(400, 'validation_error', "Некорректные или обязательные поля: {$fields}.");
}

$payload = [
    'name' => $name,
    'company' => $company,
    'email' => $email,
    'phone' => $phone_normalized,
    'task' => $message,
    'page_url' => $page_url,
];

if ($group_id !== '') {
    $payload['group_id'] = $group_id;
}

$form_id = trim((string)($data['form_id'] ?? ''));
if ($form_id !== '') {
    $payload['form_id'] = $form_id;
}

$is_specialist_order = !empty($data['specialist']) || !empty($data['rate']) || !empty($data['duration']);
if ($is_specialist_order) {
    $rate_raw = (string)($data['rate'] ?? '');
    $start_date = trim((string)($data['startDate'] ?? $data['start_date'] ?? ''));
    $duration = trim((string)($data['duration'] ?? ''));
    $specialist = trim((string)($data['specialist'] ?? ''));
    $level = trim((string)($data['level'] ?? ''));

    $durationLabels = [
        '1-month' => '1 месяц',
        '3-months' => '3 месяца',
        '6-months' => '6 месяцев',
    ];
    $durationLabel = $durationLabels[$duration] ?? $duration;

    $taskParts = [];
    if ($specialist !== '') {
        $taskParts[] = 'Специалист: ' . $specialist . ($level !== '' ? ' (' . $level . ')' : '');
    }
    if ($rate_raw !== '') {
        $taskParts[] = 'Ставка: ' . $rate_raw . ' руб/час';
    }
    if ($start_date !== '') {
        $taskParts[] = 'Дата начала: ' . $start_date;
    }
    if ($durationLabel !== '') {
        $taskParts[] = 'Срок работы: ' . $durationLabel;
    }
    $taskParts[] = 'Описание задачи: ' . $message;

    $payload['task'] = implode("\n", $taskParts);
}

if ($lead_context !== '') {
    $payload['task'] .= "\n\n" . $lead_context;
}

$result = tacticum_rest_submit_chat_agent_sale(
    $payload,
    'tacticum_form_chat_agent',
    'tacticum_form',
    'Ошибка отправки во внешний сервис.'
);

if (tacticum_rest_is_successful_upstream_response($result)) {
    tacticum_form_response(true, null, 'ok');
}

tacticum_rest_fail_chat_agent_sale_upstream($result, 'tacticum_form_chat_agent');
