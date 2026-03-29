<?php

declare(strict_types=1);

namespace App\Services\ExecutiveSummary;

use App\Models\SpatialMovement;
use App\Traits\MpdHelpers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExecutiveSummaryService
{
    use MpdHelpers;

    public const JABODETABEK_PROVINCES = ['31', '32', '36'];

    private function getStartDate(): string
    {
        return config('mpd.start_date', '2026-03-13');
    }

    private function getEndDate(): string
    {
        return config('mpd.end_date', '2026-03-29');
    }

    private function cacheTtl(): int
    {
        return (int) config('mpd.cache_ttl.dashboard', 10800);
    }

    private function getKoefisienArray(): array
    {
        $finalKoef = config('mpd_koefisien.final');
        if (!empty($finalKoef) && is_array($finalKoef)) {
            return $finalKoef;
        }

        $endDate  = $this->getEndDate();
        $batches  = config('mpd_koefisien.batches', []);
        $selBatch = null;

        foreach ($batches as $batch) {
            if (isset($batch['end_date']) && $batch['end_date'] >= $endDate) {
                $selBatch = $batch;
                break;
            }
        }

        if (! $selBatch && ! empty($batches)) {
            $selBatch = end($batches);
        }

        return $selBatch ?: ['TSEL' => 1.0, 'IOH' => 1.0, 'XLSMART' => 1.0];
    }

    public function getFullSummary(?string $opsel, string $dataType = 'real'): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getFullSummary:{$dataType}:{$opsel}:all_v14_godmode:{$dateKey}";

        try {
            return Cache::remember($key, $this->cacheTtl(), fn () => $this->buildFullSummary($opsel, $dataType));
        } catch (\Throwable $e) {
            return $this->buildFullSummary($opsel, $dataType);
        }
    }

    private function buildFullSummary(?string $opsel, string $dataType): array
    {
        return [
            'nasional' => $this->getNasionalMetrics($dataType, $opsel),
            'peak' => $this->getPeakDay($dataType, $opsel),
            'opsel' => $this->getOpselContribution($dataType),
            'opsel_intra' => $this->getOpselContribution($dataType, 'intra'),
            'opsel_inter' => $this->getOpselContribution($dataType, 'inter'),
            'forecast' => $this->getForecastComparison($dataType, $opsel),
            'yoy' => $this->getYoyComparison($dataType, $opsel),
            'intra' => $this->getIntraJabodetabek($dataType, $opsel),
            'inter' => $this->getInterJabodetabek($dataType, $opsel),
            'trend_pergerakan' => $this->getDailyTrend('PERGERAKAN', $dataType, $opsel),
            'trend_orang' => $this->getDailyTrend('ORANG', $dataType, $opsel),
            'trend_intra' => $this->getDailyTrend('ORANG', $dataType, $opsel, 'intra'),
            'trend_inter' => $this->getDailyTrend('ORANG', $dataType, $opsel, 'inter'),
            'kesimpulan' => $this->generateNarrative([], 'kesimpulan'),
        ];
    }

    private function getRealDates(string $kategori, ?string $opsel): array
    {
        $key = "executive_summary:real_dates:{$kategori}:{$opsel}:{$this->getStartDate()}:{$this->getEndDate()}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($kategori, $opsel) {
            $q = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
                ->where('kategori', $kategori)
                ->where('is_forecast', false);
            if ($opsel) {
                $q->where('opsel', $opsel);
            }
            return $q->distinct()->pluck('tanggal')->toArray();
        });
    }

    private function baseQuery(string $kategori, string $dataType, ?string $opsel)
    {
        $dataType = strtolower($dataType);
        $q = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()]);
        // Note: Removed 'kategori != ORANG' to allow total data reach for Jabo/Nasional aggregates

        if ($dataType === 'combined') {
            $q->where(function ($query) {
                $query->where(function ($realQ) {
                    $realQ->where('is_forecast', false)
                          ->whereNotIn(DB::raw('DATE(tanggal)'), ['2026-03-27', '2026-03-28', '2026-03-29']);
                })->orWhere(function ($forecastQ) {
                    $forecastQ->where('is_forecast', true)
                              ->where(function ($cond) {
                                  $cond->whereIn(DB::raw('DATE(tanggal)'), ['2026-03-27', '2026-03-28', '2026-03-29'])
                                       ->orWhereNotExists(function ($exists) {
                                           $exists->select(DB::raw(1))
                                                  ->from('spatial_movements as sm2')
                                                  ->whereColumn(DB::raw('DATE(sm2.tanggal)'), DB::raw('DATE(spatial_movements.tanggal)'))
                                                  ->whereColumn('sm2.opsel', 'spatial_movements.opsel')
                                                  ->where('sm2.kategori', '!=', 'ORANG')
                                                  ->where('sm2.is_forecast', false);
                                       });
                              });
                });
            });
        } else {
            $q->where('is_forecast', $dataType === 'forecast');
        }
        if ($opsel) {
            // Kita tidak bisa langsung where('opsel', $opsel) karena DB mungkin punya 'TELKOMSEL' untuk 'TSEL'
            // Tapi baseQuery harus mengembalikan query builder.
            // Strategi: Jika opsel difilter, kita ambil variasi namanya dari helper.
            $searchOpsels = [];
            if (in_array($this->normalizeOpsel($opsel), ['TSEL', 'IOH', 'XLSMART'])) {
                 // Untuk filter dashboard, kita percayakan normalisasi di level aggregation.
                 // Namun untuk query dasar, kita filter string mentahnya agar performant.
                 $q->where('opsel', 'LIKE', '%' . $opsel . '%');
            } else {
                 $q->where('opsel', $opsel);
            }
        }

        return $q;
    }

    public function getNasionalMetrics(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getNasionalMetrics:{$dataType}:{$opsel}:nasional_v9:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $selBatch = $this->getKoefisienArray();
            $dType = strtoupper($dataType);

            // Jika mode bukan COMBINED, kita tetap bisa gunakan query agregasi sederhana
            // Tapi untuk performa maksimal dan konsistensi, kita gunakan agregasi harian per-opsel per-is_forecast
            $query = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
                ->where('kategori', '!=', 'ORANG')
                ->select(
                    DB::raw('DATE(tanggal) as date_val'), 
                    'opsel', 
                    'is_forecast',
                    DB::raw('SUM(total) as t')
                )
                ->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'is_forecast');

            if ($opsel) {
                $query->where('opsel', 'LIKE', '%' . $opsel . '%');
            }

            $rows = $query->get();

            // Mapping data ke struktur [date][opsel][is_forecast]
            $temp = [];
            foreach ($rows as $row) {
                $d  = $row->date_val;
                $op = $this->normalizeOpsel($row->opsel);
                $isF = $row->is_forecast ? 'F' : 'R';
                $temp[$d][$op][$isF] = (float) $row->t;
            }

            $totalUnique     = 0;
            $totalPergerakan = 0;
            $dates = array_keys($temp);
            sort($dates);

            $opsels = ['TSEL', 'IOH', 'XLSMART'];
            $forceForecastDates = ['2026-03-27', '2026-03-28', '2026-03-29'];

            foreach ($dates as $d) {
                foreach ($opsels as $op) {
                    $realVol     = $temp[$d][$op]['R'] ?? 0;
                    $forecastVol = $temp[$d][$op]['F'] ?? 0;

                    $vol = 0;
                    if ($dType === 'COMBINED') {
                        // Aturan COMBINED: Gunakan Forecast jika TGL 27-29 atau Real tidak ada
                        $isForced = in_array($d, $forceForecastDates);
                        $vol = ($realVol > 0 && !$isForced) ? $realVol : $forecastVol;
                    } elseif ($dType === 'FORECAST') {
                        $vol = $forecastVol;
                    } else {
                        $vol = $realVol;
                    }

                    if ($vol <= 0) continue;

                    $koef = (float) ($selBatch[$op] ?? 1.0);
                    // Bulatkan per-opsel per-hari (Metode Kanonik Terkunci 100%)
                    $totalUnique     += $koef > 0 ? round($vol / $koef) : 0;
                    $totalPergerakan += $vol;
                }
            }

            // Tidak ada pembulatan tambahan karena sudah dijumlah dari bilangan bulat
            $finalUnique = (int) $totalUnique;
            $koefisienAvgValue = $finalUnique > 0 ? round($totalPergerakan / $finalUnique, 2) : 0.0;

            return [
                'pergerakan' => $totalPergerakan,
                'orang'      => $finalUnique,
                'koefisien'  => $koefisienAvgValue,
                'narrative'  => $this->generateNarrative(['pergerakan' => $totalPergerakan], 'nasional_pergerakan'),
            ];
        });
    }


    public function getOpselContribution(string $dataType, ?string $region = null): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getOpselContribution:{$dataType}:all:".($region ?? 'nasional').":v5_{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $region) {
            $selBatch = $this->getKoefisienArray();
            $dType = strtoupper($dataType);

            // 1. Fetch data per-hari per-opsel per-is_forecast
            $query = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
                ->where('kategori', '!=', 'ORANG')
                ->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', 'is_forecast', DB::raw('SUM(total) as t'))
                ->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'is_forecast');

            if ($region) {
                $this->applyJaboFilter($query, $region);
            }

            $rows = $query->get();

            // 2. Map ke struktur [date][opsel][is_forecast]
            $temp = [];
            foreach ($rows as $row) {
                $temp[$row->date_val][$this->normalizeOpsel($row->opsel)][$row->is_forecast ? 'F' : 'R'] = (float) $row->t;
            }

            // 3. Process Logic
            $opStats = ['TSEL' => ['p' => 0, 'o' => 0], 'IOH' => ['p' => 0, 'o' => 0], 'XLSMART' => ['p' => 0, 'o' => 0]];
            $forceForecastDates = ['2026-03-27', '2026-03-28', '2026-03-29'];

            foreach ($temp as $d => $opselsInDate) {
                foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
                    $r = $opselsInDate[$op]['R'] ?? 0;
                    $f = $opselsInDate[$op]['F'] ?? 0;

                    if ($dType === 'COMBINED') {
                        $vol = ($r > 0 && !in_array($d, $forceForecastDates)) ? $r : $f;
                    } elseif ($dType === 'FORECAST') {
                        $vol = $f;
                    } else {
                        $vol = $r;
                    }

                    if ($vol <= 0) continue;

                    $koef = (float) ($selBatch[$op] ?? 1.0);
                    $opStats[$op]['p'] += $vol;
                    $opStats[$op]['o'] += ($vol / $koef);
                }
            }

            // 4. Format Finals
            $totalP = array_sum(array_column($opStats, 'p'));
            $totalO = array_sum(array_column($opStats, 'o'));
            
            $data = ['pergerakan' => [], 'orang' => []];
            foreach ($opStats as $op => $vals) {
                $data['pergerakan'][$op] = [
                    'total' => $vals['p'], 
                    'pct'   => $totalP > 0 ? round(($vals['p'] / $totalP) * 100, 1) : 0
                ];
                $data['orang'][$op] = [
                    'total' => round($vals['o']), 
                    'pct'   => $totalO > 0 ? round(($vals['o'] / $totalO) * 100, 1) : 0
                ];
            }

            $data['narrative'] = $this->generateNarrative($data, 'opsel');
            return $data;
        });
    }


    public function getDailyTrend(string $kategori, string $dataType, ?string $opsel, string $region = 'nasional'): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getDailyTrend:{$dataType}:{$opsel}:{$region}_{$kategori}:v6_{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($kategori, $dataType, $opsel, $region) {
            $selBatch = $this->getKoefisienArray();
            $dType = strtoupper($dataType);

            // 1. Fetch Aggregated Daily Data (High Performance)
            $query = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
                ->where('kategori', '!=', 'ORANG')
                ->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', 'is_forecast', DB::raw('SUM(total) as t'))
                ->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'is_forecast');

            if ($opsel) {
                $query->where('opsel', 'LIKE', '%' . $opsel . '%');
            }

            if ($region === 'intra' || $region === 'inter') {
                $this->applyJaboFilter($query, $region);
            }

            $rows = $query->get();

            // 2. Map ketersediaan data
            $temp = [];
            foreach ($rows as $row) {
                $temp[$row->date_val][$this->normalizeOpsel($row->opsel)][$row->is_forecast ? 'F' : 'R'] = (float) $row->t;
            }

            // 3. Process Per-Hari (PHP Logic)
            $final = [];
            $dates = array_keys($temp);
            sort($dates);
            $forceForecastDates = ['2026-03-27', '2026-03-28', '2026-03-29'];

            foreach ($dates as $d) {
                $dailyTotal = 0;
                $dailyOrang = 0;

                foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
                    if ($opsel && $this->normalizeOpsel($op) !== $this->normalizeOpsel($opsel)) {
                        continue;
                    }

                    $r = $temp[$d][$op]['R'] ?? 0;
                    $f = $temp[$d][$op]['F'] ?? 0;

                    if ($dType === 'COMBINED') {
                        $isForced = in_array($d, $forceForecastDates);
                        $vol = ($r > 0 && !$isForced) ? $r : $f;
                    } elseif ($dType === 'FORECAST') {
                        $vol = $f;
                    } else {
                        $vol = $r;
                    }

                    if ($vol <= 0) continue;

                    $koef = (float) ($selBatch[$op] ?? 1.0);
                    $dailyTotal += $vol;
                    $dailyOrang += ($vol / $koef);
                }

                $final[$d] = ($kategori === 'ORANG') ? round($dailyOrang) : $dailyTotal;
            }

            return $final;
        });
    }


    public function getPeakDay(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getPeakDay:{$dataType}:{$opsel}:nasional:v2_{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $trend = $this->getDailyTrend('PERGERAKAN', $dataType, $opsel);
            if (empty($trend)) {
                return [];
            }
            arsort($trend);
            $total = array_sum($trend);
            $peaks = [];
            foreach (array_slice($trend, 0, 3, true) as $tgl => $val) {
                $peaks[] = ['tanggal' => $tgl, 'total' => $val, 'pct' => $total > 0 ? round(($val / $total) * 100, 1) : 0];
            }

            return ['top' => $peaks[0] ?? null, 'list' => $peaks];
        });
    }

    private function applyJaboFilter($query, string $type): void
    {
        $jabo = implode("','", self::JABODETABEK_PROVINCES);
        $condOrigin = "LEFT(kode_origin_kabupaten_kota, 2) IN ('$jabo')";
        $condDest = "LEFT(kode_dest_kabupaten_kota, 2) IN ('$jabo')";

        if ($type === 'intra') {
            $query->whereRaw("($condOrigin AND $condDest)");
        } else {
            $query->whereRaw("(($condOrigin OR $condDest) AND NOT ($condOrigin AND $condDest))");
        }
    }

    public function getIntraJabodetabek(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getIntraJabodetabek:{$dataType}:{$opsel}:intra_v4:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $sums = $this->batchJaboSums($dataType, $opsel, 'intra');

            // Koefisien per-opsel per-batch (konsisten dengan getNasionalMetrics)
            $koefisien = $this->getKoefisienWeighted($dataType, $opsel, 'intra');

            return [
                'pergerakan' => $sums['PERGERAKAN'],
                'orang'      => $sums['ORANG'],
                'koefisien'  => $koefisien,
                'narrative'  => $this->generateNarrative(['pergerakan' => $sums['PERGERAKAN']], 'intra'),
            ];
        });
    }

    public function getInterJabodetabek(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getInterJabodetabek:{$dataType}:{$opsel}:inter_v4:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $sums = $this->batchJaboSums($dataType, $opsel, 'inter');

            // Koefisien per-opsel per-batch (konsisten dengan getNasionalMetrics)
            $koefisien = $this->getKoefisienWeighted($dataType, $opsel, 'inter');

            return [
                'pergerakan' => $sums['PERGERAKAN'],
                'orang'      => $sums['ORANG'],
                'koefisien'  => $koefisien,
                'narrative'  => $this->generateNarrative(['pergerakan' => $sums['PERGERAKAN']], 'inter'),
            ];
        });
    }

    /**
     * Batch query: get PERGERAKAN + ORANG sums for intra/inter Jabodetabek in a single query
     */
    private function batchJaboSums(string $dataType, ?string $opsel, string $region): array
    {
        $dType = strtolower($dataType);

        // Fast Base Query tanpa subquery lambat
        $buildQ = function($isForecastFlag) use ($opsel, $region) {
            $q = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
                ->where('is_forecast', $isForecastFlag);
            if ($opsel) {
                $q->where('opsel', 'LIKE', '%' . $this->normalizeOpsel($opsel) . '%');
            }
            $this->applyJaboFilter($q, $region);
            return $q;
        };

        if ($dType === 'combined') {
            $qReal     = $buildQ(false);
            $qForecast = $buildQ(true);

            $rowsReal = $qReal->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', 'kategori', DB::raw('SUM(total) as t'))
                ->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'kategori')
                ->get();
            $rowsForecast = $qForecast->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', 'kategori', DB::raw('SUM(total) as t'))
                ->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'kategori')
                ->get();
        } else {
            $isF = ($dType === 'forecast');
            $q = $buildQ($isF);
            $rowsReal = $q->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', 'kategori', DB::raw('SUM(total) as t'))
                ->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'kategori')
                ->get();
            $rowsForecast = [];
        }

        $temp = [];
        foreach ($rowsReal as $row) {
            $cat = strtoupper($row->kategori) === 'ORANG' ? 'orang' : 'pergerakan';
            $temp[$row->date_val][$this->normalizeOpsel($row->opsel)][$cat]['R'] = (float) $row->t;
        }
        foreach ($rowsForecast as $row) {
            $cat = strtoupper($row->kategori) === 'ORANG' ? 'orang' : 'pergerakan';
            $temp[$row->date_val][$this->normalizeOpsel($row->opsel)][$cat]['F'] = (float) $row->t;
        }

        $forceForecastDates = ['2026-03-27', '2026-03-28', '2026-03-29'];
        $totPerg   = 0;
        $totOrang  = 0;
        $opsels    = ['TSEL', 'IOH', 'XLSMART'];
        
        foreach ($temp as $d => $opData) {
            foreach ($opsels as $op) {
                if ($opsel && $this->normalizeOpsel($opsel) !== $op) continue;

                $rMov   = $opData[$op]['pergerakan']['R'] ?? 0;
                $fMov   = $opData[$op]['pergerakan']['F'] ?? 0;
                $rOrang = $opData[$op]['orang']['R'] ?? 0;
                $fOrang = $opData[$op]['orang']['F'] ?? 0;

                if ($dType === 'combined') {
                    $isForced = in_array($d, ['2026-03-27', '2026-03-28', '2026-03-29']);
                    $volMov   = ($rMov > 0 && !$isForced) ? $rMov : $fMov;
                    $volOrang = ($rMov > 0 && !$isForced) ? $rOrang : $fOrang;
                } elseif ($dType === 'forecast') {
                    $volMov   = $fMov;
                    $volOrang = $fOrang;
                } else {
                    $volMov   = $rMov;
                    $volOrang = $rOrang;
                }

                if ($volMov <= 0 && $volOrang <= 0) continue;

                $totPerg  += $volMov;
                $totOrang += $volOrang > 0 ? $volOrang : $volMov; 
            }
        }

        return [
            'PERGERAKAN' => $totPerg,
            'ORANG'      => $totOrang,
        ];
    }

    public function getKoefisien(string $dataType, ?string $opsel, ?string $region = null): float
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getKoefisien:{$dataType}:{$opsel}:{$region}:v2_{$dateKey}";

        return (float) Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel, $region) {
            $selBatch = $this->getKoefisienArray();
            $dType    = strtoupper($dataType);

            $query = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
                ->where('kategori', '!=', 'ORANG')
                ->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', 'is_forecast', DB::raw('SUM(total) as t'))
                ->groupBy(DB::raw('DATE(tanggal)'), 'opsel', 'is_forecast');

            if ($opsel) {
                $query->where('opsel', 'LIKE', '%' . $opsel . '%');
            }
            if ($region) {
                $this->applyJaboFilter($query, $region);
            }

            $rows = $query->get();
            $temp = [];
            foreach ($rows as $row) {
                $temp[$row->date_val][$this->normalizeOpsel($row->opsel)][$row->is_forecast ? 'F' : 'R'] = (float) $row->t;
            }

            $forceForecastDates = ['2026-03-27', '2026-03-28', '2026-03-29'];
            $totalUnique = 0;
            $totalPerg   = 0;

            foreach ($temp as $d => $opData) {
                foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
                    $r = $opData[$op]['R'] ?? 0;
                    $f = $opData[$op]['F'] ?? 0;

                    if ($dType === 'COMBINED') {
                        $vol = ($r > 0 && !in_array($d, $forceForecastDates)) ? $r : $f;
                    } elseif ($dType === 'FORECAST') {
                        $vol = $f;
                    } else {
                        $vol = $r;
                    }

                    if ($vol <= 0) continue;

                    $koef = (float) ($selBatch[$op] ?? 1.0);
                    $totalUnique += $koef > 0 ? round($vol / $koef) : 0;
                    $totalPerg   += $vol;
                }
            }

            $finalUnique = (int) $totalUnique;
            return $finalUnique > 0 ? round($totalPerg / $finalUnique, 2) : 0.0;
        });
    }

    /**
     * Hitung weighted average koefisien per-opsel per-batch.
     */
    private function getKoefisienWeighted(string $dataType, ?string $opsel, ?string $region = null): float
    {
        // Untuk skalar, gunakan getKoefisien agar konsisten
        return $this->getKoefisien($dataType, $opsel, $region);
    }

    public function getForecastComparison(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getForecastComparison:{$dataType}:{$opsel}:nasional:v6:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $real = $this->getDailyTrend('PERGERAKAN', $dataType, $opsel);
            
            // Menggunakan ketetapan konstanta proporsi hasil survei kemenhub (prakiraan)
            $forecastConstants = [
                '2026-03-13' => 4776474,
                '2026-03-14' => 12006272,
                '2026-03-15' => 5928034,
                '2026-03-16' => 23201446,
                '2026-03-17' => 13448227,
                '2026-03-18' => 24077633,
                '2026-03-19' => 10293952,
                '2026-03-20' => 14885174,
                '2026-03-21' => 8771889,
                '2026-03-22' => 8947126,
                '2026-03-23' => 5632634,
                '2026-03-24' => 3815171,
                '2026-03-25' => 3194329,
                '2026-03-26' => 1206635,
                '2026-03-27' => 1136540,
                '2026-03-28' => 996350,
                '2026-03-29' => 1141547,
            ];

            $totReal = array_sum($real);
            $totFore = array_sum($forecastConstants);
            $res = [];
            foreach (array_keys($forecastConstants) as $dt) {
                $r = $real[$dt] ?? 0;
                $f = $forecastConstants[$dt] ?? 0;
                $res[$dt] = [
                    'real_val' => $r,
                    'real_pct' => $totReal > 0 ? round(($r / $totReal) * 100, 1) : 0,
                    'fore_val' => $f,
                    'fore_pct' => $totFore > 0 ? round(($f / $totFore) * 100, 1) : 0,
                ];
            }
            ksort($res);

            return $res;
        });
    }

    public function getYoyComparison(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getYoyComparison:{$dataType}:{$opsel}:nasional:v5:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $nasional = $this->getNasionalMetrics($dataType, $opsel);
            $curr = (float) $nasional['orang'];
            $prev = config('mpd.historical_baselines.2025_orang', 115197227); // default fallback

            return [
                'current' => $curr, 'previous' => $prev,
                'growth_pct' => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 2) : 0,
                'narrative' => 'Angka tersebut berada pada selisih sekitar '.($prev > 0 ? round((($curr - $prev) / $prev) * 100, 2) : 0).'% terhadap estimasi masyarakat tahun sebelumnya.',
            ];
        });
    }

    public function generateNarrative(array $metrics, string $type): string
    {
        if ($type === 'opsel') {
            $opselData = $metrics['pergerakan'] ?? [];
            $maxOpsel = '';
            $maxPct = -1;
            foreach ($opselData as $name => $data) {
                if (($data['pct'] ?? 0) > $maxPct) {
                    $maxPct = $data['pct'] ?? 0;
                    $maxOpsel = $name;
                }
            }
            $maxOpselName = $maxOpsel ?: 'Satu operator';

            return "{$maxOpselName} mendominasi perekaman mobilitas dengan menyumbang sekitar {$maxPct}% dari total pergerakan.";
        }
        $val = number_format($metrics['pergerakan'] ?? 0, 0, ',', '.');
        if ($type === 'intra') {
            return "Jumlah pergerakan Masyarakat Intra Jabodetabek pada periode ini adalah {$val} pergerakan.";
        }
        if ($type === 'inter') {
            return "Sedangkan untuk jumlah pergerakan Masyarakat Inter Jabodetabek sebesar {$val} pergerakan.";
        }
        if ($type === 'nasional_pergerakan') {
            return "Jumlah pergerakan masyarakat pada Periode Angkutan Lebaran 2026, dengan nilai realisasi adalah {$val} pergerakan.";
        }

        return 'Distribusi pergerakan penduduk relatif stabil selama periode pengamatan.';
    }

    private function normalizeOpsel(string $opsel): string
    {
        $opsel = strtoupper($opsel);
        if (str_contains($opsel, 'TSEL') || str_contains($opsel, 'TELKOMSEL')) {
            return 'TSEL';
        }
        if (str_contains($opsel, 'IOH') || str_contains($opsel, 'INDOSAT')) {
            return 'IOH';
        }
        if (str_contains($opsel, 'XL')) {
            return 'XLSMART';
        }

        return $opsel;
    }
}
