<?php

declare(strict_types=1);

require __DIR__ . '/../../local/lib/Tacticum/Divatech/Origin.php';
require __DIR__ . '/../../local/lib/Tacticum/Divatech/LeadPayload.php';

use Tacticum\Divatech\Origin;
use Tacticum\Divatech\LeadPayload;

$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (!$condition) { throw new RuntimeException($message); }
};
$source = ['HTTP_HOST' => 'divatech.space:443', 'HTTP_ORIGIN' => 'https://divatech.space'];
$check(Origin::allowed($source, 's2'), 'Canonical s2 origin rejected');
$check(!Origin::allowed($source, 's1'), 's1 can submit Divatech leads');
foreach (['https://tacticum.ru', 'https://divatech.space.evil.test', 'http://divatech.space', 'null', '', 'https://divatech.space:444', 'https://user@divatech.space'] as $origin) {
    $check(!Origin::allowed(array_replace($source, ['HTTP_ORIGIN' => $origin, 'HTTP_REFERER' => 'https://divatech.space/']), 's2'), 'Origin bypass: ' . $origin);
}
$check(!Origin::allowed(['HTTP_HOST' => 'divatech.space'], 's2'), 'No-source request accepted');
$check(!Origin::allowed(array_replace($source, ['HTTP_HOST' => 'tacticum.ru']), 's2'), 'Wrong host accepted');
$check(Origin::allowed(['HTTP_HOST' => 'divatech.space', 'HTTP_REFERER' => 'https://divatech.space/?x=1'], 's2'), 'Valid referer rejected');
$valid = ['request_id' => '10101010-1010-4010-8010-101010101010', 'kind' => 'contact', 'email' => 'synthetic@example.invalid', 'name' => 'Synthetic test', 'message' => 'Rehearsal only', 'consent' => true];
$check(LeadPayload::parse($valid)['kind'] === 'contact', 'Contact rejected');
$check(LeadPayload::parse(array_replace($valid, ['kind' => 'callback', 'phone' => '+90 (500) 000-00-00']))['kind'] === 'callback', 'Callback rejected');
foreach ([['email' => 'bad'], ['email' => "x@example.test\r\nBcc: bad"], ['name' => []], ['name' => ''], ['message' => str_repeat('x', 4001)], ['consent' => 'true'], ['consent' => false], ['website' => 'bot'], ['request_id' => '../bad'], ['kind' => 'other'], ['kind' => 'callback', 'phone' => '<script>'], ['kind' => 'callback', 'phone' => '-------'], ['kind' => 'callback', 'phone' => '1234567890123456'], ['message' => "\0"]] as $change) {
    try { LeadPayload::parse(array_replace($valid, $change)); $accepted = true; }
    catch (InvalidArgumentException) { $accepted = false; }
    $check(!$accepted, 'Invalid payload accepted: ' . array_key_first($change));
}
echo "Divatech contracts: $checks checks passed.\n";
