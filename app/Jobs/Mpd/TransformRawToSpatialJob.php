<?php

namespace App\Jobs\Mpd;

use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ETL Job: Transforms raw_mpd_data → spatial_movements
 *
 * Proses:
 * 1. Ambil data dari raw_mpd_data berdasarkan import_job_id
 * 2. Aggregasi per (tanggal, opsel, kategori, origin_kab, dest_kab, origin_simpul, dest_simpul, kode_moda)
 * 3. Enrich dengan PostGIS coordinates dari ref_transport_nodes
 * 4. Hitung distance_meters menggunakan ST_Distance
 * 5. Upsert ke spatial_movements
 * 6. Invalidate cache
 */
class TransformRawToSpatialJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $importJobId;

    public int $timeout = 3600; // 1 hour max

    public int $tries = 3;

    public function __construct(int $importJobId)
    {
        $this->importJobId = $importJobId;
    }

    public function handle(): void
    {
        $this->updateEtlStatus('processing', 0, "Starting ETL transform for import_job_id={$this->importJobId}");

        try {
            $this->calculateIntegrityMetrics('start');
            $this->transformData();
            $this->calculateIntegrityMetrics('end');
            $this->refreshMaterializedViews();
            $this->invalidateCache();

            $this->updateEtlStatus('completed', 100, "Completed ETL transform for import_job_id={$this->importJobId}");
        } catch (\Throwable $e) {
            $this->updateEtlStatus('failed', 0, "Failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }

    private function updateEtlStatus(string $status, int $progress, string $message, string $level = 'info'): void
    {
        // 1. Dedicated Logging (Safely handle channel failures)
        try {
            Log::channel('etl')->log($level, "[Job ID: {$this->importJobId}] {$message}");
        } catch (\Throwable $logErr) {
            // Fallback to default log if 'etl' channel is misconfigured
            Log::log($level, "[ETL FALLBACK][Job ID: {$this->importJobId}] {$message}");
        }
        
        // 2. Database Metadata Update
        try {
            $job = ImportJob::find($this->importJobId);
            if ($job) {
                // Ensure metadata is retrieved as array (Laravel cast should handle this)
                $meta = $job->metadata ?? [];
                $meta['etl_status'] = $status;
                $meta['etl_progress'] = $progress;
                
                $logs = $meta['etl_logs'] ?? [];
                $logs[] = [
                    'time' => now()->toDateTimeString(),
                    'level' => strtoupper($level),
                    'message' => $message,
                ];
                
                // Keep last 100 log entries
                if (count($logs) > 100) {
                    array_shift($logs);
                }
                $meta['etl_logs'] = $logs;
                
                $job->metadata = $meta;
                $job->save();
            }
        } catch (\Throwable $e) {
            Log::error("Failed to update ETL database metadata for job {$this->importJobId}: " . $e->getMessage());
        }
    }

    private function calculateIntegrityMetrics(string $phase): void
    {
        try {
            $job = ImportJob::find($this->importJobId);
            if (!$job) return;

            $meta = $job->metadata ?? [];
            $stats = $meta['etl_stats'] ?? [
                'raw_volume' => 0,
                'mapped_volume' => 0,
                'unmapped_volume' => 0,
                'success_rate' => 0,
                'missing_nodes' => []
            ];

            if ($phase === 'start') {
                $stats['raw_volume'] = (float) DB::table('raw_mpd_data')
                    ->where('import_job_id', $this->importJobId)
                    ->sum('total');
                
                $this->updateEtlStatus('processing', 0, "Calculated raw volume: " . number_format($stats['raw_volume'], 0));
            } else {
                // Volume check: records that couldn't be mapped because nodes are missing from ref_transport_nodes
                $unmappedVolume = (float) DB::table('raw_mpd_data as r')
                    ->leftJoin('ref_transport_nodes as n1', 'r.kode_origin_simpul', '=', 'n1.code')
                    ->leftJoin('ref_transport_nodes as n2', 'r.kode_dest_simpul', '=', 'n2.code')
                    ->where('r.import_job_id', $this->importJobId)
                    ->where(function($q) {
                        $q->whereNull('n1.code')->orWhereNull('n2.code');
                    })
                    ->sum('r.total');

                $stats['unmapped_volume'] = $unmappedVolume;
                $stats['mapped_volume'] = $stats['raw_volume'] - $unmappedVolume;
                $stats['success_rate'] = $stats['raw_volume'] > 0 
                    ? round(($stats['mapped_volume'] / $stats['raw_volume']) * 100, 2) 
                    : 100;

                // Identification of top missing nodes causing unmapped data
                $missingOrigins = DB::table('raw_mpd_data as r')
                    ->leftJoin('ref_transport_nodes as n', 'r.kode_origin_simpul', '=', 'n.code')
                    ->where('r.import_job_id', $this->importJobId)
                    ->whereNull('n.code')
                    ->select('r.kode_origin_simpul as code', DB::raw('SUM(total) as t'))
                    ->groupBy('r.kode_origin_simpul')
                    ->orderByDesc('t')
                    ->take(5)
                    ->get();

                $stats['missing_nodes'] = $missingOrigins->map(fn($m) => ['code' => $m->code, 'vol' => $m->t])->toArray();

                $msg = "Integrity Check: " . $stats['success_rate'] . "% data successfully mapped. " .
                       number_format($unmappedVolume, 0) . " volume lost due to unmapped nodes.";
                $this->updateEtlStatus('processing', 82, $msg, $stats['success_rate'] < 90 ? 'warning' : 'info');
            }

            $meta['etl_stats'] = $stats;
            $job->metadata = $meta;
            $job->save();
        } catch (\Throwable $e) {
            Log::error("Failed to calculate ETL integrity metrics: " . $e->getMessage());
        }
    }

    /**
     * Main ETL: aggregate raw_mpd_data and upsert into spatial_movements
     * with PostGIS enrichment (origin/dest location + distance)
     */
    private function transformData(): void
    {
        // Process in date-based batches to avoid memory issues
        $dates = DB::table('raw_mpd_data')
            ->where('import_job_id', $this->importJobId)
            ->select('tanggal')
            ->distinct()
            ->pluck('tanggal');

        $totalDates = $dates->count();
        if ($totalDates === 0) {
            $this->updateEtlStatus('processing', 10, "No raw data found to process.");
            return;
        }

        $processed = 0;
        foreach ($dates as $date) {
            $this->processDateBatch($date);
            $processed++;
            // Calculate progress up to 80% for this phase
            $progress = (int) (($processed / $totalDates) * 80);
            $this->updateEtlStatus('processing', $progress, "Processed date batch: {$date} ({$processed}/{$totalDates})");
        }
    }

    private function processDateBatch(string $date): void
    {
        // Single SQL statement: aggregate + enrich + upsert
        // This is the most efficient approach — pure SQL, no PHP loop
        DB::statement('
            INSERT INTO spatial_movements (
                tanggal, opsel, kategori,
                kode_origin_kabupaten_kota, kode_dest_kabupaten_kota,
                kode_origin_simpul, kode_dest_simpul,
                kode_moda, total, is_forecast,
                origin_location, dest_location, distance_meters,
                created_at, updated_at
            )
            SELECT
                r.tanggal,
                r.opsel,
                r.kategori,
                r.kode_origin_kabupaten_kota,
                r.kode_dest_kabupaten_kota,
                r.kode_origin_simpul,
                r.kode_dest_simpul,
                r.kode_moda,
                SUM(r.total) as total,
                r.is_forecast,
                -- PostGIS enrichment: lookup coordinates from ref_transport_nodes
                n_origin.location as origin_location,
                n_dest.location as dest_location,
                -- Calculate distance in meters using ST_Distance (geography)
                CASE
                    WHEN n_origin.location IS NOT NULL AND n_dest.location IS NOT NULL
                    THEN ST_Distance(n_origin.location, n_dest.location)
                    ELSE NULL
                END as distance_meters,
                NOW() as created_at,
                NOW() as updated_at
            FROM raw_mpd_data r
            LEFT JOIN ref_transport_nodes n_origin ON r.kode_origin_simpul = n_origin.code
            LEFT JOIN ref_transport_nodes n_dest ON r.kode_dest_simpul = n_dest.code
            WHERE r.import_job_id = ?
              AND r.tanggal = ?
            GROUP BY
                r.tanggal, r.opsel, r.kategori,
                r.kode_origin_kabupaten_kota, r.kode_dest_kabupaten_kota,
                r.kode_origin_simpul, r.kode_dest_simpul,
                r.kode_moda, r.is_forecast,
                n_origin.location, n_dest.location
            ON CONFLICT (tanggal, opsel, kategori, kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, kode_origin_simpul, kode_dest_simpul, kode_moda, is_forecast)
            DO UPDATE SET
                total = EXCLUDED.total,
                origin_location = EXCLUDED.origin_location,
                dest_location = EXCLUDED.dest_location,
                distance_meters = EXCLUDED.distance_meters,
                updated_at = NOW()
        ', [$this->importJobId, $date]);
    }

    /**
     * Refresh pre-computed materialized views (P3.4)
     */
    private function refreshMaterializedViews(): void
    {
        $this->updateEtlStatus('processing', 85, "Refreshing materialized views...");
        try {
            DB::statement("REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_summary;");
            $this->updateEtlStatus('processing', 90, "Refreshed mv_daily_summary...");
            DB::statement("REFRESH MATERIALIZED VIEW CONCURRENTLY mv_jabodetabek_daily;");
            $this->updateEtlStatus('processing', 95, "Refreshed mv_jabodetabek_daily...");
        } catch (\Throwable $e) {
            $this->updateEtlStatus('processing', 95, "Materialized view refresh skipped/failed: " . $e->getMessage(), 'warning');
        }
    }

    /**
     * Clear all dashboard/chart caches after new data is loaded
     */
    private function invalidateCache(): void
    {
        $this->updateEtlStatus('processing', 98, "Flushing application cache...");
        try {
            Cache::flush();
            $this->updateEtlStatus('processing', 99, "Cache flushed successfully.");
        } catch (\Throwable $e) {
            $this->updateEtlStatus('processing', 99, "Cache flush failed: " . $e->getMessage(), 'warning');
        }
    }
}
