<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Cache Key for Dashboard - 1 Hour
        // Uses simple cache key assuming data updates via ETL which clears cache or auto-expires
        $cacheKey = 'dashboard:index:v1';

        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
            $startDate = '2026-03-13';
            $endDate = '2026-03-29';

            // 1. Totals (Real & Forecast)
            $totals = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->selectRaw("
                    SUM(CASE WHEN is_forecast = false THEN total ELSE 0 END) as total_real,
                    SUM(CASE WHEN is_forecast = true THEN total ELSE 0 END) as total_forecast
                ")
                ->first();
            
            $totalReal = (int) $totals->total_real;
            $totalForecast = (int) $totals->total_forecast;
            
            // Avoid division by zero
            $persenCapaian = $totalForecast > 0 ? ($totalReal / $totalForecast) * 100 : 0;
            $selisih = $totalReal - $totalForecast; // Positive means exceeding forecast

            // 2. Chart Opsel: Real vs Forecast per Opsel
            $opselData = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->selectRaw("
                    opsel,
                    SUM(CASE WHEN is_forecast = false THEN total ELSE 0 END) as real_val,
                    SUM(CASE WHEN is_forecast = true THEN total ELSE 0 END) as forecast_val
                ")
                ->groupBy('opsel')
                ->get();
            
            // Order: IOH, TSEL, XL (Arbitrary or specific)
            $opselOrder = ['IOH', 'TSEL', 'XL']; // Ensure consistent order
            $opselSeriesReal = [];
            $opselSeriesForecast = [];
            $opselLabels = $opselOrder;

            // Map results to order
            $opselMap = $opselData->keyBy('opsel');
            foreach ($opselOrder as $o) {
                $row = $opselMap->get($o);
                $opselSeriesReal[] = $row ? (int) $row->real_val : 0;
                $opselSeriesForecast[] = $row ? (int) $row->forecast_val : 0;
            }

            // 3. Chart Moda: Daily Trend (Real Data Only for Monitoring Focus)
            $modaData = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                // ->where('is_forecast', false) // Show Real Only? Or Total? Let's show Real.
                ->selectRaw("tanggal, kode_moda, SUM(total) as total") // Aggregating both usually doubles, but here likely we want Real.
                ->where('is_forecast', false)
                ->groupBy('tanggal', 'kode_moda')
                ->orderBy('tanggal')
                ->get();
            
            // Process dates
            $dates = \Carbon\CarbonPeriod::create($startDate, $endDate)->toArray();
            $dateLabels = array_map(fn($d) => $d->format('d M'), $dates);
            $dateKeys = array_map(fn($d) => $d->format('Y-m-d'), $dates);

            // Group by Moda
            $groupedModa = $modaData->groupBy('kode_moda');
            $validModes = ['Jalan', 'Kereta Api', 'Udara', 'Laut', 'Penyeberangan']; // Common ones
            $modaSeries = [];

            foreach ($groupedModa as $moda => $rows) {
                $dataPoints = [];
                $rowMap = $rows->keyBy(fn($item) => \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d'));
                
                foreach ($dateKeys as $date) {
                    $dataPoints[] = isset($rowMap[$date]) ? (int) $rowMap[$date]->total : 0;
                }

                $modaSeries[] = [
                    'name' => $moda, // You might want to map code to Name e.g. 'KA' -> 'Kereta Api'
                    'data' => $dataPoints
                ];
            }

            // 4. Analysis Logic
            $topOpsel = $opselData->sortByDesc('real_val')->first();
            $topOpselName = $topOpsel ? $topOpsel->opsel : '-';

            $totalRealFormatted = number_format($totalReal, 0, ',', '.');
            $totalForecastFormatted = number_format($totalForecast, 0, ',', '.');
            $percentFormatted = number_format($persenCapaian, 1, ',', '.');
            $statusStr = $selisih > 0 ? "melebihi sasaran" : "masih di bawah sasaran";

            $analysis = [
                'general' => "Total pergerakan aktual tercatat sebesar <strong>{$totalRealFormatted}</strong>, mencapai <strong>{$percentFormatted}%</strong> dari forecast ({$totalForecastFormatted}). Kondisi ini menunjukkan pergerakan {$statusStr}.",
                'opsel' => "Operator seluler dengan kontribusi pergerakan tertinggi adalah <strong>{$topOpselName}</strong>. Tren pergerakan real vs forecast menunjukkan performa operasi di lapangan.",
                'moda' => "Fluktuasi harian pergerakan moda menunjukkan pola perjalanan masyarakat selama periode Angkutan Lebaran. Puncak pergerakan dapat diamati pada grafik harian."
            ];

            return [
                'total_real' => $totalReal,
                'total_forecast' => $totalForecast,
                'persen_capaian' => $persenCapaian,
                'analysis' => $analysis,
                'chart_opsel' => [
                    'categories' => $opselLabels,
                    'series' => [
                        ['name' => 'Aktual', 'data' => $opselSeriesReal, 'color' => '#34c38f'], // Green
                        ['name' => 'Forecast', 'data' => $opselSeriesForecast, 'color' => '#f46a6a'] // Red
                    ]
                ],
                'chart_moda' => [
                    'categories' => $dateLabels,
                    'series' => $modaSeries
                ]
            ];
        });

        // Adapting old view variable names to new structure or passing array directly
        // The view expects 'chartData', but I'll pass individual vars 
        // to make it easier to maintain in view refactor
        
        return view('dashboard.index', $data);
    }
}
