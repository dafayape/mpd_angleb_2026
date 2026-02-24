<?php

namespace App\Http\Controllers;

use App\Models\Simpul;
use App\Models\SpatialMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExecutiveSummaryController extends Controller
{
    public function index()
    {
        return view('executive.summary');
    }

    public function getData(Request $request)
    {
        try {
            $startDate = $request->input('start_date', '2026-03-13');
            $endDate = $request->input('end_date', '2026-03-30');

            // Enforce Date Limits Server-Side (13 Mar 2026 - 29 Mar 2026)
            if ($startDate < '2026-03-13') {
                $startDate = '2026-03-13';
            }
            if ($startDate > '2026-03-30') {
                $startDate = '2026-03-30';
            }
            if ($endDate < '2026-03-13') {
                $endDate = '2026-03-13';
            }
            if ($endDate > '2026-03-30') {
                $endDate = '2026-03-30';
            }

            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            $cacheKey = "executive:summary:v1:{$startDate}:{$endDate}";

            return Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {

                // Fetch Simpuls for names
                $simpuls = Simpul::select('code', 'name')->get()->keyBy('code');

                // Get Aggregated Data per Simpul (PERGERAKAN only)
                $raw = SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', 'PERGERAKAN')
                    ->select(
                        'kode_origin_simpul',
                        'is_forecast',
                        DB::raw('SUM(total) as total_volume')
                    )
                    ->groupBy('kode_origin_simpul', 'is_forecast')
                    ->get();

                // Get Unique Subscriber (ORANG) totals
                $orangTotals = SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', 'ORANG')
                    ->selectRaw("
                        SUM(CASE WHEN is_forecast = false THEN total ELSE 0 END) as orang_real,
                        SUM(CASE WHEN is_forecast = true THEN total ELSE 0 END) as orang_forecast
                    ")
                    ->first();

                // Per-Opsel ORANG breakdown (Slide 3, 6)
                $orangPerOpsel = SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', 'ORANG')
                    ->where('is_forecast', false)
                    ->selectRaw("opsel, SUM(total) as total")
                    ->groupBy('opsel')
                    ->get()
                    ->keyBy('opsel');

                $tableData = [];
                foreach ($raw as $row) {
                    $code = $row->kode_origin_simpul;
                    if (! isset($tableData[$code])) {
                        $tableData[$code] = [
                            'code' => $code,
                            'name' => $simpuls[$code]->name ?? $code,
                            'paparan' => 0,
                            'aktual' => 0,
                        ];
                    }
                    if ($row->is_forecast) {
                        $tableData[$code]['paparan'] = (int) $row->total_volume;
                    } else {
                        $tableData[$code]['aktual'] = (int) $row->total_volume;
                    }
                }

                // Sort by Aktual Desc
                $tableData = collect($tableData)->sortByDesc('aktual')->values()->toArray();

                $totalPaparan = array_sum(array_column($tableData, 'paparan'));
                $totalAktual = array_sum(array_column($tableData, 'aktual'));

                // --- Jabodetabek Totals (P2.5) ---
                $jabodetabekCodes = [
                    '3171', '3172', '3173', '3174', '3175', '3101',
                    '3201', '3271', '3276',
                    '3603', '3671', '3674',
                    '3216', '3275'
                ];

                $jaboTotals = SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', 'PERGERAKAN')
                    ->where(function($q) use ($jabodetabekCodes) {
                        $q->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                          ->orWhereIn('kode_dest_kabupaten_kota', $jabodetabekCodes);
                    })
                    ->selectRaw("
                        SUM(CASE WHEN is_forecast = false THEN total ELSE 0 END) as jabo_real,
                        SUM(CASE WHEN is_forecast = true THEN total ELSE 0 END) as jabo_forecast
                    ")
                    ->first();

                $jaboReal = (int) ($jaboTotals->jabo_real ?? 0);
                $jaboForecast = (int) ($jaboTotals->jabo_forecast ?? 0);

                // --- Analysis ---
                $analysis = [];

                // 1. Volume Analysis (Nasional)
                if ($totalPaparan > 0) {
                    $diff = $totalAktual - $totalPaparan;
                    $percentDiff = round((abs($diff) / $totalPaparan) * 100, 1);
                    $trend = $diff >= 0 ? 'melampaui' : 'berada di bawah';
                    $analysis[] = 'Total pergerakan aktual Nasional pada periode ini tercatat sebesar **'.number_format($totalAktual, 0, ',', '.')."**. Angka ini **{$percentDiff}%** {$trend} target paparan (".number_format($totalPaparan, 0, ',', '.').').';
                } else {
                    $analysis[] = 'Total pergerakan aktual Nasional tercatat sebesar **'.number_format($totalAktual, 0, ',', '.').'**.';
                }

                // 1b. Unique Subscriber
                $orangRealVal = (int) ($orangTotals->orang_real ?? 0);
                if ($orangRealVal > 0) {
                    $analysis[] = 'Jumlah unique subscriber (individu) yang melakukan perjalanan tercatat sebesar **'.number_format($orangRealVal, 0, ',', '.').'** orang.';
                }

                // 2. Top Simpul
                if (! empty($tableData)) {
                    $top1 = $tableData[0];
                    $analysis[] = "Simpul transportasi terpadat secara nasional terpantau di **{$top1['name']}** dengan total pergerakan **".number_format($top1['aktual'], 0, ',', '.').'**.';

                    if (count($tableData) > 1) {
                        $top2 = $tableData[1];
                        $top3 = $tableData[2] ?? null;
                        $otherTops = $top3 ? "**{$top2['name']}** dan **{$top3['name']}**" : "**{$top2['name']}**";
                        $analysis[] = 'Simpul utama lainnya: '.$otherTops.'.';
                    }
                }

                // 3. Performance
                $lowPerformers = array_filter($tableData, fn($r) => $r['paparan'] > 0 && ($r['aktual'] / $r['paparan']) < 0.8);
                $highPerformers = array_filter($tableData, fn($r) => $r['paparan'] > 0 && ($r['aktual'] / $r['paparan']) > 1.2);

                if (count($lowPerformers) > 0) {
                    $names = array_slice(array_column($lowPerformers, 'name'), 0, 3);
                    $analysis[] = 'Terdapat **'.count($lowPerformers).' simpul** di bawah target (<80%), antara lain: '.implode(', ', $names).'.';
                }
                if (count($highPerformers) > 0) {
                    $namesHigh = array_slice(array_column($highPerformers, 'name'), 0, 3);
                    $analysis[] = 'Sebanyak **'.count($highPerformers).' simpul** melebihi proyeksi (>120%): '.implode(', ', $namesHigh).'.';
                }

                // 4. Kesimpulan Jabodetabek (P2.5 — Slide 34)
                $jaboAnalysis = [];
                if ($jaboReal > 0) {
                    $jaboPercent = $jaboForecast > 0 ? round(($jaboReal / $jaboForecast) * 100, 1) : 0;
                    $jaboTrend = $jaboReal >= $jaboForecast ? 'melampaui' : 'di bawah';
                    $jaboAnalysis[] = 'Pergerakan wilayah Jabodetabek tercatat **'.number_format($jaboReal, 0, ',', '.').'** ('.($jaboPercent).'% dari forecast), '.$jaboTrend.' target.';
                    $jaboContrib = $totalAktual > 0 ? round(($jaboReal / $totalAktual) * 100, 1) : 0;
                    $jaboAnalysis[] = 'Kontribusi Jabodetabek terhadap pergerakan nasional sebesar **'.$jaboContrib.'%**.';
                }

                // 5. Rekomendasi
                if ($totalAktual > $totalPaparan) {
                    $analysis[] = '**Rekomendasi**: Tambah kapasitas layanan pada simpul-simpul utama yang mengalami lonjakan.';
                } else {
                    $analysis[] = '**Rekomendasi**: Evaluasi simpul dengan performa di bawah target untuk identifikasi peralihan moda atau jalur alternatif.';
                }

                Carbon::setLocale('id');
                $periodLabel = Carbon::parse($startDate)->isoFormat('D MMMM YYYY');
                if ($startDate !== $endDate) {
                    $periodLabel = Carbon::parse($startDate)->isoFormat('D MMM YYYY').' - '.Carbon::parse($endDate)->isoFormat('D MMM YYYY');
                }

                return response()->json([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'period_label' => $periodLabel,
                    'analysis' => $analysis,
                    'analysis_jabodetabek' => $jaboAnalysis,
                    'summary' => [
                        'total_paparan' => $totalPaparan,
                        'total_aktual' => $totalAktual,
                        'total_orang' => $orangRealVal,
                        'orang_per_opsel' => $orangPerOpsel,
                        'selisih' => $totalAktual - $totalPaparan,
                        'persen' => $totalPaparan > 0 ? round(($totalAktual / $totalPaparan) * 100, 1) : 0,
                        'jabo_real' => $jaboReal,
                        'jabo_forecast' => $jaboForecast,
                    ],
                ]);
            });

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
