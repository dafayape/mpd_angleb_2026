<?php

namespace App\Jobs\Mpd;

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
        Log::info("[ETL] Starting transform for import_job_id={$this->importJobId}");

        try {
            $this->transformData();
            $this->refreshMaterializedViews();
            $this->invalidateCache();

            Log::info("[ETL] Completed transform for import_job_id={$this->importJobId}");
        } catch (\Throwable $e) {
            Log::error("[ETL] Failed for import_job_id={$this->importJobId}: ".$e->getMessage());
            throw $e;
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

        foreach ($dates as $date) {
            $this->processDateBatch($date);
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
        try {
            DB::statement("REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_summary;");
            DB::statement("REFRESH MATERIALIZED VIEW CONCURRENTLY mv_jabodetabek_daily;");
            Log::info("[ETL] Materialized views refreshed.");
        } catch (\Throwable $e) {
            // Views might not exist yet (migration not run) — non-fatal
            Log::warning("[ETL] Materialized view refresh skipped: " . $e->getMessage());
        }
    }

    /**
     * Clear all dashboard/chart caches after new data is loaded
     */
    private function invalidateCache(): void
    {
        $patterns = [
            'dashboard:*',
            'keynote:*',
            'executive:*',
            'dailyreport:*',
        ];

        try {
            $prefix = config('cache.prefix', 'mpd_angleb_');
            $redis = \Illuminate\Support\Facades\Redis::connection();
            foreach ($patterns as $pattern) {
                $keys = $redis->keys($prefix.$pattern);
                if (! empty($keys)) {
                    foreach ($keys as $key) {
                        $redis->del($key);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback: flush entire cache if Redis pattern delete fails
            Log::warning('[ETL] Cache invalidation fallback: '.$e->getMessage());
            Cache::flush();
        }
    }
}
