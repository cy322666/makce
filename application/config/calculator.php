<?php

return [
    // Наценка в процентах по типу клиента.
    'markup_percent' => [
        'direct' => 65,
        'agency' => 75,
    ],

    // Технологический запас бумаги.
    'paper' => [
        // По ручному расчету клиента используется +10% к листам тиража.
        'waste_percent' => 110,
    ],

    'print' => [
        // Минималка офсета без пластин.
        'offset_minimum' => 3000,
    ],

    // Константы для расчетов приладок.
    'prebuild' => [
        'offset_per_color_sheets' => 50,
        'silk_per_color_sheets' => 10,
        'plashka_sheets' => 50,
        'lamination_step' => 2000,
        'felling_step' => 2000,
    ],

    // Константы для резки.
    'cutting' => [
        'minimum_price' => 200,
        'step_quantity' => 1000,
        'step_price' => 200,
    ],

    // ID продуктов со служебными ценами.
    'products' => [
        'felling_blow' => 1,
        'felling_prebuild' => 2,
        'print_plate' => 3,
        'lamination_work' => 4,
        'lamination_prebuild' => 5,
        'package_box' => 32,
    ],

    // Как маппим тип бумаги на префикс товарного slug.
    'paper_slug_map' => [
        'melovannaia' => 'bumaga-mel',
        'karton' => 'bumaga-karton',
    ],

    // Как маппим размер листа на хвост товарного slug бумаги.
    'size_paper_suffix_map' => [
        '720*1040' => '72104',
        '700*1000' => '72104',
        '620*940' => '6294',
    ],

    // Доплаты в УФ-лаке.
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

    // Стоимость способа крепления ручки.
    'bracing_handle_prices' => [
        'klipsa' => 2,
        'uzel' => 2,
        'vkleika' => 2,
    ],

    // Сколько изделий с листа A2 по типу формы.
    'count_per_a2_sheet' => [
        'A1' => 1,
        'A2' => 2,
        'A3' => 2,
        'A4' => 4,
        'A5' => 8,
        'A6' => 16,
    ],

    // Дополнительный клей по формату.
    'glue_per_format' => [
        'A1' => 2,
        'A2' => 1.5,
        'A3' => 1.5,
        'A4' => 1,
        'A5' => 0.6,
        'A6' => 0.6,
    ],
];
