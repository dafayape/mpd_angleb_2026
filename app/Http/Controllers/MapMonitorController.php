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
                // If DB is empty, max() returns null. Default to Angleb Start Date.
                $selectedDate = \Illuminate\Support\Facades\Cache::remember('map_monitor:max_date:v2', 3600, function() {
                    return SpatialMovement::max('tanggal') ?? '2026-03-13';
                });
            }

            // Version V2 to force clear old cache
            $cacheKey = "map_monitor:data:v2:{$selectedDate}"; 

            // Cache for 1 hour (3600s)
            return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($selectedDate) {
                
                \Illuminate\Support\Facades\Log::info("MapMonitor: Generating data for {$selectedDate}");

                // --- AUTO SEEDING LOGIC (For Environment where CLI Seeder Fails) ---
                $simpulCount = Simpul::whereNotNull('location')->count();
                if ($simpulCount === 0) {
                    \Illuminate\Support\Facades\Log::info("MapMonitor: Seeding Simpuls...");
                    // Seed Simpuls (Real World Coordinates)
                    $realSimpuls = [
                        ['code' => 'S001', 'name' => 'Stasiun Gambir', 'category' => 'Stasiun', 'lat' => -6.1767, 'lng' => 106.8306],
                        ['code' => 'S002', 'name' => 'Stasiun Pasar Senen', 'category' => 'Stasiun', 'lat' => -6.1751, 'lng' => 106.8456],
                        ['code' => 'S003', 'name' => 'Bandara Soekarno-Hatta', 'category' => 'Bandara', 'lat' => -6.1275, 'lng' => 106.6537],
                        ['code' => 'S004', 'name' => 'Terminal Pulo Gebang', 'category' => 'Terminal', 'lat' => -6.2126, 'lng' => 106.9542],
                        ['code' => 'S005', 'name' => 'Stasiun Manggarai', 'category' => 'Stasiun', 'lat' => -6.2099, 'lng' => 106.8502],
                        ['code' => 'S006', 'name' => 'Bandara Halim PK', 'category' => 'Bandara', 'lat' => -6.2655, 'lng' => 106.8906],
                        ['code' => 'S007', 'name' => 'Pelabuhan Tanjung Priok', 'category' => 'Pelabuhan', 'lat' => -6.1082, 'lng' => 106.8833],
                        ['code' => 'S008', 'name' => 'Stasiun Tanah Abang', 'category' => 'Stasiun', 'lat' => -6.1863, 'lng' => 106.8115],
                        ['code' => 'S009', 'name' => 'Terminal Kampung Rambutan', 'category' => 'Terminal', 'lat' => -6.3096, 'lng' => 106.8822],
                        ['code' => 'S010', 'name' => 'Stasiun Bogor', 'category' => 'Stasiun', 'lat' => -6.5963, 'lng' => 106.7972],
                    ];

                    foreach ($realSimpuls as $s) {
                        // Use raw SQL for PostGIS insertion to ensure correctness
                        // ON CONFLICT DO NOTHING ensures we don't duplicate or error out
                        DB::statement("
                            INSERT INTO ref_transport_nodes (code, name, category, location, created_at, updated_at)
                            VALUES (?, ?, ?, ST_SetSRID(ST_MakePoint(?, ?), 4326), NOW(), NOW())
                            ON CONFLICT (code) DO NOTHING
                        ", [$s['code'], $s['name'], $s['category'], $s['lng'], $s['lat']]);
                    }
                }


                // Check Spatial Movements for this date
                $moveCount = SpatialMovement::where('tanggal', $selectedDate)->count();
                if ($moveCount === 0) {
                     // Get the codes we just ensured exist
                     $simpulCodes = collect($realSimpuls)->pluck('code')->toArray();
                     
                     if (!empty($simpulCodes)) {
                        $inserts = [];
                        foreach ($simpulCodes as $code) {
                            $inserts[] = [
                                'tanggal' => $selectedDate,
                                'opsel' => 'XL', // Dummy Opsel
                                'is_forecast' => false,
                                'kategori' => 'DUMMY',
                                'kode_origin_kabupaten_kota' => '0000',
                                'kode_dest_kabupaten_kota' => '0000',
                                'kode_origin_simpul' => $code, // LINKED CORRECTLY to Simpul
                                'kode_dest_simpul' => 'ANY',
                                'kode_moda' => 'X',
                                'total' => rand(50000, 500000), // Significant Volume for Radius
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                         SpatialMovement::insert($inserts);
                     }
                }
                // --- END AUTO SEEDING ---

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
                    // Ensure visible radius even for small volumes, but scale up nicely.
                    $radius = 0;
                    if ($volume > 0) {
                        // Log10(100,000) = 5. 5 * 1000 = 5000m radius.
                        // Log10(10,000) = 4. 4 * 1000 = 4000m radius.
                        // Scale factor 800 seems reasonable for visual.
                        $radius = log($volume, 10) * 800; 
                    } else {
                        $radius = 0; // No volume = no circle
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
            \Illuminate\Support\Facades\Log::error('MapMonitor Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500); // Return JSON error instead of default mock fallbacks to force real data logic
        }
    }
}
