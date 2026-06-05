<?php

declare(strict_types=1);

namespace Tacticum\CalcRequests;

final class Validator
{
    public static function validate(array $params): array
    {
        $errors = [];
        $normalized = $params;

        self::validatePrimitiveFields($params, $normalized, $errors);
        self::validateSlug($params, $normalized, $errors);
        self::validateStringFields($params, $errors);
        self::validateArrayFields($params, $errors);
        self::validateRiskFields($params, $errors);
        self::validateIdentifiers($params, $normalized, $errors);
        self::validateKeywords($params, $errors);

        return ['errors' => $errors, 'data' => $normalized];
    }

    private static function validatePrimitiveFields(array $params, array &$normalized, array &$errors): void
    {
        if (isset($params['if_final']) && !is_bool($params['if_final'])) {
            $errors[] = ['field' => 'if_final', 'message' => 'Должно быть булевым значением.'];
        }

        if (!isset($params['budget'])) {
            return;
        }
        if (!is_array($params['budget'])) {
            $errors[] = ['field' => 'budget', 'message' => 'Должен быть объектом.'];
            return;
        }

        $amount = $params['budget']['amount'] ?? null;
        $currency = $params['budget']['currency'] ?? null;
        if ($amount !== null && !is_numeric($amount)) {
            $errors[] = ['field' => 'budget.amount', 'message' => 'Должно быть числом.'];
        }
        if ($currency !== null && (!is_string($currency) || !preg_match('/^[a-zA-Z]{3}$/', $currency))) {
            $errors[] = ['field' => 'budget.currency', 'message' => 'Должен быть 3-буквенный код валюты.'];
        }
    }

    private static function validateSlug(array $params, array &$normalized, array &$errors): void
    {
        $slug = $params['slug'] ?? null;
        if (!is_array($slug)) {
            $errors[] = ['field' => 'slug', 'message' => 'Обязательный объект.'];
            return;
        }

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

        foreach (['title' => 255, 'description' => 500, 'h1' => 255] as $field => $limit) {
            self::validateOptionalString($slug, 'slug.' . $field, $field, $limit, $errors);
        }
    }

    private static function validateStringFields(array $params, array &$errors): void
    {
        foreach ([
            'business_context' => 2000,
            'summary' => 2000,
            'response' => 12000,
            'client_name' => 255,
            'timeline' => 255,
        ] as $field => $limit) {
            self::validateOptionalString($params, $field, $field, $limit, $errors);
        }
    }

    private static function validateArrayFields(array $params, array &$errors): void
    {
        foreach (['stack', 'team', 'nonfunctional_requirements', 'functional_requirements', 'goals'] as $field) {
            if (!isset($params[$field])) {
                continue;
            }
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

    private static function validateRiskFields(array $params, array &$errors): void
    {
        foreach (['tech_risks', 'business_risks'] as $field) {
            if (!isset($params[$field])) {
                continue;
            }
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
                    $errors[] = ['field' => $field . '.' . $index . '.description', 'message' => 'Должно быть строкой.'];
                }
            }
        }
    }

    private static function validateIdentifiers(array $params, array &$normalized, array &$errors): void
    {
        foreach (['group_id' => 64, 'response_id' => 128] as $field => $limit) {
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
    }

    private static function validateKeywords(array $params, array &$errors): void
    {
        if (!isset($params['slug']['keywords'])) {
            return;
        }

        $keywords = $params['slug']['keywords'];
        if (!is_array($keywords)) {
            $errors[] = ['field' => 'slug.keywords', 'message' => 'Должно быть массивом строк.'];
            return;
        }
        foreach ($keywords as $index => $keyword) {
            if (!is_string($keyword)) {
                $errors[] = ['field' => 'slug.keywords.' . $index, 'message' => 'Должно быть строкой.'];
            }
        }
    }

    private static function validateOptionalString(
        array $source,
        string $errorField,
        string $sourceField,
        int $limit,
        array &$errors
    ): void {
        if (!isset($source[$sourceField])) {
            return;
        }
        if (!is_string($source[$sourceField])) {
            $errors[] = ['field' => $errorField, 'message' => 'Должно быть строкой.'];
            return;
        }
        if (mb_strlen($source[$sourceField]) > $limit) {
            $errors[] = ['field' => $errorField, 'message' => 'Превышена длина ' . $limit . ' символов.'];
        }
    }
}
