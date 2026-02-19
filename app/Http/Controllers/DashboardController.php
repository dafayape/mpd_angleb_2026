<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Dummy data for charts
        $chartData = [
            'tren_orang_pergerakan_opsel' => [
                'categories' => ['IOH', 'TSEL', 'XL'],
                'series' => [
                    [
                        'name' => 'Orang',
                        'data' => [150000, 220000, 75000],
                        'color' => '#f1b44c' // Yellowish
                    ],
                    [
                        'name' => 'Pergerakan',
                        'data' => [320000, 410000, 160000],
                        'color' => '#f46a6a' // Reddish
                    ]
                ]
            ],
            'jumlah_pergerakan_per_moda' => [
                'categories' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '23:59'],
                'series' => [
                    [
                        'name' => 'Jalan',
                        'data' => [120, 132, 101, 134, 90, 230, 210]
                    ],
                    [
                        'name' => 'Kereta Api',
                        'data' => [220, 182, 191, 234, 290, 330, 310]
                    ],
                    [
                        'name' => 'Udara',
                        'data' => [150, 232, 201, 154, 190, 330, 410]
                    ],
                    [
                        'name' => 'Laut',
                        'data' => [320, 332, 301, 334, 390, 330, 320]
                    ]
                ]
            ]
        ];

        return view('dashboard.index', compact('chartData'));
    }
}
