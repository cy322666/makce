<?php

return [
    'markup_percent' => [
        'direct' => 65,
        'agency' => 75,
    ],

    'paper' => [
        'waste_percent' => 108,
    ],

    'prebuild' => [
        'offset_per_color_sheets' => 50,
        'silk_per_color_sheets' => 10,
        'plashka_sheets' => 50,
        'lamination_step' => 2000,
        'felling_step' => 2000,
    ],

    'cutting' => [
        'minimum_price' => 200,
        'step_quantity' => 1000,
        'step_price' => 200,
    ],

    'products' => [
        'felling_blow' => 1,
        'felling_prebuild' => 2,
        'print_plate' => 3,
        'lamination_work' => 4,
        'lamination_prebuild' => 5,
        'package_box' => 32,
    ],

    'paper_slug_map' => [
        'melovannaia' => 'bumaga-mel',
        'karton' => 'bumaga-karton',
    ],

    'size_paper_suffix_map' => [
        '720*1040' => '72104',
        '700*1000' => '72104',
        '620*940' => '6294',
    ],

    'uv_lac' => [
        'drying_per_sheet' => [
            'A1' => 5,
            'A2' => 3,
            'A3' => 2.5,
        ],
        'discharge_per_sheet' => [
            'A1' => 9,
            'A2' => 5,
            'A3' => 3,
        ],
    ],

    'bracing_handle_prices' => [
        'klipsa' => 2,
        'uzel' => 2,
        'vkleika' => 2,
    ],

    'count_per_a2_sheet' => [
        'A1' => 1,
        'A2' => 2,
        'A3' => 2,
        'A4' => 4,
        'A5' => 8,
        'A6' => 16,
    ],

    'glue_per_format' => [
        'A1' => 2,
        'A2' => 1.5,
        'A3' => 1.5,
        'A4' => 1,
        'A5' => 0.6,
        'A6' => 0.6,
    ],
];
