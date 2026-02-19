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
        // 1. Date Range: 13 March 2026 - 29 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);
        
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
        // 1. Date Range: 13 March 2026 - 29 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);
        
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

    // --- NASIONAL METHODS ---

    public function nasionalOdSimpul(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);
        $dates = $this->getDatesCollection($startDate, $endDate);
        
        $cacheKey = 'mpd:nasional:od-simpul:split:v1';
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
                return $this->getNasionalOdSimpulData($startDate, $endDate);
            });
        } catch (\Throwable $e) {
            $data = $this->getNasionalOdSimpulData($startDate, $endDate);
        }

        return view('data-mpd.nasional.od-simpul', [
            'title' => 'O-D Simpul Nasional',
            'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'O-D Simpul'],
            'dates' => $dates,
            'simpul_darat' => $data['darat'],
            'simpul_laut' => $data['laut'],
            'simpul_udara' => $data['udara'],
            'simpul_kereta' => $data['kereta'],
        ]);
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
        $endDate = Carbon::create(2026, 3, 29);
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

    public function nasionalPergerakan(Request $request)
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);
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
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

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
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

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
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

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
        // 1. Date Range: 13 March 2026 - 29 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);
        
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
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

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
        // 1. Date Range: 13 March 2026 - 29 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);
        
        $dates = collect();
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates->push($curr->format('Y-m-d'));
            $curr->addDay();
        }

        // 2. Caching Key
        $cacheKey = 'mpd:jabodetabek:pergerakan-orang:v1';
        
        $jabodetabekCodes = $this->getJabodetabekCodes();
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $jabodetabekCodes) {
                return $this->getPergerakanOrangData($startDate, $endDate, $jabodetabekCodes);
            });
        } catch (\Throwable $e) {
            // Fallback
            $data = $this->getPergerakanOrangData($startDate, $endDate, $jabodetabekCodes);
        }

        return view('data-mpd.jabodetabek.pergerakan-orang', [
            'title' => 'Akumulasi Pergerakan & Orang Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Pergerakan & Orang'],
            'dates' => $dates,
            'data' => $data
        ]);
    }

    private function getPergerakanOrangData($startDate, $endDate, $jabodetabekCodes)
    {
        $dailyData = [];
        
        try {
            $results = DB::table('spatial_movements')
                ->select('tanggal', DB::raw('SUM(total) as total_volume'))
                ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('tanggal')
                ->get();

            foreach ($results as $row) {
                $dailyData[$row->tanggal] = [
                    'movement' => $row->total_volume,
                    'people' => $row->total_volume // 1:1 for now
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Pergerakan Orang DB Error: ' . $e->getMessage());
        }

        return $dailyData;
    }
}
