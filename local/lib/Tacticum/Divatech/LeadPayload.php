<?php

declare(strict_types=1);

namespace Tacticum\Divatech;

use InvalidArgumentException;

final class LeadPayload
{
    public static function parse(array $input): array
    {
        $data = [];
        foreach (['request_id' => 36, 'kind' => 10, 'email' => 254, 'name' => 120, 'phone' => 40, 'message' => 4000, 'website' => 250] as $key => $limit) {
            $value = $input[$key] ?? '';
            if (!is_string($value) || !mb_check_encoding($value, 'UTF-8') || mb_strlen($value) > $limit || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                throw new InvalidArgumentException('Invalid field.');
            }
            $data[$key] = trim($value);
        }
        $data['request_id'] = strtolower($data['request_id']);
        $consent = $input['consent'] ?? null;
        $consentAccepted = $consent === true
            || (is_string($consent) && in_array(strtolower(trim($consent)), ['1', 'on', 'true'], true));
        if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D', $data['request_id'])
            || !in_array($data['kind'], ['contact', 'callback'], true) || !$consentAccepted) {
            throw new InvalidArgumentException('Invalid request.');
        }
        if ($data['kind'] === 'contact' && (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || $data['name'] === '' || $data['message'] === '')) {
            throw new InvalidArgumentException('Email, name and request are required.');
        }
        $phoneDigits = strlen((string)preg_replace('/\D/', '', $data['phone']));
        if ($data['kind'] === 'callback' && (!preg_match('/^\+?[\d ()-]{7,40}$/D', $data['phone']) || $phoneDigits < 7 || $phoneDigits > 15)) {
            throw new InvalidArgumentException('A valid phone number is required.');
        }
        unset($data['website']);
        return $data;
    }
}
