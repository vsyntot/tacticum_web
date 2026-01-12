<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header('Content-Type: application/json; charset=UTF-8');

$data = json_decode(file_get_contents('php://input'), true);

$client_name      = $data['name']      ?? '';
$company   = $data['company']   ?? '';
$email     = $data['email']     ?? '';
$phone     = $data['phone']     ?? '';
$task      = $data['description'] ?? ($data['task'] ?? '');
$startDate = $data['startDate'] ?? '';
$duration  = $data['duration']  ?? '';
$specialist = $data['specialist'] ?? '';
$rate      = $data['rate']      ?? '';
$cnt      = 1;
$level     = $data['level']     ?? '';

$payload = [
    'client_name'       => $client_name,
    'company'    => $company,
    'email'      => $email,
    'phone'      => $phone,
    'task'       => $task,
    'start_date'  => $startDate,
    'worker_timeline'   => $duration,
    'workers' => [
        [
            'role' => $specialist,
            'level'      => $level,
            'cost_per_hour'       => $rate,
            'amount_of_workers'       => $cnt,
        ]
    ],
];

if (isset($data['group_id'])) {
    $payload['group_id'] = $data['group_id'];
}

AddMessage2Log(serialize($data), "sale_workers_data");
AddMessage2Log(serialize($payload), "sale_workers_request");

$ch = curl_init('http://5.35.90.193:8000/tacticum/v1/sale/workers');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

AddMessage2Log(serialize($response), "sale_workers_response");

if ($http_status !== 200 || !$response) {
    http_response_code(500);
    $error_text = is_string($response) && $response ? $response : 'AI endpoint error';
    echo json_encode(['error' => $error_text]);
    exit;
}

if (is_string($response) && !empty($response)) {
    $json = json_decode($response, true);
    if ($json === null) {
        echo json_encode(['success' => true]);
        exit;
    }
    echo $response;
} else {
    echo json_encode(['success' => true]);
}
