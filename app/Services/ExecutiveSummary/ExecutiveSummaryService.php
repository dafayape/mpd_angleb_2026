<?php
declare(strict_types=1);

namespace App\Services\ExecutiveSummary;

use App\Models\SpatialMovement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExecutiveSummaryService
{
    public const PERIOD_START = '2026-03-13';
    public const PERIOD_END = '2026-03-30';
    public const JABODETABEK_PROVINCES = ['31', '32', '36'];

    public function getFullSummary(?string $opsel, string $dataType = 'real'): array
    {
        $key = "executive_summary:getFullSummary:{$dataType}:{$opsel}:all";
        return Cache::remember($key, 1800, fn() => [
            'nasional' => $this->getNasionalMetrics($dataType, $opsel),
            'peak' => $this->getPeakDay($dataType, $opsel),
            'opsel' => $this->getOpselContribution($dataType),
            'forecast' => $this->getForecastComparison(),
            'yoy' => $this->getYoyComparison(),
            'intra' => $this->getIntraJabodetabek($dataType, $opsel),
            'inter' => $this->getInterJabodetabek($dataType, $opsel),
            'trend_pergerakan' => $this->getDailyTrend('PERGERAKAN', $dataType, $opsel),
            'trend_orang' => $this->getDailyTrend('ORANG', $dataType, $opsel),
            'trend_intra' => $this->getDailyTrend('ORANG', $dataType, $opsel, 'intra'),
            'trend_inter' => $this->getDailyTrend('ORANG', $dataType, $opsel, 'inter'),
            'kstmp' => $this->generateNarrative([], 'kesimpulan'),
        ]);
    }

    private function baseQuery(string $kategori, string $dataType, ?string $opsel)
    {
        $q = SpatialMovement::whereBetween('tanggal', [self::PERIOD_START, self::PERIOD_END])
            ->where('kategori', $kategori)
            ->where('is_forecast', $dataType === 'forecast');
        if ($opsel) $q->where('opsel', $opsel);
        return $q;
    }

    public function getNasionalMetrics(string $dataType, ?string $opsel): array
    {
        $key = "executive_summary:getNasionalMetrics:{$dataType}:{$opsel}:nasional";
        return Cache::remember($key, 1800, function() use ($dataType, $opsel) {
            $pergerakan = (float) $this->baseQuery('PERGERAKAN', $dataType, $opsel)->sum('total');
            $orang = (float) $this->baseQuery('ORANG', $dataType, $opsel)->sum('total');
            $koefisien = $orang > 0 ? round($pergerakan / $orang, 2) : 0.0;
            return [
                'pergerakan' => $pergerakan,
                'orang' => $orang,
                'koefisien' => $koefisien,
                'narrative' => $this->generateNarrative(['pergerakan' => $pergerakan], 'nasional_pergerakan')
            ];
        });
    }

    public function getOpselContribution(string $dataType): array
    {
        $key = "executive_summary:getOpselContribution:{$dataType}:all:nasional";
        return Cache::remember($key, 1800, function() use ($dataType) {
            $data = [];
            foreach (['PERGERAKAN', 'ORANG'] as $kat) {
                $sums = $this->baseQuery($kat, $dataType, null)
                    ->select('opsel', DB::raw('SUM(total) as t'))->groupBy('opsel')->get()->pluck('t', 'opsel');
                $total = $sums->sum();
                foreach (['TSEL', 'IOH', 'XL'] as $op) {
                    $val = $sums[$op] ?? 0;
                    $data[strtolower($kat)][$op] = ['total' => $val, 'pct' => $total > 0 ? round(($val/$total)*100, 1) : 0];
                }
            }
            $data['narrative'] = $this->generateNarrative($data, 'opsel');
            return $data;
        });
    }

    public function getDailyTrend(string $kategori, string $dataType, ?string $opsel, string $region = 'nasional'): array
    {
        $key = "executive_summary:getDailyTrend:{$dataType}:{$opsel}:{$region}_{$kategori}";
        return Cache::remember($key, 1800, function() use ($kategori, $dataType, $opsel, $region) {
            $q = $this->baseQuery($kategori, $dataType, $opsel);
            if ($region === 'intra') $this->applyJaboFilter($q, 'intra');
            elseif ($region === 'inter') $this->applyJaboFilter($q, 'inter');
            
            $dbData = $q->select('tanggal', DB::raw('SUM(total) as t'))
                ->groupBy('tanggal')->orderBy('tanggal')
                ->get()->pluck('t', 'tanggal')->toArray();
                
            $result = [];
            $period = new \DatePeriod(
                new \DateTime(self::PERIOD_START),
                new \DateInterval('P1D'),
                (new \DateTime(self::PERIOD_END))->modify('+1 day')
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
        $key = "executive_summary:getPeakDay:{$dataType}:{$opsel}:nasional";
        return Cache::remember($key, 1800, function() use ($dataType, $opsel) {
            $trend = $this->getDailyTrend('PERGERAKAN', $dataType, $opsel);
            if(empty($trend)) return [];
            arsort($trend);
            $total = array_sum($trend);
            $peaks = [];
            foreach (array_slice($trend, 0, 3, true) as $tgl => $val) {
                $peaks[] = ['tanggal' => $tgl, 'total' => $val, 'pct' => $total > 0 ? round(($val/$total)*100, 1) : 0];
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
        $key = "executive_summary:getIntraJabodetabek:{$dataType}:{$opsel}:intra";
        return Cache::remember($key, 1800, function() use ($dataType, $opsel) {
            $p = (float) $this->baseQuery('PERGERAKAN', $dataType, $opsel)->tap(fn($q) => $this->applyJaboFilter($q, 'intra'))->sum('total');
            $o = (float) $this->baseQuery('ORANG', $dataType, $opsel)->tap(fn($q) => $this->applyJaboFilter($q, 'intra'))->sum('total');
            return [
                'pergerakan' => $p, 'orang' => $o, 'koefisien' => $this->getKoefisien($dataType, $opsel, 'intra'),
                'narrative' => $this->generateNarrative(['pergerakan' => $p], 'intra')
            ];
        });
    }

    public function getInterJabodetabek(string $dataType, ?string $opsel): array
    {
        $key = "executive_summary:getInterJabodetabek:{$dataType}:{$opsel}:inter";
        return Cache::remember($key, 1800, function() use ($dataType, $opsel) {
            $p = (float) $this->baseQuery('PERGERAKAN', $dataType, $opsel)->tap(fn($q) => $this->applyJaboFilter($q, 'inter'))->sum('total');
            $o = (float) $this->baseQuery('ORANG', $dataType, $opsel)->tap(fn($q) => $this->applyJaboFilter($q, 'inter'))->sum('total');
            return [
                'pergerakan' => $p, 'orang' => $o, 'koefisien' => $this->getKoefisien($dataType, $opsel, 'inter'),
                'narrative' => $this->generateNarrative(['pergerakan' => $p], 'inter')
            ];
        });
    }

    public function getKoefisien(string $dataType, ?string $opsel, ?string $region = null): float
    {
        $key = "executive_summary:getKoefisien:{$dataType}:{$opsel}:{$region}";
        return Cache::remember($key, 1800, function() use ($dataType, $opsel, $region) {
            $pQ = $this->baseQuery('PERGERAKAN', $dataType, $opsel);
            $oQ = $this->baseQuery('ORANG', $dataType, $opsel);
            if ($region) { $this->applyJaboFilter($pQ, $region); $this->applyJaboFilter($oQ, $region); }
            $o = (float) $oQ->sum('total');
            return $o > 0 ? round((float) $pQ->sum('total') / $o, 2) : 0.0;
        });
    }

    public function getForecastComparison(): array
    {
        $key = "executive_summary:getForecastComparison:all:all:nasional";
        return Cache::remember($key, 1800, function() {
            $real = $this->getDailyTrend('PERGERAKAN', 'real', null);
            $forecast = $this->getDailyTrend('PERGERAKAN', 'forecast', null);
            $totReal = array_sum($real); $totFore = array_sum($forecast);
            $res = [];
            foreach (array_keys($real + $forecast) as $dt) {
                $r = $real[$dt] ?? 0; $f = $forecast[$dt] ?? 0;
                $res[$dt] = [
                    'real_pct' => $totReal > 0 ? round(($r/$totReal)*100, 1) : 0,
                    'fore_pct' => $totFore > 0 ? round(($f/$totFore)*100, 1) : 0
                ];
            }
            ksort($res);
            return $res;
        });
    }

    public function getYoyComparison(): array
    {
        $key = "executive_summary:getYoyComparison:real:all:nasional";
        return Cache::remember($key, 1800, function() {
            $curr = (float) $this->baseQuery('ORANG', 'real', null)->sum('total');
            $prev = config('mpd.historical_baselines.2025_orang', 115197227); // default fallback
            return [
                'current' => $curr, 'previous' => $prev,
                'growth_pct' => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 2) : 0,
                'narrative' => "Angka tersebut lebih besar sekitar ".($prev>0?round((($curr-$prev)/$prev)*100,2):0)."% dari estimasi masyarakat tahun sebelumnya."
            ];
        });
    }

    public function generateNarrative(array $metrics, string $type): string
    {
        if ($type === 'opsel') {
            $tsel = $metrics['pergerakan']['TSEL']['pct'] ?? 0;
            return "Telkomsel mendominasi perekaman mobilitas dengan menyumbang sekitar {$tsel}% dari total pergerakan.";
        }
        $val = number_format($metrics['pergerakan'] ?? 0, 0, ',', '.');
        if ($type === 'intra') return "Jumlah pergerakan Masyarakat Intra Jabodetabek pada periode ini adalah {$val} pergerakan.";
        if ($type === 'inter') return "Sedangkan untuk jumlah pergerakan Masyarakat Inter Jabodetabek sebesar {$val} pergerakan.";
        if ($type === 'nasional_pergerakan') return "Jumlah pergerakan masyarakat pada Periode Angkutan Lebaran 2026, dengan nilai realisasi adalah {$val} pergerakan.";
        return "Distribusi pergerakan penduduk relatif stabil selama periode pengamatan.";
    }
}
