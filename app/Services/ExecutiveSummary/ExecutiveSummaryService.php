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
        return config('mpd.end_date', '2026-03-30');
    }

    private function cacheTtl(): int
    {
        return (int) config('mpd.cache_ttl.dashboard', 10800);
    }

    public function getFullSummary(?string $opsel, string $dataType = 'real'): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getFullSummary:{$dataType}:{$opsel}:all_v5:{$dateKey}";

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

    private function baseQuery(string $kategori, string $dataType, ?string $opsel)
    {
        $q = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
            ->where('kategori', $kategori)
            ->where('is_forecast', $dataType === 'forecast');
        if ($opsel) {
            $q->where('opsel', $opsel);
        }

        return $q;
    }

    public function getNasionalMetrics(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getNasionalMetrics:{$dataType}:{$opsel}:nasional:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $pergerakan = (float) $this->baseQuery('PERGERAKAN', $dataType, $opsel)->sum('total');
            $orang = (float) $this->baseQuery('ORANG', $dataType, $opsel)->sum('total');
            $koefisien = $orang > 0 ? round($pergerakan / $orang, 2) : 0.0;

            return [
                'pergerakan' => $pergerakan,
                'orang' => $orang,
                'koefisien' => $koefisien,
                'narrative' => $this->generateNarrative(['pergerakan' => $pergerakan], 'nasional_pergerakan'),
            ];
        });
    }

    public function getOpselContribution(string $dataType, ?string $region = null): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getOpselContribution:{$dataType}:all:".($region ?? 'nasional').":{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $region) {
            $data = [];
            foreach (['PERGERAKAN', 'ORANG'] as $kat) {
                $q = $this->baseQuery($kat, $dataType, null);
                if ($region) {
                    $this->applyJaboFilter($q, $region);
                }
                $sums = $q->select('opsel', DB::raw('SUM(total) as t'))->groupBy('opsel')->get()->pluck('t', 'opsel');
                $total = $sums->sum();
                foreach (['TSEL', 'IOH', 'XL'] as $op) {
                    $val = $sums[$op] ?? 0;
                    $data[strtolower($kat)][$op] = ['total' => $val, 'pct' => $total > 0 ? round(($val / $total) * 100, 1) : 0];
                }
            }
            $data['narrative'] = $this->generateNarrative($data, 'opsel');

            return $data;
        });
    }

    public function getDailyTrend(string $kategori, string $dataType, ?string $opsel, string $region = 'nasional'): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getDailyTrend:{$dataType}:{$opsel}:{$region}_{$kategori}:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($kategori, $dataType, $opsel, $region) {
            $q = $this->baseQuery($kategori, $dataType, $opsel);
            if ($region === 'intra') {
                $this->applyJaboFilter($q, 'intra');
            } elseif ($region === 'inter') {
                $this->applyJaboFilter($q, 'inter');
            }

            $dbData = $q->select('tanggal', DB::raw('SUM(total) as t'))
                ->groupBy('tanggal')->orderBy('tanggal')
                ->get()
                ->mapWithKeys(fn ($item) => [\Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') => $item->t])
                ->toArray();

            $result = [];
            $period = new \DatePeriod(
                new \DateTime($this->getStartDate()),
                new \DateInterval('P1D'),
                (new \DateTime($this->getEndDate()))->modify('+1 day')
            );
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $result[$dateStr] = isset($dbData[$dateStr]) ? (float) $dbData[$dateStr] : 0.0;
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
        $key = "executive_summary:getIntraJabodetabek:{$dataType}:{$opsel}:intra_v2:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $sums = $this->batchJaboSums($dataType, $opsel, 'intra');

            return [
                'pergerakan' => $sums['PERGERAKAN'],
                'orang' => $sums['ORANG'],
                'koefisien' => $sums['ORANG'] > 0 ? round($sums['PERGERAKAN'] / $sums['ORANG'], 2) : 0.0,
                'narrative' => $this->generateNarrative(['pergerakan' => $sums['PERGERAKAN']], 'intra'),
            ];
        });
    }

    public function getInterJabodetabek(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getInterJabodetabek:{$dataType}:{$opsel}:inter_v2:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $sums = $this->batchJaboSums($dataType, $opsel, 'inter');

            return [
                'pergerakan' => $sums['PERGERAKAN'],
                'orang' => $sums['ORANG'],
                'koefisien' => $sums['ORANG'] > 0 ? round($sums['PERGERAKAN'] / $sums['ORANG'], 2) : 0.0,
                'narrative' => $this->generateNarrative(['pergerakan' => $sums['PERGERAKAN']], 'inter'),
            ];
        });
    }

    /**
     * Batch query: get PERGERAKAN + ORANG sums for intra/inter Jabodetabek in a single query
     */
    private function batchJaboSums(string $dataType, ?string $opsel, string $region): array
    {
        $q = SpatialMovement::whereBetween('tanggal', [$this->getStartDate(), $this->getEndDate()])
            ->whereIn('kategori', ['PERGERAKAN', 'ORANG'])
            ->where('is_forecast', $dataType === 'forecast');
        if ($opsel) {
            $q->where('opsel', $opsel);
        }
        $this->applyJaboFilter($q, $region);

        $rows = $q->select('kategori', DB::raw('SUM(total) as t'))
            ->groupBy('kategori')
            ->pluck('t', 'kategori');

        return [
            'PERGERAKAN' => (float) ($rows['PERGERAKAN'] ?? 0),
            'ORANG' => (float) ($rows['ORANG'] ?? 0),
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

    public function getForecastComparison(?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getForecastComparison:all:{$opsel}:nasional:v4_static:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($opsel) {
            $real = $this->getDailyTrend('PERGERAKAN', 'real', $opsel);
            
            // Menggunakan ketetapan konstanta proporsi hasil survei kemenhub (prakiraan)
            $forecastConstants = [
                '2026-03-13' => 3.32,
                '2026-03-14' => 8.34,
                '2026-03-15' => 4.12,
                '2026-03-16' => 16.12,
                '2026-03-17' => 9.34,
                '2026-03-18' => 16.73,
                '2026-03-19' => 7.15,
                '2026-03-20' => 10.34,
                '2026-03-21' => 6.10,
                '2026-03-22' => 6.22,
                '2026-03-23' => 3.91,
                '2026-03-24' => 2.65,
                '2026-03-25' => 2.22,
                '2026-03-26' => 0.84,
                '2026-03-27' => 0.79,
                '2026-03-28' => 0.69,
                '2026-03-29' => 0.79,
                '2026-03-30' => 0.32,
            ];

            $totReal = array_sum($real);
            $res = [];
            foreach (array_keys($real) as $dt) {
                $r = $real[$dt] ?? 0;
                $res[$dt] = [
                    'real_pct' => $totReal > 0 ? round(($r / $totReal) * 100, 1) : 0,
                    'fore_pct' => $forecastConstants[$dt] ?? 0,
                ];
            }
            ksort($res);

            return $res;
        });
    }

    public function getYoyComparison(string $dataType, ?string $opsel): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getYoyComparison:{$dataType}:{$opsel}:nasional:v3:{$dateKey}";

        return Cache::remember($key, $this->cacheTtl(), function () use ($dataType, $opsel) {
            $curr = (float) $this->baseQuery('ORANG', $dataType, $opsel)->sum('total');
            $prev = config('mpd.historical_baselines.2025_orang', 115197227); // default fallback

            return [
                'current' => $curr, 'previous' => $prev,
                'growth_pct' => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 2) : 0,
                'narrative' => 'Angka tersebut lebih besar sekitar '.($prev > 0 ? round((($curr - $prev) / $prev) * 100, 2) : 0).'% dari estimasi masyarakat tahun sebelumnya.',
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
