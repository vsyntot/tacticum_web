<?php

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return;
}

function tacticum_calcrequests_build_error(string $code, string $message, array $extra = []): array
{
    $payload = [
        'success' => false,
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ];

    if (!empty($extra)) {
        $payload = array_merge($payload, $extra);
    }

    return $payload;
}

function tacticum_calcrequests_build_success(array $extra = []): array
{
    return array_merge(['success' => true], $extra);
}

function tacticum_calcrequests_get_config(): array
{
    $config = Configuration::getValue('tacticum_calcrequests');
    if (!is_array($config)) {
        $config = [];
    }

    $fallback = Configuration::getValue('tacticum_rest');
    if (is_array($fallback)) {
        $config = array_merge($fallback, $config);
    }

    return $config;
}

function tacticum_calcrequests_get_offer_iblock_id(): int
{
    if (function_exists('tacticum_rest_get_iblock_id')) {
        return tacticum_rest_get_iblock_id('offer');
    }

    return 0;
}

function tacticum_calcrequests_user_has_access(int $iblockId): bool
{
    global $USER;

    if (isset($USER) && is_object($USER) && method_exists($USER, 'IsAuthorized') && $USER->IsAuthorized()) {
        if (method_exists($USER, 'IsAdmin') && $USER->IsAdmin()) {
            return true;
        }

        $userId = method_exists($USER, 'GetID') ? (int)$USER->GetID() : 0;
        return $iblockId > 0 && $userId > 0 && Loader::includeModule('iblock') && \CIBlock::GetPermission($iblockId, $userId) >= 'W';
    }

    $user = CurrentUser::get();
    $userId = method_exists($user, 'getId') ? (int)$user->getId() : 0;
    if ($userId <= 0) {
        return false;
    }

    if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
        return true;
    }

    return $iblockId > 0 && Loader::includeModule('iblock') && \CIBlock::GetPermission($iblockId, $userId) >= 'W';
}

function tacticum_calcrequests_check_access(array $params, int $iblockId = 0): ?array
{
    $config = tacticum_calcrequests_get_config();
    $request = Context::getCurrent()->getRequest();
    $ip = (string)$request->getRemoteAddress();

    $allowedIps = $config['allowed_ips'] ?? [];
    if (is_string($allowedIps)) {
        $allowedIps = array_filter(array_map('trim', explode(',', $allowedIps)));
    }
    if (is_array($allowedIps) && !empty($allowedIps) && in_array($ip, $allowedIps, true)) {
        return null;
    }

    $apiKey = (string)($config['api_key'] ?? '');
    $providedKey = (string)($params['api_key'] ?? $request->getHeader('X-Api-Key'));
    if ($apiKey !== '' && $providedKey !== '' && hash_equals($apiKey, $providedKey)) {
        return null;
    }

    if (tacticum_calcrequests_user_has_access($iblockId)) {
        return null;
    }

    return tacticum_calcrequests_build_error(
        'access_denied',
        'Недостаточно прав для выполнения запроса.'
    );
}

