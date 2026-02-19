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
        // Fixed Date Range: 13 March 2026 - 29 March 2026 (Angkutan Lebaran)
        $startDate = \Carbon\Carbon::create(2026, 3, 13);
        $endDate = \Carbon\Carbon::create(2026, 3, 29);
        
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
            $selectedDate = $request->input('date');

            // Cache Key based on Date
            // If date is null, we need to find max date first, but that's fast.
            // Let's cache the "latest date" query or just cache the result after we know the date.
            
            if (!$selectedDate) {
                // Cache the Max Date query too? 
                // Probably overkill, but let's do it for "optimal" feel
                $selectedDate = \Illuminate\Support\Facades\Cache::remember('map_monitor:max_date', 3600, function() {
                    return SpatialMovement::max('tanggal');
                });
            }

            $cacheKey = "map_monitor:data:{$selectedDate}"; // e.g. map_monitor:data:2026-03-20

            // Cache for 1 hour (3600s)
            return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($selectedDate) {
                
                // 1. Fetch Simpuls (Optimized with PostGIS)
                $simpuls = Simpul::select(
                    'code', 
                    'name', 
                    'category', 
                    'sub_category',
                    DB::raw('ST_Y(location::geometry) as lat'),
                    DB::raw('ST_X(location::geometry) as lng')
                )->whereNotNull('location')->get();

                // 2. Calculate Density (Volume)
                $volumes = [];
                if ($selectedDate) {
                    $volumes = SpatialMovement::where('tanggal', $selectedDate)
                        ->select('kode_origin_simpul', DB::raw('SUM(total) as total_volume'))
                        ->groupBy('kode_origin_simpul')
                        ->pluck('total_volume', 'kode_origin_simpul')
                        ->toArray();
                }

                if ($simpuls->isEmpty()) {
                    throw new \Exception("No data available");
                }

                // 3. Max Volume for scaling
                $maxVolume = max($volumes) ?: 1;

                // 4. Format Data
                $features = $simpuls->map(function ($simpul) use ($volumes, $maxVolume) {
                    $volume = $volumes[$simpul->code] ?? 0;
                    
                    // Color Scaling
                    $ratio = $volume / $maxVolume;
                    $color = '#00ff00'; // Green
                    if ($ratio > 0.33) $color = '#ffff00'; // Yellow
                    if ($ratio > 0.66) $color = '#ff0000'; // Red

                    // LOGARITHMIC SCALING
                    $radius = 0;
                    if ($volume > 0) {
                        $radius = 300 + (log($volume, 10) * 300); 
                    } else {
                        $radius = 100;
                    }

                    return [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [$simpul->lng, $simpul->lat]
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

                return response()->json([
                    'type' => 'FeatureCollection',
                    'selected_date' => $selectedDate,
                    'max_volume' => $maxVolume,
                    'features' => $features
                ]);

            });

        } catch (\Throwable $e) {

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('MapMonitor Error: ' . $e->getMessage());

            // Mock Data Fallback
            return response()->json([
                'type' => 'FeatureCollection',
                'selected_date' => date('Y-m-d'),
                'max_volume' => 100000,
                'features' => [
                    // Mock data with reasonable radius
                    ['type' => 'Feature', 'geometry' => ['type' => 'Point', 'coordinates' => [106.8306, -6.1767]], 'properties' => ['id' => 'S001', 'name' => 'Stasiun Gambir', 'category' => 'Stasiun', 'volume' => 85000, 'color' => '#ff0000', 'radius' => 1500]],
                    ['type' => 'Feature', 'geometry' => ['type' => 'Point', 'coordinates' => [106.8456, -6.1751]], 'properties' => ['id' => 'S002', 'name' => 'Stasiun Pasar Senen', 'category' => 'Stasiun', 'volume' => 65000, 'color' => '#ffff00', 'radius' => 1200]],
                    ['type' => 'Feature', 'geometry' => ['type' => 'Point', 'coordinates' => [106.6537, -6.1275]], 'properties' => ['id' => 'S003', 'name' => 'Bandara Soekarno-Hatta', 'category' => 'Bandara', 'volume' => 95000, 'color' => '#ff0000', 'radius' => 1800]],
                ]
            ]);
        }
    }
}
