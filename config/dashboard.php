<?php

return [
    'targets' => [
        'students' => [
            'default' => (int) env('DASHBOARD_TARGET_STUDENTS', 600),
            'per_year' => [
                // '2024' => 800,
            ],
        ],
        'revenue' => [
            'default' => (float) env('DASHBOARD_TARGET_REVENUE', 120000000),
            'per_year' => [
                // '2024' => 150000000,
            ],
        ],
    ],
];