function tacticum_calcrequests_validate_payload(array $params): array
{
    $errors = [];
    $normalized = $params;

    if (isset($params['if_final']) && !is_bool($params['if_final'])) {
        $errors[] = ['field' => 'if_final', 'message' => 'Должно быть булевым значением.'];
    }

    if (isset($params['budget'])) {
        if (!is_array($params['budget'])) {
            $errors[] = ['field' => 'budget', 'message' => 'Должен быть объектом.'];
        } else {
            $amount = $params['budget']['amount'] ?? null;
            $currency = $params['budget']['currency'] ?? null;
            if ($amount !== null && !is_numeric($amount)) {
                $errors[] = ['field' => 'budget.amount', 'message' => 'Должно быть числом.'];
            }
            if ($currency !== null && (!is_string($currency) || !preg_match('/^[a-zA-Z]{3}$/', $currency))) {
                $errors[] = ['field' => 'budget.currency', 'message' => 'Должен быть 3-буквенный код валюты.'];
            }
        }
    }

    $slug = $params['slug'] ?? null;
    if (!is_array($slug)) {
        $errors[] = ['field' => 'slug', 'message' => 'Обязательный объект.'];
    } else {
        $slugValue = $slug['slug'] ?? '';
        $slugTitle = $slug['title'] ?? '';
        $hasSlugValue = is_string($slugValue) && trim($slugValue) !== '';
        $hasSlugTitle = is_string($slugTitle) && trim($slugTitle) !== '';

        if (!$hasSlugValue && !$hasSlugTitle) {
            $errors[] = ['field' => 'slug', 'message' => 'Нужен slug.slug или slug.title.'];
        }

        if ($hasSlugValue) {
            $normalized['slug']['slug'] = trim($slugValue);
        }
        if ($hasSlugTitle) {
            $normalized['slug']['title'] = trim($slugTitle);
        }
    }

    $stringLimits = [
        'business_context' => 2000,
        'summary' => 2000,
        'response' => 12000,
        'client_name' => 255,
        'timeline' => 255,
    ];
    foreach ($stringLimits as $field => $limit) {
        if (isset($params[$field])) {
            if (!is_string($params[$field])) {
                $errors[] = ['field' => $field, 'message' => 'Должно быть строкой.'];
                continue;
            }
            if (mb_strlen($params[$field]) > $limit) {
                $errors[] = ['field' => $field, 'message' => 'Превышена длина ' . $limit . ' символов.'];
            }
        }
    }

    $slugLimits = [
        'title' => 255,
        'description' => 500,
        'h1' => 255,
    ];
    if (is_array($slug)) {
        foreach ($slugLimits as $field => $limit) {
            if (isset($slug[$field])) {
                if (!is_string($slug[$field])) {
                    $errors[] = ['field' => 'slug.' . $field, 'message' => 'Должно быть строкой.'];
                    continue;
                }
                if (mb_strlen($slug[$field]) > $limit) {
                    $errors[] = [
                        'field' => 'slug.' . $field,
                        'message' => 'Превышена длина ' . $limit . ' символов.',
                    ];
                }
            }
        }
    }

    $arrayFields = [
        'stack',
        'team',
        'nonfunctional_requirements',
        'functional_requirements',
        'goals',
    ];
    foreach ($arrayFields as $field) {
        if (isset($params[$field])) {
            if (!is_array($params[$field])) {
                $errors[] = ['field' => $field, 'message' => 'Должно быть массивом строк.'];
                continue;
            }
            foreach ($params[$field] as $index => $value) {
                if (!is_string($value)) {
                    $errors[] = ['field' => $field . '.' . $index, 'message' => 'Должно быть строкой.'];
                }
            }
        }
    }

    $riskFields = ['tech_risks', 'business_risks'];
    foreach ($riskFields as $field) {
        if (isset($params[$field])) {
            if (!is_array($params[$field])) {
                $errors[] = ['field' => $field, 'message' => 'Должно быть массивом.'];
                continue;
            }
            foreach ($params[$field] as $index => $risk) {
                if (is_string($risk)) {
                    continue;
                }
                if (!is_array($risk) || !isset($risk['risk']) || !is_string($risk['risk'])) {
                    $errors[] = ['field' => $field . '.' . $index, 'message' => 'Требуется поле risk.'];
                    continue;
                }
                if (isset($risk['description']) && !is_string($risk['description'])) {
                    $errors[] = [
                        'field' => $field . '.' . $index . '.description',
                        'message' => 'Должно быть строкой.',
                    ];
                }
            }
        }
    }

    $identifierLimits = [
        'group_id' => 64,
        'response_id' => 128,
    ];
    foreach ($identifierLimits as $field => $limit) {
        if (!isset($params[$field])) {
            continue;
        }

        if (!is_string($params[$field]) && !is_numeric($params[$field])) {
            $errors[] = ['field' => $field, 'message' => 'Должно быть строковым идентификатором.'];
            continue;
        }

        $value = trim((string)$params[$field]);
        if (mb_strlen($value) > $limit) {
            $errors[] = ['field' => $field, 'message' => 'Превышена длина ' . $limit . ' символов.'];
            continue;
        }

        $normalized[$field] = $value;
    }

    if (isset($params['slug']['keywords'])) {
        $keywords = $params['slug']['keywords'];
        if (!is_array($keywords)) {
            $errors[] = ['field' => 'slug.keywords', 'message' => 'Должно быть массивом строк.'];
        } else {
            foreach ($keywords as $index => $keyword) {
                if (!is_string($keyword)) {
                    $errors[] = ['field' => 'slug.keywords.' . $index, 'message' => 'Должно быть строкой.'];
                }
            }
        }
    }

    return ['errors' => $errors, 'data' => $normalized];
}

