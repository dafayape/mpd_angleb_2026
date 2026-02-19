<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GrafikMpdController extends Controller
{
    /**
     * Display Nasional Pergerakan Dashboard
     */
    public function nasionalPergerakan(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);
        
        // Cache Key
        $cacheKey = 'grafik:nasional:pergerakan:v1';

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getChartData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            $data = $this->getChartData($startDate, $endDate);
        }

        return view('grafik-mpd.nasional.pergerakan', [
            'title' => 'Dashboard Grafik Pergerakan Nasional',
            'breadcrumb' => ['Grafik MPD', 'Nasional', 'Pergerakan'],
            'charts' => $data
        ]);
    }

    private function getChartData($startDate, $endDate)
    {
        // Init Daily Dates
        $dates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // 1. Fetch Daily Trend (Real vs Forecast)
        // Query
        $daily = DB::table('spatial_movements')
            ->select('tanggal', 'is_forecast', DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('tanggal', 'is_forecast')
            ->orderBy('tanggal')
            ->get();

        // 2. Fetch Daily Operator Breakdown (Real Only usually)
        $opselDaily = DB::table('spatial_movements')
            ->select('tanggal', 'opsel', DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_forecast', false)
            ->groupBy('tanggal', 'opsel')
            ->get();

        $dailySeries = [
            'REAL' => array_fill_keys($dates, 0),
            'FORECAST' => array_fill_keys($dates, 0)
        ];

        foreach ($daily as $row) {
            $type = $row->is_forecast ? 'FORECAST' : 'REAL';
            $dailySeries[$type][$row->tanggal] = (int) $row->total;
        }

        $opselSeries = [
            'XL' => array_fill_keys($dates, 0),
            'IOH' => array_fill_keys($dates, 0),
            'TSEL' => array_fill_keys($dates, 0)
        ];

        foreach ($opselDaily as $row) {
             // Normalize
            $raw = strtoupper($row->opsel);
            $name = 'OTHER';
            if (str_contains($raw, 'XL') || str_contains($raw, 'AXIS')) $name = 'XL';
            elseif (str_contains($raw, 'INDOSAT') || str_contains($raw, 'IOH') || str_contains($raw, 'TRI')) $name = 'IOH';
            elseif (str_contains($raw, 'TELKOMSEL') || str_contains($raw, 'TSEL')) $name = 'TSEL';

            if ($name === 'OTHER') continue;
            
            $opselSeries[$name][$row->tanggal] += (int) $row->total;
        }

        // 3. Calculate Totals for Summary
        $totalReal = array_sum($dailySeries['REAL']);
        $totalForecast = array_sum($dailySeries['FORECAST']);
        $totalPpl = $totalReal; 

        return [
            'dates' => $dates,
            'summary' => [
                'real' => $totalReal,
                'forecast' => $totalForecast,
                'people' => $totalPpl
            ],
            // Chart 1 & 2 Data (Pergerakan & Orang) - Real vs Forecast
            'series_trend' => [
                ['name' => 'REAL', 'data' => array_values($dailySeries['REAL']), 'color' => '#2caffe'], // Light Blue
                ['name' => 'FORECAST', 'data' => array_values($dailySeries['FORECAST']), 'color' => '#fec107'] // Yellow
            ],
            // Chart 3 & 4 Data (Opsel) - Stacked or Grouped? Image 2 shows Grouped.
            'series_opsel' => [
                ['name' => 'XL', 'data' => array_values($opselSeries['XL']), 'color' => '#2caffe'], // Blue
                ['name' => 'IOH', 'data' => array_values($opselSeries['IOH']), 'color' => '#fec107'], // Yellow
                ['name' => 'TSEL', 'data' => array_values($opselSeries['TSEL']), 'color' => '#ff3d60'] // Red
            ]
        ];
    }
    
    // Placeholder methods for other routes to prevent errors
    public function nasionalOdProvinsi(Request $request) 
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);

        $cacheKey = 'grafik:nasional:od-provinsi:v1';

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getOdProvinsiData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            $data = $this->getOdProvinsiData($startDate, $endDate);
        }

        return view('grafik-mpd.nasional.od-provinsi', [
            'title' => 'O-D Provinsi',
            'breadcrumb' => ['Grafik MPD', 'Nasional', 'O-D Provinsi'],
            'data' => $data
        ]);
    }

    private function getOdProvinsiData($startDate, $endDate)
    {
        // 1. Query: Sum Total by Origin Prov & Dest Prov
        // We assume spatial_movements.kode_origin_kabupaten_kota matches ref_cities.code
        // And ref_cities.province_code matches ref_provinces.code
        
        try {
            $query = DB::table('spatial_movements as sm')
                // Join Origin City & Province
                ->join('ref_cities as oc', 'sm.kode_origin_kabupaten_kota', '=', 'oc.code')
                ->join('ref_provinces as op', 'oc.province_code', '=', 'op.code')
                // Join Dest City & Province
                ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
                ->join('ref_provinces as dp', 'dc.province_code', '=', 'dp.code')
                ->select(
                    'op.code as origin_code',
                    'op.name as origin_name',
                    'dp.code as dest_code',
                    'dp.name as dest_name',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.is_forecast', false) // Real Only as per request? "O-D Provinsi Real"
                ->groupBy('op.code', 'op.name', 'dp.code', 'dp.name')
                ->get();

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OD Provinsi Query Error: ' . $e->getMessage());
            $query = collect();
        }

        // 2. Process Data
        $totalNational = $query->sum('total_volume');
        
        // A. Top 10 Origin
        $topOrigin = $query->groupBy('origin_code')
            ->map(function ($rows) use ($totalNational) {
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $rows->first()->origin_code,
                    'name' => $rows->first()->origin_name,
                    'total' => $subTotal,
                    'pct' => $totalNational > 0 ? ($subTotal / $totalNational) * 100 : 0
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // B. Top 10 Destination
        $topDest = $query->groupBy('dest_code')
            ->map(function ($rows) use ($totalNational) {
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $rows->first()->dest_code,
                    'name' => $rows->first()->dest_name,
                    'total' => $subTotal,
                    'pct' => $totalNational > 0 ? ($subTotal / $totalNational) * 100 : 0
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // C. Sankey Data
        // Nodes: List of distinct Origin & Dest Provinces
        // Links: Origin -> Dest with weight
        // Highcharts Sankey requires 'from', 'to', 'weight'
        // To avoid circular or weird rendering, usually we might prefix nodes or just use names if distinct enough.
        // Province names are unique.
        
        $sankeyData = $query->map(function($row) {
            return [
                'from' => $row->origin_name,
                'to' => $row->dest_name,
                'weight' => (int) $row->total_volume
            ];
        })->values();

        return [
            'top_origin' => $topOrigin,
            'top_dest' => $topDest,
            'sankey' => $sankeyData,
            'total_national' => $totalNational
        ];
    }
    
    public function nasionalTopKabkota() { return view('placeholder', ['title' => 'Top Kab/Kota', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Top Kab/Kota']]); }
    public function nasionalModeShare(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);

        $cacheKey = 'grafik:nasional:mode-share:v6';

        try {
            // \Illuminate\Support\Facades\Log::info("Generating Mode Share Data v5 for $startDate to $endDate");
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getModeShareData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
             \Illuminate\Support\Facades\Log::error('Mode Share Error: ' . $e->getMessage());
            $data = $this->getModeShareData($startDate, $endDate);
        }

        return view('grafik-mpd.nasional.mode-share', [
            'title' => 'Mode Share Nasional',
            'breadcrumb' => ['Grafik MPD', 'Nasional', 'Mode Share'],
            'data' => $data
        ]);
    }

    private function getModeShareData($startDate, $endDate)
    {
        // 1. Get All Modes from Reference
        $modes = DB::table('ref_transport_modes')->orderBy('code')->get();
        
        // 2. Prepare Date Range
        $dates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // 3. Query Daily Data (All Modes)
        $dailyQuery = DB::table('spatial_movements as sm')
            ->join('ref_transport_modes as m', 'sm.kode_moda', '=', 'm.code')
            ->select(
                'm.code as mode_code',
                'm.name as mode_name',
                'sm.tanggal',
                'sm.is_forecast',
                DB::raw('SUM(sm.total) as total')
            )
            ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('m.code', 'm.name', 'sm.tanggal', 'sm.is_forecast')
            ->get();

        // 4. Structure Data
        
        // Detailed Occupancy Factors (Estimates for Orang -> Pergerakan conv)
        $occupancy = [
            'A' => 30,  // Bus AKAP
            'B' => 25,  // Bus AKDP
            'C' => 300, // KA Antarkota
            'D' => 600, // KA KCJB
            'E' => 100, // KA Perkotaan
            'F' => 200, // Laut
            'G' => 50,  // Penyeberangan
            'H' => 100, // Udara
            'I' => 3.5, // Mobil
            'J' => 1.5, // Motor
            'K' => 1,   // Sepeda
        ];

        // Init Totals
        $totals = [];
        foreach ($modes as $mode) {
            $totals[$mode->code] = ['ppl' => 0, 'mov' => 0, 'name' => $mode->name];
        }

        // Init Daily Series containers
        // We will store raw data first, then format
        $dailyRaw = []; // [ModeCode][Type (Real/Ppl, etc)][Date] = Value

        foreach ($dailyQuery as $row) {
            $code = $row->mode_code;
            $date = $row->tanggal;
            $ppl = (int) $row->total;
            $isForecast = (bool) $row->is_forecast;
            $type = $isForecast ? 'forecast' : 'real';
            
            // Calculate Movement
            $factor = $occupancy[$code] ?? 1;
            $mov = (int) round($ppl / $factor);

            // Accumulate for Pie (Real Only)
            if (!$isForecast) {
                if (!isset($totals[$code])) {
                    $totals[$code] = ['ppl' => 0, 'mov' => 0, 'name' => $row->mode_name];
                }
                $totals[$code]['ppl'] += $ppl;
                $totals[$code]['mov'] += $mov;
            }

            // Accumulate for Daily
            if (!isset($dailyRaw[$code])) $dailyRaw[$code] = [];
            
            // Initialize date keys if needed (optimization: do it on demand or pre-fill)
            // Simpler: Just add to flat array, fill zeros later
            $dailyRaw[$code]['ppl'][$type][$date] = $ppl;
            $dailyRaw[$code]['mov'][$type][$date] = $mov;
        }



        // Format Pie Data
        $piePeople = [];
        $pieMovement = [];
        foreach ($totals as $code => $data) {
            // ALWAYS include, even if 0, as per request "tampilin tapi 0 nilainya"
            $piePeople[] = ['name' => $data['name'], 'y' => $data['ppl']];
            $pieMovement[] = ['name' => $data['name'], 'y' => $data['mov']];
        }
        
        // Sort Pie Data (Optional: If we want fixed order A-K, remove usort. If we want big to small, keep it.)
        // Request said: "moda nya nyesuaian sama @[database/seeders/ModaSeeder.php]" -> Implies Order A-K?
        // Reference image shows sorting by size (Motor 63%, Mobil 18%...). 
        // But user said "tampilin tapi 0 nilainya".
        // Let's keep sorting by size for readability, but allow 0s to exist at bottom.
        usort($piePeople, fn($a, $b) => $b['y'] <=> $a['y']);
        usort($pieMovement, fn($a, $b) => $b['y'] <=> $a['y']);

        // Format Daily Charts
        $dailyCharts = [];
        // Only Mobil (I) and Motor (J) for Daily Charts as per request
        $targetDailyModes = ['I', 'J'];

        foreach ($targetDailyModes as $code) {
            // Check if exists in totals (it should if seeder has it)
            if (!isset($totals[$code])) continue;

            $modeName = $totals[$code]['name'];
            
            // 1. Chart: Harian Pergerakan
            $movReal = []; $movFc = [];
            // 2. Chart: Harian Orang
            $pplReal = []; $pplFc = [];

            foreach ($dates as $d) {
                // Use data if exists, else 0
                $movReal[] = $dailyRaw[$code]['mov']['real'][$d] ?? 0;
                $movFc[] = $dailyRaw[$code]['mov']['forecast'][$d] ?? 0;
                $pplReal[] = $dailyRaw[$code]['ppl']['real'][$d] ?? 0;
                $pplFc[] = $dailyRaw[$code]['ppl']['forecast'][$d] ?? 0;
            }

            // Add Charts pair
            $dailyCharts[] = [
                'title' => $modeName . ' Harian Pergerakan',
                'series' => [
                    ['name' => 'REAL', 'data' => $movReal, 'color' => '#2caffe'],
                    ['name' => 'FORECAST', 'data' => $movFc, 'color' => '#fec107']
                ]
            ];
            $dailyCharts[] = [
                'title' => $modeName . ' Harian Orang',
                'series' => [
                    ['name' => 'REAL', 'data' => $pplReal, 'color' => '#2caffe'],
                    ['name' => 'FORECAST', 'data' => $pplFc, 'color' => '#fec107']
                ]
            ];
        }

        return [
            'dates' => $dates,
            'pie_people' => $piePeople,
            'pie_movement' => $pieMovement,
            'daily_charts' => $dailyCharts
        ];
    }
    public function nasionalSimpul(Request $request)
    {
        $startDate = \Carbon\Carbon::create(2026, 3, 13);
        $endDate = \Carbon\Carbon::create(2026, 3, 29);

        // Cache Key v6: Connected to Real Data Source (PostGIS/Spatial)
        $cacheKey = 'grafik:nasional:simpul:v6';

        try {
            $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getSimpulDashboardData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Nasional Simpul Dashboard Error: ' . $e->getMessage());
            $data = $this->getSimpulDashboardData($startDate, $endDate);
        }

        return view('grafik-mpd.nasional.simpul', [
            'title' => 'Dashboard Simpul & Pergerakan',
            'breadcrumb' => ['Grafik MPD', 'Nasional', 'Simpul'],
            'data' => $data
        ]);
    }

    private function getSimpulDashboardData($startDate, $endDate)
    {
        // Define Tabs with Sections
        // Tab -> Sections -> Charts/Logic
        $tabs = [
            'DARAT' => [
                'sections' => [
                    [
                        'title' => 'Simpul Darat',
                        'subtitle' => 'Kode 1.3.a.1',
                        'modes' => ['A', 'B', 'I', 'J', 'K'], 
                        'daily_charts' => ['A' => 'Bus AKAP', 'B' => 'Bus AKDP'],
                        'show_top_10' => true,
                        'show_top_od' => false // REMOVE per request
                    ]
                ]
            ],
            'LAUT' => [
                'sections' => [
                    [
                        'title' => 'Pelabuhan Penyeberangan',
                        'subtitle' => 'Kode 1.3.d.1',
                        'modes' => ['G'], 
                        'daily_charts' => ['G' => 'Kapal Penyeberangan'],
                        'show_top_10' => true,
                        'show_top_od' => true
                    ],
                    [
                        'title' => 'Pelabuhan Laut',
                        'subtitle' => 'Kode 1.3.f.1',
                        'modes' => ['F'],
                        'daily_charts' => ['F' => 'Kapal Laut'],
                        'show_top_10' => true,
                        'show_top_od' => true
                    ]
                ]
            ],
            'UDARA' => [
                'sections' => [
                    [
                        'title' => 'Simpul Udara',
                        'subtitle' => 'Kode 1.3.e.1',
                        'modes' => ['H'],
                        'daily_charts' => ['H' => 'Angkutan Udara'],
                        'show_top_10' => true,
                        'show_top_od' => false // REMOVE per request
                    ]
                ]
            ],
            'KERETA' => [
                'sections' => [
                     [
                        'title' => 'Kereta Api (Antar Kota)',
                        'subtitle' => 'Kode 1.3.b.1',
                        'modes' => ['C'],
                        'daily_charts' => ['C' => 'K.A. Antar Kota'],
                        'show_top_10' => true, // "tambahin top 10 stasiun antar kota"
                        'show_top_od' => true  // "tambahin top 10 rute"
                    ],
                    [
                        'title' => 'Kereta Api (Perkotaan)',
                        'subtitle' => 'Kode 1.3.b.2',
                        'modes' => ['E'],
                        'daily_charts' => ['E' => 'K.A. Perkotaan'],
                        'show_top_10' => true, // "tambahin top 10 stasiun perkotaan"
                        'show_top_od' => true
                    ],
                    [
                        'title' => 'Kereta Api Cepat (Whoosh)',
                        'subtitle' => 'Kode 1.3.d.1',
                        'modes' => ['D'],
                        'daily_charts' => ['D' => 'K.A. Cepat Whoosh'],
                        'show_top_10' => false, // Only "rute" requested for global, specific stasiun not mentioned for Whoosh
                        'show_top_od' => true
                    ]
                ]
            ]
        ];

        // Prepare Date Labels
        $dates = [];
        $dbDates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates[] = $curr->format('d M');
            $dbDates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        $result = ['dates' => $dates, 'tabs' => []];

        foreach ($tabs as $key => $tabConfig) {
            $tabData = ['sections' => []];
            
            foreach ($tabConfig['sections'] as $section) {
                $secData = [
                    'title' => $section['title'],
                    'daily_charts' => [],
                    'top_origin' => [],
                    'top_dest' => [],
                    'top_od' => []
                ];
                
                $modeCodes = $section['modes'];

                // 1. DAILY CHARTS
                foreach ($section['daily_charts'] as $code => $label) {
                    $daily = DB::table('spatial_movements')
                        ->select('tanggal', 'is_forecast', DB::raw('SUM(total) as total'))
                        ->where('kode_moda', $code)
                        ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->groupBy('tanggal', 'is_forecast')
                        ->get();
                    
                    $realSeries = array_fill(0, count($dates), 0);
                    $fcSeries = array_fill(0, count($dates), 0);
                    $totalReal = 0;
                    $totalFc = 0;

                    foreach ($daily as $row) {
                        $idx = array_search($row->tanggal, $dbDates);
                        if ($idx !== false) {
                            if ($row->is_forecast) {
                                $fcSeries[$idx] += (int) $row->total;
                                $totalFc += (int) $row->total;
                            } else {
                                $realSeries[$idx] += (int) $row->total;
                                $totalReal += (int) $row->total;
                            }
                        }
                    }

                    $secData['daily_charts'][] = [
                        'label' => $label,
                        'series_real' => $realSeries,
                        'series_forecast' => $fcSeries,
                        'total_real' => $totalReal,
                        'total_forecast' => $totalFc
                    ];
                }

                // 2. TOP 10 ORIGIN
                if ($section['show_top_10']) {
                    $secData['top_origin'] = DB::table('spatial_movements as sm')
                        ->join('ref_transport_nodes as n', 'sm.kode_origin_simpul', '=', 'n.code')
                        ->select('n.name', DB::raw('SUM(sm.total) as total'))
                        ->whereIn('sm.kode_moda', $modeCodes)
                        ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->groupBy('n.name')
                        ->orderByDesc('total')
                        ->limit(10)
                        ->get();

                    $secData['top_dest'] = DB::table('spatial_movements as sm')
                        ->join('ref_transport_nodes as n', 'sm.kode_dest_simpul', '=', 'n.code')
                        ->select('n.name', DB::raw('SUM(sm.total) as total'))
                        ->whereIn('sm.kode_moda', $modeCodes)
                        ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->groupBy('n.name')
                        ->orderByDesc('total')
                        ->limit(10)
                        ->get();
                }

                // 3. TOP OD ROUTE (New Requirement)
                if ($section['show_top_od']) {
                     // Join Origin Node AND Dest Node
                     $secData['top_od'] = DB::table('spatial_movements as sm')
                        ->join('ref_transport_nodes as no', 'sm.kode_origin_simpul', '=', 'no.code')
                        ->join('ref_transport_nodes as nd', 'sm.kode_dest_simpul', '=', 'nd.code')
                        ->select('no.name as origin', 'nd.name as dest', DB::raw('SUM(sm.total) as total'))
                        ->whereIn('sm.kode_moda', $modeCodes)
                        ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->groupBy('no.name', 'nd.name')
                        ->orderByDesc('total')
                        ->limit(10)
                        ->get()
                        ->map(function($item) {
                            return [
                                'name' => '[' . $item->origin . '] -> [' . $item->dest . ']',
                                'total' => $item->total
                            ];
                        });
                }

                $tabData['sections'][] = $secData;
            }
            $result['tabs'][$key] = $tabData;
        }

        return $result;
    }
    
    public function jabodetabekPergerakanOrang() { return view('placeholder', ['title' => 'Pergerakan & Orang', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang']]); }
    public function jabodetabekPergerakanOrangOpsel() { return view('placeholder', ['title' => 'Pergerakan & Orang (Opsel)', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang (Opsel)']]); }
    public function jabodetabekOdKabkota() { return view('placeholder', ['title' => 'O-D Kab/Kota', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'O-D Kab/Kota']]); }
    public function jabodetabekModeShare() { return view('placeholder', ['title' => 'Mode Share', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Mode Share']]); }
    public function jabodetabekSimpul() { return view('placeholder', ['title' => 'Simpul', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Simpul']]); }

}
