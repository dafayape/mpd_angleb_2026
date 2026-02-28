<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpatialMovement;
use App\Models\Simpul;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DataMpdController extends Controller
{
    /**
     * Get Jabodetabek City Codes
     */
    private function getJabodetabekCodes()
    {
        return [
            // DKI Jakarta
            '3171', '3172', '3173', '3174', '3175', '3101', // Kepulauan Seribu included? Usually yes for province. 3101 is Kep Seribu.
            // Bogor
            '3201', '3271',
            // Depok
            '3276',
            // Tangerang
            '3603', '3671', '3674', // Kab Tangerang, Kota Tangerang, Kota Tangsel
            // Bekasi
            '3216', '3275'
        ];
    }

    public function jabodetabekOdSimpul(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 30 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        
        $dates = collect();
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates->push($curr->format('Y-m-d'));
            $curr->addDay();
        }

        // 2. Caching Key
        $cacheKey = 'mpd:jabodetabek:od-simpul:matrix:v2';
        
        $jabodetabekCodes = $this->getJabodetabekCodes();

        try {
            $matrix = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $jabodetabekCodes) {
                return $this->getOdSimpulData($startDate, $endDate, $jabodetabekCodes);
            });
        } catch (\Throwable $e) {
            // Redis/DB Fallback
            $matrix = $this->getOdSimpulData($startDate, $endDate, $jabodetabekCodes);
        }

        return view('data-mpd.jabodetabek.od-simpul', [
            'title' => 'O-D Simpul Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'O-D Simpul'],
            'dates' => $dates,
            'matrix' => $matrix
        ]);
    }

    public function jabodetabekModeShare(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 30 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        
        $dates = collect();
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates->push($curr->format('Y-m-d'));
            $curr->addDay();
        }

        // 2. Caching Key
        $cacheKey = 'mpd:jabodetabek:mode-share:matrix:v2';
        
        $jabodetabekCodes = $this->getJabodetabekCodes();
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $jabodetabekCodes) {
                return $this->getModeShareData($startDate, $endDate, $jabodetabekCodes);
            });
        } catch (\Throwable $e) {
            // Redis/DB Fallback
            $data = $this->getModeShareData($startDate, $endDate, $jabodetabekCodes);
        }

        return view('data-mpd.jabodetabek.mode-share', [
            'title' => 'Mode Share Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Mode Share'],
            'dates' => $dates,
            'movementMatrix' => $data['movement'],
            'peopleMatrix' => $data['people']
        ]);
    }

    public function jabodetabekIntraPergerakanPage(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        
        $dString = $startDate->format('Ymd') . '_' . $endDate->format('Ymd');
        $cacheKey = "mpd:jabodetabek:intra-pergerakan:v1:{$dString}";
        
        $jabodetabekCodes = $this->getJabodetabekCodes();

        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $jabodetabekCodes) {
                return $this->getJabodetabekIntraPergerakanData($startDate, $endDate, $jabodetabekCodes);
            });
        } catch (\Throwable $e) {
            $data = $this->getJabodetabekIntraPergerakanData($startDate, $endDate, $jabodetabekCodes);
        }

        return view('pages.jabodetabek.intra-pergerakan', [
            'dates' => $this->getDatesCollection($startDate, $endDate),
            'data' => $data
        ]);
    }

    private function getJabodetabekIntraPergerakanData($startDate, $endDate, $jabodetabekCodes)
    {
        $opsels = ['XL', 'TSEL', 'IOH'];
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
                    'orang' => 0
                ];
            }
        }

        try {
            $query = DB::table('spatial_movements as sm')
                ->select(
                    'sm.tanggal',
                    'sm.opsel',
                    'sm.kategori',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.is_forecast', false) // Only REAL data
                ->whereIn(DB::raw('UPPER(sm.kategori)'), $categories)
                ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->whereIn('sm.kode_dest_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('sm.tanggal', 'sm.opsel', 'sm.kategori')
                ->get();

            foreach ($query as $row) {
                $date = $row->tanggal;
                $rawOpsel = strtoupper($row->opsel);
                $kat = strtoupper($row->kategori) === 'PERGERAKAN' ? 'pergerakan' : 'orang';
                $vol = (int) $row->total_volume;

                // Normalize Opsel
                $opsel = 'OTHER';
                if (str_contains($rawOpsel, 'XL') || str_contains($rawOpsel, 'AXIS')) $opsel = 'XL';
                elseif (str_contains($rawOpsel, 'INDOSAT') || str_contains($rawOpsel, 'IOH') || str_contains($rawOpsel, 'TRI')) $opsel = 'IOH';
                elseif (str_contains($rawOpsel, 'TELKOMSEL') || str_contains($rawOpsel, 'TSEL') || str_contains($rawOpsel, 'SIMPATI')) $opsel = 'TSEL';

                if ($opsel !== 'OTHER' && isset($result[$date][$opsel])) {
                    $result[$date][$opsel][$kat] += $vol;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Jabodetabek Intra Pergerakan DB Error: ' . $e->getMessage());
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

        // Calculate Overall Totals and Overall %
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
            'overall_orang' => $overallTotalOrang
        ];
    }

    // --- NASIONAL METHODS ---

    public function nasionalOdSimpul(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        $dates = $this->getDatesCollection($startDate, $endDate);
        
        $dString = $startDate->format('Ymd') . '_' . $endDate->format('Ymd');
        $cacheKey = "mpd:nasional:od-simpul:split:v1:{$dString}";
        $cacheKeyOdProv = "mpd:nasional:od-simpul:prov:v1:{$dString}";
        $cacheKeyOdKabKota = "mpd:nasional:od-simpul:kabkota:v1:{$dString}";
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getNasionalOdSimpulData($startDate, $endDate);
            });
            $dataProv = Cache::remember($cacheKeyOdProv, 3600, function () use ($startDate, $endDate) {
                return $this->getNasionalOdProvinsiAsalData($startDate, $endDate);
            });
            $dataKabKota = Cache::remember($cacheKeyOdKabKota, 3600, function () use ($startDate, $endDate) {
                return $this->getNasionalOdKabKotaData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            $data = $this->getNasionalOdSimpulData($startDate, $endDate);
            $dataProv = $this->getNasionalOdProvinsiAsalData($startDate, $endDate);
            $dataKabKota = $this->getNasionalOdKabKotaData($startDate, $endDate);
        }

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
            'total_national' => $dataProv['total_national'],
            'prov_coords' => $dataProv['prov_coords'],
        ]);
    }

    private function getNasionalOdKabKotaData($startDate, $endDate)
    {
        try {
            $query = DB::table('spatial_movements as sm')
                // Join Origin City
                ->join('ref_cities as oc', 'sm.kode_origin_kabupaten_kota', '=', 'oc.code')
                // Join Dest City
                ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
                ->select(
                    'oc.code as origin_code',
                    'oc.name as origin_name',
                    'dc.code as dest_code',
                    'dc.name as dest_name',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn(DB::raw('UPPER(sm.kategori)'), ['PERGERAKAN', 'ORANG'])
                ->groupBy('oc.code', 'oc.name', 'dc.code', 'dc.name')
                ->orderByRaw('total_volume DESC')
                ->get();

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OD KabKota Query Error (DataMpd): ' . $e->getMessage());
            $query = collect();
        }

        $totalNational = $query->sum('total_volume');
        
        $topOrigin = $query->groupBy('origin_code')
            ->map(function ($rows) {
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $rows->first()->origin_code,
                    'name' => $rows->first()->origin_name,
                    'total' => $subTotal
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        $topDest = $query->groupBy('dest_code')
            ->map(function ($rows) {
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $rows->first()->dest_code,
                    'name' => $rows->first()->dest_name,
                    'total' => $subTotal
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // Top 20 overall routes for Sankey diagram
        $sankeyData = $query->take(20)->map(function($row) {
            return [
                'from' => '(O) ' . $row->origin_name,
                'to' => '(D) ' . $row->dest_name,
                'weight' => (int) $row->total_volume
            ];
        })->values();

        return [
            'top_origin' => $topOrigin,
            'top_dest' => $topDest,
            'sankey' => $sankeyData
        ];
    }

    private function getNasionalOdProvinsiAsalData($startDate, $endDate)
    {
        try {
            $query = DB::table('spatial_movements as sm')
                // Join Origin City & Province
                ->join('ref_cities as oc', 'sm.kode_origin_kabupaten_kota', '=', 'oc.code')
                ->join('ref_provinces as op', 'oc.province_code', '=', 'op.code')
                // Join Dest City & Province
                ->join('ref_cities as dc', 'sm.kode_dest_kabupaten_kota', '=', 'dc.code')
                ->join('ref_provinces as dp', 'dc.province_code', '=', 'dp.code')
                ->select(
                    'op.code as origin_code',
                    'op.name as origin_name',
                    'dp.code as dest_code',
                    'dp.name as dest_name',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn(DB::raw('UPPER(sm.kategori)'), ['PERGERAKAN', 'ORANG'])
                ->groupBy('op.code', 'op.name', 'dp.code', 'dp.name')
                ->orderByRaw('total_volume DESC')
                ->get();

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OD Provinsi Query Error (DataMpd): ' . $e->getMessage());
            $query = collect();
        }

        $totalNational = $query->sum('total_volume');
        
        $topOrigin = $query->groupBy('origin_code')
            ->map(function ($rows) use ($totalNational) {
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $rows->first()->origin_code,
                    'name' => $rows->first()->origin_name,
                    'total' => $subTotal,
                    'pct' => $totalNational > 0 ? ($subTotal / $totalNational) * 100 : 0
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        $topDest = $query->groupBy('dest_code')
            ->map(function ($rows) use ($totalNational) {
                $subTotal = $rows->sum('total_volume');
                return [
                    'code' => $rows->first()->dest_code,
                    'name' => $rows->first()->dest_name,
                    'total' => $subTotal,
                    'pct' => $totalNational > 0 ? ($subTotal / $totalNational) * 100 : 0
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        $sankeyData = $query->map(function($row) {
            return [
                'from' => '(O) ' . $row->origin_name,
                'to' => '(D) ' . $row->dest_name,
                'weight' => (int) $row->total_volume
            ];
        })->values();

        $provCoordsDB = DB::table('ref_provinces')->get();
        $provCoordsMapping = [];
        foreach ($provCoordsDB as $prov) {
            if (!empty($prov->latitude) && !empty($prov->longitude)) {
                $provCoordsMapping[strtoupper($prov->name)] = [(float) $prov->latitude, (float) $prov->longitude];
            }
        }

        return [
            'top_origin' => $topOrigin,
            'top_dest' => $topDest,
            'sankey' => $sankeyData,
            'total_national' => $totalNational,
            'prov_coords' => $provCoordsMapping
        ];
    }

    private function getNasionalOdSimpulData($startDate, $endDate)
    {
        // categories mapping
        $catMap = [
            'Terminal' => 'darat',
            'Pelabuhan' => 'laut',
            'Bandara' => 'udara',
            'Stasiun' => 'kereta'
        ];

        // Opsel list
        $opsels = ['XL', 'IOH', 'TSEL'];
        // Types
        $types = ['FORECAST', 'REAL'];

        // Initialize Structure
        $result = [
            'darat' => [], 'laut' => [], 'udara' => [], 'kereta' => []
        ];

        // Helper to init row
        $initRow = function($opsel, $tipe) use ($startDate, $endDate) {
            $row = [
                'tipe_data' => $tipe,
                'opsel' => $opsel,
                'total' => 0
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
           foreach ($types as $tipe) {
               foreach ($opsels as $opsel) {
                   // Create a key for easy access, e.g. "FORECAST_IOH"
                   $rowKey = $tipe . '_' . $opsel;
                   $result[$key][$rowKey] = $initRow($opsel, $tipe);
               }
           }
        }

        try {
            $query = DB::table('spatial_movements as sm')
                ->join('ref_transport_nodes as simpul', 'sm.kode_origin_simpul', '=', 'simpul.code')
                ->select(
                    'simpul.category as kategori_simpul',
                    'sm.tanggal',
                    'sm.opsel',
                    'sm.is_forecast',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN')
                ->groupBy('simpul.category', 'sm.tanggal', 'sm.opsel', 'sm.is_forecast')
                ->get();

            foreach ($query as $row) {
                $dbCat = $row->kategori_simpul;
                if (!isset($catMap[$dbCat])) continue; // Skip unknown categories

                $key = $catMap[$dbCat];
                $date = $row->tanggal;
                
                // Colors/Opsel Normalization
                $rawOpsel = strtoupper($row->opsel);
                $opsel = 'OTHER';
                if (str_contains($rawOpsel, 'XL') || str_contains($rawOpsel, 'AXIS')) $opsel = 'XL';
                elseif (str_contains($rawOpsel, 'INDOSAT') || str_contains($rawOpsel, 'IOH') || str_contains($rawOpsel, 'TRI')) $opsel = 'IOH';
                elseif (str_contains($rawOpsel, 'TELKOMSEL') || str_contains($rawOpsel, 'TSEL') || str_contains($rawOpsel, 'SIMPATI')) $opsel = 'TSEL';

                if ($opsel === 'OTHER') continue;

                $tipe = $row->is_forecast ? 'FORECAST' : 'REAL';
                $rowKey = $tipe . '_' . $opsel;

                if (isset($result[$key][$rowKey])) {
                    $vol = $row->total_volume;
                    $result[$key][$rowKey][$date] += $vol;
                    $result[$key][$rowKey]['total'] += $vol;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Nasional OD Simpul Error: ' . $e->getMessage());
        }

        return $result;
    }

    public function nasionalModeShare(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        $dates = $this->getDatesCollection($startDate, $endDate);
        
        $cacheKey = 'mpd:nasional:mode-share:tables:v1';
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getNasionalModeShareData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            $data = $this->getNasionalModeShareData($startDate, $endDate);
        }

        return view('data-mpd.nasional.mode-share', [
            'title' => 'Mode Share Nasional',
            'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'Mode Share'],
            'dates' => $dates,
            'data_umum' => $data['umum'],
            'data_pribadi' => $data['pribadi'],
            'data_detail' => $data['detail']
        ]);
    }

    private function getNasionalModeShareData($startDate, $endDate)
    {
        // 1. Definition & Initialization
        $opsels = ['XL', 'IOH', 'TSEL'];
        $types = ['REAL', 'FORECAST'];
        $pribadiModes = ['Mobil Pribadi', 'Motor Pribadi', 'Sepeda'];
        
        // Helper to check category
        $getCat = fn($modeName) => in_array($modeName, $pribadiModes) ? 'PRIBADI' : 'UMUM';

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
            'PRIBADI' => []
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
             $allModes = [
                'Angkutan Jalan (Bus AKAP)', 'Angkutan Jalan (Bus AKDP)', 'Angkutan Kereta Api Antarkota',
                'Angkutan Kereta Api KCJB', 'Angkutan Kereta Api Perkotaan', 'Angkutan Laut',
                'Angkutan Penyeberangan', 'Angkutan Udara', 'Mobil Pribadi', 'Motor Pribadi', 'Sepeda'
            ];
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
                        'daily' => array_fill_keys($dateKeys, 0)
                    ];
                }
            }
        }

        // 2. Fetch Data
        try {
            $query = DB::table('spatial_movements as sm')
                ->join('ref_transport_modes as moda', 'sm.kode_moda', '=', 'moda.code')
                ->select(
                    'moda.name as moda_name',
                    'sm.tanggal',
                    'sm.opsel',
                    'sm.is_forecast',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN')
                ->groupBy('moda.name', 'sm.tanggal', 'sm.opsel', 'sm.is_forecast')
                ->get();

            foreach ($query as $row) {
                $date = $row->tanggal;
                $modeName = $row->moda_name;
                $vol = $row->total_volume;
                $type = $row->is_forecast ? 'FORECAST' : 'REAL';
                
                 // Normalize Opsel
                $rawOpsel = strtoupper($row->opsel);
                $opsel = 'OTHER';
                if (str_contains($rawOpsel, 'XL') || str_contains($rawOpsel, 'AXIS')) $opsel = 'XL';
                elseif (str_contains($rawOpsel, 'INDOSAT') || str_contains($rawOpsel, 'IOH') || str_contains($rawOpsel, 'TRI')) $opsel = 'IOH';
                elseif (str_contains($rawOpsel, 'TELKOMSEL') || str_contains($rawOpsel, 'TSEL') || str_contains($rawOpsel, 'SIMPATI')) $opsel = 'TSEL';

                if ($opsel === 'OTHER') continue;

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
            \Illuminate\Support\Facades\Log::error('Nasional Mode Share Error: ' . $e->getMessage());
        }

        return [
            'umum' => $aggregated['UMUM'],
            'pribadi' => $aggregated['PRIBADI'],
            'detail' => array_values($detailRows) // Re-index for simpler loop
        ];
    }
    public function nasionalModeSharePage(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        
        $dString = $startDate->format('Ymd') . '_' . $endDate->format('Ymd');
        $cacheKey = "mpd:nasional:mode-share-page:v2:{$dString}";
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return [
                    'summary' => $this->getModeSharePageData($startDate, $endDate),
                    'daily' => $this->getDailyModeShareData($startDate, $endDate)
                ];
            });
        } catch (\Throwable $e) {
            $data = [
                'summary' => $this->getModeSharePageData($startDate, $endDate),
                'daily' => $this->getDailyModeShareData($startDate, $endDate)
            ];
        }

        return view('pages.nasional.mode-share', [
            'data' => $data['summary'],
            'dailyData' => $data['daily'],
            'dates' => $this->getDatesCollection($startDate, $endDate)
        ]);
    }

    private function getModeSharePageData($startDate, $endDate)
    {
        try {
            $query = DB::table('spatial_movements as sm')
                ->select(
                    'sm.kode_moda',
                    'sm.kategori',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.is_forecast', false) // As per image saying "Real"
                ->whereIn(DB::raw('UPPER(sm.kategori)'), ['PERGERAKAN', 'ORANG'])
                ->groupBy('sm.kode_moda', 'sm.kategori')
                ->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Mode Share Page Query Error (DataMpd): ' . $e->getMessage());
            $query = collect();
        }

        $modes = [
            'A' => 'Angkutan Jalan (Bus AKAP)',
            'B' => 'Angkutan Jalan (Bus AKDP)',
            'C' => 'Angkutan Kereta Api Antarkota',
            'D' => 'Angkutan Kereta Api KCJB',
            'E' => 'Angkutan Kereta Api Perkotaan',
            'F' => 'Angkutan Laut',
            'G' => 'Angkutan Penyeberangan',
            'H' => 'Angkutan Udara',
            'I' => 'Mobil Pribadi',
            'J' => 'Motor Pribadi',
            'K' => 'Sepeda'
        ];

        $pergerakanMap = [];
        $orangMap = [];

        foreach ($modes as $code => $name) {
            $pergerakanMap[$code] = ['name' => $name, 'y' => 0];
            $orangMap[$code] = ['name' => $name, 'y' => 0];
        }

        $totalPergerakan = 0;
        $totalOrang = 0;

        foreach ($query as $row) {
            $code = strtoupper($row->kode_moda);
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
        usort($pergerakanMap, fn($a, $b) => $b['y'] <=> $a['y']);
        usort($orangMap, fn($a, $b) => $b['y'] <=> $a['y']);

        return [
            'pergerakan' => array_values($pergerakanMap),
            'orang' => array_values($orangMap),
            'total_pergerakan' => $totalPergerakan,
            'total_orang' => $totalOrang
        ];
    }

    private function getDailyModeShareData($startDate, $endDate)
    {
        $modes = [
            'A' => 'Angkutan Jalan (Bus AKAP)',
            'B' => 'Angkutan Jalan (Bus AKDP)',
            'C' => 'Angkutan Kereta Api Antarkota',
            'D' => 'Angkutan Kereta Api KCJB',
            'E' => 'Angkutan Kereta Api Perkotaan',
            'F' => 'Angkutan Laut',
            'G' => 'Angkutan Penyeberangan',
            'H' => 'Angkutan Udara',
            'I' => 'Mobil Pribadi',
            'J' => 'Motor Pribadi',
            'K' => 'Sepeda'
        ];

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
                'daily' => array_fill_keys($dateKeys, 0)
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
                ->where(DB::raw('UPPER(sm.kategori)'), 'PERGERAKAN')
                ->groupBy('sm.tanggal', 'sm.kode_moda')
                ->get();

            foreach ($query as $row) {
                $date = $row->tanggal;
                $code = strtoupper($row->kode_moda);
                $vol = (int) $row->total_volume;

                if (isset($dailyData[$code]) && isset($dailyData[$code]['daily'][$date])) {
                    $dailyData[$code]['daily'][$date] += $vol;
                    $dailyData[$code]['total_pergerakan'] += $vol;
                }
            }

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Daily Mode Share Query Error (DataMpd): ' . $e->getMessage());
        }

        return $dailyData;
    }

    public function nasionalPergerakanHarianPage(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        $dates = $this->getDatesCollection($startDate, $endDate);
        
        $cacheKey = 'mpd:nasional:pergerakan-harian:v5';
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getPergerakanHarianData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            $data = $this->getPergerakanHarianData($startDate, $endDate);
        }

        return view('pages.nasional.pergerakan-harian', [
            'dates' => $dates,
            'data' => $data
        ]);
    }

    private function getPergerakanHarianData($startDate, $endDate)
    {
        $opsels = ['XL', 'IOH', 'TSEL'];
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
            $query = DB::table('spatial_movements')
                ->select(
                    'tanggal',
                    'opsel',
                    'kategori',
                    DB::raw('SUM(total) as total_volume')
                )
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn(DB::raw('UPPER(kategori)'), ['PERGERAKAN', 'ORANG'])
                ->groupBy('tanggal', 'opsel', 'kategori')
                ->get();

            $hasOrang = [];
            foreach ($query as $row) {
                $date = substr($row->tanggal, 0, 10);
                $rawOpsel = strtoupper($row->opsel);
                $cat = strtoupper($row->kategori);
                $vol = (int) $row->total_volume;

                $opsel = 'OTHER';
                if (str_contains($rawOpsel, 'XL') || str_contains($rawOpsel, 'AXIS')) $opsel = 'XL';
                elseif (str_contains($rawOpsel, 'INDOSAT') || str_contains($rawOpsel, 'IOH') || str_contains($rawOpsel, 'TRI')) $opsel = 'IOH';
                elseif (str_contains($rawOpsel, 'TELKOMSEL') || str_contains($rawOpsel, 'TSEL') || str_contains($rawOpsel, 'SIMPATI')) $opsel = 'TSEL';

                if ($opsel === 'OTHER' || !isset($dates[$date])) continue;

                if ($cat === 'PERGERAKAN') {
                    $dates[$date][$opsel]['movement'] += $vol;
                } elseif ($cat === 'ORANG') {
                    $dates[$date][$opsel]['people'] += $vol;
                    $hasOrang[$date][$opsel] = true;
                }
            }
            
            // Fallback for missing ORANG data
            foreach ($dates as $date => &$row) {
                foreach ($opsels as $op) {
                   if (!isset($hasOrang[$date][$op])) {
                       $row[$op]['people'] = $row[$op]['movement']; // 1:1 fallback
                   }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pergerakan Harian DB Error: ' . $e->getMessage());
        }

        $totals = [];
        foreach ($opsels as $op) {
            $totals[$op] = ['movement' => 0, 'people' => 0];
            foreach ($dates as $date => $row) {
                $totals[$op]['movement'] += $row[$op]['movement'];
                $totals[$op]['people'] += $row[$op]['people'];
            }
        }

        foreach ($dates as $date => &$row) {
            foreach ($opsels as $op) {
                $gMov = $totals[$op]['movement'];
                $gPpl = $totals[$op]['people'];
                
                $row[$op]['movement_pct'] = $gMov > 0 ? ($row[$op]['movement'] / $gMov) * 100 : 0;
                $row[$op]['people_pct'] = $gPpl > 0 ? ($row[$op]['people'] / $gPpl) * 100 : 0;
            }
        }

        // --- AKUMULASI (Section 02) ---
        $akumulasiDaily = [];
        $totalAkumulasiMov = 0;
        $totalAkumulasiPpl = 0;
        
        foreach ($dates as $date => $row) {
            $amov = 0; $appl = 0;
            foreach ($opsels as $op) {
                $amov += $row[$op]['movement'];
                $appl += $row[$op]['people'];
            }
            $akumulasiDaily[$date] = [
                'movement' => $amov,
                'people' => $appl
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
        uasort($sortedDaily, fn($a, $b) => $b['movement'] <=> $a['movement']);
        $peakDays = array_slice(array_keys($sortedDaily), 0, 2);

        // Calculate unique subscriber
        $uniqueSubscriber = $totalAkumulasiMov > 0 ? round($totalAkumulasiMov / 2.13) : 0;
        $koefisien = $uniqueSubscriber > 0 ? round($totalAkumulasiMov / $uniqueSubscriber, 2) : 0;

        $akumulasiData = [
            'daily' => $akumulasiDaily,
            'total_movement' => $totalAkumulasiMov,
            'total_people' => $totalAkumulasiPpl,
            'peak_days' => $peakDays,
            'unique_subscriber' => $uniqueSubscriber,
            'koefisien' => $koefisien
        ];

        return [
            'daily' => $dates,
            'totals' => $totals,
            'akumulasi' => $akumulasiData
        ];
    }

    public function nasionalPergerakan(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        $dates = $this->getDatesCollection($startDate, $endDate);
        
        $cacheKey = 'mpd:nasional:pergerakan:tables:v2';
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getPergerakanDataTables($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            $data = $this->getPergerakanDataTables($startDate, $endDate);
        }

        return view('data-mpd.nasional.pergerakan', [
            'title' => 'Pergerakan Nasional',
            'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'Pergerakan'],
            'dates' => $dates,
            'real' => $data['real'],
            'forecast' => $data['forecast'],
            'accum' => $data['accum']
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

        $opsels = ['XL', 'IOH', 'TSEL'];
        $types = ['REAL', 'FORECAST'];

        // Temporary storage to calc totals first (for percentage)
        $temp = [
            'REAL' => [],
            'FORECAST' => []
        ];
        
        $opselTotals = [
            'REAL' => array_fill_keys($opsels, 0),
            'FORECAST' => array_fill_keys($opsels, 0)
        ];

        // Fetch Data
        try {
            $query = DB::table('spatial_movements')
                ->select(
                    'tanggal',
                    'opsel',
                    'is_forecast',
                    DB::raw('SUM(total) as total_volume')
                )
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('kategori', 'PERGERAKAN');

            // Apply Filters if provided (e.g. Jabodetabek)
            if (!empty($filterCodes)) {
                 $query->whereIn('kode_origin_kabupaten_kota', $filterCodes);
            }

            $rows = $query->groupBy('tanggal', 'opsel', 'is_forecast')->get();

            foreach ($rows as $row) {
                $type = $row->is_forecast ? 'FORECAST' : 'REAL';
                $date = $row->tanggal;
                
                // Colors/Opsel Normalization
                $rawOpsel = strtoupper($row->opsel);
                $opsel = 'OTHER';
                if (str_contains($rawOpsel, 'XL') || str_contains($rawOpsel, 'AXIS')) $opsel = 'XL';
                elseif (str_contains($rawOpsel, 'INDOSAT') || str_contains($rawOpsel, 'IOH') || str_contains($rawOpsel, 'TRI')) $opsel = 'IOH';
                elseif (str_contains($rawOpsel, 'TELKOMSEL') || str_contains($rawOpsel, 'TSEL') || str_contains($rawOpsel, 'SIMPATI')) $opsel = 'TSEL';

                if ($opsel === 'OTHER') continue;

                $vol = $row->total_volume;

                if (!isset($temp[$type][$date])) $temp[$type][$date] = [];
                if (!isset($temp[$type][$date][$opsel])) $temp[$type][$date][$opsel] = 0;
                
                $temp[$type][$date][$opsel] += $vol;
                $opselTotals[$type][$opsel] += $vol;
            }
        } catch (\Throwable $e) {
             \Illuminate\Support\Facades\Log::error('Pergerakan Tables Error: ' . $e->getMessage());
        }

        // Process Final Structure
        $final = [
            'real' => [],
            'forecast' => [],
            'accum' => [] // Specifically for the Accumulation Table (Real)
        ];

        // Helper for Label
        $formatLabel = function($val) {
             if ($val >= 1000000) return number_format($val / 1000000, 2, ',', '.') . ' juta';
             if ($val >= 1000) return number_format($val / 1000, 2, ',', '.') . ' ribu';
             return number_format($val, 0, ',', '.');
        };

        // Running Accumulators
        $runningAccum = [
            'REAL' => ['total_mov' => 0, 'total_ppl' => 0],
            'FORECAST' => ['total_mov' => 0, 'total_ppl' => 0]
        ];

        foreach ($types as $type) {
            $arrKey = strtolower($type);
            
            foreach ($dateKeys as $date) {
                $row = [
                    'date' => $date,
                    'opsels' => [],
                    'total_mov' => 0,
                    'total_ppl' => 0,
                    'accum_mov' => 0,
                    'accum_ppl' => 0
                ];

                // Opsels
                foreach ($opsels as $op) {
                    $vol = $temp[$type][$date][$op] ?? 0;
                    $grand = $opselTotals[$type][$op];
                    $pct = $grand > 0 ? ($vol / $grand) * 100 : 0;
                    
                    $row['opsels'][$op] = [
                        'vol' => $vol,
                        'pct' => $pct,
                        'label' => $formatLabel($vol)
                    ];

                    $row['total_mov'] += $vol;
                }
                
                // People 1:1 ratio assume
                $row['total_ppl'] = $row['total_mov']; 

                // Update Accumulation
                $runningAccum[$type]['total_mov'] += $row['total_mov'];
                $runningAccum[$type]['total_ppl'] += $row['total_ppl'];

                $row['accum_mov'] = $runningAccum[$type]['total_mov'];
                $row['accum_ppl'] = $runningAccum[$type]['total_ppl'];

                $final[$arrKey][$date] = $row;
            }
        }

        // Accumulation Table (Derived from Real)
        foreach ($final['real'] as $date => $row) {
            $grandTotalReal = $runningAccum['REAL']['total_mov'];
            
            $pctMov = $grandTotalReal > 0 ? ($row['total_mov'] / $grandTotalReal) * 100 : 0;
            $pctPpl = $grandTotalReal > 0 ? ($row['total_ppl'] / $grandTotalReal) * 100 : 0;

            $final['accum'][$date] = [
                'mov' => [
                    'vol' => $row['total_mov'],
                    'pct' => $pctMov,
                    'label' => $formatLabel($row['total_mov']),
                    'accum' => $row['accum_mov']
                ],
                'ppl' => [
                    'vol' => $row['total_ppl'],
                    'pct' => $pctPpl,
                    'label' => $formatLabel($row['total_ppl']),
                    'accum' => $row['accum_ppl']
                ]
            ];
        }
        
        // Re-loop to fix percentages in Accum table (since GrandTotal is only known at end)
        $grandTotalMov = $runningAccum['REAL']['total_mov'];
        $grandTotalPpl = $runningAccum['REAL']['total_ppl'];

        foreach ($final['accum'] as $date => &$row) {
             $row['mov']['pct'] = $grandTotalMov > 0 ? ($row['mov']['vol'] / $grandTotalMov) * 100 : 0;
             $row['ppl']['pct'] = $grandTotalPpl > 0 ? ($row['ppl']['vol'] / $grandTotalPpl) * 100 : 0;
        }

        return $final;
    }

    private function getDatesCollection($startDate, $endDate)
    {
        $dates = collect();
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates->push($curr->format('Y-m-d'));
            $curr->addDay();
        }
        return $dates;
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
                ->join('ref_transport_nodes as simpul', 'sm.kode_origin_simpul', '=', 'simpul.code')
                ->select(
                    'simpul.category as kategori_simpul',
                    'sm.tanggal',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN');

            if (!empty($filterCodes)) {
                $query->whereIn('sm.kode_origin_kabupaten_kota', $filterCodes);
            }

            $data = $query->groupBy('simpul.category', 'sm.tanggal')->get();

            // C. Merge Data
            foreach ($data as $row) {
                $cat = $row->kategori_simpul;
                $date = $row->tanggal;
                $vol = $row->total_volume;

                if (isset($pivot[$cat])) {
                    $pivot[$cat][$date] = $vol;
                    $pivot[$cat]['total'] += $vol;
                }
            }
        } catch (\Throwable $e) {
            // If DB Query fails, we return the initialized empty pivot
            \Illuminate\Support\Facades\Log::error('OD Simpul DB Error: ' . $e->getMessage());
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
            $modes = [
                'Angkutan Jalan (Bus AKAP)', 'Angkutan Jalan (Bus AKDP)', 
                'Angkutan Kereta Api', 'Angkutan Laut', 'Angkutan Udara', 
                'Mobil Pribadi', 'Motor Pribadi'
            ];
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
                ->join('ref_transport_modes as moda', 'sm.kode_moda', '=', 'moda.code')
                ->select(
                    'moda.name as moda_name',
                    'sm.tanggal',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('sm.kategori', 'PERGERAKAN');

            if (!empty($filterCodes)) {
                $query->whereIn('sm.kode_origin_kabupaten_kota', $filterCodes);
            }

            $results = $query->groupBy('moda.name', 'sm.tanggal')->get();

            // C. Merge Data
            foreach ($results as $row) {
                $cat = $row->moda_name;
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
             \Illuminate\Support\Facades\Log::error('Mode Share DB Error: ' . $e->getMessage());
        }

        return ['movement' => $pivotMovement, 'people' => $pivotPeople];
    }

    public function jabodetabekPergerakan(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 30 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        
        $dates = $this->getDatesCollection($startDate, $endDate);

        // 2. Caching Key
        $cacheKey = 'mpd:jabodetabek:pergerakan:tables:v2';
        
        $jabodetabekCodes = $this->getJabodetabekCodes();
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $jabodetabekCodes) {
                return $this->getPergerakanDataTables($startDate, $endDate, $jabodetabekCodes);
            });
        } catch (\Throwable $e) {
            // Redis/DB Fallback
            $data = $this->getPergerakanDataTables($startDate, $endDate, $jabodetabekCodes);
        }

        return view('data-mpd.jabodetabek.pergerakan', [
            'title' => 'Pergerakan Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Pergerakan'],
            'dates' => $dates,
            'real' => $data['real'],
            'forecast' => $data['forecast'],
            'accum' => $data['accum']
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
                'XL'   => ['movement' => 0, 'people' => 0],
                'IOH'  => ['movement' => 0, 'people' => 0],
                'TSEL' => ['movement' => 0, 'people' => 0],
                'Total' => ['movement' => 0, 'people' => 0]
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

            if (!empty($filterCodes)) {
                $query->whereIn('kode_origin_kabupaten_kota', $filterCodes);
            }

            $results = $query->groupBy('tanggal', 'opsel')->get();

            foreach ($results as $row) {
                $date = $row->tanggal;
                $rawOpsel = strtoupper($row->opsel);
                $vol = $row->total_volume;

                // Normalize Opsel Name
                $opsel = 'OTHER';
                if (str_contains($rawOpsel, 'XL') || str_contains($rawOpsel, 'AXIS')) $opsel = 'XL';
                elseif (str_contains($rawOpsel, 'INDOSAT') || str_contains($rawOpsel, 'IOH') || str_contains($rawOpsel, 'TRI')) $opsel = 'IOH';
                elseif (str_contains($rawOpsel, 'TELKOMSEL') || str_contains($rawOpsel, 'TSEL') || str_contains($rawOpsel, 'SIMPATI')) $opsel = 'TSEL';

                if (isset($dates[$date]) && isset($dates[$date][$opsel])) {
                    $dates[$date][$opsel]['movement'] += $vol;
                    $dates[$date][$opsel]['people'] += $vol; // Assumed 1:1
                    
                    // Add to Day Total
                    $dates[$date]['Total']['movement'] += $vol;
                    $dates[$date]['Total']['people'] += $vol;
                }
            }

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pergerakan DB Error: ' . $e->getMessage());
        }

        return $dates;
    }

    public function jabodetabekPergerakanOrang(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 30 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 30);
        
        $dates = collect();
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates->push($curr->format('Y-m-d'));
            $curr->addDay();
        }

        $kategoriFilter = $request->input('kategori', 'REAL');
        $isForecast = ($kategoriFilter === 'FORECAST');

        // 2. Caching Key
        $cacheKey = "mpd:jabodetabek:pergerakan-orang:v2:{$isForecast}";
        
        $jabodetabekCodes = $this->getJabodetabekCodes();
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $jabodetabekCodes, $isForecast) {
                return $this->getPergerakanOrangData($startDate, $endDate, $jabodetabekCodes, $isForecast);
            });
        } catch (\Throwable $e) {
            // Fallback
            $data = $this->getPergerakanOrangData($startDate, $endDate, $jabodetabekCodes, $isForecast);
        }

        return view('data-mpd.jabodetabek.pergerakan-orang', [
            'title' => 'Akumulasi Pergerakan & Orang Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Pergerakan & Orang'],
            'dates' => $dates,
            'data' => $data
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
                    'people' => 0
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
                if (!isset($dailyData[$row->tanggal])) {
                    $dailyData[$row->tanggal] = ['movement' => 0, 'people' => 0];
                }
                $dailyData[$row->tanggal]['people'] = (int) $row->total_volume;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pergerakan Orang DB Error: ' . $e->getMessage());
        }

        return $dailyData;
    }
}
