<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', '2026-03-13');
        $endDate = $request->input('end_date', '2026-03-30');

        // Enforce Date Limits Server-Side (13 Mar 2026 - 30 Mar 2026)
        if ($startDate < '2026-03-13') $startDate = '2026-03-13';
        if ($startDate > '2026-03-30') $startDate = '2026-03-30';
        if ($endDate < '2026-03-13') $endDate = '2026-03-13';
        if ($endDate > '2026-03-30') $endDate = '2026-03-30';
        
        $kategoriFilter = $request->input('kategori', 'REAL');
        $isForecast = ($kategoriFilter === 'FORECAST');

        // Cache data for report
        $cacheKey = "dailyreport:text:v2:{$startDate}:{$endDate}:{$isForecast}";
        $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $isForecast) {
            
            // Jabodetabek codes
            $jabodetabekCodes = [
                '3171', '3172', '3173', '3174', '3175', '3101', // DKI
                '3201', '3271', // Bogor
                '3276', // Depok
                '3603', '3671', '3674', // Tangerang
                '3216', '3275' // Bekasi
            ];

            // --- A. NASIONAL ---
            // 1. Total Nasional (PERGERAKAN)
            $nasionalTotal = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)
                ->where('kategori', 'PERGERAKAN')
                ->sum('total');

            // 1b. Total ORANG (Unique Subscriber)
            $nasionalOrang = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)
                ->where('kategori', 'ORANG')
                ->sum('total');

            // 2. Highest Day Nasional
            $nasionalHighest = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)
                ->where('kategori', 'PERGERAKAN')
                ->select('tanggal', DB::raw('SUM(total) as daily_total'))
                ->groupBy('tanggal')
                ->orderByDesc('daily_total')
                ->first();

            // --- B. JABODETABEK ---
            // 1. Total Jabodetabek (PERGERAKAN)
            $jaboTotal = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)
                ->where('kategori', 'PERGERAKAN')
                ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->sum('total');

            // 1b. Total ORANG Jabo
            $jaboOrang = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)
                ->where('kategori', 'ORANG')
                ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->sum('total');

            // 2. Highest Day Jabodetabek
            $jaboHighest = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)
                ->where('kategori', 'PERGERAKAN')
                ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->select('tanggal', DB::raw('SUM(total) as daily_total'))
                ->groupBy('tanggal')
                ->orderByDesc('daily_total')
                ->first();

            // Formatted Dates
            \Carbon\Carbon::setLocale('id');
            $formattedStart = \Carbon\Carbon::parse($startDate)->isoFormat('D MMM YYYY');
            $formattedEnd = \Carbon\Carbon::parse($endDate)->isoFormat('D MMM YYYY');
            
            $nasionalHighestDate = $nasionalHighest ? \Carbon\Carbon::parse($nasionalHighest->tanggal)->isoFormat('dddd, D MMMM YYYY') : '-';
            $jaboHighestDate = $jaboHighest ? \Carbon\Carbon::parse($jaboHighest->tanggal)->isoFormat('dddd, D MMMM YYYY') : '-';

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_string' => "tgl {$formattedStart} s.d. {$formattedEnd}",
                'nasional_total' => $nasionalTotal,
                'nasional_orang' => $nasionalOrang,
                'nasional_highest_date' => $nasionalHighestDate,
                'nasional_highest_total' => $nasionalHighest ? $nasionalHighest->daily_total : 0,
                'jabo_total' => $jaboTotal,
                'jabo_orang' => $jaboOrang,
                'jabo_highest_date' => $jaboHighestDate,
                'jabo_highest_total' => $jaboHighest ? $jaboHighest->daily_total : 0,
            ];
        });

        return view('executive.daily-report', $data);
    }
}

