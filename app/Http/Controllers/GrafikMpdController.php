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
        // Init Daily Dates
        $dates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // 1. Fetch Daily Trend (Real vs Forecast)
        // Query
        $daily = DB::table('spatial_movements')
            ->select('tanggal', 'is_forecast', DB::raw('SUM(total) as total'))
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('tanggal', 'is_forecast')
            ->orderBy('tanggal')
            ->get();

        // SIMULATION MODE: Generate Dummy Data if DB is empty
        if ($daily->isEmpty()) {
             $dates = [];
             $curr = $startDate->copy();
             $simDaily = [];
             
             while ($curr->lte($endDate)) {
                 $d = $curr->format('Y-m-d');
                 $dates[] = $d;
                 
                 // Real
                 $simDaily[] = (object)['tanggal' => $d, 'is_forecast' => false, 'total' => rand(500000, 2000000)];
                 // Forecast
                 $simDaily[] = (object)['tanggal' => $d, 'is_forecast' => true, 'total' => rand(600000, 2100000)];
                 
                 $curr->addDay();
             }
             $daily = collect($simDaily);
             
             // Also simulate Opsel Data
             $opsel = collect([
                 (object)['opsel' => 'XL', 'total' => rand(5000000, 10000000)],
                 (object)['opsel' => 'IOH', 'total' => rand(5000000, 10000000)],
                 (object)['opsel' => 'TSEL', 'total' => rand(8000000, 15000000)],
             ]);
             
             // Override Opsel Logic below
             $opselDaily = collect(); // We will handle opsel daily generation in loop below
        } else {
             // 2. Fetch Daily Operator Breakdown (Real Only usually)
            $opselDaily = DB::table('spatial_movements')
                ->select('tanggal', 'opsel', DB::raw('SUM(total) as total'))
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('is_forecast', false)
                ->groupBy('tanggal', 'opsel')
                ->get();
        }

        $dailySeries = [
            'REAL' => array_fill_keys($dates, 0),
            'FORECAST' => array_fill_keys($dates, 0)
        ];

        foreach ($daily as $row) {
            $type = $row->is_forecast ? 'FORECAST' : 'REAL';
            $dailySeries[$type][$row->tanggal] = (int) $row->total;
        }

        $opselSeries = [
            'XL' => array_fill_keys($dates, 0),
            'IOH' => array_fill_keys($dates, 0),
            'TSEL' => array_fill_keys($dates, 0)
        ];

        if (isset($simDaily)) {
            // Simulation Opsel Daily
             $curr = $startDate->copy();
             while ($curr->lte($endDate)) {
                 $d = $curr->format('Y-m-d');
                 $opselSeries['XL'][$d] = rand(100000, 500000);
                 $opselSeries['IOH'][$d] = rand(100000, 500000);
                 $opselSeries['TSEL'][$d] = rand(200000, 800000);
                 $curr->addDay();
             }
        } else {
            foreach ($opselDaily as $row) {
                 // Normalize
                $raw = strtoupper($row->opsel);
                $name = 'OTHER';
                if (str_contains($raw, 'XL') || str_contains($raw, 'AXIS')) $name = 'XL';
                elseif (str_contains($raw, 'INDOSAT') || str_contains($raw, 'IOH') || str_contains($raw, 'TRI')) $name = 'IOH';
                elseif (str_contains($raw, 'TELKOMSEL') || str_contains($raw, 'TSEL')) $name = 'TSEL';
    
                if ($name === 'OTHER') continue;
                
                $opselSeries[$name][$row->tanggal] += (int) $row->total;
            }
        }

        // 3. Calculate Totals for Summary
        $totalReal = array_sum($dailySeries['REAL']);
        $totalForecast = array_sum($dailySeries['FORECAST']);
        $totalPpl = $totalReal; 

        return [
            'dates' => $dates,
            'summary' => [
                'real' => $totalReal,
                'forecast' => $totalForecast,
                'people' => $totalPpl
            ],
            // Chart 1 & 2 Data (Pergerakan & Orang) - Real vs Forecast
            'series_trend' => [
                ['name' => 'REAL', 'data' => array_values($dailySeries['REAL']), 'color' => '#2caffe'], // Light Blue
                ['name' => 'FORECAST', 'data' => array_values($dailySeries['FORECAST']), 'color' => '#fec107'] // Yellow
            ],
            // Chart 3 & 4 Data (Opsel) - Stacked or Grouped? Image 2 shows Grouped.
            'series_opsel' => [
                ['name' => 'XL', 'data' => array_values($opselSeries['XL']), 'color' => '#2caffe'], // Blue
                ['name' => 'IOH', 'data' => array_values($opselSeries['IOH']), 'color' => '#fec107'], // Yellow
                ['name' => 'TSEL', 'data' => array_values($opselSeries['TSEL']), 'color' => '#ff3d60'] // Red
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
