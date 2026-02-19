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
        // 1. Fetch Daily Trend (Real vs Forecast)
        $daily = DB::table('spatial_movements')
            ->select('tanggal', 'is_forecast', DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('tanggal', 'is_forecast')
            ->orderBy('tanggal')
            ->get();

        $dates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        $series = [
            'REAL' => array_fill_keys($dates, 0),
            'FORECAST' => array_fill_keys($dates, 0)
        ];

        foreach ($daily as $row) {
            $type = $row->is_forecast ? 'FORECAST' : 'REAL';
            $series[$type][$row->tanggal] = (int) $row->total;
        }

        // 2. Fetch Operator Share (Total Period)
        $opsel = DB::table('spatial_movements')
            ->select('opsel', DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('is_forecast', false) // Real only for share? Or both? User usually wants Real share.
            ->groupBy('opsel')
            ->get();
        
        $opselData = [];
        foreach ($opsel as $row) {
            // Normalize
            $raw = strtoupper($row->opsel);
            $name = 'OTHER';
            if (str_contains($raw, 'XL') || str_contains($raw, 'AXIS')) $name = 'XL';
            elseif (str_contains($raw, 'INDOSAT') || str_contains($raw, 'IOH') || str_contains($raw, 'TRI')) $name = 'IOH';
            elseif (str_contains($raw, 'TELKOMSEL') || str_contains($raw, 'TSEL')) $name = 'TSEL';

            if ($name === 'OTHER') continue;

            if (!isset($opselData[$name])) $opselData[$name] = 0;
            $opselData[$name] += $row->total;
        }

        // 3. Calculate Totals
        $totalReal = array_sum($series['REAL']);
        $totalForecast = array_sum($series['FORECAST']);
        $totalPpl = $totalReal; // 1:1 Assumption as per previous logic

        return [
            'dates' => $dates,
            'series' => [
                'real' => array_values($series['REAL']),
                'forecast' => array_values($series['FORECAST'])
            ],
            'pie_opsel' => [
                ['name' => 'XL', 'y' => $opselData['XL'] ?? 0, 'color' => '#556ee6'], // Primary Blue
                ['name' => 'IOH', 'y' => $opselData['IOH'] ?? 0, 'color' => '#f1b44c'], // Warning Yellow
                ['name' => 'TSEL', 'y' => $opselData['TSEL'] ?? 0, 'color' => '#f46a6a'] // Danger Red
            ],
            'summary' => [
                'real' => $totalReal,
                'forecast' => $totalForecast,
                'people' => $totalPpl
            ]
        ];
    }
    
    // Placeholder methods for other routes to prevent errors
    public function nasionalOdProvinsi() { return view('placeholder', ['title' => 'O-D Provinsi', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'O-D Provinsi']]); }
    public function nasionalTopKabkota() { return view('placeholder', ['title' => 'Top Kab/Kota', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Top Kab/Kota']]); }
    public function nasionalModeShare() { return view('placeholder', ['title' => 'Mode Share', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Mode Share']]); }
    public function nasionalSimpul() { return view('placeholder', ['title' => 'Simpul', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Simpul']]); }
    
    public function jabodetabekPergerakanOrang() { return view('placeholder', ['title' => 'Pergerakan & Orang', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang']]); }
    public function jabodetabekPergerakanOrangOpsel() { return view('placeholder', ['title' => 'Pergerakan & Orang (Opsel)', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang (Opsel)']]); }
    public function jabodetabekOdKabkota() { return view('placeholder', ['title' => 'O-D Kab/Kota', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'O-D Kab/Kota']]); }
    public function jabodetabekModeShare() { return view('placeholder', ['title' => 'Mode Share', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Mode Share']]); }
    public function jabodetabekSimpul() { return view('placeholder', ['title' => 'Simpul', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Simpul']]); }

}
