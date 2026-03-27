<?php

namespace App\Jobs\Mpd;

use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class TransformRawToSpatialJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200; // 2 Jam
    private $importJobId;

    /**
     * Unique ID based on importJobId to prevent duplicate processing
     */
    public function uniqueId(): string
    {
        return (string) $this->importJobId;
    }

    public function __construct(int $importJobId)
    {
        $this->importJobId = $importJobId;
    }

    public function handle(): void
    {
        $this->updateEtlStatus('processing', 0, "Starting ETL transform for import_job_id={$this->importJobId}");

        try {
            // STEP 1: Calculate initial volume
            $this->calculateIntegrityMetrics('start');

            // STEP 2: Main transformation
            $this->transformData();

            // STEP 3: Final metrics and status update
            $this->calculateIntegrityMetrics('end');
            
            // Refresh views (wrapped in try-catch to not fail the whole job)
            $this->refreshMaterializedViews();

            // Clear specific caches
            $this->invalidateCache();

            $this->updateEtlStatus('completed', 100, "ETL Transformation successful");

        } catch (\Throwable $e) {
            Log::error("ETL Transformation Failed (Job: {$this->importJobId}): " . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->updateEtlStatus('failed', 0, "Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function transformData(): void
    {
        $dates = DB::table('raw_mpd_data')
            ->where('import_job_id', $this->importJobId)
            ->distinct()
            ->pluck('tanggal')
            ->toArray();

        $totalDates = count($dates);
        if ($totalDates === 0) {
            Log::warning("[ETL Job: {$this->importJobId}] No raw data found for transformation");
            return;
        }

        $processed = 0;
        foreach ($dates as $date) {
            $this->processDateBatch($date);
            $processed++;
            $progress = (int) (($processed / $totalDates) * 85);
            $this->updateEtlStatus('processing', $progress, "Processed date batch: {$date} ({$processed}/{$totalDates})");
        }
    }

    private function processDateBatch(string $date): void
    {
        // 1. Get unique combinations from raw data
        $combinations = DB::table('raw_mpd_data')
            ->where('import_job_id', $this->importJobId)
            ->where('tanggal', $date)
            ->select('opsel', 'kategori', 'is_forecast')
            ->distinct()
            ->get();

        if ($combinations->isEmpty()) return;

        foreach ($combinations as $combo) {
            // Use Transaction to prevent data loss if insert fails
            DB::transaction(function () use ($date, $combo) {
                // INSERT from aggregated raw data with UPSERT SUM to prevent wiping out other jobs
                $sql = "
                    INSERT INTO spatial_movements (
                        tanggal, opsel, kategori,
                        kode_origin_kabupaten_kota, kode_dest_kabupaten_kota,
                        kode_origin_simpul, kode_dest_simpul,
                        kode_moda, total, is_forecast,
                        origin_location, dest_location, distance_meters,
                        created_at, updated_at
                    )
                    SELECT
                        r.tanggal, r.opsel, r.kategori,
                        r.kode_origin_kabupaten_kota, r.kode_dest_kabupaten_kota,
                        r.kode_origin_simpul, r.kode_dest_simpul,
                        r.kode_moda, SUM(r.total), r.is_forecast,
                        n1.location, n2.location,
                        CASE WHEN n1.location IS NOT NULL AND n2.location IS NOT NULL
                             THEN ST_Distance(n1.location, n2.location)
                             ELSE NULL END,
                        NOW(), NOW()
                    FROM raw_mpd_data r
                    LEFT JOIN ref_transport_nodes n1 ON r.kode_origin_simpul = n1.code
                    LEFT JOIN ref_transport_nodes n2 ON r.kode_dest_simpul = n2.code
                    WHERE r.import_job_id = ? 
                      AND r.tanggal = ? 
                      AND r.opsel = ? 
                      AND r.kategori = ? 
                      AND r.is_forecast = ?
                    GROUP BY r.tanggal, r.opsel, r.kategori,
                             r.kode_origin_kabupaten_kota, r.kode_dest_kabupaten_kota,
                             r.kode_origin_simpul, r.kode_dest_simpul, r.kode_moda, r.is_forecast,
                             n1.location, n2.location
                    ON CONFLICT (tanggal, opsel, kategori, kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, kode_origin_simpul, kode_dest_simpul, kode_moda, is_forecast)
                    DO UPDATE SET
                        total = spatial_movements.total + EXCLUDED.total,
                        updated_at = NOW()
                ";

                DB::statement($sql, [
                    $this->importJobId, 
                    $date, 
                    $combo->opsel, 
                    $combo->kategori, 
                    $combo->is_forecast
                ]);
            });
        }
    }

    private function calculateIntegrityMetrics(string $phase): void
    {
        $targetJob = ImportJob::find($this->importJobId);
        if (!$targetJob) return;

        $meta = $targetJob->metadata ?? [];
        
        if ($phase === 'start') {
            $totalRaw = DB::table('raw_mpd_data')
                ->where('import_job_id', $this->importJobId)
                ->sum('total');
            
            $meta['etl_stats'] = [
                'raw_volume' => (int) $totalRaw,
                'started_at' => now()->toDateTimeString()
            ];
            
            $targetJob->metadata = $meta;
            $targetJob->save();
        } else {
            $dates = DB::table('raw_mpd_data')
                ->where('import_job_id', $this->importJobId)
                ->distinct()
                ->pluck('tanggal')
                ->toArray();

            $totalMapped = DB::table('spatial_movements')
                ->whereIn('tanggal', $dates)
                ->sum('total');

            $stats = $meta['etl_stats'] ?? [];
            $stats['mapped_volume'] = (int) $totalMapped;
            $stats['success_rate'] = ($stats['raw_volume'] > 0) 
                ? round(($totalMapped / $stats['raw_volume']) * 100, 2) 
                : 100; // If raw is 0, we can say it's 100% processed
            $stats['ended_at'] = now()->toDateTimeString();

            try {
                // Update specific fields without overwriting metadata object repeatedly
                DB::table('import_jobs')->where('id', $this->importJobId)->update([
                    'data_lost' => (int) max(0, ($stats['raw_volume'] - $totalMapped)),
                    'metadata' => json_encode(array_merge($meta, ['etl_stats' => $stats]))
                ]);
            } catch (\Throwable $e) {
                Log::error("Integrity update error: " . $e->getMessage());
            }
        }
    }

    private function updateEtlStatus(string $status, int $progress, string $message, string $level = 'info'): void
    {
        DB::table('import_jobs')->where('id', $this->importJobId)->update([
            'status_etl' => $status,
            'etl_progress' => $progress
        ]);

        $logPrefix = "[ETL import_job   _id: {$this->importJobId}] ";
        Log::log($level, $logPrefix . $message);
    }

    private function refreshMaterializedViews(): void
    {
        try {
            DB::statement("REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_summary;");
            DB::statement("REFRESH MATERIALIZED VIEW CONCURRENTLY mv_jabodetabek_daily;");
        } catch (\Throwable $e) {
            Log::warning("View refresh failed/skipped: " . $e->getMessage());
        }
    }

    private function invalidateCache(): void
    {
        try {
            // Specific key clearing
            $keys = ['mpd_national_stats', 'mpd_jabodetabek_stats', 'mpd_summary_total'];
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            
            // Clean specific tags if supported
            if (config('cache.default') !== 'file' && config('cache.default') !== 'database') {
                Cache::tags(['mpd', 'dashboard'])->flush();
            }
        } catch (\Throwable $e) {
            Log::warning("Cache invalidation failed: " . $e->getMessage());
        }
    }
}
