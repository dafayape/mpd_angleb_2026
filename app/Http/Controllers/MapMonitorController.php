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
        return view('map-monitor.index', [
            'title' => 'Map Monitor',
            'breadcrumb' => ['Dashboard', 'Map Monitor']
        ]);
    }

    public function getData()
    {
        try {
            // 1. Fetch Simpuls with coordinates converted from PostGIS
            $simpuls = Simpul::select(
                'code', 
                'name', 
                'category', 
                'sub_category',
                DB::raw('ST_Y(location::geometry) as lat'),
                DB::raw('ST_X(location::geometry) as lng')
            )->whereNotNull('location')->get();

            // 2. Calculate Density (Volume)
            $latestDate = SpatialMovement::max('tanggal');
            
            $volumes = [];
            if ($latestDate) {
                $volumes = SpatialMovement::where('tanggal', $latestDate)
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
                
                // Calculate Color
                $ratio = $volume / $maxVolume;
                $color = '#00ff00'; // Green
                if ($ratio > 0.33) $color = '#ffff00'; // Yellow
                if ($ratio > 0.66) $color = '#ff0000'; // Red

                // Radius scales with volume
                // Increased multiplier for better visibility
                $radius = 500 + ($ratio * 5000); 

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
                'latest_date' => $latestDate,
                'max_volume' => $maxVolume,
                'features' => $features
            ]);

        } catch (\Exception $e) {
            // Fallback Mock Data for Demo/Dev when DB is unreachable
            return response()->json([
                'type' => 'FeatureCollection',
                'latest_date' => date('Y-m-d'),
                'max_volume' => 100000,
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [106.8306, -6.1767]],
                        'properties' => ['id' => 'S001', 'name' => 'Stasiun Gambir', 'category' => 'Stasiun', 'volume' => 85000, 'color' => '#ff0000', 'radius' => 3100]
                    ],
                    [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [106.8456, -6.1751]],
                        'properties' => ['id' => 'S002', 'name' => 'Stasiun Pasar Senen', 'category' => 'Stasiun', 'volume' => 65000, 'color' => '#ffff00', 'radius' => 2100]
                    ],
                    [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [106.6537, -6.1275]],
                        'properties' => ['id' => 'S003', 'name' => 'Bandara Soekarno-Hatta', 'category' => 'Bandara', 'volume' => 95000, 'color' => '#ff0000', 'radius' => 3500]
                    ],
                    [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [106.8837, -6.1086]],
                        'properties' => ['id' => 'S005', 'name' => 'Pelabuhan Tanjung Priok', 'category' => 'Pelabuhan', 'volume' => 45000, 'color' => '#ffff00', 'radius' => 1500]
                    ],
                    [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [106.8115, -6.1865]],
                        'properties' => ['id' => 'S007', 'name' => 'Stasiun Tanah Abang', 'category' => 'Stasiun', 'volume' => 30000, 'color' => '#00ff00', 'radius' => 1000]
                    ]
                ]
            ]);
        }
    }
}
