<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mpd;

use App\Http\Controllers\Controller;
use App\Models\SpatialMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MovementAnalyticsController extends Controller
{
    public function getTopRoutes(Request $request): JsonResponse
    {
        $cacheKey = 'top_routes_' . md5(json_encode($request->all()));

        $data = Cache::remember($cacheKey, 3600, function () use ($request) {
            $limit = (int) $request->input('limit', 10);

            return SpatialMovement::query()
                ->select([
                    'kode_origin_simpul as origin',
                    'kode_dest_simpul as destination',
                    'kode_moda as mode',
                    DB::raw('SUM(total) as total_volume'),
                    DB::raw('AVG(distance_meters) as avg_distance')
                ])
                ->groupBy('kode_origin_simpul', 'kode_dest_simpul', 'kode_moda')
                ->orderByDesc('total_volume')
                ->limit($limit)
                ->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function getDailyTrend(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(7)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $cacheKey = "daily_trend_{$startDate}_{$endDate}_" . md5(json_encode($request->all()));

        $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
            return SpatialMovement::query()
                ->select([
                    'tanggal',
                    DB::raw('SUM(total) as total_volume')
                ])
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->groupBy('tanggal')
                ->orderBy('tanggal')
                ->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function getDeviationAnalysis(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->toDateString());
        $cacheKey = "deviation_{$date}_" . md5(json_encode($request->all()));

        $data = Cache::tags(['analytics', 'deviation'])->remember($cacheKey, 3600, function () use ($date) {
            return DB::table('spatial_movements as real_data')
                ->join('spatial_movements as forecast_data', function ($join) {
                    $join->on('real_data.tanggal', '=', 'forecast_data.tanggal')
                        ->on('real_data.opsel', '=', 'forecast_data.opsel')
                        ->on('real_data.kode_origin_simpul', '=', 'forecast_data.kode_origin_simpul')
                        ->on('real_data.kode_dest_simpul', '=', 'forecast_data.kode_dest_simpul')
                        ->on('real_data.kode_moda', '=', 'forecast_data.kode_moda');
                })
                ->select([
                    'real_data.tanggal',
                    'real_data.opsel',
                    'real_data.kode_origin_simpul as origin',
                    'real_data.kode_dest_simpul as destination',
                    'real_data.kode_moda',
                    'real_data.total as real_volume',
                    'forecast_data.total as forecast_volume',
                    DB::raw('ABS(real_data.total - forecast_data.total) as absolute_diff'),
                    DB::raw('CASE WHEN forecast_data.total = 0 THEN 0 ELSE (ABS(real_data.total - forecast_data.total)::float / forecast_data.total) * 100 END as percentage_error')
                ])
                ->where('real_data.tanggal', $date)
                ->where('real_data.is_forecast', false)
                ->where('forecast_data.is_forecast', true)
                ->orderByDesc('absolute_diff')
                ->limit(100)
                ->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
