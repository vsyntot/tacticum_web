<?php
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

Loader::includeModule('iblock');

EventManager::getInstance()->addEventHandler('rest', 'OnRestServiceBuildDescription', function () {
    return [
        'calcrequests_api' => [
            'calcrequests_list' => [
                'callback' => function ($params) {
                    \CModule::IncludeModule("iblock");

                    $res = CIBlockElement::GetList(
                        ['ID' => 'DESC'],
                        ['IBLOCK_ID' => 5, 'ACTIVE' => 'Y'],
                        false,
                        ['nTopCount' => 10],
                        ['ID', 'NAME', 'PROPERTY_*']
                    );

                    $items = [];
                    while ($el = $res->GetNextElement()) {
                        $fields = $el->GetFields();
                        $props = $el->GetProperties();
                        $items[] = [
                            'FIELDS' => $fields,
                            'PROPERTIES' => $props
                        ];
                    }

                    return $items;
                }
            ],
            'calcrequests_add' => [
                'callback' => function ($params) {
                    AddMessage2Log(serialize($params), "debug");

                    \CModule::IncludeModule("iblock");
                    $el = new CIBlockElement;

                    $name = 'resp_'.uniqid();

                    $props = [];

                    // 1. IS_FINAL
                    $props['IS_FINAL'] = (!empty($params['if_final']) && $params['if_final'] === true) ? 1 : 0;

                    // 2. BUDGET
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
                            in_array($srcKey, ['tech_risks', 'business_risks'])
                            && !empty($params[$srcKey]) && is_array($params[$srcKey])
                        ) {
                            $propValues = [];
                            foreach ($params[$srcKey] as $risk) {
                                if (is_array($risk) && isset($risk['risk'])) {
                                    $propValues[] = [
                                        "VALUE" => $risk['risk'],
                                        "DESCRIPTION" => $risk['description'] ?? ''
                                    ];
                                } elseif (is_string($risk)) {
                                    $propValues[] = [
                                        "VALUE" => $risk,
                                        "DESCRIPTION" => ''
                                    ];
                                }
                            }
                            $props[$propCode] = $propValues;
                        } elseif (!empty($params[$srcKey]) && is_array($params[$srcKey])) {
                            $props[$propCode] = array_values($params[$srcKey]);
                        }
                    }

                    if (!empty($params['slug']["keywords"]) && is_array($params['slug']["keywords"])) {
                        $props["KEYWORDS"] = array_values($params['slug']["keywords"]);
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
                    if (!empty($params['slug']["title"]) && is_string($params['slug']["title"])) {
                        $props["TITLE"] = $params['slug']["title"];
                    }
                    if (!empty($params['slug']["description"]) && is_string($params['slug']["description"])) {
                        $props["DESCRIPTION"] = $params['slug']["description"];
                    }
                    if (!empty($params['slug']["h1"]) && is_string($params['slug']["h1"])) {
                        $props["H1"] = $params['slug']["h1"];
                    }

                    $fields = [
                        'CODE' => $params['slug']['slug'],
                        'IBLOCK_ID' => 5,
                        'NAME' => $name,
                        'ACTIVE' => 'Y',
                        'PROPERTY_VALUES' => $props
                    ];

                    if ($ID = $el->Add($fields)) {
                        return [
                            'status' => 'ok',
                            'name' => $name,
                            'id' => $ID,
                            'link' => 'https://'.((SITE_SERVER_NAME) ? SITE_SERVER_NAME : $_SERVER["SERVER_NAME"]).'/offer/?ID='.$ID
                        ];
                    }

                    return [
                        'status' => 'error',
                        'message' => $el->LAST_ERROR
                    ];
                }
            ]
        ]
    ];
});