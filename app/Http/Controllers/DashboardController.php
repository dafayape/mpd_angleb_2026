<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        // Cache Key for Dashboard - 1 Hour
        // Uses simple cache key assuming data updates via ETL which clears cache or auto-expires
        $cacheKey = 'dashboard:index:v1';

        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
            $startDate = '2026-03-13';
            $endDate = '2026-03-30';

            // 1. Totals — PERGERAKAN (Real & Forecast)
            $totals = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('kategori', 'PERGERAKAN')
                ->selectRaw('
                    SUM(CASE WHEN is_forecast = false THEN total ELSE 0 END) as total_real,
                    SUM(CASE WHEN is_forecast = true THEN total ELSE 0 END) as total_forecast
                ')
                ->first();

            // 1b. Totals — ORANG (Unique Subscriber)
            $totalsOrang = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('kategori', 'ORANG')
                ->selectRaw('
                    SUM(CASE WHEN is_forecast = false THEN total ELSE 0 END) as total_real,
                    SUM(CASE WHEN is_forecast = true THEN total ELSE 0 END) as total_forecast
                ')
                ->first();

            $totalReal = (int) $totals->total_real;
            $totalForecast = (int) $totals->total_forecast;
            $totalOrangReal = (int) ($totalsOrang->total_real ?? 0);
            $totalOrangForecast = (int) ($totalsOrang->total_forecast ?? 0);

            // Avoid division by zero
            $persenCapaian = $totalForecast > 0 ? ($totalReal / $totalForecast) * 100 : 0;
            $selisih = $totalReal - $totalForecast;

            // 2. Chart Opsel: Real Volume per Opsel (Matching Reference Style)
            // Colors: IOH (Yellow/Orange), TSEL (Red), XL (Blue)
            $opselData = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', false)
                ->where('kategori', 'PERGERAKAN')
                ->selectRaw("opsel, SUM(total) as total")
                ->groupBy('opsel')
                ->get();

            // Map to colors
            $opselConfig = [
                'IOH' => ['color' => '#f1b44c', 'label' => 'IOH'],
                'TSEL' => ['color' => '#f46a6a', 'label' => 'TSEL'], // Telekonsel Red
                'XL' => ['color' => '#34c38f', 'label' => 'XL'],    // XL (Reference shows Blue? Let's check image. Text description said Blue. Let's use Blue #556ee6)
            ];
            // Correction: XL usually Blue/Green. Reference image description "XL (Blue)".
            // I'll use standard Admin template Blue: #556ee6
            $opselConfig['XL']['color'] = '#556ee6';

            $opselCategories = [];
            $opselSeriesData = [];

            // Order: IOH, TSEL, XL as per image
            $targetOrder = ['IOH', 'TSEL', 'XL'];

            $opselMap = $opselData->keyBy('opsel');

            foreach ($targetOrder as $opsel) {
                $val = $opselMap->get($opsel)->total ?? 0;
                $opselCategories[] = $opsel;
                $opselSeriesData[] = [
                    'y' => (int) $val,
                    'color' => $opselConfig[$opsel]['color'] ?? '#cccccc',
                ];
            }

            // 3. Chart Moda: Daily Trend
            $modaData = \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                // ->where('is_forecast', false) // Show Real Only? Or Total? Let's show Real.
                ->where('is_forecast', false)
                ->where('kategori', 'PERGERAKAN')
                ->selectRaw("tanggal, kode_moda, SUM(total) as total")
                ->groupBy('tanggal', 'kode_moda')
                ->orderBy('tanggal')
                ->get();

            // Process dates
            $dates = \Carbon\CarbonPeriod::create($startDate, $endDate)->toArray();
            $dateLabels = array_map(fn ($d) => $d->format('d M'), $dates);
            $dateKeys = array_map(fn ($d) => $d->format('Y-m-d'), $dates);

            // Group by Moda
            $groupedModa = $modaData->groupBy('kode_moda');
            $validModes = ['Jalan', 'Kereta Api', 'Udara', 'Laut', 'Penyeberangan']; // Common ones
            $modaSeries = [];

            foreach ($groupedModa as $moda => $rows) {
                $dataPoints = [];
                $rowMap = $rows->keyBy(fn ($item) => \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d'));

                foreach ($dateKeys as $date) {
                    $dataPoints[] = isset($rowMap[$date]) ? (int) $rowMap[$date]->total : 0;
                }

                $modaSeries[] = [
                    'name' => $moda, // You might want to map code to Name e.g. 'KA' -> 'Kereta Api'
                    'data' => $dataPoints,
                ];
            }

            // 4. Analysis Logic
            $topOpsel = $opselData->sortByDesc('real_val')->first();
            $topOpselName = $topOpsel ? $topOpsel->opsel : '-';

            $totalRealFormatted = number_format($totalReal, 0, ',', '.');
            $totalForecastFormatted = number_format($totalForecast, 0, ',', '.');
            $percentFormatted = number_format($persenCapaian, 1, ',', '.');
            $statusStr = $selisih > 0 ? 'melebihi sasaran' : 'masih di bawah sasaran';

            $analysis = [
                'general' => "Total pergerakan aktual tercatat sebesar <strong>{$totalRealFormatted}</strong>, mencapai <strong>{$percentFormatted}%</strong> dari forecast ({$totalForecastFormatted}). Kondisi ini menunjukkan pergerakan {$statusStr}.",
                'opsel' => "Operator seluler dengan kontribusi pergerakan tertinggi adalah <strong>{$topOpselName}</strong>. Tren pergerakan real vs forecast menunjukkan performa operasi di lapangan.",
                'moda' => 'Fluktuasi harian pergerakan moda menunjukkan pola perjalanan masyarakat selama periode Angkutan Lebaran. Puncak pergerakan dapat diamati pada grafik harian.',
            ];

            // P3.6: Dynamic disclaimer — latest data date
            $latestDate = \App\Models\SpatialMovement::where('is_forecast', false)
                ->max('tanggal');
            $disclaimer = $latestDate
                ? 'Data terakhir diperbarui: '.\Carbon\Carbon::parse($latestDate)->isoFormat('D MMMM YYYY')
                : 'Belum ada data yang dimuat.';

            return [
                'total_real' => $totalReal,
                'total_forecast' => $totalForecast,
                'total_orang_real' => $totalOrangReal,
                'total_orang_forecast' => $totalOrangForecast,
                'persen_capaian' => $persenCapaian,
                'disclaimer' => $disclaimer,
                'analysis' => $analysis,
                'chart_opsel' => [
                    'categories' => $opselCategories,
                    'series' => [
                        ['name' => 'Pergerakan', 'data' => $opselSeriesData, 'colorByPoint' => true],
                    ],
                ],
                'chart_moda' => [
                    'categories' => $dateLabels,
                    'series' => $modaSeries,
                ],
            ];
        });

        // Adapting old view variable names to new structure or passing array directly
        // The view expects 'chartData', but I'll pass individual vars
        // to make it easier to maintain in view refactor

        return view('dashboard.index', $data);
    }
}