function tacticum_calcrequests_is_code_unique(string $code, int $iblockId): bool
{
    $res = \CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, '=CODE' => $code],
        false,
        ['nTopCount' => 1],
        ['ID']
    );

    return !$res->Fetch();
}

function tacticum_calcrequests_normalize_code(string $raw, int $iblockId, int $maxLength = 100): string
{
    $code = \CUtil::translit($raw, 'ru', [
        'replace_space' => '-',
        'replace_other' => '-',
        'change_case' => 'L',
    ]);
    $code = trim($code, '-');
    if ($code === '') {
        $code = 'resp-' . uniqid();
    }

    $code = mb_substr($code, 0, $maxLength);
    $code = rtrim($code, '-');

    $unique = $code;
    $suffix = 1;
    while (!tacticum_calcrequests_is_code_unique($unique, $iblockId)) {
        $suffixStr = '-' . $suffix;
        $base = mb_substr($code, 0, max(0, $maxLength - mb_strlen($suffixStr)));
        $unique = rtrim($base, '-') . $suffixStr;
        $suffix++;
        if ($suffix > 99) {
            $unique = rtrim($code, '-') . '-' . uniqid();
            break;
        }
    }

    return $unique;
}

function tacticum_calcrequests_build_offer_code(array $params, int $iblockId, int $maxLength = 100): string
{
    $slug = is_array($params['slug'] ?? null) ? $params['slug'] : [];
    $title = trim((string)($slug['title'] ?? ''));
    $fallbackSlug = trim((string)($slug['slug'] ?? ''));
    $source = $title !== '' ? $title : $fallbackSlug;
    if ($source === '') {
        $source = 'offer';
    }

    $timestamp = date('Ymd-His');
    $suffix = '-' . $timestamp;
    $baseMaxLength = max(20, $maxLength - mb_strlen($suffix));
    $base = \CUtil::translit($source, 'ru', [
        'replace_space' => '-',
        'replace_other' => '-',
        'change_case' => 'L',
    ]);
    $base = trim($base, '-');
    if ($base === '') {
        $base = 'offer';
    }
    $base = rtrim(mb_substr($base, 0, $baseMaxLength), '-');

    return tacticum_calcrequests_normalize_code($base . $suffix, $iblockId, $maxLength);
}

function tacticum_calcrequests_list(array $params): array
{
    $iblockId = tacticum_calcrequests_get_offer_iblock_id();
    if ($iblockId <= 0) {
        return tacticum_calcrequests_build_error('iblock_not_configured', 'Инфоблок заявок не настроен.');
    }

    $accessError = tacticum_calcrequests_check_access($params, $iblockId);
    if ($accessError !== null) {
        return $accessError;
    }

    if (!Loader::includeModule('iblock')) {
        return tacticum_calcrequests_build_error('iblock_missing', 'Модуль инфоблоков не установлен.');
    }

    $res = \CIBlockElement::GetList(
        ['ID' => 'DESC'],
        ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 10],
        ['ID', 'NAME', 'PROPERTY_*']
    );

    $items = [];
    while ($el = $res->GetNextElement()) {
        $items[] = [
            'FIELDS' => $el->GetFields(),
            'PROPERTIES' => $el->GetProperties(),
        ];
    }

    return $items;
}

