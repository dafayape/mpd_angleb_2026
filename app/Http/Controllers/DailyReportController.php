<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', '2026-03-14'); // Default date for testing
        
        // Cache data for report
        $cacheKey = "dailyreport:v1:{$date}";
        $data = Cache::remember($cacheKey, 3600, function () use ($date) {
            
            // 1. Totals
            $totals = \App\Models\SpatialMovement::where('tanggal', $date)
                ->selectRaw("
                    SUM(CASE WHEN is_forecast = false THEN total ELSE 0 END) as total_real,
                    SUM(CASE WHEN is_forecast = true THEN total ELSE 0 END) as total_forecast
                ")
                ->first();

            // 2. By Opsel (Real)
            $opselData = \App\Models\SpatialMovement::where('tanggal', $date)
                ->where('is_forecast', false)
                ->select('opsel', DB::raw('SUM(total) as total'))
                ->groupBy('opsel')
                ->get();

            // 3. By Mode (Real) - Top 3
            $modeData = \App\Models\SpatialMovement::where('tanggal', $date)
                ->where('is_forecast', false)
                ->select('kode_moda', DB::raw('SUM(total) as total'))
                ->groupBy('kode_moda')
                ->orderByDesc('total')
                ->limit(3)
                ->get();

            return [
                'date' => $date,
                'formattedDate' => \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y'),
                'total_real' => (int) $totals->total_real ?? 0,
                'total_forecast' => (int) $totals->total_forecast ?? 0,
                'opsel_data' => $opselData,
                'mode_data' => $modeData
            ];
        });

        return view('executive.daily-report', $data);
    }
}
