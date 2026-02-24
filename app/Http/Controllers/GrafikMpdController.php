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
        $endDate = Carbon::create(2026, 3, 30);
        
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
        $dates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // 1. Daily Trend — PERGERAKAN (Real vs Forecast)
        $dailyPergerakan = DB::table('spatial_movements')
            ->select('tanggal', 'is_forecast', DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('kategori', 'PERGERAKAN')
            ->groupBy('tanggal', 'is_forecast')
            ->orderBy('tanggal')
            ->get();

        // 2. Daily Trend — ORANG (Real vs Forecast)
        $dailyOrang = DB::table('spatial_movements')
            ->select('tanggal', 'is_forecast', DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('kategori', 'ORANG')
            ->groupBy('tanggal', 'is_forecast')
            ->orderBy('tanggal')
            ->get();

        // 3. Daily Operator Breakdown (PERGERAKAN, Real Only)
        $opselDaily = DB::table('spatial_movements')
            ->select('tanggal', 'opsel', DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_forecast', false)
            ->where('kategori', 'PERGERAKAN')
            ->groupBy('tanggal', 'opsel')
            ->get();

        // 4. Daily Operator Breakdown (ORANG, Real Only)
        $opselDailyOrang = DB::table('spatial_movements')
            ->select('tanggal', 'opsel', DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_forecast', false)
            ->where('kategori', 'ORANG')
            ->groupBy('tanggal', 'opsel')
            ->get();

        // Build PERGERAKAN series
        $movSeries = ['REAL' => array_fill_keys($dates, 0), 'FORECAST' => array_fill_keys($dates, 0)];
        foreach ($dailyPergerakan as $row) {
            $type = $row->is_forecast ? 'FORECAST' : 'REAL';
            $movSeries[$type][$row->tanggal] = (int) $row->total;
        }

        // Build ORANG series
        $pplSeries = ['REAL' => array_fill_keys($dates, 0), 'FORECAST' => array_fill_keys($dates, 0)];
        foreach ($dailyOrang as $row) {
            $type = $row->is_forecast ? 'FORECAST' : 'REAL';
            $pplSeries[$type][$row->tanggal] = (int) $row->total;
        }

        // Build Opsel series (PERGERAKAN)
        $opselMov = ['XL' => array_fill_keys($dates, 0), 'IOH' => array_fill_keys($dates, 0), 'TSEL' => array_fill_keys($dates, 0)];
        foreach ($opselDaily as $row) {
            $name = $this->normalizeOpsel($row->opsel);
            if ($name === 'OTHER') continue;
            $opselMov[$name][$row->tanggal] += (int) $row->total;
        }

        // Build Opsel series (ORANG)
        $opselPpl = ['XL' => array_fill_keys($dates, 0), 'IOH' => array_fill_keys($dates, 0), 'TSEL' => array_fill_keys($dates, 0)];
        foreach ($opselDailyOrang as $row) {
            $name = $this->normalizeOpsel($row->opsel);
            if ($name === 'OTHER') continue;
            $opselPpl[$name][$row->tanggal] += (int) $row->total;
        }

        $totalMovReal = array_sum($movSeries['REAL']);
        $totalMovFc = array_sum($movSeries['FORECAST']);
        $totalPplReal = array_sum($pplSeries['REAL']);
        $totalPplFc = array_sum($pplSeries['FORECAST']);

        return [
            'dates' => $dates,
            'summary' => [
                'real' => $totalMovReal,
                'forecast' => $totalMovFc,
                'people' => $totalPplReal,
                'people_forecast' => $totalPplFc,
            ],
            'series_trend' => [
                ['name' => 'REAL', 'data' => array_values($movSeries['REAL']), 'color' => '#2caffe'],
                ['name' => 'FORECAST', 'data' => array_values($movSeries['FORECAST']), 'color' => '#fec107']
            ],
            'series_trend_orang' => [
                ['name' => 'REAL', 'data' => array_values($pplSeries['REAL']), 'color' => '#2caffe'],
                ['name' => 'FORECAST', 'data' => array_values($pplSeries['FORECAST']), 'color' => '#fec107']
            ],
            'series_opsel' => [
                ['name' => 'XL', 'data' => array_values($opselMov['XL']), 'color' => '#2caffe'],
                ['name' => 'IOH', 'data' => array_values($opselMov['IOH']), 'color' => '#fec107'],
                ['name' => 'TSEL', 'data' => array_values($opselMov['TSEL']), 'color' => '#ff3d60']
            ],
            'series_opsel_orang' => [
                ['name' => 'XL', 'data' => array_values($opselPpl['XL']), 'color' => '#2caffe'],
                ['name' => 'IOH', 'data' => array_values($opselPpl['IOH']), 'color' => '#fec107'],
                ['name' => 'TSEL', 'data' => array_values($opselPpl['TSEL']), 'color' => '#ff3d60']
            ]
        ];
    }

    private function normalizeOpsel(string $opsel): string
    {
        $raw = strtoupper($opsel);
        if (str_contains($raw, 'XL') || str_contains($raw, 'AXIS')) return 'XL';
        if (str_contains($raw, 'INDOSAT') || str_contains($raw, 'IOH') || str_contains($raw, 'TRI')) return 'IOH';
        if (str_contains($raw, 'TELKOMSEL') || str_contains($raw, 'TSEL')) return 'TSEL';
        return 'OTHER';
    }
    
    // Placeholder methods for other routes to prevent errors
    public function nasionalOdProvinsi(Request $request) 
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);

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
                ->where('sm.is_forecast', false)
                ->where('sm.kategori', 'PERGERAKAN')
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
    
    public function nasionalTopKabkota(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);

        // Cache Key
        $cacheKey = 'grafik:nasional:top-kabkota:v1';

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getTopKabKotaData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Top KabKota Error: ' . $e->getMessage());
            $data = $this->getTopKabKotaData($startDate, $endDate);
        }

        return view('grafik-mpd.nasional.top-kabkota', [
            'title' => 'Top 10 Kabupaten/Kota',
            'breadcrumb' => ['Grafik MPD', 'Nasional', 'Top Kab/Kota'],
            'data' => $data
        ]);
    }

    private function getTopKabKotaData($startDate, $endDate)
    {
        // 1. Query: Sum Total by Origin City & Dest City
        // Join with ref_cities to get Name
        
        try {
            $query = DB::table('spatial_movements as sm')
                // Join Origin City
                ->join('ref_cities as oc', 'sm.kode_origin_kabupaten_kota', '=', 'oc.code')
                ->join('ref_provinces as op', 'oc.province_code', '=', 'op.code')
                // Join Dest City
                ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
                ->join('ref_provinces as dp', 'dc.province_code', '=', 'dp.code')
                ->select(
                    'oc.code as origin_code',
                    'oc.name as origin_name',
                    'op.name as origin_prov_name', // Optional: Show Prov
                    'dc.code as dest_code',
                    'dc.name as dest_name',
                    'dp.name as dest_prov_name',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                // ->where('sm.is_forecast', false) // Use Real + Forecast? Or just Real? Usually Real for Rankings.
                // Let's stick to Real for consistency with other ranking pages unless requested otherwise.
                ->where('sm.is_forecast', false) 
                ->where('sm.kategori', 'PERGERAKAN')
                ->groupBy('oc.code', 'oc.name', 'op.name', 'dc.code', 'dc.name', 'dp.name')
                ->get();

        } catch (\Throwable $e) {
             \Illuminate\Support\Facades\Log::error('Top KabKota Query Error: ' . $e->getMessage());
             return ['top_origin' => [], 'top_dest' => [], 'total_national' => 0];
        }

        // 2. Process Data
        $totalNational = $query->sum('total_volume');
        
        // A. Top 10 Origin
        $topOrigin = $query->groupBy('origin_code')
            ->map(function ($rows) use ($totalNational) {
                $first = $rows->first();
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $first->origin_code,
                    'name' => $first->origin_name . ' (' . $first->origin_prov_name . ')', // Append Prov for clarity
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
                $first = $rows->first();
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $first->dest_code,
                    'name' => $first->dest_name . ' (' . $first->dest_prov_name . ')',
                    'total' => $subTotal,
                    'pct' => $totalNational > 0 ? ($subTotal / $totalNational) * 100 : 0
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        return [
            'top_origin' => $topOrigin,
            'top_dest' => $topDest,
            'total_national' => $totalNational
        ];
    }
    public function nasionalModeShare(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);

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
            ->where('sm.kategori', 'PERGERAKAN')
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
        $endDate = \Carbon\Carbon::create(2026, 3, 30);

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
                        ->where('kategori', 'PERGERAKAN')
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
                        ->where('sm.kategori', 'PERGERAKAN')
                        ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->groupBy('n.name')
                        ->orderByDesc('total')
                        ->limit(10)
                        ->get();

                    $secData['top_dest'] = DB::table('spatial_movements as sm')
                        ->join('ref_transport_nodes as n', 'sm.kode_dest_simpul', '=', 'n.code')
                        ->select('n.name', DB::raw('SUM(sm.total) as total'))
                        ->whereIn('sm.kode_moda', $modeCodes)
                        ->where('sm.kategori', 'PERGERAKAN')
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
                        ->where('sm.kategori', 'PERGERAKAN')
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
    
    public function jabodetabekPergerakanOrang(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);

        // Cache Key
        $cacheKey = 'grafik:jabodetabek:pergerakan-orang:v1';

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getJabodetabekChartData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jabodetabek Chart Error: ' . $e->getMessage());
            $data = $this->getJabodetabekChartData($startDate, $endDate);
        }

        return view('grafik-mpd.jabodetabek.pergerakan-orang', [
            'title' => 'Dashboard Pergerakan & Orang Jabodetabek',
            'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang'],
            'data' => $data
        ]);
    }

    private function getJabodetabekChartData($startDate, $endDate)
    {
        $jabodetabekCodes = [
            '3171', '3172', '3173', '3174', '3175', '3101',
            '3201', '3271', '3276',
            '3603', '3671', '3674',
            '3216', '3275'
        ];

        $dates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // Query all Jabo-related movements (origin OR dest in Jabo)
        $daily = DB::table('spatial_movements')
            ->select('tanggal', 'kategori', 'is_forecast',
                'kode_origin_kabupaten_kota as origin',
                'kode_dest_kabupaten_kota as dest',
                DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where(function($q) use ($jabodetabekCodes) {
                $q->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                  ->orWhereIn('kode_dest_kabupaten_kota', $jabodetabekCodes);
            })
            ->groupBy('tanggal', 'kategori', 'is_forecast', 'origin', 'dest')
            ->get();

        // Init series: Intra/Inter × PERGERAKAN/ORANG × Real/Forecast
        $init = fn() => ['REAL' => array_fill_keys($dates, 0), 'FORECAST' => array_fill_keys($dates, 0)];
        $intraMov = $init(); $intraPpl = $init();
        $interMov = $init(); $interPpl = $init();

        foreach ($daily as $row) {
            $originInJabo = in_array($row->origin, $jabodetabekCodes);
            $destInJabo = in_array($row->dest, $jabodetabekCodes);
            $type = $row->is_forecast ? 'FORECAST' : 'REAL';
            $val = (int) $row->total;

            $isIntra = $originInJabo && $destInJabo;

            if ($row->kategori === 'PERGERAKAN') {
                if ($isIntra) $intraMov[$type][$row->tanggal] += $val;
                else $interMov[$type][$row->tanggal] += $val;
            } elseif ($row->kategori === 'ORANG') {
                if ($isIntra) $intraPpl[$type][$row->tanggal] += $val;
                else $interPpl[$type][$row->tanggal] += $val;
            }
        }

        // Combine Intra + Inter for Total charts (backward compat)
        $totalMov = ['REAL' => [], 'FORECAST' => []];
        $totalPpl = ['REAL' => [], 'FORECAST' => []];
        foreach ($dates as $d) {
            $totalMov['REAL'][$d] = ($intraMov['REAL'][$d] ?? 0) + ($interMov['REAL'][$d] ?? 0);
            $totalMov['FORECAST'][$d] = ($intraMov['FORECAST'][$d] ?? 0) + ($interMov['FORECAST'][$d] ?? 0);
            $totalPpl['REAL'][$d] = ($intraPpl['REAL'][$d] ?? 0) + ($interPpl['REAL'][$d] ?? 0);
            $totalPpl['FORECAST'][$d] = ($intraPpl['FORECAST'][$d] ?? 0) + ($interPpl['FORECAST'][$d] ?? 0);
        }

        return [
            'dates' => $dates,
            'summary' => [
                'intra_mov_real' => array_sum($intraMov['REAL']),
                'intra_mov_fc' => array_sum($intraMov['FORECAST']),
                'intra_ppl_real' => array_sum($intraPpl['REAL']),
                'inter_mov_real' => array_sum($interMov['REAL']),
                'inter_mov_fc' => array_sum($interMov['FORECAST']),
                'inter_ppl_real' => array_sum($interPpl['REAL']),
            ],
            // Total (backward compat for existing view)
            'chart_movement' => [
                ['name' => 'REAL', 'data' => array_values($totalMov['REAL']), 'color' => '#2caffe'],
                ['name' => 'FORECAST', 'data' => array_values($totalMov['FORECAST']), 'color' => '#fec107']
            ],
            'chart_people' => [
                ['name' => 'REAL', 'data' => array_values($totalPpl['REAL']), 'color' => '#2caffe'],
                ['name' => 'FORECAST', 'data' => array_values($totalPpl['FORECAST']), 'color' => '#fec107']
            ],
            // Intra/Inter breakdown
            'chart_intra_mov' => [
                ['name' => 'REAL', 'data' => array_values($intraMov['REAL']), 'color' => '#2caffe'],
                ['name' => 'FORECAST', 'data' => array_values($intraMov['FORECAST']), 'color' => '#fec107']
            ],
            'chart_intra_ppl' => [
                ['name' => 'REAL', 'data' => array_values($intraPpl['REAL']), 'color' => '#2caffe'],
                ['name' => 'FORECAST', 'data' => array_values($intraPpl['FORECAST']), 'color' => '#fec107']
            ],
            'chart_inter_mov' => [
                ['name' => 'REAL', 'data' => array_values($interMov['REAL']), 'color' => '#2caffe'],
                ['name' => 'FORECAST', 'data' => array_values($interMov['FORECAST']), 'color' => '#fec107']
            ],
            'chart_inter_ppl' => [
                ['name' => 'REAL', 'data' => array_values($interPpl['REAL']), 'color' => '#2caffe'],
                ['name' => 'FORECAST', 'data' => array_values($interPpl['FORECAST']), 'color' => '#fec107']
            ],
        ];
    }


    public function jabodetabekPergerakanOrangOpsel(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);

        // Cache Key
        $cacheKey = 'grafik:jabodetabek:pergerakan-orang-opsel:v1';

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getJabodetabekOpselData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jabodetabek Opsel Error: ' . $e->getMessage());
            $data = $this->getJabodetabekOpselData($startDate, $endDate);
        }

        return view('grafik-mpd.jabodetabek.pergerakan-orang-opsel', [
            'title' => 'Dashboard Pergerakan & Orang per Operator (Jabodetabek)',
            'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang (Opsel)'],
            'data' => $data
        ]);
    }

    private function getJabodetabekOpselData($startDate, $endDate)
    {
        $jabodetabekCodes = [
            '3171', '3172', '3173', '3174', '3175', '3101',
            '3201', '3271', '3276',
            '3603', '3671', '3674',
            '3216', '3275'
        ];

        $dates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // Query with kategori + origin/dest for Intra/Inter classification
        $query = DB::table('spatial_movements')
            ->select('tanggal', 'opsel', 'kategori',
                'kode_origin_kabupaten_kota as origin',
                'kode_dest_kabupaten_kota as dest',
                DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_forecast', false)
            ->where(function($q) use ($jabodetabekCodes) {
                $q->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                  ->orWhereIn('kode_dest_kabupaten_kota', $jabodetabekCodes);
            })
            ->groupBy('tanggal', 'opsel', 'kategori', 'origin', 'dest')
            ->orderBy('tanggal')
            ->get();

        // Init: Intra/Inter × Pergerakan/Orang × Opsel
        $initOpsel = fn() => ['XL' => array_fill_keys($dates, 0), 'IOH' => array_fill_keys($dates, 0), 'TSEL' => array_fill_keys($dates, 0)];
        $intraMovOpsel = $initOpsel(); $intraPplOpsel = $initOpsel();
        $interMovOpsel = $initOpsel(); $interPplOpsel = $initOpsel();

        foreach ($query as $row) {
            $name = $this->normalizeOpsel($row->opsel);
            if ($name === 'OTHER') continue;

            $isIntra = in_array($row->origin, $jabodetabekCodes) && in_array($row->dest, $jabodetabekCodes);
            $val = (int) $row->total;

            if ($row->kategori === 'PERGERAKAN') {
                if ($isIntra) $intraMovOpsel[$name][$row->tanggal] += $val;
                else $interMovOpsel[$name][$row->tanggal] += $val;
            } elseif ($row->kategori === 'ORANG') {
                if ($isIntra) $intraPplOpsel[$name][$row->tanggal] += $val;
                else $interPplOpsel[$name][$row->tanggal] += $val;
            }
        }

        $formatSeries = fn($data) => [
            ['name' => 'XL', 'data' => array_values($data['XL']), 'color' => '#2caffe'],
            ['name' => 'IOH', 'data' => array_values($data['IOH']), 'color' => '#fec107'],
            ['name' => 'TSEL', 'data' => array_values($data['TSEL']), 'color' => '#ff3d60']
        ];

        // Combine Intra + Inter per opsel for backward-compat
        $totalMovOpsel = ['XL' => [], 'IOH' => [], 'TSEL' => []];
        $totalPplOpsel = ['XL' => [], 'IOH' => [], 'TSEL' => []];
        foreach (['XL', 'IOH', 'TSEL'] as $op) {
            foreach ($dates as $d) {
                $totalMovOpsel[$op][$d] = ($intraMovOpsel[$op][$d] ?? 0) + ($interMovOpsel[$op][$d] ?? 0);
                $totalPplOpsel[$op][$d] = ($intraPplOpsel[$op][$d] ?? 0) + ($interPplOpsel[$op][$d] ?? 0);
            }
        }

        return [
            'dates' => $dates,
            // Total (backward compat)
            'chart_movement' => $formatSeries($totalMovOpsel),
            'chart_people' => $formatSeries($totalPplOpsel),
            // Intra/Inter breakdown
            'chart_intra_mov' => $formatSeries($intraMovOpsel),
            'chart_intra_ppl' => $formatSeries($intraPplOpsel),
            'chart_inter_mov' => $formatSeries($interMovOpsel),
            'chart_inter_ppl' => $formatSeries($interPplOpsel),
        ];
    }
    public function jabodetabekOdKabkota(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);

        // Cache Key
        $cacheKey = 'grafik:jabodetabek:od-kabkota:v2';

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getJabodetabekOdData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jabodetabek OD Error: ' . $e->getMessage());
            $data = $this->getJabodetabekOdData($startDate, $endDate);
        }

        return view('grafik-mpd.jabodetabek.od-kabkota', [
            'title' => 'O-D Kabupaten/Kota (Jabodetabek)',
            'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'O-D Kab/Kota'],
            'data' => $data
        ]);
    }

    private function getJabodetabekOdData($startDate, $endDate)
    {
        $jabodetabekCodes = [
            '3171', '3172', '3173', '3174', '3175', '3101',
            '3201', '3271',
            '3276',
            '3603', '3671', '3674',
            '3216', '3275'
        ];

        // 1. Query Top/Sankey: Sum Total by Origin City & Dest City (INTERNAL FLOW Only)
        try {
            $query = DB::table('spatial_movements as sm')
                ->join('ref_cities as oc', 'sm.kode_origin_kabupaten_kota', '=', 'oc.code')
                ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
                ->select(
                    'oc.code as origin_code',
                    'oc.name as origin_name',
                    'dc.code as dest_code',
                    'dc.name as dest_name',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.is_forecast', false)
                ->where(function($q) use ($jabodetabekCodes) {
                     // Internal O-D: Both must be in Jabodetabek
                     $q->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                       ->whereIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes);
                })
                ->groupBy('oc.code', 'oc.name', 'dc.code', 'dc.name')
                ->get();

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jabodetabek OD Query Error: ' . $e->getMessage());
            $query = collect();
        }

        // 2. Process Top/Sankey Data
        $totalVol = $query->sum('total_volume');

        // A. Top Origin
        $topOrigin = $query->groupBy('origin_code')
            ->map(function ($rows) use ($totalVol) {
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $rows->first()->origin_code,
                    'name' => $rows->first()->origin_name,
                    'total' => $subTotal,
                    'pct' => $totalVol > 0 ? ($subTotal / $totalVol) * 100 : 0
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // B. Top Destination
        $topDest = $query->groupBy('dest_code')
            ->map(function ($rows) use ($totalVol) {
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $rows->first()->dest_code,
                    'name' => $rows->first()->dest_name,
                    'total' => $subTotal,
                    'pct' => $totalVol > 0 ? ($subTotal / $totalVol) * 100 : 0
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // C. Sankey Data
        $sankeyData = $query->map(function($row) {
            return [
                'from' => $row->origin_name,
                'to' => $row->dest_name,
                'weight' => (int) $row->total_volume
            ];
        })->values();

        // 3. Inter Jabodetabek — Top 10 Provinsi Tujuan (Slide 32)
        // Origin in Jabo, Dest OUTSIDE Jabo, grouped by Provinsi Tujuan
        try {
            $interProvinsi = DB::table('spatial_movements as sm')
                ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
                ->join('ref_provinces as dp', 'dc.province_code', '=', 'dp.code')
                ->select('dp.code as prov_code', 'dp.name as prov_name', DB::raw('SUM(sm.total) as total_volume'))
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.is_forecast', false)
                ->where('sm.kategori', 'PERGERAKAN')
                ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->whereNotIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('dp.code', 'dp.name')
                ->orderByDesc('total_volume')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $interProvinsi = collect();
        }

        return [
            'dates' => [],
            'top_origin' => $topOrigin,
            'top_dest' => $topDest,
            'sankey' => $sankeyData,
            'total_volume' => $totalVol,
            'top_inter_provinsi' => $interProvinsi,
        ];
    }
    public function jabodetabekModeShare(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);

        // Cache Key
        $cacheKey = 'grafik:jabodetabek:mode-share:v2';

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getJabodetabekModeShareData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jabodetabek Mode Share Error: ' . $e->getMessage());
            $data = $this->getJabodetabekModeShareData($startDate, $endDate);
        }

        return view('grafik-mpd.jabodetabek.mode-share', [
            'title' => 'Mode Share (Jabodetabek)',
            'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Mode Share'],
            'data' => $data
        ]);
    }

    private function getJabodetabekModeShareData($startDate, $endDate)
    {
        $jabodetabekCodes = [
            '3171', '3172', '3173', '3174', '3175', '3101',
            '3201', '3271',
            '3276',
            '3603', '3671', '3674',
            '3216', '3275'
        ];

        // 1. Get All Modes
        $allModes = DB::table('ref_transport_modes')->select('code', 'name')->orderBy('code')->get();

        // Occupancy Factors (Avg People per Vehicle)
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

        // Mode Colors (approximate based on image/common usage)
        $colors = [
            'Motor' => '#2caffe', // Blue
            'Mobil' => '#00e396', // Green
            'Angkutan Kereta Api Perkotaan' => '#546E7A', // Dark Grey/Blue
            'Angkutan Jalan (Bus AKAP)' => '#ff3d60', // Red
            'Angkutan Jalan (Bus AKDP)' => '#008ffb', // Light Blue
            'Angkutan Kereta Api Antarkota' => '#feb019', // Orange
            'Angkutan Udara' => '#775dd0', // Purple
            'Angkutan Laut' => '#ff4560', 
            'Angkutan Penyeberangan' => '#00D9E9',
            'Angkutan Kereta Api KCJB' => '#A300D6',
            'Sepeda' => '#4CAF50'
        ];

        // 2. Query Data (Real Only, Jabo Origin)
        $query = DB::table('spatial_movements as sm')
            ->select('kode_moda', DB::raw('SUM(total) as total_people'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_forecast', false)
            ->where('sm.kategori', 'PERGERAKAN')
            ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
            ->groupBy('kode_moda')
            ->get()
            ->keyBy('kode_moda');

        $pieMovement = [];
        $piePeople = [];

        foreach ($allModes as $mode) {
            $code = $mode->code;
            $name = $mode->name;
            
            // Get Data or 0
            $ppl = isset($query[$code]) ? (int) $query[$code]->total_people : 0;
            
            // Calculate Movement (Vehicle units)
            $factor = $occupancy[$code] ?? 1;
            $mov = (int) round($ppl / $factor);

            $color = $colors[$name] ?? '#999999';

            $pieMovement[] = ['name' => $name, 'y' => $mov, 'color' => $color];
            $piePeople[] = ['name' => $name, 'y' => $ppl, 'color' => $color];
        }

        // Sort by Y desc (keeping 0s at bottom naturally)
        usort($pieMovement, fn($a, $b) => $b['y'] <=> $a['y']);
        usort($piePeople, fn($a, $b) => $b['y'] <=> $a['y']);

        return [
            'pie_movement' => $pieMovement,
            'pie_people' => $piePeople
        ];
    }
    public function jabodetabekSimpul(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);

        // Cache Key
        $cacheKey = 'grafik:jabodetabek:simpul:v1';

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getJabodetabekSimpulData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jabodetabek Simpul Error: ' . $e->getMessage());
            $data = $this->getJabodetabekSimpulData($startDate, $endDate);
        }

        return view('grafik-mpd.jabodetabek.simpul', [
            'title' => 'Dashboard Simpul Transportasi (Jabodetabek)',
            'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Simpul'],
            'data' => $data
        ]);
    }

    private function getJabodetabekSimpulData($startDate, $endDate)
    {
        // 1. Define Jabodetabek Cities
        $jabodetabekCodes = [
            '3171', '3172', '3173', '3174', '3175', '3101', // DKI
            '3201', '3271', // Bogor
            '3276', // Depok
            '3603', '3671', '3674', // Tangerang
            '3216', '3275' // Bekasi
        ];

        // 2. Define Tabs & Categories
        $tabs = [
            'DARAT' => ['A', 'B'], // Bus AKAP, AKDP
            'LAUT' => ['F', 'G'],  // Laut, Penyeberangan
            'UDARA' => ['H'],      // Udara
            'KERETA' => ['C', 'D', 'E'] // KA Antar, KCJB, KRL
        ];

        $dates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        $result = ['dates' => $dates, 'tabs' => []];

        // 3. Process Each Tab
        foreach ($tabs as $tabName => $modes) {
            
            // Daily Chart Data
            $dailyQuery = DB::table('spatial_movements')
                ->select('tanggal', 'is_forecast', DB::raw('SUM(total) as total'))
                ->where('kategori', 'PERGERAKAN')
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('kode_moda', $modes)
                ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes) // Filter Jabo Origin
                ->groupBy('tanggal', 'is_forecast')
                ->get();

            $seriesReal = array_fill_keys($dates, 0);
            $seriesForecast = array_fill_keys($dates, 0);
            $totalReal = 0;
            $totalForecast = 0;

            foreach ($dailyQuery as $row) {
                if ($row->is_forecast) {
                    $seriesForecast[$row->tanggal] = (int) $row->total;
                    $totalForecast += (int) $row->total;
                } else {
                    $seriesReal[$row->tanggal] = (int) $row->total;
                    $totalReal += (int) $row->total;
                }
            }

            // Top 10 Origin Simpul (Real Only)
            $topOrigin = DB::table('spatial_movements as sm')
                // Join to get Name (optional, or use code)
                ->leftJoin('ref_transport_nodes as n', 'sm.kode_origin_simpul', '=', 'n.code')
                ->select('sm.kode_origin_simpul as code', DB::raw('COALESCE(n.name, sm.kode_origin_simpul) as name'), DB::raw('SUM(sm.total) as total'))
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('sm.kode_moda', $modes)
                ->where('sm.is_forecast', false)
                ->where('sm.kategori', 'PERGERAKAN')
                ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('sm.kode_origin_simpul', 'n.name')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            // Top 10 Dest Simpul (Real Only)
            $topDest = DB::table('spatial_movements as sm')
                ->leftJoin('ref_transport_nodes as n', 'sm.kode_dest_simpul', '=', 'n.code')
                ->select('sm.kode_dest_simpul as code', DB::raw('COALESCE(n.name, sm.kode_dest_simpul) as name'), DB::raw('SUM(sm.total) as total'))
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('sm.kode_moda', $modes)
                ->where('sm.is_forecast', false)
                ->where('sm.kategori', 'PERGERAKAN')
                // Filter Dest Jabo? Probably yes for consistency "Simpul Jabo"
                ->whereIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('sm.kode_dest_simpul', 'n.name')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            // Top 10 OD Route
             $topOD = DB::table('spatial_movements as sm')
                ->leftJoin('ref_transport_nodes as o', 'sm.kode_origin_simpul', '=', 'o.code')
                ->leftJoin('ref_transport_nodes as d', 'sm.kode_dest_simpul', '=', 'd.code')
                ->select(
                    DB::raw("CONCAT(COALESCE(o.name, sm.kode_origin_simpul), ' -> ', COALESCE(d.name, sm.kode_dest_simpul)) as name"),
                    DB::raw('SUM(sm.total) as total')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('sm.kode_moda', $modes)
                ->where('sm.is_forecast', false)
                ->where('sm.kategori', 'PERGERAKAN')
                ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('o.name', 'd.name', 'sm.kode_origin_simpul', 'sm.kode_dest_simpul')
                ->orderByDesc('total')
                ->limit(10)
                ->get();


            $result['tabs'][$tabName] = [
                'sections' => [
                    [
                        'title' => 'Simpul ' . $tabName, // e.g. "Simpul DARAT"
                        'subtitle' => 'Periode 13-30 Maret 2026 (Jabodetabek)',
                        'daily_charts' => [
                            [
                                'label' => 'Total Pergerakan ' . $tabName,
                                'series_real' => array_values($seriesReal),
                                'series_forecast' => array_values($seriesForecast),
                                'total_real' => $totalReal,
                                'total_forecast' => $totalForecast
                            ]
                        ],
                        'top_origin' => $topOrigin,
                        'top_dest' => $topDest,
                        'top_od' => $topOD
                    ]
                ]
            ];
        }

        return $result;
    }

}
