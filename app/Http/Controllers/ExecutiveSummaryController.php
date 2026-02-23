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
            $endDate = $request->input('end_date', '2026-03-29');

            // Enforce Date Limits Server-Side (13 Mar 2026 - 29 Mar 2026)
            if ($startDate < '2026-03-13') {
                $startDate = '2026-03-13';
            }
            if ($startDate > '2026-03-29') {
                $startDate = '2026-03-29';
            }
            if ($endDate < '2026-03-13') {
                $endDate = '2026-03-13';
            }
            if ($endDate > '2026-03-29') {
                $endDate = '2026-03-29';
            }

            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            $cacheKey = "executive:summary:v1:{$startDate}:{$endDate}";

            return Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {

                // Fetch Simpuls for names
                $simpuls = Simpul::select('code', 'name')->get()->keyBy('code');

                // Get Aggregated Data per Simpul
                $raw = SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->select(
                        'kode_origin_simpul',
                        'is_forecast',
                        DB::raw('SUM(total) as total_volume')
                    )
                    ->groupBy('kode_origin_simpul', 'is_forecast')
                    ->get();

                $tableData = [];
                // Process Raw Data
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

                // --- AI Analysis Logic ---
                $analysis = [];

                // 1. Volume Analysis
                if ($totalPaparan > 0) {
                    $diff = $totalAktual - $totalPaparan;
                    $percentDiff = round((abs($diff) / $totalPaparan) * 100, 1);
                    $trend = $diff >= 0 ? 'melampaui' : 'berada di bawah';
                    $analysis[] = 'Total pergerakan aktual pada periode ini tercatat sebesar **'.number_format($totalAktual, 0, ',', '.')."** orang. Angka ini **{$percentDiff}%** {$trend} target paparan yang telah diprediksi sebesar ".number_format($totalPaparan, 0, ',', '.').'.';
                } else {
                    $analysis[] = 'Total pergerakan aktual tercatat sebesar **'.number_format($totalAktual, 0, ',', '.').'** orang.';
                }

                // 2. Top Simpul Analysis
                if (! empty($tableData)) {
                    $top1 = $tableData[0];
                    $analysis[] = "Titik kepadatan (simpul) tertinggi secara nasional terpantau di **{$top1['name']}** dengan total volume pergerakan mencapai **".number_format($top1['aktual'], 0, ',', '.').'** orang.';

                    if (count($tableData) > 1) {
                        $top2 = $tableData[1];
                        $top3 = $tableData[2] ?? null;
                        $otherTops = $top3 ? "**{$top2['name']}** dan **{$top3['name']}**" : "**{$top2['name']}**";
                        $analysis[] = 'Simpul utama lainnya yang menyumbang volume pergerakan terbesar adalah '.$otherTops.'.';
                    }
                }

                // 3. Achievement Analysis
                $lowPerformers = array_filter($tableData, function ($row) {
                    return $row['paparan'] > 0 && ($row['aktual'] / $row['paparan']) < 0.8;
                });
                $highPerformers = array_filter($tableData, function ($row) {
                    return $row['paparan'] > 0 && ($row['aktual'] / $row['paparan']) > 1.2;
                });

                if (count($lowPerformers) > 0) {
                    $names = array_slice(array_column($lowPerformers, 'name'), 0, 3);
                    $analysis[] = 'Perlu diperhatikan bahwa terdapat **'.count($lowPerformers).' simpul** yang realisasinya berada secara signifikan di bawah target (<80%), di antaranya: '.implode(', ', $names).'.';
                }

                if (count($highPerformers) > 0) {
                    $namesHigh = array_slice(array_column($highPerformers, 'name'), 0, 3);
                    $analysis[] = 'Sebaliknya, **'.count($highPerformers).' simpul** mengalami lonjakan penumpang yang jauh melebihi proyeksi (>120%), seperti pada '.implode(', ', $namesHigh).'.';
                }

                if (count($lowPerformers) == 0 && count($highPerformers) == 0) {
                    $analysis[] = 'Secara umum, mayoritas simpul menunjukkan performa pergerakan yang stabil dan sangat mendekati angka prediksi paparan.';
                }

                // 4. Recommendation
                if ($totalAktual > $totalPaparan) {
                    $analysis[] = 'Rekomendasi: Fokuskan penambahan kapasitas layanan dan personel keamanan pada simpul-simpul utama yang mengalami lonjakan (peringkat 3 teratas) untuk meminimalisir penumpukan penumpang.';
                } else {
                    $analysis[] = 'Rekomendasi: Lakukan evaluasi mendalam terhadap simpul-simpul yang performanya di bawah target. Kemungkinan adanya peralihan moda atau preferensi jalur alternatif perlu dikaji lebih lanjut.';
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
                    'summary' => [
                        'total_paparan' => $totalPaparan,
                        'total_aktual' => $totalAktual,
                        'selisih' => $totalAktual - $totalPaparan,
                        'persen' => $totalPaparan > 0 ? round(($totalAktual / $totalPaparan) * 100, 1) : 0,
                    ],
                ]);
            });

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
