<?php

declare(strict_types=1);

namespace Tacticum\CalcRequests;

final class PropertyMapper
{
    public static function properties(array $params): array
    {
        $props = [
            'IS_FINAL' => (!empty($params['if_final']) && $params['if_final'] === true) ? 1 : 0,
        ];

        if (!empty($params['budget']['amount']) && !empty($params['budget']['currency'])) {
            $props['BUDGET'] = $params['budget']['amount'] . ' ' . strtoupper($params['budget']['currency']);
        }

        self::mapArrayProps($params, $props);
        self::mapTextProps($params, $props);
        self::mapSlugProps($params, $props);

        foreach (['group_id' => 'GROUP_ID', 'response_id' => 'RESPONSE_ID'] as $srcKey => $propCode) {
            if (!empty($params[$srcKey])) {
                $props[$propCode] = $params[$srcKey];
            }
        }

        return $props;
    }

    private static function mapArrayProps(array $params, array &$props): void
    {
        foreach ([
            'stack' => 'STACK',
            'team' => 'TEAM',
            'nonfunctional_requirements' => 'NONFUNCTIONAL_REQUIREMENTS',
            'functional_requirements' => 'FUNCTIONAL_REQUIREMENTS',
            'goals' => 'GOALS',
            'tech_risks' => 'TECH_RISKS',
            'business_risks' => 'BUSINESS_RISKS',
        ] as $srcKey => $propCode) {
            if (in_array($srcKey, ['tech_risks', 'business_risks'], true)) {
                self::mapRiskProp($params, $props, $srcKey, $propCode);
                continue;
            }

            if (!empty($params[$srcKey]) && is_array($params[$srcKey])) {
                $props[$propCode] = array_values($params[$srcKey]);
            }
        }

        if (!empty($params['slug']['keywords']) && is_array($params['slug']['keywords'])) {
            $props['KEYWORDS'] = array_values($params['slug']['keywords']);
        }
    }

    private static function mapRiskProp(array $params, array &$props, string $srcKey, string $propCode): void
    {
        if (empty($params[$srcKey]) || !is_array($params[$srcKey])) {
            return;
        }

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
    }

    private static function mapTextProps(array $params, array &$props): void
    {
        foreach ([
            'business_context' => 'BUSINESS_CONTEXT',
            'goals' => 'GOALS',
            'summary' => 'SUMMARY',
            'response' => 'RESPONSE',
            'client_name' => 'CLIENT_NAME',
            'timeline' => 'TIMELINE',
        ] as $srcKey => $propCode) {
            if (!empty($params[$srcKey]) && is_string($params[$srcKey])) {
                $props[$propCode] = $params[$srcKey];
            }
        }
    }

    private static function mapSlugProps(array $params, array &$props): void
    {
        foreach (['title' => 'TITLE', 'description' => 'DESCRIPTION', 'h1' => 'H1'] as $slugKey => $propCode) {
            if (!empty($params['slug'][$slugKey]) && is_string($params['slug'][$slugKey])) {
                $props[$propCode] = $params['slug'][$slugKey];
            }
        }
    }
}
