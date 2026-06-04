<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    return [];
}

return [
    'eyebrow' => 'Invalid fixture',
    'title' => '',
    'lead' => 'This fixture intentionally violates product content schema.',
    'primary_cta_text' => 'Primary',
    'secondary_cta_text' => 'Secondary',
    'secondary_cta_href' => 'http://example.invalid/unsafe',
    'badges' => ['invalid'],
    'hero_cards' => [
        ['title' => 'Hero', 'text' => 'Text'],
    ],
    'fit_guide' => [
        'fits' => ['items' => ['fit']],
        'not_fits' => ['items' => ['not']],
        'start' => ['items' => ['start']],
    ],
    'sections' => [
        ['title' => 'Section', 'cards' => [['title' => 'Card', 'text' => 'Text']]],
    ],
    'architecture' => [
        'title' => 'Architecture',
        'text' => 'Text',
        'layers' => [
            ['title' => 'Layer', 'text' => 'Text', 'items' => ['Item']],
        ],
    ],
    'use_cases' => [
        'title' => 'Use cases',
        'text' => 'Text',
        'items' => [
            [
                'title' => 'Use case 1',
                'trigger' => 'Trigger',
                'owner' => 'Owner',
                'pilot_input' => 'Input',
                'pilot_output' => 'Output',
                'limitation' => 'Limitation',
            ],
            [
                'title' => 'Use case 2',
                'trigger' => 'Trigger',
                'owner' => 'Owner',
                'pilot_input' => 'Input',
                'pilot_output' => 'Output',
                'limitation' => 'Limitation',
            ],
        ],
    ],
    'comparison' => [
        'title' => 'Compare',
        'text' => 'Text',
        'columns' => [
            ['title' => 'A', 'text' => 'Text', 'items' => ['One']],
            ['title' => 'B', 'text' => 'Text', 'items' => ['Two']],
        ],
    ],
    'procurement' => [
        'title' => 'Procurement',
        'text' => 'Text',
        'note_text' => 'Safe note',
        'items' => [
            ['title' => 'Item', 'text' => 'Text'],
        ],
    ],
    'rollout' => [
        'title' => 'Rollout',
        'text' => 'Text',
        'steps' => [
            ['title' => 'Step 1', 'text' => 'Text'],
            ['title' => 'Step 2', 'text' => 'Text'],
            ['title' => 'Step 3', 'text' => 'Text'],
        ],
    ],
    'proof' => [
        'title' => 'Proof',
        'text' => 'Text',
        'items' => [
            ['meta' => 'Pilot', 'title' => 'Proof 1', 'text' => 'Text'],
            ['meta' => 'Pilot', 'title' => 'Proof 2', 'text' => 'Text'],
            ['meta' => 'Pilot', 'title' => 'Proof 3', 'text' => 'Text'],
        ],
    ],
    'faq' => [
        'title' => 'FAQ',
        'text' => 'Text',
        'items' => [
            ['question' => 'Q1', 'answer' => 'A'],
            ['question' => 'Q2', 'answer' => 'A'],
            ['question' => 'Q3', 'answer' => 'A'],
        ],
    ],
    'cta' => [
        'form_id' => 'platform-cta',
        'field_prefix' => 'platform',
        'title' => 'CTA',
        'text' => 'Text',
        'form_title' => 'Form',
        'button_text' => 'Send',
        'scenario_label' => 'Scenario',
        'scenario_empty_label' => 'Choose',
        'scenario_options' => [
            ['VALUE' => 'demo', 'LABEL' => 'Demo'],
        ],
        'lead_context' => [
            'lead_entry' => 'platform',
            'lead_page_role' => 'product-page',
            'lead_product' => 'agents',
            'lead_intent' => 'demo',
            'lead_cta' => 'platform-cta',
            'lead_next_step' => 'demo',
        ],
    ],
];
