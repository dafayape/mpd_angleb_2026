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
        // 1. Fetch Simpuls with coordinates converted from PostGIS
        // 'location' is GEOGRAPHY(POINT, 4326). casting to geometry allows ST_X/ST_Y
        $simpuls = Simpul::select(
            'code', 
            'name', 
            'category', 
            'sub_category',
            DB::raw('ST_Y(location::geometry) as lat'),
            DB::raw('ST_X(location::geometry) as lng')
        )->whereNotNull('location')->get();

        // 2. Calculate Density (Volume)
        // We use the latest available date for relevant data
        $latestDate = SpatialMovement::max('tanggal');
        
        $volumes = [];
        if ($latestDate) {
            $volumes = SpatialMovement::where('tanggal', $latestDate)
                ->select('kode_origin_simpul', DB::raw('SUM(total) as total_volume'))
                ->groupBy('kode_origin_simpul')
                ->pluck('total_volume', 'kode_origin_simpul')
                ->toArray();
        }

        // 3. Max Volume for scaling
        $maxVolume = max($volumes) ?: 1;

        // 4. Format Data
        $features = $simpuls->map(function ($simpul) use ($volumes, $maxVolume) {
            $volume = $volumes[$simpul->code] ?? 0;
            
            // Calculate Color based on relative density
            // simple linear interpolation or thresholds.
            // Low (<33%), Medium (<66%), High (>66%)
            $ratio = $volume / $maxVolume;
            $color = '#00ff00'; // Green
            if ($ratio > 0.33) $color = '#ffff00'; // Yellow
            if ($ratio > 0.66) $color = '#ff0000'; // Red

            // Radius scales with volume (min 100m, max 2000m?)
            // sqrt scales area linearly with volume
            $radius = 100 + ($ratio * 3000); 

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
    }
}
