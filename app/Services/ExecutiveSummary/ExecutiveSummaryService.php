<?php

declare(strict_types=1);

namespace App\Services\ExecutiveSummary;

use App\Models\SpatialMovement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExecutiveSummaryService
{
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
        $key = "executive_summary:getFullSummary:{$dataType}:{$opsel}:all_v8_final_justice:{$dateKey}";

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
            'forecast' => $this->getForecastComparison($opsel),
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
        $q = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
            ->where('kategori', '!=', 'ORANG');

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
            $q->where('opsel', $opsel);
        }

        return $q;
    }

    public function getNasionalMetrics(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getNasionalMetrics:{$dataType}:{$opsel}:nasional_v2:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $pergerakan = (float) $this->baseQuery('PERGERAKAN', $dataType, $opsel)->sum('total');
            $orang = (float) $this->baseQuery('ORANG', $dataType, $opsel)->sum('total');

            // Hitung koefisien & unique subscriber menggunakan koefisien per-opsel per-batch
            // (Konsisten dengan DataMpdController & DailyReportController)
            $selBatch = $this->getKoefisienArray();

            // Ambil pergerakan per-opsel
            $pergerakanPerOpsel = $this->baseQuery('PERGERAKAN', $dataType, $opsel)
                ->select('opsel', DB::raw('SUM(total) as t'))
                ->groupBy('opsel')
                ->pluck('t', 'opsel');

            $totalUnique     = 0;
            $totalPergerakan = 0;
            foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
                $perg   = (float) ($pergerakanPerOpsel[$op] ?? 0);
                $koef   = (float) ($selBatch[$op] ?? 1.0);
                $totalUnique += $koef > 0 ? round($perg / $koef) : 0;
                $totalPergerakan += $perg;
            }

            // Weighted average koefisien untuk tampilan KPI
            $koefisien = $totalUnique > 0 ? round($totalPergerakan / $totalUnique, 2) : 0.0;

            return [
                'pergerakan' => $pergerakan,
                'orang'      => $totalUnique, // unique subscriber per koefisien per-opsel
                'koefisien'  => $koefisien,
                'narrative'  => $this->generateNarrative(['pergerakan' => $pergerakan], 'nasional_pergerakan'),
            ];
        });
    }

    public function getOpselContribution(string $dataType, ?string $region = null): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getOpselContribution:{$dataType}:all:".($region ?? 'nasional').":v4_{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $region) {
            $data = [];
            $selBatch = $this->getKoefisienArray();
            
            $q = $this->baseQuery('PERGERAKAN', $dataType, null);
            if ($region) {
                $this->applyJaboFilter($q, $region);
            }
            $sums = $q->select('opsel', DB::raw('SUM(total) as t'))->groupBy('opsel')->get()->pluck('t', 'opsel');
            
            $totalPerg = $sums->sum();
            $totalOrang = 0;
            $orangSums = [];
            
            foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
                $valP = (float) ($sums[$op] ?? 0);
                $koef = (float) ($selBatch[$op] ?? 1.0);
                $valO = $koef > 0 ? round($valP / $koef) : 0;
                $orangSums[$op] = $valO;
                $totalOrang += $valO;
            }
            
            foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
                $valP = (float) ($sums[$op] ?? 0);
                $valO = $orangSums[$op];
                
                $data['pergerakan'][$op] = ['total' => $valP, 'pct' => $totalPerg > 0 ? round(($valP / $totalPerg) * 100, 1) : 0];
                $data['orang'][$op]      = ['total' => $valO, 'pct' => $totalOrang > 0 ? round(($valO / $totalOrang) * 100, 1) : 0];
            }
            
            $data['narrative'] = $this->generateNarrative($data, 'opsel');
            return $data;
        });
    }

    public function getDailyTrend(string $kategori, string $dataType, ?string $opsel, string $region = 'nasional'): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getDailyTrend:{$dataType}:{$opsel}:{$region}_{$kategori}:v4_{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($kategori, $dataType, $opsel, $region) {
            $q = $this->baseQuery('PERGERAKAN', $dataType, $opsel);
            if ($region === 'intra') {
                $this->applyJaboFilter($q, 'intra');
            } elseif ($region === 'inter') {
                $this->applyJaboFilter($q, 'inter');
            }

            $dbData = $q->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', DB::raw('SUM(total) as t'))
                ->groupBy(DB::raw('DATE(tanggal)'), 'opsel')
                ->get();

            $result = [];
            $selBatch = $this->getKoefisienArray();
            $period = new \DatePeriod(
                new \DateTime($this->getStartDate()),
                new \DateInterval('P1D'),
                (new \DateTime($this->getEndDate()))->modify('+1 day')
            );
            
            foreach ($period as $date) {
                $result[$date->format('Y-m-d')] = 0.0;
            }

            foreach ($dbData as $item) {
                $dateStr = $item->date_val;
                if (isset($result[$dateStr])) {
                    $op = $item->opsel;
                    $val = (float) $item->t;
                    
                    if ($kategori === 'ORANG') {
                        $koef = (float) ($selBatch[$op] ?? 1.0);
                        $val = $koef > 0 ? ($val / $koef) : 0;
                    }
                    $result[$dateStr] += $val;
                }
            }

            if ($kategori === 'ORANG') {
                foreach ($result as $d => $v) {
                    $result[$d] = round($v);
                }
            }

            return $result;
        });
    }

    public function getPeakDay(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getPeakDay:{$dataType}:{$opsel}:nasional:{$dateKey}";

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
        $key = "executive_summary:getIntraJabodetabek:{$dataType}:{$opsel}:intra_v3:{$dateKey}";

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
        $key = "executive_summary:getInterJabodetabek:{$dataType}:{$opsel}:inter_v3:{$dateKey}";

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
        $q = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
            ->where('kategori', '!=', 'ORANG');

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
            $q->where('opsel', $opsel);
        }
        $this->applyJaboFilter($q, $region);

        $pergPerOpsel = $q->select('opsel', DB::raw('SUM(total) as t'))
            ->groupBy('opsel')
            ->pluck('t', 'opsel');

        $selBatch = $this->getKoefisienArray();
        $totPerg = 0;
        $totOrang = 0;
        
        foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
            $p = (float) ($pergPerOpsel[$op] ?? 0);
            $k = (float) ($selBatch[$op] ?? 1.0);
            $totPerg += $p;
            $totOrang += $k > 0 ? round($p / $k) : 0;
        }

        return [
            'PERGERAKAN' => $totPerg,
            'ORANG'      => $totOrang,
        ];
    }

    public function getKoefisien(string $dataType, ?string $opsel, ?string $region = null): float
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getKoefisien:{$dataType}:{$opsel}:{$region}:{$dateKey}";

        return (float) Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel, $region) {
            $pQ = $this->baseQuery('PERGERAKAN', $dataType, $opsel);
            $oQ = $this->baseQuery('ORANG', $dataType, $opsel);
            if ($region) {
                $this->applyJaboFilter($pQ, $region);
                $this->applyJaboFilter($oQ, $region);
            }
            $o = (float) $oQ->sum('total');

            return $o > 0 ? round((float) $pQ->sum('total') / $o, 2) : 0.0;
        });
    }

    /**
     * Hitung weighted average koefisien per-opsel per-batch.
     * Konsisten dengan getNasionalMetrics & DataMpdController.
     */
    private function getKoefisienWeighted(string $dataType, ?string $opsel, ?string $region = null): float
    {
        $q = $this->baseQuery('PERGERAKAN', $dataType, $opsel);
        if ($region) {
            $this->applyJaboFilter($q, $region);
        }

        $pergerakanPerOpsel = $q->select('opsel', DB::raw('SUM(total) as t'))
            ->groupBy('opsel')
            ->pluck('t', 'opsel');

        $selBatch = $this->getKoefisienArray();
        $totalUnique = 0;
        $totalPergerakan = 0;

        foreach (['TSEL', 'IOH', 'XLSMART'] as $op) {
            $perg             = (float) ($pergerakanPerOpsel[$op] ?? 0);
            $koef             = (float) ($selBatch[$op] ?? 1.0);
            $totalUnique     += $koef > 0 ? round($perg / $koef) : 0;
            $totalPergerakan += $perg;
        }

        return $totalUnique > 0 ? round($totalPergerakan / $totalUnique, 2) : 0.0;
    }

    public function getForecastComparison(?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getForecastComparison:all:{$opsel}:nasional:v4_static:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($opsel) {
            $real = $this->getDailyTrend('PERGERAKAN', 'real', $opsel);
            
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
        $key = "executive_summary:getYoyComparison:{$dataType}:{$opsel}:nasional:v4_clean:{$dateKey}";

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
}
