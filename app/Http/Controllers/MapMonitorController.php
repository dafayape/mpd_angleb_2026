<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Simpul;
use App\Models\SpatialMovement;
use Illuminate\Support\Facades\DB;

class MapMonitorController extends Controller
{
    public function index()
    {
        // Fixed Date Range: 13 March 2026 - 30 March 2026 (Angkutan Lebaran)
        $startDate = \Carbon\Carbon::create(2026, 3, 13);
        $endDate = \Carbon\Carbon::create(2026, 3, 30);
        
        $dates = collect();
        while ($startDate->lte($endDate)) {
            $dates->push($startDate->format('Y-m-d'));
            $startDate->addDay();
        }

        // Sort descending (latest first)
        $dates = $dates->sortDesc();

        return view('map-monitor.index', [
            'title' => 'Map Monitor',
            'breadcrumb' => ['Dashboard', 'Map Monitor'],
            'available_dates' => $dates
        ]);
    }

    public function getData(Request $request)
    {
        try {
            // Periode (date range) support
            $startDate = $request->input('start_date', '2026-03-13');
            $endDate = $request->input('end_date', '2026-03-30');
            $opselFilter = $request->input('opsel', ''); // '', 'TSEL', 'IOH', 'XL'

            // Validate dates
            try {
                $startDate = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
                $endDate = \Carbon\Carbon::parse($endDate)->format('Y-m-d');
            } catch (\Throwable $e) {
                $startDate = '2026-03-13';
                $endDate = '2026-03-30';
            }

            // Ensure start <= end
            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            // Validate opsel
            $validOpsels = ['TSEL', 'IOH', 'XL'];
            if ($opselFilter && !in_array($opselFilter, $validOpsels)) {
                $opselFilter = '';
            }

            // Cache key based on period + opsel - Increment V to force refresh
            $cacheKey = "map_monitor:data:v6:{$startDate}:{$endDate}:{$opselFilter}";

            // Cache for 1 hour (3600s)
            return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $opselFilter) {
                
                \Illuminate\Support\Facades\Log::info("MapMonitor: Generating data for {$startDate} to {$endDate}");

                // (AUTO SEEDING REMOVED - Relying on formal NodeSeeder)

                // 1. Fetch Simpuls (Optimized with PostGIS)
                $simpuls = Simpul::select(
                    'code', 
                    'name', 
                    'category', 
                    'sub_category',
                    'radius',
                    DB::raw('ST_Y(location::geometry) as lat'),
                    DB::raw('ST_X(location::geometry) as lng')
                )->whereNotNull('location')->get();
                
                \Illuminate\Support\Facades\Log::info("MapMonitor: Found " . $simpuls->count() . " nodes with location.");

                // 2. Calculate Density (Volume) — aggregated across the period, filtered by opsel
                $volumeQuery = SpatialMovement::whereBetween('tanggal', [$startDate, $endDate]);
                if ($opselFilter) {
                    $volumeQuery->where('opsel', $opselFilter);
                }
                $volumes = $volumeQuery
                    ->select('kode_origin_simpul', DB::raw('SUM(total) as total_volume'))
                    ->groupBy('kode_origin_simpul')
                    ->pluck('total_volume', 'kode_origin_simpul')
                    ->toArray();

                // --- ULTIMATE FALLBACK: Return empty if DB is empty ---
                if ($simpuls->isEmpty()) {
                    \Illuminate\Support\Facades\Log::warning("MapMonitor: DB Empty.");
                    $volumes = [];
                }

                // 3. Max Volume for scaling (Safe handling for empty arrays)
                $maxVolume = !empty($volumes) ? max($volumes) : 1;

                // 4. Format Data
                $features = $simpuls->map(function ($simpul) use ($volumes, $maxVolume) {
                    $volume = $volumes[$simpul->code] ?? 0;
                    
                    // Color Scaling
                    $color = '#808080'; // Default: Gray
                    if ($volume > 0) {
                        $ratio = $volume / $maxVolume;
                        $color = '#00ff00'; // Green
                        if ($ratio > 0.33) $color = '#ffff00'; // Yellow
                        if ($ratio > 0.66) $color = '#ff0000'; // Red
                    }

                    // RADIUS INTEGRATION: Use radius from DB directly
                    $radius = (float)($simpul->radius ?: 100);

                    return [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [(float)$simpul->lng, (float)$simpul->lat]
                        ],
                        'properties' => [
                            'id' => $simpul->code,
                            'name' => $simpul->name,
                            'category' => $simpul->category,
                            'volume' => $volume,
                            'color' => $color,
                            'radius' => $radius
                        ]
                    ];
                });

                // Format period label
                $periodLabel = \Carbon\Carbon::parse($startDate)->format('d M Y');
                if ($startDate !== $endDate) {
                    $periodLabel .= ' — ' . \Carbon\Carbon::parse($endDate)->format('d M Y');
                }

                return response()->json([
                    'type' => 'FeatureCollection',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'period_label' => $periodLabel,
                    'opsel_filter' => $opselFilter ?: 'Semua Opsel',
                    'max_volume' => $maxVolume,
                    'features' => $features
                ]);

            });

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('MapMonitor Error: ' . $e->getMessage());
             // Format period label for fallback
             $fallbackLabel = \Carbon\Carbon::parse($startDate)->format('d M Y');

             return response()->json([
                'type' => 'FeatureCollection',
                'start_date' => $startDate,
                'end_date' => $endDate ?? $startDate,
                'period_label' => $fallbackLabel,
                'max_volume' => 1,
                'features' => []
             ]);
        }
    }

    public function searchSimpul(Request $request) 
    {
        $search = $request->input('q'); // Select2 sends 'q' parameter

        try {
            $query = Simpul::select('code', 'name', 'category', DB::raw('ST_Y(location::geometry) as lat'), DB::raw('ST_X(location::geometry) as lng'))
                ->whereNotNull('location');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('code', 'ilike', "%{$search}%");
                });
            }

            $simpuls = $query->orderBy('name', 'asc')->limit(100)->get();

            // Return empty results if DB is empty
            if ($simpuls->isEmpty()) {
                return response()->json(['results' => []]);
            }

            $results = $simpuls->map(function($item) {
                return [
                    'id' => $item->code,
                    'text' => '[' . $item->code . '] ' . $item->name . ' (' . $item->category . ')',
                    'lat' => (float)$item->lat,
                    'lng' => (float)$item->lng
                ];
            });

            return response()->json(['results' => $results]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('SearchSimpul Error: ' . $e->getMessage());
            return response()->json(['results' => []]);
        }
    }

    /**
     * Netflow Pergerakan (P2.2)
     * Calculates inflow - outflow per kabupaten/kota
     * Positive = net inflow, Negative = net outflow
     */
    public function getNetflow(Request $request)
    {
        try {
            $startDate = $request->input('start_date', '2026-03-13');
            $endDate = $request->input('end_date', '2026-03-29');

            try {
                $startDate = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
                $endDate = \Carbon\Carbon::parse($endDate)->format('Y-m-d');
            } catch (\Throwable $e) {
                $startDate = '2026-03-13';
                $endDate = '2026-03-29';
            }

            $cacheKey = "netflow:{$startDate}:{$endDate}";

            $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                // Inflow: sum(total) grouped by dest kab/kota
                $inflow = DB::table('spatial_movements')
                    ->select('kode_dest_kabupaten_kota as code', DB::raw('SUM(total) as total'))
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->where('is_forecast', false)
                    ->where('kategori', 'PERGERAKAN')
                    ->groupBy('kode_dest_kabupaten_kota')
                    ->pluck('total', 'code');

                // Outflow: sum(total) grouped by origin kab/kota
                $outflow = DB::table('spatial_movements')
                    ->select('kode_origin_kabupaten_kota as code', DB::raw('SUM(total) as total'))
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->where('is_forecast', false)
                    ->where('kategori', 'PERGERAKAN')
                    ->groupBy('kode_origin_kabupaten_kota')
                    ->pluck('total', 'code');

                // Merge all codes
                $allCodes = $inflow->keys()->merge($outflow->keys())->unique();

                // Get city names
                $cities = DB::table('ref_cities as c')
                    ->join('ref_provinces as p', 'c.province_code', '=', 'p.code')
                    ->select('c.code', 'c.name as city_name', 'p.name as prov_name')
                    ->whereIn('c.code', $allCodes)
                    ->get()
                    ->keyBy('code');

                $result = [];
                foreach ($allCodes as $code) {
                    $inf = $inflow[$code] ?? 0;
                    $outf = $outflow[$code] ?? 0;
                    $net = $inf - $outf;
                    $city = $cities[$code] ?? null;

                    $result[] = [
                        'code' => $code,
                        'name' => $city ? $city->city_name . ' (' . $city->prov_name . ')' : $code,
                        'inflow' => (int) $inf,
                        'outflow' => (int) $outf,
                        'netflow' => (int) $net,
                    ];
                }

                // Sort by absolute netflow desc
                usort($result, fn($a, $b) => abs($b['netflow']) <=> abs($a['netflow']));

                return $result;
            });

            return response()->json([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'data' => $data,
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Netflow Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