function tacticum_calcrequests_add(array $params): array
{
    $iblockId = tacticum_calcrequests_get_offer_iblock_id();
    if ($iblockId <= 0) {
        return tacticum_calcrequests_build_error('iblock_not_configured', 'Инфоблок заявок не настроен.');
    }

    $accessError = tacticum_calcrequests_check_access($params, $iblockId);
    if ($accessError !== null) {
        return $accessError;
    }

    $validation = tacticum_calcrequests_validate_payload($params);
    if (!empty($validation['errors'])) {
        return tacticum_calcrequests_build_error(
            'validation_error',
            'Некорректные данные запроса.',
            ['fields' => $validation['errors']]
        );
    }
    $params = $validation['data'];

    if (!Loader::includeModule('iblock')) {
        return tacticum_calcrequests_build_error('iblock_missing', 'Модуль инфоблоков не установлен.');
    }
    $el = new \CIBlockElement();

    $name = 'resp_' . uniqid();
    $normalizedCode = tacticum_calcrequests_build_offer_code($params, $iblockId);

    $props = [];
    $props['IS_FINAL'] = (!empty($params['if_final']) && $params['if_final'] === true) ? 1 : 0;

    if (!empty($params['budget']['amount']) && !empty($params['budget']['currency'])) {
        $props['BUDGET'] = $params['budget']['amount'] . ' ' . strtoupper($params['budget']['currency']);
    }

    $arrayProps = [
        'stack' => 'STACK',
        'team' => 'TEAM',
        'nonfunctional_requirements' => 'NONFUNCTIONAL_REQUIREMENTS',
        'functional_requirements' => 'FUNCTIONAL_REQUIREMENTS',
        'goals' => 'GOALS',
        'tech_risks' => 'TECH_RISKS',
        'business_risks' => 'BUSINESS_RISKS',
    ];
    foreach ($arrayProps as $srcKey => $propCode) {
        if (
            in_array($srcKey, ['tech_risks', 'business_risks'], true)
            && !empty($params[$srcKey]) && is_array($params[$srcKey])
        ) {
            $propValues = [];
            foreach ($params[$srcKey] as $risk) {
                if (is_array($risk) && isset($risk['risk'])) {
                    $propValues[] = [
                        'VALUE' => $risk['risk'],
                        'DESCRIPTION' => $risk['description'] ?? '',
                    ];
                } elseif (is_string($risk)) {
                    $propValues[] = [
                        'VALUE' => $risk,
                        'DESCRIPTION' => '',
                    ];
                }
            }
            $props[$propCode] = $propValues;
        } elseif (!empty($params[$srcKey]) && is_array($params[$srcKey])) {
            $props[$propCode] = array_values($params[$srcKey]);
        }
    }

    if (!empty($params['slug']['keywords']) && is_array($params['slug']['keywords'])) {
        $props['KEYWORDS'] = array_values($params['slug']['keywords']);
    }

    $textProps = [
        'business_context' => 'BUSINESS_CONTEXT',
        'goals' => 'GOALS',
        'summary' => 'SUMMARY',
        'response' => 'RESPONSE',
        'client_name' => 'CLIENT_NAME',
        'timeline' => 'TIMELINE',
    ];
    foreach ($textProps as $srcKey => $propCode) {
        if (!empty($params[$srcKey]) && is_string($params[$srcKey])) {
            $props[$propCode] = $params[$srcKey];
        }
    }

    if (!empty($params['group_id'])) {
        $props['GROUP_ID'] = $params['group_id'];
    }
    if (!empty($params['response_id'])) {
        $props['RESPONSE_ID'] = $params['response_id'];
    }
    if (!empty($params['slug']['title']) && is_string($params['slug']['title'])) {
        $props['TITLE'] = $params['slug']['title'];
    }
    if (!empty($params['slug']['description']) && is_string($params['slug']['description'])) {
        $props['DESCRIPTION'] = $params['slug']['description'];
    }
    if (!empty($params['slug']['h1']) && is_string($params['slug']['h1'])) {
        $props['H1'] = $params['slug']['h1'];
    }

    $fields = [
        'CODE' => $normalizedCode,
        'IBLOCK_ID' => $iblockId,
        'NAME' => $name,
        'ACTIVE' => 'Y',
        'PROPERTY_VALUES' => $props,
    ];

    $id = (int)$el->Add($fields);
    if ($id > 0) {
        if (function_exists('tacticum_offer_catalog_clear_cache')) {
            tacticum_offer_catalog_clear_cache($iblockId);
        }

        return tacticum_calcrequests_build_success([
            'status' => 'ok',
            'name' => $name,
            'id' => $id,
            'link' => tacticum_offer_detail_url($normalizedCode),
        ]);
    }

    $message = $el->LAST_ERROR;
    if (function_exists('tacticum_rest_mask_string')) {
        $message = tacticum_rest_mask_string($message);
    }

    return tacticum_calcrequests_build_error('add_failed', 'Не удалось сохранить элемент.', [
        'details' => $message,
    ]);
}

function tacticum_register_calcrequests_rest(): void
{
    static $registered = false;
    if ($registered) {
        return;
    }
    $registered = true;

    EventManager::getInstance()->addEventHandler('rest', 'OnRestServiceBuildDescription', static function (): array {
        return [
            'calcrequests_api' => [
                'calcrequests_list' => [
                    'callback' => static fn($params): array => tacticum_calcrequests_list((array)$params),
                ],
                'calcrequests_add' => [
                    'callback' => static fn($params): array => tacticum_calcrequests_add((array)$params),
                ],
            ],
        ];
    });
}
