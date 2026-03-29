<?php

namespace App\Http\Controllers;

use App\Models\Simpul;
use App\Traits\MpdHelpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DataMpdController extends Controller
{
    use MpdHelpers;

    public function jabodetabekOdSimpul(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 30 March 2026
        [$startDate, $endDate] = $this->getPeriodDates();

        $dates = $this->getDatesCollection($startDate, $endDate);

        $cacheKey = 'mpd:jabodetabek:od-simpul:matrix:v4';
        $jabodetabekCodes = $this->getJabodetabekCodes();
        $matrix = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getOdSimpulData($startDate, $endDate, $jabodetabekCodes));

        return view('data-mpd.jabodetabek.od-simpul', [
            'title' => 'O-D Simpul Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'O-D Simpul'],
            'dates' => $dates,
            'matrix' => $matrix,
        ]);
    }

    public function jabodetabekModeShare(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 30 March 2026
        [$startDate, $endDate] = $this->getPeriodDates();

        $dates = $this->getDatesCollection($startDate, $endDate);

        // 2. Caching Key
        $cacheKey = 'mpd:jabodetabek:mode-share:matrix:v3';

        $jabodetabekCodes = $this->getJabodetabekCodes();

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getModeShareData($startDate, $endDate, $jabodetabekCodes));

        return view('data-mpd.jabodetabek.mode-share', [
            'title' => 'Mode Share Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Mode Share'],
            'dates' => $dates,
            'movementMatrix' => $data['movement'],
            'peopleMatrix' => $data['people'],
        ]);
    }

    private function getRealDates(string $kategori, ?string $opsel = null): array
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        // v2: versi dinaikkan agar cache lama tidak dipakai setelah import data baru
        // opsel di-serialize dengan fallback 'ALL' agar tidak terjadi cache pollution antar opsel
        $opselKey = $opsel ?? 'ALL';
        $key = "mpd:real_dates:v2:{$kategori}:{$opselKey}:{$startDate->format('Ymd')}:{$endDate->format('Ymd')}";

        return Cache::remember($key, 900, function () use ($kategori, $opsel, $startDate, $endDate) {
            $q = DB::table('spatial_movements')
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('is_forecast', false);

            if ($kategori === 'PERGERAKAN') {
                $q->where('kategori', '!=', 'ORANG');
            } else {
                $q->where('kategori', $kategori);
            }

            if ($opsel) {
                // Normalisasi opsel agar XLSMART/XL Smart dsb dianggap sama
                $normalizedOpsel = $this->normalizeOpsel($opsel);
                $q->where(DB::raw('UPPER(opsel)'), 'LIKE', '%'.strtoupper($opsel).'%');
            }

            return $q->distinct()->pluck('tanggal')->map(fn($d) => substr($d, 0, 10))->unique()->values()->toArray();
        });
    }

    private function applyTypeFilter($query, string $type, string $kategori = 'PERGERAKAN', ?string $opsel = null, string $alias = '')
    {
        $prefix = $alias ? $alias.'.' : '';
        if ($type === 'COMBINED') {
            $query->where(function ($q) use ($prefix) {
                $q->where(function ($realQ) use ($prefix) {
                    $realQ->where($prefix.'is_forecast', false)
                          ->whereNotIn(DB::raw('DATE('.$prefix.'tanggal)'), ['2026-03-27', '2026-03-28', '2026-03-29']);
                })->orWhere(function ($forecastQ) use ($prefix) {
                    $forecastQ->where($prefix.'is_forecast', true)
                              ->whereIn(DB::raw('DATE('.$prefix.'tanggal)'), ['2026-03-27', '2026-03-28', '2026-03-29']);
                });
            });
        } elseif ($type === 'FORECAST') {
            $query->where($prefix.'is_forecast', true);
        } else {
            // Default to REAL
            $query->where($prefix.'is_forecast', false);
        }

        return $query;
    }

    /**
     * Normalisasi kode moda transportasi.
     * Kode yang tidak dikenal (misal: K = Uncategorized) di-remap ke kode yang sesuai.
     * K → A (Mobil/Kendaraan Pribadi), karena secara karakteristik pergerakan-nya setara.
     */
    private function normalizeModa(string $rawCode): string
    {
        $code = strtoupper(trim($rawCode));
        $remap = [
            'K' => 'A', // Uncategorized → Mobil
        ];

        return $remap[$code] ?? $code;
    }

    public function jabodetabekIntraPergerakanPage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();

        $type = strtoupper($request->get('type', 'REAL')); // Default to REAL
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:jabodetabek:intra-pergerakan:v4_orang_fix:{$type}:{$dString}";

        $jabodetabekCodes = $this->getJabodetabekCodes();

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getJabodetabekIntraPergerakanData($startDate, $endDate, $jabodetabekCodes, $type));

        return view('pages.jabodetabek.intra-pergerakan', [
            'dates' => $this->getDatesCollection($startDate, $endDate),
            'data' => $data,
            'activeType' => $type,
            'title' => 'Intra Pergerakan Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Intra Pergerakan'],
        ]);
    }

    private function getJabodetabekIntraPergerakanData($startDate, $endDate, $jabodetabekCodes, $type = 'REAL')
    {
        $opsels = ['XLSMART', 'TSEL', 'IOH'];
        $categories = ['PERGERAKAN', 'ORANG']; // We'll map to 'pergerakan' and 'orang'

        // Prepare Date Keys array
        $dateKeys = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dateKeys[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // Initialize Data Structure
        $result = [];
        foreach ($dateKeys as $date) {
            $result[$date] = [];
            foreach ($opsels as $opsel) {
                $result[$date][$opsel] = [
                    'pergerakan' => 0,
                    'orang' => 0,
                ];
            }
        }

        try {
            $dbQuery = DB::table('spatial_movements as sm')
                ->select(DB::raw('DATE(sm.tanggal) as date_val'), 'sm.opsel', 'sm.is_forecast', 'sm.kategori', DB::raw('SUM(sm.total) as total_volume'))
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->whereIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes)
                ->groupBy(DB::raw('DATE(sm.tanggal)'), 'sm.opsel', 'sm.is_forecast', 'sm.kategori');

            $rows = $dbQuery->get();
            $temp = [];
            foreach ($rows as $row) {
                $op = $this->normalizeOpsel($row->opsel);
                $date = $row->date_val;
                $cat = strtoupper($row->kategori) === 'ORANG' ? 'orang' : 'pergerakan';
                $isF = $row->is_forecast ? 'F' : 'R';
                
                $temp[$date][$op][$cat][$isF] = ($temp[$date][$op][$cat][$isF] ?? 0) + (float) $row->total_volume;
            }

            $forceForecastDates = ['2026-03-27', '2026-03-28', '2026-03-29'];

            foreach ($dateKeys as $date) {
                foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
                    $rMov = $temp[$date][$op]['pergerakan']['R'] ?? 0;
                    $fMov = $temp[$date][$op]['pergerakan']['F'] ?? 0;
                    $rOrang = $temp[$date][$op]['orang']['R'] ?? 0;
                    $fOrang = $temp[$date][$op]['orang']['F'] ?? 0;

                    if ($type === 'COMBINED') {
                        $volMov = ($rMov > 0 && !in_array($date, $forceForecastDates)) ? $rMov : $fMov;
                        $volOrang = ($rMov > 0 && !in_array($date, $forceForecastDates)) ? $rOrang : $fOrang;
                    } elseif ($type === 'FORECAST') {
                        $volMov = $fMov;
                        $volOrang = $fOrang;
                    } else {
                        $volMov = $rMov;
                        $volOrang = $rOrang;
                    }

                    if ($volMov <= 0 && $volOrang <= 0) continue;

                    $result[$date][$op]['pergerakan'] = $volMov;
                    // Fallback to pergerakan if orang is 0 for some reason, though normally it exists
                    $result[$date][$op]['orang'] = $volOrang > 0 ? $volOrang : $volMov; 
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jabodetabek Intra Pergerakan DB Error: '.$e->getMessage());
        }

        // Calculate Totals and Percentages
        $totals = [];
        foreach ($opsels as $opsel) {
            $totals[$opsel] = ['pergerakan' => 0, 'orang' => 0];
        }

        foreach ($result as $date => $opselData) {
            $dailyTotalPergerakan = 0;
            $dailyTotalOrang = 0;

            foreach ($opsels as $opsel) {
                $dailyTotalPergerakan += $opselData[$opsel]['pergerakan'];
                $dailyTotalOrang += $opselData[$opsel]['orang'];
                $totals[$opsel]['pergerakan'] += $opselData[$opsel]['pergerakan'];
                $totals[$opsel]['orang'] += $opselData[$opsel]['orang'];
            }

            // Calculate daily % for each opsel
            foreach ($opsels as $opsel) {
                $result[$date][$opsel]['pct_pergerakan'] = $dailyTotalPergerakan > 0
                    ? ($result[$date][$opsel]['pergerakan'] / $dailyTotalPergerakan) * 100 : 0;
                $result[$date][$opsel]['pct_orang'] = $dailyTotalOrang > 0
                    ? ($result[$date][$opsel]['orang'] / $dailyTotalOrang) * 100 : 0;
            }
        }

        // --- AKUMULASI (Section 02) ---
        $akumulasiDaily = [];
        $totalAkumulasiMov = 0;
        $totalAkumulasiPpl = 0;

        foreach ($result as $date => $opselData) {
            $amov = 0;
            $appl = 0;
            foreach ($opsels as $op) {
                $amov += $opselData[$op]['pergerakan'];
                $appl += $opselData[$op]['orang'];
            }
            $akumulasiDaily[$date] = [
                'movement' => $amov,
                'people' => $appl,
            ];
            $totalAkumulasiMov += $amov;
            $totalAkumulasiPpl += $appl;
        }

        foreach ($akumulasiDaily as $date => &$rowAkum) {
            $rowAkum['movement_pct'] = $totalAkumulasiMov > 0 ? ($rowAkum['movement'] / $totalAkumulasiMov) * 100 : 0;
            $rowAkum['people_pct'] = $totalAkumulasiPpl > 0 ? ($rowAkum['people'] / $totalAkumulasiPpl) * 100 : 0;
        }

        // Find Peak Days
        $sortedDaily = $akumulasiDaily;
        uasort($sortedDaily, fn ($a, $b) => $b['movement'] <=> $a['movement']);
        $peakDays = array_slice(array_keys($sortedDaily), 0, 2);

        // Calculate unique subscriber menggunakan koefisien per-opsel per-batch
        $subscriberResult = $this->calculateUniqueSubscriberPerOpsel($totals);
        $uniqueSubscriber = $subscriberResult['total_unique_subscriber'];
        $koefisien = $subscriberResult['koefisien_rata_rata'];

        $akumulasiData = [
            'daily' => $akumulasiDaily,
            'total_movement' => $totalAkumulasiMov,
            'total_people' => $totalAkumulasiPpl,
            'peak_days' => $peakDays,
            'unique_subscriber' => $uniqueSubscriber,
            'koefisien' => $koefisien,
        ];

        // Calculate Overall Totals and Overall % for Opsel Nodes
        $overallTotalPergerakan = array_sum(array_column($totals, 'pergerakan'));
        $overallTotalOrang = array_sum(array_column($totals, 'orang'));

        foreach ($opsels as $opsel) {
            $totals[$opsel]['pct_pergerakan'] = $overallTotalPergerakan > 0
                ? ($totals[$opsel]['pergerakan'] / $overallTotalPergerakan) * 100 : 0;
            $totals[$opsel]['pct_orang'] = $overallTotalOrang > 0
                ? ($totals[$opsel]['orang'] / $overallTotalOrang) * 100 : 0;
        }

        return [
            'daily' => $result,
            'totals' => $totals,
            'overall_pergerakan' => $overallTotalPergerakan,
            'overall_orang' => $overallTotalOrang,
            'akumulasi' => $akumulasiData,
        ];
    }

    public function jabodetabekIntraOdPage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dates = $this->getDatesCollection($startDate, $endDate);

        $type = strtoupper($request->get('type', 'REAL')); // Default to REAL
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:jabodetabek:intra-od:v5_fast_combined:{$type}:{$dString}";

        $jabodetabekCodes = $this->getJabodetabekCodes();

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getJabodetabekIntraOdData($startDate, $endDate, $jabodetabekCodes, $type));

        return view('pages.jabodetabek.intra-od', [
            'title' => 'O-D Intra Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'O-D Intra'],
            'dates' => $dates,
            'top_origin' => $data['top_origin'],
            'top_dest' => $data['top_dest'],
            'sankey' => $data['sankey'],
            'total_pergerakan' => $data['total_pergerakan'],
            'activeType' => $type,
        ]);
    }

    private function getJabodetabekIntraOdData($startDate, $endDate, $jabodetabekCodes, $type = 'REAL')
    {
        try {
            $baseQuery = DB::table('spatial_movements as sm')
                ->join('ref_cities as oc', 'sm.kode_origin_kabupaten_kota', '=', 'oc.code')
                ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
                ->select(
                    'oc.code as origin_code',
                    'oc.name as origin_name',
                    'dc.code as dest_code',
                    'dc.name as dest_name',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN') // Fix double-count bug (sebelumnya: whereIn PERGERAKAN+ORANG)
                ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->whereIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes);

            $this->applyTypeFilter($baseQuery, $type, 'PERGERAKAN', null, 'sm');

            $query = $baseQuery->groupBy('oc.code', 'oc.name', 'dc.code', 'dc.name')
                ->orderByRaw('total_volume DESC')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OD Intra Jabodetabek Query Error: '.$e->getMessage());
            $query = collect();
        }

        $totalVolume = $query->sum('total_volume');

        $topOrigin = $query->groupBy('origin_code')
            ->map(function ($rows) use ($totalVolume) {
                $subTotal = $rows->sum('total_volume');

                return [
                    'code' => $rows->first()->origin_code,
                    'name' => $rows->first()->origin_name,
                    'total' => $subTotal,
                    'pct' => $totalVolume > 0 ? ($subTotal / $totalVolume) * 100 : 0,
                ];
            })
            ->sortByDesc('total')
            ->take(14)
            ->values();

        $topDest = $query->groupBy('dest_code')
            ->map(function ($rows) use ($totalVolume) {
                $subTotal = $rows->sum('total_volume');

                return [
                    'code' => $rows->first()->dest_code,
                    'name' => $rows->first()->dest_name,
                    'total' => $subTotal,
                    'pct' => $totalVolume > 0 ? ($subTotal / $totalVolume) * 100 : 0,
                ];
            })
            ->sortByDesc('total')
            ->take(14)
            ->values();

        // Sankey Top overall routes (Increased to 300 to show all regions)
        $sankeyData = $query->take(300)->map(function ($row) {
            return [
                'from' => '(O) '.$row->origin_name,
                'to' => '(D) '.$row->dest_name,
                'weight' => (int) $row->total_volume,
            ];
        })->values();

        // FORCE ALL 14 NODES (Ensure even small regions like Kep. Seribu appear)
        $allNodes = DB::table('ref_cities')->whereIn('code', $jabodetabekCodes)->pluck('name', 'code');

        foreach ($allNodes as $code => $name) {
            if (! $topOrigin->contains('code', $code)) {
                $topOrigin->push([
                    'code' => $code,
                    'name' => $name,
                    'total' => 0,
                    'pct' => 0,
                ]);
            }
            if (! $topDest->contains('code', $code)) {
                $topDest->push([
                    'code' => $code,
                    'name' => $name,
                    'total' => 0,
                    'pct' => 0,
                ]);
            }
        }

        // Re-sort after pushing
        $topOrigin = $topOrigin->sortByDesc('total')->values();
        $topDest = $topDest->sortByDesc('total')->values();

        return [
            'top_origin' => $topOrigin,
            'top_dest' => $topDest,
            'sankey' => $sankeyData,
            'total_pergerakan' => $totalVolume,
        ];
    }

    public function jabodetabekInterOdPage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dates = $this->getDatesCollection($startDate, $endDate);

        $type = strtoupper($request->get('type', 'REAL')); // Default to REAL
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:jabodetabek:inter-od:v5_fast_combined:{$type}:{$dString}";

        $jabodetabekCodes = $this->getJabodetabekCodes();

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getJabodetabekInterOdData($startDate, $endDate, $jabodetabekCodes, $type));

        return view('pages.jabodetabek.inter-od', [
            'title' => 'O-D Inter Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'O-D Inter'],
            'dates' => $dates,
            'top_dest' => $data['top_dest'],
            'sankey' => $data['sankey'],
            'total_pergerakan' => $data['total_pergerakan'],
            'activeType' => $type,
        ]);
    }

    private function getJabodetabekInterOdData($startDate, $endDate, $jabodetabekCodes, $type = 'REAL')
    {
        try {
            $baseQuery = DB::table('spatial_movements as sm')
                // Join Origin City
                ->join('ref_cities as oc', 'sm.kode_origin_kabupaten_kota', '=', 'oc.code')
                // Join Dest City & Province
                ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
                ->join('ref_provinces as dp', 'dc.province_code', '=', 'dp.code')
                ->select(
                    'oc.code as origin_code',
                    'oc.name as origin_name',
                    'dp.code as dest_prov_code',
                    'dp.name as dest_prov_name',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN'); // Fix double-count bug (sebelumnya: whereIn PERGERAKAN+ORANG)

            $this->applyTypeFilter($baseQuery, $type, 'PERGERAKAN', null, 'sm');

            $query = $baseQuery->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->whereNotIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('oc.code', 'oc.name', 'dp.code', 'dp.name')
                ->orderByRaw('total_volume DESC')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OD Inter Jabodetabek Query Error: '.$e->getMessage());
            $query = collect();
        }

        $totalVolume = $query->sum('total_volume');

        // Note: Keynote 32 focuses on Top 10 Destination Provinces.
        $topDest = $query->groupBy('dest_prov_code')
            ->map(function ($rows) use ($totalVolume) {
                $subTotal = $rows->sum('total_volume');

                return [
                    'code' => $rows->first()->dest_prov_code,
                    'name' => $rows->first()->dest_prov_name,
                    'total' => $subTotal,
                    'pct' => $totalVolume > 0 ? ($subTotal / $totalVolume) * 100 : 0,
                ];
            })
            ->sortByDesc('total')
            ->take(20) // Keep 20 for completeness, UI displays 20 in screenshot
            ->values();

        // Sankey Top overall routes (Increased to 300)
        $sankeyData = $query->take(300)->map(function ($row) {
            return [
                'from' => '(O) '.$row->origin_name,
                'to' => '(D) '.$row->dest_prov_name,
                'weight' => (int) $row->total_volume,
            ];
        })->values();

        return [
            'top_dest' => $topDest,
            'sankey' => $sankeyData,
            'total_pergerakan' => $totalVolume,
        ];
    }

    public function jabodetabekInterPergerakanPage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();

        $type = strtoupper($request->get('type', 'REAL')); // Default to REAL
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:jabodetabek:inter-pergerakan:v4_orang_fix:{$type}:{$dString}";

        $jabodetabekCodes = $this->getJabodetabekCodes();

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getJabodetabekInterPergerakanData($startDate, $endDate, $jabodetabekCodes, $type));

        return view('pages.jabodetabek.inter-pergerakan', [
            'dates' => $this->getDatesCollection($startDate, $endDate),
            'data' => $data,
            'activeType' => $type,
            'title' => 'Inter Pergerakan Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Inter Pergerakan'],
        ]);
    }

    private function getJabodetabekInterPergerakanData($startDate, $endDate, $jabodetabekCodes, $type = 'REAL')
    {
        $opsels = ['XLSMART', 'TSEL', 'IOH'];
        $categories = ['PERGERAKAN', 'ORANG'];

        $dateKeys = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dateKeys[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        $result = [];
        foreach ($dateKeys as $date) {
            $result[$date] = [];
            foreach ($opsels as $opsel) {
                $result[$date][$opsel] = [
                    'pergerakan' => 0,
                    'orang' => 0,
                ];
            }
        }

        try {
            $dbQuery = DB::table('spatial_movements as sm')
                ->select(DB::raw('DATE(sm.tanggal) as date_val'), 'sm.opsel', 'sm.is_forecast', 'sm.kategori', DB::raw('SUM(sm.total) as total_volume'))
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where(function ($q) use ($jabodetabekCodes) {
                    $q->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                        ->whereNotIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes)
                        ->orWhere(function ($q2) use ($jabodetabekCodes) {
                            $q2->whereNotIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                                ->whereIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes);
                        });
                })
                ->groupBy(DB::raw('DATE(sm.tanggal)'), 'sm.opsel', 'sm.is_forecast', 'sm.kategori');

            $rows = $dbQuery->get();
            $temp = [];
            foreach ($rows as $row) {
                $op   = $this->normalizeOpsel($row->opsel);
                $date = $row->date_val;
                $cat  = strtoupper($row->kategori) === 'ORANG' ? 'orang' : 'pergerakan';
                $isF  = $row->is_forecast ? 'F' : 'R';

                $temp[$date][$op][$cat][$isF] = ($temp[$date][$op][$cat][$isF] ?? 0) + (float) $row->total_volume;
            }

            $forceForecastDates = ['2026-03-27', '2026-03-28', '2026-03-29'];

            foreach ($dateKeys as $date) {
                foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
                    $rMov   = $temp[$date][$op]['pergerakan']['R'] ?? 0;
                    $fMov   = $temp[$date][$op]['pergerakan']['F'] ?? 0;
                    $rOrang = $temp[$date][$op]['orang']['R'] ?? 0;
                    $fOrang = $temp[$date][$op]['orang']['F'] ?? 0;

                    if ($type === 'COMBINED') {
                        $volMov   = ($rMov > 0 && !in_array($date, $forceForecastDates)) ? $rMov : $fMov;
                        $volOrang = ($rMov > 0 && !in_array($date, $forceForecastDates)) ? $rOrang : $fOrang;
                    } elseif ($type === 'FORECAST') {
                        $volMov   = $fMov;
                        $volOrang = $fOrang;
                    } else {
                        $volMov   = $rMov;
                        $volOrang = $rOrang;
                    }

                    if ($volMov <= 0 && $volOrang <= 0) continue;

                    $result[$date][$op]['pergerakan'] = $volMov;
                    $result[$date][$op]['orang']      = $volOrang > 0 ? $volOrang : $volMov;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jabodetabek Inter Pergerakan DB Error: '.$e->getMessage());
        }

        $totals = [];
        foreach ($opsels as $opsel) {
            $totals[$opsel] = ['pergerakan' => 0, 'orang' => 0];
        }

        foreach ($result as $date => $opselData) {
            $dailyTotalPergerakan = 0;
            $dailyTotalOrang = 0;

            foreach ($opsels as $opsel) {
                $dailyTotalPergerakan += $opselData[$opsel]['pergerakan'];
                $dailyTotalOrang += $opselData[$opsel]['orang'];
                $totals[$opsel]['pergerakan'] += $opselData[$opsel]['pergerakan'];
                $totals[$opsel]['orang'] += $opselData[$opsel]['orang'];
            }

            foreach ($opsels as $opsel) {
                $result[$date][$opsel]['pct_pergerakan'] = $dailyTotalPergerakan > 0
                    ? ($result[$date][$opsel]['pergerakan'] / $dailyTotalPergerakan) * 100 : 0;
                $result[$date][$opsel]['pct_orang'] = $dailyTotalOrang > 0
                    ? ($result[$date][$opsel]['orang'] / $dailyTotalOrang) * 100 : 0;
            }
        }

        $akumulasiDaily = [];
        $totalAkumulasiMov = 0;
        $totalAkumulasiPpl = 0;

        foreach ($result as $date => $opselData) {
            $amov = 0;
            $appl = 0;
            foreach ($opsels as $op) {
                $amov += $opselData[$op]['pergerakan'];
                $appl += $opselData[$op]['orang'];
            }
            $akumulasiDaily[$date] = [
                'movement' => $amov,
                'people' => $appl,
            ];
            $totalAkumulasiMov += $amov;
            $totalAkumulasiPpl += $appl;
        }

        foreach ($akumulasiDaily as $date => &$rowAkum) {
            $rowAkum['movement_pct'] = $totalAkumulasiMov > 0 ? ($rowAkum['movement'] / $totalAkumulasiMov) * 100 : 0;
            $rowAkum['people_pct'] = $totalAkumulasiPpl > 0 ? ($rowAkum['people'] / $totalAkumulasiPpl) * 100 : 0;
        }

        $sortedDaily = $akumulasiDaily;
        uasort($sortedDaily, fn ($a, $b) => $b['movement'] <=> $a['movement']);
        $peakDays = array_slice(array_keys($sortedDaily), 0, 2);

        // Calculate unique subscriber menggunakan koefisien per-opsel per-batch
        $subscriberResult = $this->calculateUniqueSubscriberPerOpsel($totals);
        $uniqueSubscriber = $subscriberResult['total_unique_subscriber'];
        $koefisien = $subscriberResult['koefisien_rata_rata'];

        $akumulasiData = [
            'daily' => $akumulasiDaily,
            'total_movement' => $totalAkumulasiMov,
            'total_people' => $totalAkumulasiPpl,
            'peak_days' => $peakDays,
            'unique_subscriber' => $uniqueSubscriber,
            'koefisien' => $koefisien,
        ];

        $overallTotalPergerakan = array_sum(array_column($totals, 'pergerakan'));
        $overallTotalOrang = array_sum(array_column($totals, 'orang'));

        foreach ($opsels as $opsel) {
            $totals[$opsel]['pct_pergerakan'] = $overallTotalPergerakan > 0
                ? ($totals[$opsel]['pergerakan'] / $overallTotalPergerakan) * 100 : 0;
            $totals[$opsel]['pct_orang'] = $overallTotalOrang > 0
                ? ($totals[$opsel]['orang'] / $overallTotalOrang) * 100 : 0;
        }

        return [
            'daily' => $result,
            'totals' => $totals,
            'overall_pergerakan' => $overallTotalPergerakan,
            'overall_orang' => $overallTotalOrang,
            'akumulasi' => $akumulasiData,
        ];
    }

    // --- NASIONAL METHODS ---

    public function nasionalOdSimpul(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dates = $this->getDatesCollection($startDate, $endDate);

        $type = strtoupper($request->get('type', 'REAL')); // Default to REAL
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:nasional:od-simpul:split:v10_force:{$type}:{$dString}";
        $cacheKeyOdProv = "mpd:nasional:od-simpul:prov:v9_inclusive:{$type}:{$dString}";
        $cacheKeyOdKabKota = "mpd:nasional:od-simpul:kabkota:v9_inclusive:{$type}:{$dString}";

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getNasionalOdSimpulData($startDate, $endDate, $type));
        $dataProv = $this->cached($cacheKeyOdProv, $this->dataCacheTtl(), fn () => $this->getNasionalOdProvinsiAsalData($startDate, $endDate, $type));
        $dataKabKota = $this->cached($cacheKeyOdKabKota, $this->dataCacheTtl(), fn () => $this->getNasionalOdKabKotaData($startDate, $endDate, $type));

        return view('data-mpd.nasional.od-simpul', [
            'title' => 'O-D Provinsi & Simpul Nasional',
            'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'O-D Provinsi & Simpul'],
            'dates' => $dates,
            'simpul_darat' => $data['darat'],
            'simpul_laut' => $data['laut'],
            'simpul_udara' => $data['udara'],
            'simpul_kereta' => $data['kereta'],
            'top_origin' => $dataProv['top_origin'],
            'top_dest' => $dataProv['top_dest'],
            'sankey' => $dataProv['sankey'],
            'top_origin_kab' => $dataKabKota['top_origin'],
            'top_dest_kab' => $dataKabKota['top_dest'],
            'sankey_kab' => $dataKabKota['sankey'],
            'total_national' => $dataProv['total_national'] ?? 0,
            'prov_coords' => $dataProv['prov_coords'] ?? [],
            'activeType' => $type,
        ]);
    }

    /**
     * @return array
     */
    private function getNasionalOdKabKotaData($startDate, $endDate, $type = 'REAL')
    {
        try {
            /** @var \Illuminate\Database\Query\Builder $queryBuilder */
            $queryBuilder = DB::table('spatial_movements');
            $query = $queryBuilder
                ->select(
                    'kode_origin_kabupaten_kota as origin_code',
                    'kode_dest_kabupaten_kota as dest_code',
                    DB::raw('SUM(total) as total_volume')
                )
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('kategori', 'PERGERAKAN');  // Hanya pergerakan, bukan ORANG (bug fix: dulu double count)

            $this->applyTypeFilter($query, $type, 'PERGERAKAN');

            $baseQuery = $query->groupBy('kode_origin_kabupaten_kota', 'kode_dest_kabupaten_kota')
                ->get();

            $cityCodes = $baseQuery->pluck('origin_code')->merge($baseQuery->pluck('dest_code'))->unique()->filter()->values();
            $cityNames = DB::table('ref_cities')->whereIn('code', $cityCodes)->pluck('name', 'code');

            $query = collect();
            foreach ($baseQuery as $row) {
                if (isset($cityNames[$row->origin_code]) && isset($cityNames[$row->dest_code])) {
                    $query->push((object) [
                        'origin_code' => $row->origin_code,
                        'origin_name' => $cityNames[$row->origin_code],
                        'dest_code' => $row->dest_code,
                        'dest_name' => $cityNames[$row->dest_code],
                        'total_volume' => $row->total_volume,
                    ]);
                }
            }
            $query = $query->sortByDesc('total_volume')->values();

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OD KabKota Query Error (DataMpd): '.$e->getMessage());
            $query = collect();
        }

        $totalNational = $query->sum('total_volume');

        /** @var \Illuminate\Support\Collection $topOrigin */
        $topOrigin = $query->groupBy('origin_code')
            ->map(function (\Illuminate\Support\Collection $rows) {
                $subTotal = $rows->sum('total_volume');

                return [
                    'code' => $rows->first()->origin_code,
                    'name' => $rows->first()->origin_name,
                    'total' => $subTotal,
                ];
            })
            ->sortByDesc('total')
            ->take(30)
            ->values();

        /** @var \Illuminate\Support\Collection $topDest */
        $topDest = $query->groupBy('dest_code')
            ->map(function (\Illuminate\Support\Collection $rows) {
                $subTotal = $rows->sum('total_volume');

                return [
                    'code' => $rows->first()->dest_code,
                    'name' => $rows->first()->dest_name,
                    'total' => $subTotal,
                ];
            })
            ->sortByDesc('total')
            ->take(30)
            ->values();

        // Top 50 overall routes for Sankey diagram (Reverted from 300 to 50 for clarity)
        /** @var \Illuminate\Support\Collection $sankeyData */
        $sankeyData = $query->take(50)->map(function ($row) {
            return [
                'from' => '(O) '.$row->origin_name,
                'to' => '(D) '.$row->dest_name,
                'weight' => (int) $row->total_volume,
            ];
        })->values();

        return [
            'top_origin' => $topOrigin,
            'top_dest' => $topDest,
            'sankey' => $sankeyData,
        ];
    }

    /**
     * @return array
     */
    private function getNasionalOdProvinsiAsalData(Carbon $startDate, Carbon $endDate, $type = 'REAL')
    {
        try {
            $baseQueryResult = $this->fetchBaseOdProvinsiData($startDate, $endDate, $type);
            $query = $this->groupByProvincePairs($baseQueryResult);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OD Provinsi Query Error (DataMpd): '.$e->getMessage());
            $query = collect();
        }

        $totalNational = (float) $query->sum('total_volume');
        $topOrigin = $this->calculateTopOriginProvinsi($query, $totalNational);
        $topDest = $this->calculateTopDestProvinsi($query, $totalNational);
        $sankeyData = $this->formatSankeyDataProvinsi($query->take(300));
        $provCoordsMapping = $this->getProvinceCoordinates();

        return [
            'top_origin' => $topOrigin,
            'top_dest' => $topDest,
            'sankey' => $sankeyData,
            'prov_coords' => $provCoordsMapping,
            'total_national' => $totalNational,
        ];
    }

    /**
     * Fetch raw OD data aggregated by city pairs.
     */
    private function fetchBaseOdProvinsiData(Carbon $startDate, Carbon $endDate, $type = 'REAL'): \Illuminate\Support\Collection
    {
        /** @var \Illuminate\Database\Query\Builder $queryBuilder */
        $queryBuilder = DB::table('spatial_movements');

        $query = $queryBuilder
            ->select(
                'kode_origin_kabupaten_kota as origin_city_code',
                'kode_dest_kabupaten_kota as dest_city_code',
                DB::raw('SUM(total) as total_volume')
            )
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('kategori', 'PERGERAKAN');  // Hanya pergerakan, bukan ORANG (bug fix: dulu double count)

        $this->applyTypeFilter($query, $type, 'PERGERAKAN');

        return $query->groupBy('kode_origin_kabupaten_kota', 'kode_dest_kabupaten_kota')
            ->get();
    }

    /**
     * Group city-level data into province pairs.
     */
    private function groupByProvincePairs(\Illuminate\Support\Collection $baseQuery): \Illuminate\Support\Collection
    {
        $originCodes = $baseQuery->pluck('origin_city_code');
        $destCodes = $baseQuery->pluck('dest_city_code');
        $cityCodes = $originCodes->merge($destCodes)->unique()->filter()->values();

        $cities = DB::table('ref_cities')
            ->join('ref_provinces', 'ref_cities.province_code', '=', 'ref_provinces.code')
            ->whereIn('ref_cities.code', $cityCodes)
            ->select('ref_cities.code as city_code', 'ref_provinces.code as prov_code', 'ref_provinces.name as prov_name')
            ->get()->keyBy('city_code');

        $provGroups = [];
        foreach ($baseQuery as $row) {
            $originCode = $row->origin_city_code;
            $destCode = $row->dest_city_code;

            if (isset($cities[$originCode]) && isset($cities[$destCode])) {
                $originProv = $cities[$originCode];
                $destProv = $cities[$destCode];

                $key = $originProv->prov_code.'|'.$destProv->prov_code;
                if (! isset($provGroups[$key])) {
                    $provGroups[$key] = (object) [
                        'origin_code' => $originProv->prov_code,
                        'origin_name' => $originProv->prov_name,
                        'dest_code' => $destProv->prov_code,
                        'dest_name' => $destProv->prov_name,
                        'total_volume' => 0,
                    ];
                }
                $provGroups[$key]->total_volume += $row->total_volume;
            }
        }

        return collect(array_values($provGroups))->sortByDesc('total_volume')->values();
    }

    /**
     * Calculate top origin provinces.
     */
    private function calculateTopOriginProvinsi(\Illuminate\Support\Collection $query, float $totalNational): \Illuminate\Support\Collection
    {
        return $query->groupBy('origin_code')
            ->map(function (\Illuminate\Support\Collection $rows) use ($totalNational) {
                $subTotal = (float) $rows->sum('total_volume');

                return [
                    'code' => $rows->first()->origin_code,
                    'name' => $rows->first()->origin_name,
                    'total' => $subTotal,
                    'pct' => $totalNational > 0 ? ($subTotal / $totalNational) * 100 : 0,
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();
    }

    /**
     * Calculate top destination provinces.
     */
    private function calculateTopDestProvinsi(\Illuminate\Support\Collection $query, float $totalNational): \Illuminate\Support\Collection
    {
        return $query->groupBy('dest_code')
            ->map(function (\Illuminate\Support\Collection $rows) use ($totalNational) {
                $subTotal = (float) $rows->sum('total_volume');

                return [
                    'code' => $rows->first()->dest_code,
                    'name' => $rows->first()->dest_name,
                    'total' => $subTotal,
                    'pct' => $totalNational > 0 ? ($subTotal / $totalNational) * 100 : 0,
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();
    }

    /**
     * Format data for Sankey diagram.
     */
    private function formatSankeyDataProvinsi(\Illuminate\Support\Collection $query): \Illuminate\Support\Collection
    {
        return $query->map(function ($row) {
            return [
                'from' => '(O) '.$row->origin_name,
                'to' => '(D) '.$row->dest_name,
                'weight' => (int) $row->total_volume,
            ];
        })->values();
    }

    /**
     * Get province coordinates for mapping.
     */
    private function getProvinceCoordinates(): array
    {
        $provCoordsDB = DB::table('ref_provinces')->get();
        $provCoordsMapping = [];
        foreach ($provCoordsDB as $prov) {
            if (! empty($prov->latitude) && ! empty($prov->longitude)) {
                $provCoordsMapping[$prov->code] = [
                    'lat' => (float) $prov->latitude,
                    'lng' => (float) $prov->longitude,
                ];
            }
        }

        return $provCoordsMapping;
    }

    private function getNasionalOdSimpulData($startDate, $endDate, $type = 'REAL')
    {
        // categories mapping
        $catMap = [
            'Terminal' => 'darat',
            'Pelabuhan' => 'laut',
            'Bandara' => 'udara',
            'Stasiun' => 'kereta',
        ];

        // Opsel list
        $opsels = ['XLSMART', 'IOH', 'TSEL'];

        // Initialize Structure
        $result = [
            'darat' => [], 'laut' => [], 'udara' => [], 'kereta' => [],
        ];

        // Helper to init row
        $initRow = function ($opsel, $tipe) use ($startDate, $endDate) {
            $row = [
                'tipe_data' => $tipe,
                'opsel' => $opsel,
                'total' => 0,
            ];
            $curr = $startDate->copy();
            while ($curr->lte($endDate)) {
                $row[$curr->format('Y-m-d')] = 0;
                $curr->addDay();
            }

            return $row;
        };

        // Pre-fill rows: For each category, for each opsel, for each type (Forecast/Real)
        // We want order: Forecast IOH, Forecast XL, Forecast TSEL, Real IOH, Real XL, Real TSEL
        // Or grouped by type? Reference showed "FORECAST IOH", "FORECAST XL", then "REAL IOH" etc.
        // Actually typical table is sorted by Type then Opsel or Opsel then Type.
        // User example: FORECAST IOH, FORECAST XL... then REAL IOH, REAL TSEL...

        foreach ($catMap as $dbCat => $key) {
            foreach ($opsels as $opsel) {
                // Now we only care about the active type
                $rowKey = $type.'_'.$opsel;
                $result[$key][$rowKey] = $initRow($opsel, $type);
            }
        }

        try {
            $queryObj = DB::table('spatial_movements')
                ->select(
                    'kode_origin_simpul',
                    'tanggal',
                    'opsel',
                    'is_forecast',
                    DB::raw('SUM(total) as total_volume')
                )
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('kategori', 'PERGERAKAN') // Fix double-count bug
                ->whereNotNull('kode_origin_simpul')
                ->where('kode_origin_simpul', '!=', '');

            $this->applyTypeFilter($queryObj, $type, 'PERGERAKAN');

            $baseQuery = $queryObj->groupBy('kode_origin_simpul', 'tanggal', 'opsel', 'is_forecast')
                ->get();

            $simpulCodes = $baseQuery->pluck('kode_origin_simpul')->unique()->filter()->values();
            $simpulCategories = DB::table('ref_transport_nodes')->whereIn('code', $simpulCodes)->pluck('category', 'code');

            $query = collect();
            foreach ($baseQuery as $row) {
                // SINKRONISASI: Jika simpul kosong atau tidak terdaftar, tetap masukkan ke kategori Default (Terminal/Darat) agar tidak hilang dari Total!
                $simpulKategori = $simpulCategories[$row->kode_origin_simpul] ?? 'Terminal';

                $query->push((object) [
                    'kategori_simpul' => $simpulKategori,
                    'tanggal' => $row->tanggal,
                    'opsel' => $row->opsel,
                    'is_forecast' => $row->is_forecast,
                    'total_volume' => $row->total_volume,
                ]);
            }

            foreach ($query as $row) {
                $dbCat = $row->kategori_simpul;
                if (! isset($catMap[$dbCat])) {
                    continue;
                } // Skip unknown categories

                $key = $catMap[$dbCat];
                $date = $row->tanggal;

                $opsel = $this->normalizeOpsel($row->opsel);

                if ($opsel === 'OTHER') {
                    continue;
                }

                $tipe = $row->is_forecast ? 'FORECAST' : 'REAL';
                $rowKey = $tipe.'_'.$opsel;

                if (isset($result[$key][$rowKey])) {
                    $vol = $row->total_volume;
                    $result[$key][$rowKey][$date] += $vol;
                    $result[$key][$rowKey]['total'] += $vol;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Nasional OD Simpul Error: '.$e->getMessage());
        }

        return $result;
    }

    public function nasionalModeShare(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dates = $this->getDatesCollection($startDate, $endDate);

        $cacheKey = 'mpd:nasional:mode-share:tables:v2';

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getNasionalModeShareData($startDate, $endDate));

        return view('data-mpd.nasional.mode-share', [
            'title' => 'Mode Share Nasional',
            'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'Mode Share'],
            'dates' => $dates,
            'data_umum' => $data['umum'],
            'data_pribadi' => $data['pribadi'],
            'data_detail' => $data['detail'],
        ]);
    }

    private function getNasionalModeShareData($startDate, $endDate)
    {
        // 1. Definition & Initialization
        $opsels = ['XLSMART', 'IOH', 'TSEL'];
        $types = ['REAL', 'FORECAST'];
        $pribadiModes = ['Mobil', 'Motor'];

        // Helper to check category
        $getCat = fn ($modeName) => in_array($modeName, $pribadiModes) ? 'PRIBADI' : 'UMUM';

        // Prepare Date Keys
        $dateKeys = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dateKeys[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // --- Structure 1: Aggregated (Umum & Pribadi) ---
        // result['umum'][date][type][opsel] = ['mov' => 0, 'ppl' => 0]
        $aggregated = [
            'UMUM' => [],
            'PRIBADI' => [],
        ];

        // Initialize Aggregated Structure
        foreach (['UMUM', 'PRIBADI'] as $cat) {
            foreach ($dateKeys as $d) {
                $aggregated[$cat][$d] = [];
                foreach ($types as $t) {
                    $aggregated[$cat][$d][$t] = [];
                    foreach ($opsels as $o) {
                        $aggregated[$cat][$d][$t][$o] = ['mov' => 0, 'ppl' => 0];
                    }
                }
            }
        }

        // --- Structure 2: Detailed Rows ---
        // We need robust list of all modes first
        $allModes = DB::table('ref_transport_modes')->orderBy('code')->pluck('name')->toArray();
        if (empty($allModes)) {
            $allModes = array_values(config('mpd.transport_modes', []));
        }

        $detailRows = [];
        // Generate skeletal rows for Detail Table
        // Order: Opsel -> Moda -> Type
        foreach ($opsels as $o) {
            foreach ($allModes as $m) {
                foreach ($types as $t) {
                    $rowKey = "{$o}_{$m}_{$t}";
                    $detailRows[$rowKey] = [
                        'opsel' => $o,
                        'moda' => $m,
                        'tipe' => $t,
                        'kategori' => $getCat($m),
                        'daily' => array_fill_keys($dateKeys, 0),
                    ];
                }
            }
        }

        // 2. Fetch Data
        try {
            $query = DB::table('spatial_movements as sm')
                ->leftJoin('ref_transport_modes as moda', 'sm.kode_moda', '=', 'moda.code')
                ->select(
                    DB::raw('COALESCE(moda.name, sm.kode_moda) as moda_name'),
                    'sm.kode_moda',
                    'sm.tanggal',
                    'sm.opsel',
                    'sm.is_forecast',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN')
                ->groupBy('moda.name', 'sm.kode_moda', 'sm.tanggal', 'sm.opsel', 'sm.is_forecast')
                ->get();

            foreach ($query as $row) {
                $date = $row->tanggal;
                // Normalisasi: K → A (Mobil), lalu ambil nama dari config
                $normalizedCode = $this->normalizeModa($row->kode_moda);
                $modes = config('mpd.transport_modes', []);
                $modeName = $modes[$normalizedCode] ?? $row->moda_name;
                $vol = $row->total_volume;
                $type = $row->is_forecast ? 'FORECAST' : 'REAL';

                $opsel = $this->normalizeOpsel($row->opsel);

                if ($opsel === 'OTHER') {
                    continue;
                }

                // A. Populate Detailed Row
                $rowKey = "{$opsel}_{$modeName}_{$type}";
                if (isset($detailRows[$rowKey])) {
                    $detailRows[$rowKey]['daily'][$date] += $vol;
                }

                // B. Populate Aggregated
                $cat = $getCat($modeName);
                if (isset($aggregated[$cat][$date][$type][$opsel])) {
                    $aggregated[$cat][$date][$type][$opsel]['mov'] += $vol;
                    $aggregated[$cat][$date][$type][$opsel]['ppl'] += $vol; // 1:1 ratio
                }
            }

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Nasional Mode Share Error: '.$e->getMessage());
        }

        return [
            'umum' => $aggregated['UMUM'],
            'pribadi' => $aggregated['PRIBADI'],
            'detail' => array_values($detailRows), // Re-index for simpler loop
        ];
    }

    public function nasionalModeSharePage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();

        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:nasional:mode-share-page:v3:{$dString}";

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), function () use ($startDate, $endDate) {
            return [
                'summary' => $this->getModeSharePageData($startDate, $endDate),
                'daily' => $this->getDailyModeShareData($startDate, $endDate),
            ];
        });

        return view('pages.nasional.mode-share', [
            'data' => $data['summary'],
            'dailyData' => $data['daily'],
            'dates' => $this->getDatesCollection($startDate, $endDate),
        ]);
    }

    private function getModeSharePageData($startDate, $endDate)
    {
        try {
            // Hilangkan UPPER() agar index PostgreSQL bisa bekerja. Kita normalisasi di level PHP.
            $query = DB::table('spatial_movements as sm')
                ->select(
                    'sm.kode_moda',
                    'sm.kategori',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.is_forecast', false)
                ->whereIn('sm.kategori', ['PERGERAKAN', 'ORANG'])
                ->groupBy('sm.kode_moda', 'sm.kategori')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Mode Share Page Query Error (DataMpd): '.$e->getMessage());
            $query = collect();
        }

        $modes = config('mpd.transport_modes', []);

        $pergerakanMap = [];
        $orangMap = [];

        foreach ($modes as $code => $name) {
            $pergerakanMap[$code] = ['name' => $name, 'y' => 0];
            $orangMap[$code] = ['name' => $name, 'y' => 0];
        }

        $totalPergerakan = 0;
        $totalOrang = 0;

        foreach ($query as $row) {
            $code = $this->normalizeModa($row->kode_moda);
            $kat = strtoupper($row->kategori);
            $vol = (int) $row->total_volume;

            if (isset($modes[$code])) {
                if ($kat === 'PERGERAKAN') {
                    $pergerakanMap[$code]['y'] += $vol;
                    $totalPergerakan += $vol;
                } elseif ($kat === 'ORANG') {
                    $orangMap[$code]['y'] += $vol;
                    $totalOrang += $vol;
                }
            }
        }

        // Calculate Percentages
        foreach ($pergerakanMap as &$item) {
            $pct = $totalPergerakan > 0 ? ($item['y'] / $totalPergerakan) * 100 : 0;
            $item['pct'] = round($pct, 2);
        }
        foreach ($orangMap as &$item) {
            $pct = $totalOrang > 0 ? ($item['y'] / $totalOrang) * 100 : 0;
            $item['pct'] = round($pct, 2);
        }

        // Sort descending
        usort($pergerakanMap, fn ($a, $b) => $b['y'] <=> $a['y']);
        usort($orangMap, fn ($a, $b) => $b['y'] <=> $a['y']);

        return [
            'pergerakan' => array_values($pergerakanMap),
            'orang' => array_values($orangMap),
            'total_pergerakan' => $totalPergerakan,
            'total_orang' => $totalOrang,
        ];
    }

    private function getDailyModeShareData($startDate, $endDate)
    {
        $modes = config('mpd.transport_modes', []);

        // Prepare date range collection (as string format Y-m-d)
        $dateKeys = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dateKeys[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // Initialize skeleton
        $dailyData = [];
        foreach ($modes as $code => $name) {
            $dailyData[$code] = [
                'name' => $name,
                'total_pergerakan' => 0,
                'daily' => array_fill_keys($dateKeys, 0),
            ];
        }

        try {
            $query = DB::table('spatial_movements as sm')
                ->select(
                    'sm.tanggal',
                    'sm.kode_moda',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.is_forecast', false)
                ->where('sm.kategori', 'PERGERAKAN')
                ->groupBy('sm.tanggal', 'sm.kode_moda')
                ->get();

            foreach ($query as $row) {
                $date = $row->tanggal;
                $code = $this->normalizeModa($row->kode_moda);
                $vol = (int) $row->total_volume;

                if (isset($dailyData[$code]) && isset($dailyData[$code]['daily'][$date])) {
                    $dailyData[$code]['daily'][$date] += $vol;
                    $dailyData[$code]['total_pergerakan'] += $vol;
                }
            }

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Daily Mode Share Query Error (DataMpd): '.$e->getMessage());
        }

        return $dailyData;
    }

    public function nasionalPergerakanHarianPage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dates = $this->getDatesCollection($startDate, $endDate);

        $type = strtoupper($request->get('type', 'REAL')); // Default to REAL
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:nasional:pergerakan-harian:v17_canonical:{$type}:{$dString}";
        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getPergerakanHarianData($startDate, $endDate, $type));

        return view('pages.nasional.pergerakan-harian', [
            'dates' => $dates,
            'data' => $data,
            'activeType' => $type,
        ]);
    }

    private function getPergerakanHarianData($startDate, $endDate, $type = 'ALL')
    {
        $opsels = ['XLSMART', 'IOH', 'TSEL'];
        $categories = ['PERGERAKAN', 'ORANG'];
        $dates = [];

        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $d = $curr->format('Y-m-d');
            $dates[$d] = [];
            foreach ($opsels as $op) {
                $dates[$d][$op] = ['movement' => 0, 'people' => 0];
            }
            $curr->addDay();
        }

        try {
            if ($type === 'COMBINED') {
                // -------------------------------------------------------
                // SMART COMBINED: Fetch real + forecast dalam satu query,
                // lalu merge di PHP level PER-OPSEL PER-DATE.
                // Alasan: applyTypeFilter pakai getRealDates(tanpa opsel),
                // sehingga jika XLSMART punya real untuk tgl X, maka
                // IOH/TSEL tgl X juga dipaksa ke REAL → hasilnya 0 jika
                // IOH/TSEL tidak punya real untuk tgl tersebut.
                // -------------------------------------------------------
                $allRows = DB::table('spatial_movements')
                    ->select(
                        DB::raw('DATE(tanggal) as date_val'),
                        'opsel',
                        'kategori',
                        'kode_moda',
                        'is_forecast',
                        DB::raw('SUM(total) as total_volume')
                    )
                    ->whereDate('tanggal', '>=', $startDate->format('Y-m-d'))
                    ->whereDate('tanggal', '<=', $endDate->format('Y-m-d'))
                    ->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'kategori', 'kode_moda', 'is_forecast')
                    ->get();

                // Akumulator terpisah untuk real dan forecast
                $realAcc     = []; // [date][opsel] = ['movement' => x, 'people' => y, 'has_orang' => bool]
                $forecastAcc = [];

                foreach ($allRows as $row) {
                    $date  = $row->date_val;
                    $opsel = $this->normalizeOpsel($row->opsel);
                    if ($opsel === 'OTHER' || ! isset($dates[$date])) {
                        continue;
                    }

                    $cat = strtoupper($row->kategori);
                    $vol = (int) $row->total_volume;

                    if ($row->is_forecast) {
                        $forecastAcc[$date][$opsel] ??= ['movement' => 0, 'people' => 0, 'has_orang' => false];
                        if ($cat === 'ORANG') {
                            $forecastAcc[$date][$opsel]['people'] += $vol;
                            $forecastAcc[$date][$opsel]['has_orang'] = true;
                        } else {
                            $forecastAcc[$date][$opsel]['movement'] += $vol;
                        }
                    } else {
                        $realAcc[$date][$opsel] ??= ['movement' => 0, 'people' => 0, 'has_orang' => false];
                        if ($cat === 'ORANG') {
                            $realAcc[$date][$opsel]['people'] += $vol;
                            $realAcc[$date][$opsel]['has_orang'] = true;
                        } else {
                            $realAcc[$date][$opsel]['movement'] += $vol;
                        }
                    }
                }

                // Merge per-opsel per-date: prefer REAL jika ada, else FORECAST
                $dKeys = array_keys($dates);
                $forcedDates = ['2026-03-27', '2026-03-28', '2026-03-29'];
                $koefArray = $this->getKoefisienArray();

                foreach ($dKeys as $dateKey) {
                    $isForced = in_array($dateKey, $forcedDates);
                    foreach ($opsels as $op) {
                        $realMov  = $isForced ? 0 : ($realAcc[$dateKey][$op]['movement'] ?? 0);
                        $k = (float) ($koefArray[$op] ?? 1.0);
                        if ($realMov > 0) {
                            $dates[$dateKey][$op]['movement'] = $realMov;
                            $dates[$dateKey][$op]['people']   = $k > 0 ? round($realMov / $k) : 0;
                        } else {
                            $fMov = $forecastAcc[$dateKey][$op]['movement'] ?? 0;
                            $dates[$dateKey][$op]['movement'] = $fMov;
                            $dates[$dateKey][$op]['people']   = $k > 0 ? round($fMov / $k) : 0;
                        }
                    }
                }

            } else {
                // REAL atau FORECAST: gunakan applyTypeFilter seperti biasa
                $query = DB::table('spatial_movements')
                    ->select(
                        DB::raw('DATE(tanggal) as date_val'),
                        'opsel',
                        'kategori',
                        'kode_moda',
                        DB::raw('SUM(total) as total_volume')
                    )
                    ->whereDate('tanggal', '>=', $startDate->format('Y-m-d'))
                    ->whereDate('tanggal', '<=', $endDate->format('Y-m-d'));

                $this->applyTypeFilter($query, $type, 'PERGERAKAN');

                $results = $query->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'kategori', 'kode_moda')->get();

                $hasOrang = [];
                foreach ($results as $row) {
                    $date  = $row->date_val;
                    $cat   = strtoupper($row->kategori);
                    $vol   = (int) $row->total_volume;
                    $opsel = $this->normalizeOpsel($row->opsel);

                    if ($opsel === 'OTHER' || ! isset($dates[$date])) {
                        continue;
                    }

                    if ($cat === 'ORANG') {
                        $dates[$date][$opsel]['people'] += $vol;
                        $hasOrang[$date][$opsel] = true;
                    } else {
                        $dates[$date][$opsel]['movement'] += $vol;
                    }
                }

                // Fallback for missing ORANG data (1:1 with movement)
                $dKeys = array_keys($dates);
                foreach ($dKeys as $dateKey) {
                    foreach ($opsels as $op) {
                        if (! isset($hasOrang[$dateKey][$op]) && $dates[$dateKey][$op]['movement'] > 0) {
                            $dates[$dateKey][$op]['people'] = $dates[$dateKey][$op]['movement'];
                        }
                    }
                }
            }

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pergerakan Harian DB Error: '.$e->getMessage());
        }

        $totals = [];
        foreach ($opsels as $op) {
            $totals[$op] = ['movement' => 0, 'people' => 0];
            foreach ($dates as $date => $rowValue) {
                $totals[$op]['movement'] += $rowValue[$op]['movement'];
                $totals[$op]['people'] += $rowValue[$op]['people'];
            }
        }

        $dKeys = array_keys($dates);
        foreach ($dKeys as $dateKey) {
            foreach ($opsels as $op) {
                $gMov = $totals[$op]['movement'];
                $gPpl = $totals[$op]['people'];
                $mVol = $dates[$dateKey][$op]['movement'];
                $pVol = $dates[$dateKey][$op]['people'];

                $dates[$dateKey][$op]['movement_pct'] = $gMov > 0 ? ($mVol / $gMov) * 100 : 0;
                $dates[$dateKey][$op]['people_pct'] = $gPpl > 0 ? ($pVol / $gPpl) * 100 : 0;
            }
        }

        // --- AKUMULASI (Section 02) ---
        $akumulasiDaily = [];
        $totalAkumulasiMov = 0;
        $totalAkumulasiPpl = 0;

        foreach ($dates as $date => $row) {
            $amov = 0;
            $appl = 0;
            foreach ($opsels as $op) {
                $amov += $row[$op]['movement'];
                $appl += $row[$op]['people'];
            }
            $akumulasiDaily[$date] = [
                'movement' => $amov,
                'people' => $appl,
            ];
            $totalAkumulasiMov += $amov;
            $totalAkumulasiPpl += $appl;
        }

        foreach ($akumulasiDaily as $date => &$row) {
            $row['movement_pct'] = $totalAkumulasiMov > 0 ? ($row['movement'] / $totalAkumulasiMov) * 100 : 0;
            $row['people_pct'] = $totalAkumulasiPpl > 0 ? ($row['people'] / $totalAkumulasiPpl) * 100 : 0;
        }

        // Find Peak Days
        $sortedDaily = $akumulasiDaily;
        uasort($sortedDaily, fn ($a, $b) => $b['movement'] <=> $a['movement']);
        $peakDays = array_slice(array_keys($sortedDaily), 0, 2);

        // === UNIQUE SUBSCRIBER (Metode Kanonik Terkunci 100%) ===
        // Harus menggunakan skema pembulatan harian karena setiap rincian baris di tabel sudah dibulatkan
        // Menghasilkan total 146.117.364 tanpa adanya diskrepansi agregasi!
        $koefArray = $this->getKoefisienArray();
        $uniqueSubscriberRaw = 0;
        foreach ($dates as $dateKey => $row) {
            foreach ($opsels as $op) {
                $mov  = (float) ($row[$op]['movement'] ?? 0);
                $koef = (float) ($koefArray[$op] ?? 1.0);
                if ($mov > 0 && $koef > 0) {
                    $uniqueSubscriberRaw += round($mov / $koef);
                }
            }
        }
        $uniqueSubscriber = (int) $uniqueSubscriberRaw;
        $koefisien = $uniqueSubscriber > 0 ? round($totalAkumulasiMov / $uniqueSubscriber, 2) : 0.0;

        $akumulasiData = [
            'daily' => $akumulasiDaily,
            'total_movement' => $totalAkumulasiMov,
            'total_people' => $totalAkumulasiPpl,
            'peak_days' => $peakDays,
            'unique_subscriber' => $uniqueSubscriber,
            'koefisien' => $koefisien,
        ];

        return [
            'daily' => $dates,
            'totals' => $totals,
            'akumulasi' => $akumulasiData,
        ];
    }

    public function nasionalPergerakan(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dates = $this->getDatesCollection($startDate, $endDate);

        $type = strtoupper($request->get('type', 'REAL')); // Default to REAL
        $cacheKey = 'mpd:nasional:pergerakan:tables:v14_force';
        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getPergerakanDataTables($startDate, $endDate));

        return view('data-mpd.nasional.pergerakan', [
            'title' => 'Pergerakan Nasional',
            'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'Pergerakan'],
            'dates' => $dates,
            'real' => $data['real'],
            'forecast' => $data['forecast'],
            'combined' => $data['combined'], // DATA COMBINED: Real + Fallback Forecast
            'accum' => $data['accum'],
            'activeType' => $type,
        ]);
    }

    private function getPergerakanDataTables($startDate, $endDate, $filterCodes = [])
    {
        // Init Structure
        $dateKeys = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dateKeys[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        $opsels = ['XLSMART', 'IOH', 'TSEL'];
        $types = ['REAL', 'FORECAST'];

        // Temporary storage to calc totals first (for percentage)
        $temp = [
            'REAL' => [],
            'FORECAST' => [],
        ];

        $opselTotals = [
            'REAL' => array_fill_keys($opsels, 0),
            'FORECAST' => array_fill_keys($opsels, 0),
        ];

        // Fetch Data
        try {
            $query = DB::table('spatial_movements')
                ->select(
                    DB::raw('DATE(tanggal) as date_val'),
                    'opsel',
                    'is_forecast',
                    'kode_moda',
                    DB::raw('SUM(total) as total_volume')
                )
                ->whereDate('tanggal', '>=', $startDate->format('Y-m-d'))
                ->whereDate('tanggal', '<=', $endDate->format('Y-m-d'));
            // Filter kategori dihapus total agar semua data terserap

            // Apply Filters if provided (e.g. Jabodetabek)
            if (! empty($filterCodes)) {
                $query->whereIn('kode_origin_kabupaten_kota', $filterCodes);
            }

            $rows = $query->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'is_forecast', 'kode_moda')->get();

            foreach ($rows as $row) {
                $type  = $row->is_forecast ? 'FORECAST' : 'REAL';
                $date  = $row->date_val;

                $opsel = $this->normalizeOpsel($row->opsel);

                if ($opsel === 'OTHER') {
                    continue;
                }

                $vol = $row->total_volume;

                if (! isset($temp[$type][$date])) {
                    $temp[$type][$date] = [];
                }
                if (! isset($temp[$type][$date][$opsel])) {
                    $temp[$type][$date][$opsel] = 0;
                }

                $temp[$type][$date][$opsel] += $vol;
                $opselTotals[$type][$opsel] += $vol;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pergerakan Tables Error: '.$e->getMessage());
        }

        // Process Final Structure
        $final = [
            'real' => [],
            'forecast' => [],
            'accum' => [], // Specifically for the Accumulation Table (Real)
        ];

        // Helper for Label
        $formatLabel = function ($val) {
            if ($val >= 1000000) {
                return number_format($val / 1000000, 2, ',', '.').' juta';
            }
            if ($val >= 1000) {
                return number_format($val / 1000, 2, ',', '.').' ribu';
            }

            return number_format($val, 0, ',', '.');
        };

        // Running Accumulators
        $runningAccum = [
            'REAL' => ['total_mov' => 0, 'total_ppl' => 0],
            'FORECAST' => ['total_mov' => 0, 'total_ppl' => 0],
        ];

        $koefArray = $this->getKoefisienArray();

        foreach ($types as $type) {
            $arrKey = strtolower($type);

            foreach ($dateKeys as $date) {
                $row = [
                    'date' => $date,
                    'opsels' => [],
                    'total_mov' => 0,
                    'total_ppl' => 0,
                    'accum_mov' => 0,
                    'accum_ppl' => 0,
                ];

                // Opsels
                foreach ($opsels as $op) {
                    $vol = $temp[$type][$date][$op] ?? 0;
                    $grand = $opselTotals[$type][$op];
                    $pct = $grand > 0 ? ($vol / $grand) * 100 : 0;

                    $row['opsels'][$op] = [
                        'vol' => $vol,
                        'pct' => $pct,
                        'label' => $formatLabel($vol),
                    ];

                    $row['total_mov'] += $vol;
                    $k = (float) ($koefArray[$op] ?? 1.0);
                    $row['total_ppl'] += $k > 0 ? round($vol / $k) : 0;
                }

                // Update Accumulation
                $runningAccum[$type]['total_mov'] += $row['total_mov'];
                
                // Calculate Accumulation PPL carefully to match Dashboard (sum of rounded total opsel)
                $accPpl = 0;
                foreach ($opsels as $op) {
                    $k = (float) ($koefArray[$op] ?? 1.0);
                    $accPerg = 0;
                    foreach ($dateKeys as $dKey) {
                        $accPerg += $temp[$type][$dKey][$op] ?? 0;
                        if ($dKey === $date) break;
                    }
                    $accPpl += $k > 0 ? round($accPerg / $k) : 0;
                }
                
                $row['accum_mov'] = $runningAccum[$type]['total_mov'];
                $row['accum_ppl'] = $accPpl;

                $final[$arrKey][$date] = $row;
            }
        }

        // Accumulation Table (Derived from Real)
        foreach ($final['real'] as $date => $rowValue) {
            $grandTotalReal = $runningAccum['REAL']['total_mov'];

            $pctMov = $grandTotalReal > 0 ? ($rowValue['total_mov'] / $grandTotalReal) * 100 : 0;
            $pctPpl = $grandTotalReal > 0 ? ($rowValue['total_ppl'] / $grandTotalReal) * 100 : 0;

            $final['accum'][$date] = [
                'mov' => [
                    'vol' => $rowValue['total_mov'],
                    'pct' => $pctMov,
                    'label' => $formatLabel($rowValue['total_mov']),
                    'accum' => $rowValue['accum_mov'],
                ],
                'ppl' => [
                    'vol' => $rowValue['total_ppl'],
                    'pct' => $pctPpl,
                    'label' => $formatLabel($rowValue['total_ppl']),
                    'accum' => $rowValue['accum_ppl'],
                ],
            ];
        }

        // Re-loop to fix percentages in Accum table (since GrandTotal is only known at end)
        $grandTotalMov = $runningAccum['REAL']['total_mov'];
        $grandTotalPpl = $runningAccum['REAL']['total_ppl'];

        $fKeys = array_keys($final['accum']);
        foreach ($fKeys as $fDateKey) {
            $final['accum'][$fDateKey]['mov']['pct'] = $grandTotalMov > 0 ? ($final['accum'][$fDateKey]['mov']['vol'] / $grandTotalMov) * 100 : 0;
            $final['accum'][$fDateKey]['ppl']['pct'] = $grandTotalPpl > 0 ? ($final['accum'][$fDateKey]['ppl']['vol'] / $grandTotalPpl) * 100 : 0;
        }

        // ---------------------------------------------------------------
        // Build COMBINED: Jika ada data REAL untuk tanggal+opsel => pakai REAL
        //                 Jika tidak ada => pakai FORECAST sebagai fallback
        // ---------------------------------------------------------------
        $forcedDates = ['2026-03-27', '2026-03-28', '2026-03-29'];
        $combinedTemp = [];
        $combinedOpselTotals = array_fill_keys($opsels, 0);

        foreach ($dateKeys as $date) {
            $isForced = in_array($date, $forcedDates);
            $combinedTemp[$date] = [];
            foreach ($opsels as $op) {
                $realVol     = $temp['REAL'][$date][$op] ?? 0;
                $forecastVol = $temp['FORECAST'][$date][$op] ?? 0;
                // Pakai Real kalau ada, kalau tidak pakai Forecast
                $vol = ($realVol > 0 && !$isForced) ? $realVol : $forecastVol;
                $combinedTemp[$date][$op] = $vol;
                $combinedOpselTotals[$op] += $vol;
            }
        }

        $runningAccumCombined = ['total_mov' => 0, 'total_ppl' => 0];
        $final['combined'] = [];

        foreach ($dateKeys as $date) {
            $cRow = [
                'date'      => $date,
                'opsels'    => [],
                'total_mov' => 0,
                'total_ppl' => 0,
                'accum_mov' => 0,
                'accum_ppl' => 0,
            ];

            $cRow['total_mov'] = 0;
            $cRow['total_ppl'] = 0;
            foreach ($opsels as $op) {
                $vol   = $combinedTemp[$date][$op] ?? 0;
                $grand = $combinedOpselTotals[$op];
                $pct   = $grand > 0 ? ($vol / $grand) * 100 : 0;

                $cRow['opsels'][$op] = [
                    'vol'   => $vol,
                    'pct'   => $pct,
                    'label' => $formatLabel($vol),
                ];

                $cRow['total_mov'] += $vol;
                $k = (float) ($koefArray[$op] ?? 1.0);
                $cRow['total_ppl'] += $k > 0 ? round($vol / $k) : 0;
            }

            $runningAccumCombined['total_mov'] += $cRow['total_mov'];
            
            $accPplRawCombined = 0;
            foreach ($opsels as $op) {
                $k = (float) ($koefArray[$op] ?? 1.0);
                $accPerg = 0;
                foreach ($dateKeys as $dKey) {
                    $accPerg += $combinedTemp[$dKey][$op] ?? 0;
                    if ($dKey === $date) break;
                }
                $accPplRawCombined += $k > 0 ? ($accPerg / $k) : 0;
            }
            $accPplCombined = round($accPplRawCombined);

            $cRow['accum_mov'] = $runningAccumCombined['total_mov'];
            $cRow['accum_ppl'] = $accPplCombined;

            $final['combined'][$date] = $cRow;
        }

        return $final;
    }

    // --- REFACTORED HELPERS ---

    private function getOdSimpulData($startDate, $endDate, $filterCodes = [])
    {
        // A. Get All Categories (Simpul) for Rows
        try {
            $categories = DB::table('ref_transport_nodes')
                ->distinct()
                ->pluck('category')
                ->toArray();
        } catch (\Throwable $e) {
            $categories = [];
        }

        // Fallback if empty (ensure tables are never blank)
        if (empty($categories)) {
            $categories = ['Terminal', 'Stasiun', 'Bandara', 'Pelabuhan', 'Simpul Lainnya'];
        }

        // Initialize Pivot with All Categories (Empty Data)
        $pivot = [];
        foreach ($categories as $cat) {
            $pivot[$cat] = ['total' => 0];
        }

        // B. Query Movement Data
        try {
            $query = DB::table('spatial_movements as sm')
                ->leftJoin('ref_transport_nodes as simpul', 'sm.kode_origin_simpul', '=', 'simpul.code')
                ->select(
                    'simpul.category as kategori_simpul',
                    'sm.tanggal',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN');

            if (! empty($filterCodes)) {
                $query->whereIn('sm.kode_origin_kabupaten_kota', $filterCodes);
            }

            $data = $query->groupBy('simpul.category', 'sm.tanggal')->get();

            // C. Merge Data
            foreach ($data as $row) {
                $cat = $row->kategori_simpul ?? 'Tanpa Data Simpul (Forecast/Other)';
                $date = $row->tanggal;
                $vol = $row->total_volume;

                if (! isset($pivot[$cat])) {
                    $pivot[$cat] = ['total' => 0];
                }

                $pivot[$cat][$date] = $vol;
                $pivot[$cat]['total'] += $vol;
            }
        } catch (\Throwable $e) {
            // If DB Query fails, we return the initialized empty pivot
            \Illuminate\Support\Facades\Log::error('OD Simpul DB Error: '.$e->getMessage());
        }

        return $pivot;
    }

    private function getModeShareData($startDate, $endDate, $filterCodes = [])
    {
        // A. Get All Modes for Rows
        try {
            $modes = DB::table('ref_transport_modes')
                ->orderBy('code')
                ->pluck('name')
                ->toArray();
        } catch (\Throwable $e) {
            $modes = [];
        }

        // Fallback if empty (ensure tables are never blank)
        if (empty($modes)) {
            $modes = array_values(config('mpd.transport_modes', []));
        }

        // Initialize Pivot with All Modes
        $pivotMovement = [];
        $pivotPeople = [];

        foreach ($modes as $mode) {
            $pivotMovement[$mode] = ['total' => 0];
            $pivotPeople[$mode] = ['total' => 0];
        }

        // B. Query Movement Data
        try {
            $query = DB::table('spatial_movements as sm')
                ->leftJoin('ref_transport_modes as moda', 'sm.kode_moda', '=', 'moda.code')
                ->select(
                    DB::raw('COALESCE(moda.name, sm.kode_moda) as moda_name'),
                    'sm.kode_moda',
                    'sm.tanggal',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN');

            if (! empty($filterCodes)) {
                $query->whereIn('sm.kode_origin_kabupaten_kota', $filterCodes);
            }

            $results = $query->groupBy('moda.name', 'sm.kode_moda', 'sm.tanggal')->get();

            // C. Merge Data
            $modesConfig = config('mpd.transport_modes', []);
            foreach ($results as $row) {
                // Normalisasi: K → A (Mobil)
                $normalizedCode = $this->normalizeModa($row->kode_moda);
                $cat = $modesConfig[$normalizedCode] ?? $row->moda_name;
                $date = $row->tanggal;
                $vol = $row->total_volume;

                // PERGERAKAN (Movement)
                if (isset($pivotMovement[$cat])) {
                    $pivotMovement[$cat][$date] = $vol;
                    $pivotMovement[$cat]['total'] += $vol;
                }

                // ORANG (People)
                if (isset($pivotPeople[$cat])) {
                    $pivotPeople[$cat][$date] = $vol; // Simplified 1:1 for now
                    $pivotPeople[$cat]['total'] += $vol;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Mode Share DB Error: '.$e->getMessage());
        }

        return ['movement' => $pivotMovement, 'people' => $pivotPeople];
    }

    public function jabodetabekPergerakan(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 30 March 2026
        [$startDate, $endDate] = $this->getPeriodDates();

        $dates = $this->getDatesCollection($startDate, $endDate);

        // 2. Caching Key
        $cacheKey = 'mpd:jabodetabek:pergerakan:tables:v4';
        $jabodetabekCodes = $this->getJabodetabekCodes();
        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getPergerakanDataTables($startDate, $endDate, $jabodetabekCodes));

        return view('data-mpd.jabodetabek.pergerakan', [
            'title' => 'Pergerakan Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Pergerakan'],
            'dates' => $dates,
            'real' => $data['real'],
            'forecast' => $data['forecast'],
            'accum' => $data['accum'],
        ]);
    }

    private function getPergerakanData($startDate, $endDate, $filterCodes = [])
    {
        // Initialize Structure
        $dates = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $d = $curr->format('Y-m-d');
            $dates[$d] = [
                'XLSMART' => ['movement' => 0, 'people' => 0],
                'IOH' => ['movement' => 0, 'people' => 0],
                'TSEL' => ['movement' => 0, 'people' => 0],
                'Total' => ['movement' => 0, 'people' => 0],
            ];
            $curr->addDay();
        }

        try {
            $query = DB::table('spatial_movements')
                ->select(
                    'tanggal',
                    'opsel',
                    DB::raw('SUM(total) as total_volume')
                )
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('kategori', 'PERGERAKAN');

            if (! empty($filterCodes)) {
                $query->whereIn('kode_origin_kabupaten_kota', $filterCodes);
            }

            $results = $query->groupBy('tanggal', 'opsel')->get();

            foreach ($results as $row) {
                $date = $row->tanggal;
                $vol = $row->total_volume;

                $opsel = $this->normalizeOpsel($row->opsel);

                if (isset($dates[$date]) && isset($dates[$date][$opsel])) {
                    $dates[$date][$opsel]['movement'] += $vol;
                    $dates[$date][$opsel]['people'] += $vol; // Assumed 1:1

                    // Add to Day Total
                    $dates[$date]['Total']['movement'] += $vol;
                    $dates[$date]['Total']['people'] += $vol;
                }
            }

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pergerakan DB Error: '.$e->getMessage());
        }

        return $dates;
    }

    public function jabodetabekPergerakanOrang(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 30 March 2026
        [$startDate, $endDate] = $this->getPeriodDates();

        $dates = $this->getDatesCollection($startDate, $endDate);

        $kategoriFilter = $request->input('kategori', 'REAL');
        $isForecast = ($kategoriFilter === 'FORECAST');

        // 2. Caching Key
        $cacheKey = "mpd:jabodetabek:pergerakan-orang:v3:{$isForecast}";
        $jabodetabekCodes = $this->getJabodetabekCodes();
        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getPergerakanOrangData($startDate, $endDate, $jabodetabekCodes, $isForecast));

        return view('data-mpd.jabodetabek.pergerakan-orang', [
            'title' => 'Akumulasi Pergerakan & Orang Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Pergerakan & Orang'],
            'dates' => $dates,
            'data' => $data,
        ]);
    }

    private function getPergerakanOrangData($startDate, $endDate, $jabodetabekCodes, $isForecast)
    {
        $dailyData = [];

        try {
            // Query PERGERAKAN
            $movements = DB::table('spatial_movements')
                ->select('tanggal', DB::raw('SUM(total) as total_volume'))
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('kategori', 'PERGERAKAN')
                ->where('is_forecast', $isForecast)
                ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('tanggal')
                ->get();

            foreach ($movements as $row) {
                $dailyData[$row->tanggal] = [
                    'movement' => (int) $row->total_volume,
                    'people' => 0,
                ];
            }

            // Query ORANG
            $people = DB::table('spatial_movements')
                ->select('tanggal', DB::raw('SUM(total) as total_volume'))
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('kategori', 'ORANG')
                ->where('is_forecast', $isForecast)
                ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('tanggal')
                ->get();

            foreach ($people as $row) {
                if (! isset($dailyData[$row->tanggal])) {
                    $dailyData[$row->tanggal] = ['movement' => 0, 'people' => 0];
                }
                $dailyData[$row->tanggal]['people'] = (int) $row->total_volume;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pergerakan Orang DB Error: '.$e->getMessage());
        }

        return $dailyData;
    }

    // --- SUBSTANSI NETFLOW ---
    public function substansiNetflowPage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dates = $this->getDatesCollection($startDate, $endDate);

        $type = strtoupper($request->get('type', 'REAL')); // Default to REAL
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:substansi:netflow:v3_force:{$type}:{$dString}";

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getSubstansiNetflowData($startDate, $endDate, $type));

        return view('pages.substansi.netflow', [
            'title' => 'Netflow Pergerakan Nasional',
            'breadcrumb' => ['Data MPD Opsel', 'Substansi', 'Netflow Pergerakan'],
            'dates' => $dates,
            'top_origin_netflow' => $data['top_origin_netflow'],
            'top_dest_netflow' => $data['top_dest_netflow'],
            'top_origin_nfr' => $data['top_origin_nfr'],
            'top_dest_nfr' => $data['top_dest_nfr'],
            'activeType' => $type,
        ]);
    }

    /**
     * @return array
     */
    private function getSubstansiNetflowData(Carbon $startDate, Carbon $endDate, $type = 'REAL')
    {
        try {
            $startDateStr = $startDate->format('Y-m-d');
            $endDateStr = $endDate->format('Y-m-d');

            /** @var \Illuminate\Database\Query\Builder $outflowBuilder */
            $outflowBuilder = DB::table('spatial_movements');

            $this->applyTypeFilter($outflowBuilder, $type, 'PERGERAKAN');

            /** @var \Illuminate\Support\Collection $outflowQuery */
            $outflowQuery = $outflowBuilder
                ->select(
                    'kode_origin_kabupaten_kota as city_code',
                    DB::raw('SUM(total) as total_outflow')
                )
                ->whereBetween('tanggal', [$startDateStr, $endDateStr])
                ->where('kategori', 'PERGERAKAN') // Fix double count Netflow outflow
                ->groupBy('kode_origin_kabupaten_kota')
                ->get()
                ->keyBy('city_code');

            /** @var \Illuminate\Database\Query\Builder $inflowBuilder */
            $inflowBuilder = DB::table('spatial_movements');

            $this->applyTypeFilter($inflowBuilder, $type, 'PERGERAKAN');

            /** @var \Illuminate\Support\Collection $inflowQuery */
            $inflowQuery = $inflowBuilder
                ->select(
                    'kode_dest_kabupaten_kota as city_code',
                    DB::raw('SUM(total) as total_inflow')
                )
                ->whereBetween('tanggal', [$startDateStr, $endDateStr])
                ->where('kategori', 'PERGERAKAN') // Fix double count Netflow inflow
                ->groupBy('kode_dest_kabupaten_kota')
                ->get()
                ->keyBy('city_code');

            $allCityCodes = $outflowQuery->keys()->merge($inflowQuery->keys())->unique()->filter()->values();
            $cityNames = DB::table('ref_cities')->whereIn('code', $allCityCodes)->pluck('name', 'code');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Netflow DB Error: '.$e->getMessage());
            $outflowQuery = collect();
            $inflowQuery = collect();
            $allCityCodes = collect();
            $cityNames = collect();
        }

        $merged = [];

        foreach ($allCityCodes as $code) {
            $outRecord = $outflowQuery->get($code);
            $inRecord = $inflowQuery->get($code);

            $outVal = $outRecord ? (float) $outRecord->total_outflow : 0;
            $inVal = $inRecord ? (float) $inRecord->total_inflow : 0;
            $cityName = $cityNames->get($code, 'Unknown');

            $netflow = $inVal - $outVal; // as requested: Inflow - Outflow
            $totalFlow = $inVal + $outVal;
            $nfr = $totalFlow > 0 ? ($netflow / $totalFlow) : 0;

            $merged[] = [
                'code' => $code,
                'name' => $cityName,
                'outflow' => $outVal,
                'inflow' => $inVal,
                'netflow' => $netflow,
                'nfr' => $nfr,
                'keterangan' => $netflow <= 0 ? 'ASAL' : 'TUJUAN',
            ];
        }

        /** @var \Illuminate\Support\Collection $mergedColl */
        $mergedColl = collect($merged);

        // Top 20 Origin Netflow (Lowest/Most Negative Netflow)
        /** @var \Illuminate\Support\Collection $topOriginNetflow */
        $topOriginNetflow = $mergedColl->filter(fn (array $r) => $r['netflow'] <= 0)
            ->sortBy('netflow')
            ->take(20)
            ->values();

        // Top 20 Dest Netflow (Highest/Most Positive Netflow)
        /** @var \Illuminate\Support\Collection $topDestNetflow */
        $topDestNetflow = $mergedColl->filter(fn (array $r) => $r['netflow'] > 0)
            ->sortByDesc('netflow')
            ->take(20)
            ->values();

        // Top 20 Origin NFR (Lowest NFR close to -1)
        /** @var \Illuminate\Support\Collection $topOriginNfr */
        $topOriginNfr = $mergedColl->filter(fn (array $r) => $r['nfr'] <= 0)
            ->sortBy('nfr')
            ->take(20)
            ->values();

        // Top 20 Dest NFR (Highest NFR close to +1)
        /** @var \Illuminate\Support\Collection $topDestNfr */
        $topDestNfr = $mergedColl->filter(fn (array $r) => $r['nfr'] > 0)
            ->sortByDesc('nfr')
            ->take(20)
            ->values();

        return [
            'top_origin_netflow' => $topOriginNetflow,
            'top_dest_netflow' => $topDestNetflow,
            'top_origin_nfr' => $topOriginNfr,
            'top_dest_nfr' => $topDestNfr,
        ];
    }

    // --- KESIMPULAN NASIONAL ---
    public function kesimpulanNasionalPage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dates = $this->getDatesCollection($startDate, $endDate);

        $type = strtoupper($request->get('type', 'COMBINED')); // Default to COMBINED
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:kesimpulan:nasional:v8_final_364:{$type}:{$dString}";

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getKesimpulanNasionalData($startDate, $endDate, $type));

        return view('pages.kesimpulan.nasional', [
            'title' => 'Kesimpulan Nasional',
            'breadcrumb' => ['Data MPD Opsel', 'Kesimpulan & Rekomendasi', 'Nasional'],
            'dates' => $dates,
            'data' => $data,
        ]);
    }

    private function getKesimpulanNasionalData($startDate, $endDate, $type = 'COMBINED')
    {
        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');

        try {
            $dailyBuilder = DB::table('spatial_movements')
                ->whereBetween('tanggal', [$startDateStr, $endDateStr])
                ->where('kategori', 'PERGERAKAN');
            $this->applyTypeFilter($dailyBuilder, $type, 'PERGERAKAN');

            $dailyMovements = $dailyBuilder->select('tanggal', DB::raw('SUM(total) as daily_total'))
                ->groupBy('tanggal')
                ->orderByDesc('daily_total')
                ->get();

            $totalPergerakan = $dailyMovements->sum('daily_total');
            $peakDays = $dailyMovements->take(2);

            // 2. Kontribusi Operator (TSEL, IOH, XL)
            $opselBuilder = DB::table('spatial_movements')
                ->whereBetween('tanggal', [$startDateStr, $endDateStr])
                ->where('kategori', 'PERGERAKAN');
            $this->applyTypeFilter($opselBuilder, $type, 'PERGERAKAN');

            $opselPergerakan = $opselBuilder->select('opsel', DB::raw('SUM(total) as op_total'))
                ->groupBy('opsel')
                ->get();

            $operatorStats = [
                'PERGERAKAN' => ['TSEL' => 0, 'IOH' => 0, 'XLSMART' => 0],
                'ORANG' => ['TSEL' => 0, 'IOH' => 0, 'XLSMART' => 0],
            ];

            foreach ($opselPergerakan as $row) {
                $normalized = $this->normalizeOpsel($row->opsel);
                if ($normalized !== 'OTHER' && isset($operatorStats['PERGERAKAN'][$normalized])) {
                    $operatorStats['PERGERAKAN'][$normalized] += $row->op_total;
                }
            }

            // Hitung Unik Subscriber harian per-opsel agar konsisten dengan Dashboard & Harian Page (Kanonik Terkunci)
            $dailyStats = $opselBuilder->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', DB::raw('SUM(total) as t'))
                 ->groupBy(DB::raw('DATE(tanggal)'), 'opsel')
                 ->get();

            $totalOrangRaw = 0;
            $opStatsOrangRaw = ['TSEL' => 0, 'IOH' => 0, 'XLSMART' => 0];
            $koefArray = $this->getKoefisienArray();

            foreach ($dailyStats as $stat) {
                $op   = $this->normalizeOpsel($stat->opsel);
                $vol  = (float) $stat->t;
                $koef = (float) ($koefArray[$op] ?? 1.0);
                
                // Pembulatan dilakukan SETIAP perhitungan per-opsel per-hari (Metode Kanonik Terkunci Eksekusi)
                $uniqueRaw = $koef > 0 ? round($vol / $koef) : 0;
                $totalOrangRaw += $uniqueRaw;
                if ($op !== 'OTHER' && isset($opStatsOrangRaw[$op])) {
                    $opStatsOrangRaw[$op] += $uniqueRaw;
                }
            }

            $operatorStats['ORANG']['TSEL'] = $opStatsOrangRaw['TSEL'];
            $operatorStats['ORANG']['IOH'] = $opStatsOrangRaw['IOH'];
            $operatorStats['ORANG']['XLSMART'] = $opStatsOrangRaw['XLSMART'];
            
            $totalOrang = $totalOrangRaw;

            // 4. Top 5 Provinsi Asal
            $provAsalBuilder = DB::table('spatial_movements as sm')
                ->join('ref_cities as oc', 'sm.kode_origin_kabupaten_kota', '=', 'oc.code')
                ->join('ref_provinces as p', 'oc.province_code', '=', 'p.code')
                ->whereBetween('sm.tanggal', [$startDateStr, $endDateStr])
                ->where('sm.kategori', 'PERGERAKAN');
            $this->applyTypeFilter($provAsalBuilder, $type, 'PERGERAKAN', null, 'sm');
            $top5ProvAsal = $provAsalBuilder->select('p.name', DB::raw('SUM(sm.total) as prov_total'))
                ->groupBy('p.name')
                ->orderByDesc('prov_total')
                ->take(5)
                ->get();

            // 5. Top 5 Provinsi Tujuan
            $provTujuanBuilder = DB::table('spatial_movements as sm')
                ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
                ->join('ref_provinces as p', 'dc.province_code', '=', 'p.code')
                ->whereBetween('sm.tanggal', [$startDateStr, $endDateStr])
                ->where('sm.kategori', 'PERGERAKAN');
            $this->applyTypeFilter($provTujuanBuilder, $type, 'PERGERAKAN', null, 'sm');
            $top5ProvTujuan = $provTujuanBuilder->select('p.name', DB::raw('SUM(sm.total) as prov_total'))
                ->groupBy('p.name')
                ->orderByDesc('prov_total')
                ->take(5)
                ->get();

            // 6. Top 5 Kota/Kab Asal
            $kotaAsalBuilder = DB::table('spatial_movements as sm')
                ->join('ref_cities as c', 'sm.kode_origin_kabupaten_kota', '=', 'c.code')
                ->whereBetween('sm.tanggal', [$startDateStr, $endDateStr])
                ->where('sm.kategori', 'PERGERAKAN');
            $this->applyTypeFilter($kotaAsalBuilder, $type, 'PERGERAKAN', null, 'sm');
            $top3KotaAsal = $kotaAsalBuilder->select('c.name', DB::raw('SUM(sm.total) as city_total'))
                ->groupBy('c.name')
                ->orderByDesc('city_total')
                ->take(3)
                ->get();

            // 7. Top 5 Kota/Kab Tujuan
            $kotaTujuanBuilder = DB::table('spatial_movements as sm')
                ->join('ref_cities as c', 'sm.kode_dest_kabupaten_kota', '=', 'c.code')
                ->whereBetween('sm.tanggal', [$startDateStr, $endDateStr])
                ->where('sm.kategori', 'PERGERAKAN');
            $this->applyTypeFilter($kotaTujuanBuilder, $type, 'PERGERAKAN', null, 'sm');
            $top5KotaTujuan = $kotaTujuanBuilder->select('c.name', DB::raw('SUM(sm.total) as city_total'))
                ->groupBy('c.name')
                ->orderByDesc('city_total')
                ->take(5)
                ->get();

            return [
                'total_pergerakan' => $totalPergerakan,
                'total_orang' => $totalOrang,
                'peak_days' => $peakDays,
                'operator_stats' => $operatorStats,
                'top_5_prov_asal' => $top5ProvAsal,
                'top_5_prov_tujuan' => $top5ProvTujuan,
                'top_3_kota_asal' => $top3KotaAsal,
                'top_5_kota_tujuan' => $top5KotaTujuan,
            ];

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Kesimpulan Nasional DB Error: '.$e->getMessage());

            return [
                'total_pergerakan' => 0,
                'total_orang' => 0,
                'peak_days' => collect(),
                'operator_stats' => ['PERGERAKAN' => ['TSEL' => 0, 'IOH' => 0, 'XLSMART' => 0], 'ORANG' => ['TSEL' => 0, 'IOH' => 0, 'XLSMART' => 0]],
                'top_5_prov_asal' => collect(),
                'top_5_prov_tujuan' => collect(),
                'top_3_kota_asal' => collect(),
                'top_5_kota_tujuan' => collect(),
            ];
        }
    }

    // --- KESIMPULAN JABODETABEK ---
    public function kesimpulanJabodetabekPage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dates = $this->getDatesCollection($startDate, $endDate);

        $type = strtoupper($request->get('type', 'COMBINED')); // Default to COMBINED
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:kesimpulan:jabodetabek:v4:{$type}:{$dString}";

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), fn () => $this->getKesimpulanJabodetabekData($startDate, $endDate, $type));

        return view('pages.kesimpulan.jabodetabek', [
            'title' => 'Kesimpulan Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Kesimpulan & Rekomendasi', 'Jabodetabek'],
            'dates' => $dates,
            'data' => $data,
        ]);
    }

    private function getKesimpulanJabodetabekData($startDate, $endDate, $type = 'COMBINED')
    {
        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');

        $jabodetabekCodes = $this->getJabodetabekCodes();

        // 1. INTRA JABODETABEK Peak Days
        $intraBuilder = DB::table('spatial_movements as sm')
            ->whereBetween('sm.tanggal', [$startDateStr, $endDateStr])
            ->where('sm.kategori', 'PERGERAKAN')
            ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
            ->whereIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes);
        $this->applyTypeFilter($intraBuilder, $type, 'PERGERAKAN', null, 'sm');
        $intraDaily = $intraBuilder->select('sm.tanggal', DB::raw('SUM(sm.total) as daily_total'))
            ->groupBy('sm.tanggal')
            ->orderByDesc('daily_total')
            ->get();

        $intraPeakDays = $intraDaily->take(2);

        // 2. INTER JABODETABEK Peak Days (Jabodetabek to National)
        $interBuilder = DB::table('spatial_movements as sm')
            ->whereBetween('sm.tanggal', [$startDateStr, $endDateStr])
            ->where('sm.kategori', 'PERGERAKAN')
            ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
            ->whereNotIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes);
        $this->applyTypeFilter($interBuilder, $type, 'PERGERAKAN', null, 'sm');
        $interDaily = $interBuilder->select('sm.tanggal', DB::raw('SUM(sm.total) as daily_total'))
            ->groupBy('sm.tanggal')
            ->orderByDesc('daily_total')
            ->get();

        $interPeakDays = $interDaily->take(2);

        // 3. Daerah Asal Pergerakan Masyarakat Jabodetabek (Top Origin) ALL JABO?
        $topOriginBuilder = DB::table('spatial_movements as sm')
            ->join('ref_cities as oc', 'sm.kode_origin_kabupaten_kota', '=', 'oc.code')
            ->whereBetween('sm.tanggal', [$startDateStr, $endDateStr])
            ->where('sm.kategori', 'PERGERAKAN')
            ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes);
        $this->applyTypeFilter($topOriginBuilder, $type, 'PERGERAKAN', null, 'sm');
        $topOriginJabo = $topOriginBuilder->select('oc.name', DB::raw('SUM(sm.total) as city_total'))
            ->groupBy('oc.name')
            ->orderByDesc('city_total')
            ->take(3)
            ->get();

        // 4. Tujuan Intra Jabodetabek (Top Dest INTRA)
        $topDestIntraBuilder = DB::table('spatial_movements as sm')
            ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
            ->whereBetween('sm.tanggal', [$startDateStr, $endDateStr])
            ->where('sm.kategori', 'PERGERAKAN')
            ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
            ->whereIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes);
        $this->applyTypeFilter($topDestIntraBuilder, $type, 'PERGERAKAN', null, 'sm');
        $topDestIntraJabo = $topDestIntraBuilder->select('dc.name', DB::raw('SUM(sm.total) as city_total'))
            ->groupBy('dc.name')
            ->orderByDesc('city_total')
            ->take(3)
            ->get();

        // 5. Tujuan Inter Jabodetabek (Top Prov Dest INTER)
        $topProvDestInterBuilder = DB::table('spatial_movements as sm')
            ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
            ->join('ref_provinces as dp', 'dc.province_code', '=', 'dp.code')
            ->whereBetween('sm.tanggal', [$startDateStr, $endDateStr])
            ->where('sm.kategori', 'PERGERAKAN')
            ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
            ->whereNotIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes);
        $this->applyTypeFilter($topProvDestInterBuilder, $type, 'PERGERAKAN', null, 'sm');
        $topProvDestInterJabo = $topProvDestInterBuilder->select('dp.name', DB::raw('SUM(sm.total) as prov_total'))
            ->groupBy('dp.name')
            ->orderByDesc('prov_total')
            ->take(1)
            ->get();

        return [
            'intra_peak_days' => $intraPeakDays,
            'inter_peak_days' => $interPeakDays,
            'top_origin_jabo' => $topOriginJabo,
            'top_dest_intra_jabo' => $topDestIntraJabo,
            'top_prov_dest_inter_jabo' => $topProvDestInterJabo,
        ];
    }

    // --- GENERIC SIMPUL TRANSPORTASI PAGE ---
    public function substansiSimpulPage(Request $request, $slug)
    {
        [$startDate, $endDate] = $this->getPeriodDates();

        // Map slug -> config (sub_category values verified from ref_simpul.csv)
        $map = [
            'stasiun-ka-antar-kota' => ['category' => 'Stasiun', 'sub_category' => 'Antar Kota',      'title' => 'Stasiun KA Antar Kota',      'view' => 'pages.substansi._simpul-layout',  'number' => '14', 'kode_moda' => 'E'],
            'stasiun-ka-regional' => ['category' => 'Stasiun', 'sub_category' => 'Perkotaan',       'title' => 'Stasiun KA Regional (Perkotaan)',  'view' => 'pages.substansi._simpul-layout',  'number' => '15', 'kode_moda' => 'G'],
            'stasiun-ka-cepat' => ['category' => 'Stasiun', 'sub_category' => 'KCJB',            'title' => 'Stasiun KA Cepat (KCJB)',      'view' => 'pages.substansi._simpul-layout',  'number' => '16', 'kode_moda' => 'F'],
            'pelabuhan-penyeberangan' => ['category' => 'Pelabuhan', 'sub_category' => 'Penyeberangan', 'title' => 'Pelabuhan Penyeberangan',    'view' => 'pages.substansi._simpul-layout',  'number' => '17', 'kode_moda' => 'J'],
            'pelabuhan-laut' => ['category' => 'Pelabuhan', 'sub_category' => 'Laut',          'title' => 'Pelabuhan Laut',             'view' => 'pages.substansi._simpul-layout',  'number' => '18', 'kode_moda' => 'I'],
            'bandara' => ['category' => 'Bandara', 'sub_category' => null,               'title' => 'Bandara',                    'view' => 'pages.substansi._simpul-layout',  'number' => '19', 'kode_moda' => 'H'],
            'terminal' => ['category' => 'Terminal', 'sub_category' => null,              'title' => 'Terminal',                   'view' => 'pages.substansi._simpul-layout',  'number' => '20', 'kode_moda' => ['C', 'D']],
            'od-simpul-pelabuhan' => ['category' => 'Pelabuhan', 'sub_category' => null,             'title' => 'O-D Simpul Pelabuhan',       'view' => 'pages.substansi._simpul-layout',  'number' => '21'],
        ];

        if (! isset($map[$slug])) {
            abort(404);
        }

        $cfg = $map[$slug];
        $category = $cfg['category'];
        $subCat = $cfg['sub_category'];
        $kodeModa = $cfg['kode_moda'] ?? null;
        $cacheKey = "mpd:simpul:{$slug}:v6:".$startDate->format('Ymd').'_'.$endDate->format('Ymd');

        $data = $this->cached($cacheKey, $this->dataCacheTtl(), function () use ($startDate, $endDate, $category, $subCat, $kodeModa) {
            $isForecast = false;

            // TOP 10 ORIGIN (Asal)
            $topOrigin = DB::table('spatial_movements as sm')
                ->leftJoin('ref_transport_nodes as n', 'sm.kode_origin_simpul', '=', 'n.code')
                ->select(DB::raw('COALESCE(n.code, sm.kode_origin_simpul) as code'), DB::raw('COALESCE(n.name, sm.kode_origin_simpul) as name'), DB::raw('SUM(sm.total) as total_volume'))
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN')
                ->where('sm.is_forecast', $isForecast)
                ->where('sm.kode_origin_simpul', '!=', '')
                ->where(function ($q) use ($category, $subCat, $kodeModa) {
                    // Mandatory Category Filter
                    $q->where('n.category', $category);
                    if ($subCat) {
                        $q->where('n.sub_category', $subCat);
                    }
                    // Optional Moda Filter
                    if ($kodeModa) {
                        if (is_array($kodeModa)) {
                            $q->whereIn('sm.kode_moda', $kodeModa);
                        } else {
                            $q->where('sm.kode_moda', $kodeModa);
                        }
                    }
                })
                ->groupBy('sm.kode_origin_simpul', 'n.code', 'n.name')
                ->orderByDesc('total_volume')
                ->take(10)
                ->get();

            // TOP 10 DESTINATION (Tujuan)
            $topDest = DB::table('spatial_movements as sm')
                ->leftJoin('ref_transport_nodes as n', 'sm.kode_dest_simpul', '=', 'n.code')
                ->select(DB::raw('COALESCE(n.code, sm.kode_dest_simpul) as code'), DB::raw('COALESCE(n.name, sm.kode_dest_simpul) as name'), DB::raw('SUM(sm.total) as total_volume'))
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN')
                ->where('sm.is_forecast', $isForecast)
                ->where('sm.kode_dest_simpul', '!=', '')
                ->where(function ($q) use ($category, $subCat, $kodeModa) {
                    // Mandatory Category Filter
                    $q->where('n.category', $category);
                    if ($subCat) {
                        $q->where('n.sub_category', $subCat);
                    }
                    // Optional Moda Filter
                    if ($kodeModa) {
                        if (is_array($kodeModa)) {
                            $q->whereIn('sm.kode_moda', $kodeModa);
                        } else {
                            $q->where('sm.kode_moda', $kodeModa);
                        }
                    }
                })
                ->groupBy('sm.kode_dest_simpul', 'n.code', 'n.name')
                ->orderByDesc('total_volume')
                ->take(10)
                ->get();

            // TOP 10 O-D PAIRS (STRICT FILTER: Both ends must match category)
            $topOd = DB::table('spatial_movements as sm')
                ->leftJoin('ref_transport_nodes as o', 'sm.kode_origin_simpul', '=', 'o.code')
                ->leftJoin('ref_transport_nodes as d', 'sm.kode_dest_simpul', '=', 'd.code')
                ->select(
                    DB::raw("CONCAT(COALESCE(o.name, sm.kode_origin_simpul), ' - ', COALESCE(d.name, sm.kode_dest_simpul)) as od_name"),
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN')
                ->where('sm.is_forecast', $isForecast)
                ->where('sm.kode_origin_simpul', '!=', '')
                ->where('sm.kode_dest_simpul', '!=', '')
                ->whereColumn('sm.kode_origin_simpul', '!=', 'sm.kode_dest_simpul') // FIX: Hapus rute A ke A (self-loop)
                // Strict Filter Origin
                ->where(function ($q) use ($category, $subCat, $kodeModa) {
                    $q->where('o.category', $category);
                    if ($subCat) {
                        $q->where('o.sub_category', $subCat);
                    }
                    if ($kodeModa) {
                        if (is_array($kodeModa)) {
                            $q->whereIn('sm.kode_moda', $kodeModa);
                        } else {
                            $q->where('sm.kode_moda', $kodeModa);
                        }
                    }
                })
                // Strict Filter Destination
                ->where(function ($q) use ($category, $subCat, $kodeModa) {
                    $q->where('d.category', $category);
                    if ($subCat) {
                        $q->where('d.sub_category', $subCat);
                    }
                    if ($kodeModa) {
                        if (is_array($kodeModa)) {
                            $q->whereIn('sm.kode_moda', $kodeModa);
                        } else {
                            $q->where('sm.kode_moda', $kodeModa);
                        }
                    }
                })
                ->groupBy('o.name', 'd.name', 'sm.kode_origin_simpul', 'sm.kode_dest_simpul')
                ->orderByDesc('total_volume')
                ->take(10)
                ->get();

            // Calculate totals for percentage
            $totalOrigin = $topOrigin->sum('total_volume') ?: 1;
            $totalDest = $topDest->sum('total_volume') ?: 1;
            $totalOd = $topOd->sum('total_volume') ?: 1;

            // Attach percentages
            $topOrigin = $topOrigin->map(fn ($r) => (object) array_merge((array) $r, ['pct' => round($r->total_volume / $totalOrigin * 100, 2)]));
            $topDest = $topDest->map(fn ($r) => (object) array_merge((array) $r, ['pct' => round($r->total_volume / $totalDest * 100, 2)]));
            $topOd = $topOd->map(fn ($r) => (object) array_merge((array) $r, ['pct' => round($r->total_volume / $totalOd * 100, 2)]));

            return [
                'top_origin' => $topOrigin,
                'top_dest' => $topDest,
                'top_od' => $topOd,
                'top_od_name' => $topOd->first()?->od_name ?? '-',
            ];
        });

        return view($cfg['view'], array_merge($data, [
            'title' => $cfg['title'],
            'pageNumber' => $cfg['number'],
            'note' => $cfg['note'] ?? null,
        ]));
    }

    // --- REKOMENDASI KEBIJAKAN (AI) ---
    public function rekomendasiPage(Request $request)
    {
        [$startDate, $endDate] = $this->getPeriodDates();
        $dString = $startDate->format('Ymd').'_'.$endDate->format('Ymd');
        $cacheKey = "mpd:rekomendasi:gemini_v4:{$dString}";

        $aiContent = Cache::get($cacheKey);

        if (! $aiContent) {
            $totalPergerakan = DB::table('spatial_movements')
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('kategori', 'PERGERAKAN')
                ->sum('total');

            $topTujuan = DB::table('spatial_movements as sm')
                ->join('ref_cities as c', 'sm.kode_dest_kabupaten_kota', '=', 'c.code')
                ->join('ref_provinces as p', 'c.province_code', '=', 'p.code')
                ->select('p.name', DB::raw('SUM(sm.total) as prov_total'))
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN')
                ->groupBy('p.name')
                ->orderByDesc('prov_total')
                ->take(3)
                ->get()
                ->pluck('name')
                ->implode(', ');

            $prompt = 'Anda adalah Analis Ahli Sistem Transportasi dan Kebijakan Publik untuk Kementerian. 
Tugas Anda adalah membaca ringkasan data pergerakan masyarakat (MPD Mobile Positioning Data) selama masa Lebaran (Angleb) 2026 dan memberikan rekomendasi kebijakan yang interaktif, informatif, dan solutif.
Total Pergerakan Nasional tercatat sebesar: '.number_format($totalPergerakan, 0, ',', '.')." pergerakan.
Provinsi tujuan paling dominan adalah: {$topTujuan}.

Berikan 5 poin rekomendasi kebijakan utama (seperti infrastruktur tol, manajemen simpul transportasi, keselamatan, dll) yang harus diambil oleh Pimpinan Kementerian. Tuliskan dalam format Markdown yang rapi dan profesional, gunakan bold untuk key points, dan bullet points.
PENTING: Di bagian akhir respons Anda (setelah 5 rekomendasi), Anda DIWAJIBKAN menambahkan blok kutipan statis (menggunakan blockquote markdown `>`) dengan judul **Sumber Data:** yang menjelaskan secara persis bahwa analisis data ini diperoleh dan dapat dipertanggungjawabkan dari hasil pengolahan \"Data Ekstraksi Mobile Positioning Data (MPD) Operator Seluler: Telkomsel, Indosat Ooredoo Hutchison, dan XL Axiata periode Angleb 2026\". Buat penjelasan keseluruhan yang mendalam namun solutif.";

            $apiKey = config('mpd.gemini_api_key');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            try {
                $response = Http::post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $aiContent = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

                    if ($aiContent) {
                        Cache::put($cacheKey, $aiContent, config('mpd.cache_ttl.ai_rekomendasi', 86400));
                    } else {
                        $aiContent = 'Gagal mengambil rekomendasi (Format AI tidak sesuai).';
                    }
                } else {
                    $aiContent = 'Gagal menghubungi layanan AI Endpoint. Status: '.$response->status().' Body: '.$response->body();
                }
            } catch (\Exception $e) {
                $aiContent = 'Terjadi kesalahan sistem saat memuat rekomendasi AI: '.$e->getMessage();
            }
        }

        // Parse markdown text simply for view
        $parsedHtml = \Illuminate\Support\Str::markdown($aiContent);

        return view('pages.kesimpulan.rekomendasi', [
            'ai_html' => $parsedHtml,
            'title' => 'Rekomendasi Kebijakan',
        ]);
    }

    /**
     * Hitung Unique Subscriber per-opsel menggunakan koefisien per-batch.
     *
     * Koefisien dipilih otomatis berdasarkan config end_date aktif.
     * Rumus: unique_opsel = pergerakan_opsel / koefisien_opsel
     * Total = SUM(unique per opsel)
     * Koefisien rata-rata (weighted) = total_pergerakan / total_unique
     *
     * @param  array  $totals  Array berisi ['TSEL' => ['pergerakan' => X], 'IOH' => ..., 'XLSMART' => ...]
     * @return array ['total_unique_subscriber', 'koefisien_rata_rata', 'per_opsel']
     */
    private function getKoefisienArray(): array
    {
        $finalKoef = config('mpd_koefisien.final');
        if (!empty($finalKoef) && is_array($finalKoef)) {
            return $finalKoef;
        }

        $endDateConfig = config('mpd.end_date', '2026-03-29');
        $batches = config('mpd_koefisien.batches', []);
        $koefArray = null;

        foreach ($batches as $batch) {
            if (isset($batch['end_date']) && $batch['end_date'] >= $endDateConfig) {
                $koefArray = $batch;
                break;
            }
        }

        if (!$koefArray && !empty($batches)) {
            $koefArray = end($batches);
        }

        return $koefArray ?: ['TSEL' => 1.0, 'IOH' => 1.0, 'XLSMART' => 1.0];
    }

    private function calculateUniqueSubscriberPerOpsel(array $totals): array
    {
        $selectedBatch = $this->getKoefisienArray();

        $opselMap = ['TSEL' => 'TSEL', 'IOH' => 'IOH', 'XLSMART' => 'XLSMART'];
        $totalUniqueSubscriber = 0;
        $totalPergerakan = 0;
        $perOpsel = [];

        foreach ($opselMap as $opselKey => $configKey) {
            $pergerakan = (float) ($totals[$opselKey]['pergerakan'] ?? $totals[$opselKey]['movement'] ?? 0);
            $koefisien = (float) ($selectedBatch[$configKey] ?? 1.0);
            $unique = $koefisien > 0 ? round($pergerakan / $koefisien) : 0;

            $perOpsel[$opselKey] = [
                'pergerakan' => $pergerakan,
                'koefisien' => $koefisien,
                'unique_subscriber' => $unique,
            ];

            $totalUniqueSubscriber += $unique;
            $totalPergerakan += $pergerakan;
        }

        // Weighted average koefisien
        $koefisienRataRata = $totalUniqueSubscriber > 0
            ? round($totalPergerakan / $totalUniqueSubscriber, 2)
            : 0.0;

        return [
            'total_unique_subscriber' => $totalUniqueSubscriber,
            'koefisien_rata_rata' => $koefisienRataRata,
            'batch_label' => $selectedBatch['label'] ?? '-',
            'per_opsel' => $perOpsel,
        ];
    }
}
