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
 *
 * ShouldBeUnique: Mencegah duplikasi ETL job untuk import_job_id yang sama.
 * Jika user upload cepat 2x atau network retry, hanya 1 ETL yang jalan.
 */
class TransformRawToSpatialJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $importJobId;

    public int $timeout = 3600; // 1 hour max

    public int $tries = 3;

    /**
     * Backoff: jeda antar retry agar tidak langsung menghantam server.
     * Retry 1: tunggu 30 detik, Retry 2: tunggu 60 detik, Retry 3: tunggu 120 detik.
     */
    public array $backoff = [30, 60, 120];


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
            
            // Refresh views wrapped so it does not fail the whole job
            try {
                $this->refreshMaterializedViews();
            } catch (\Throwable $ve) {
                Log::warning("ETL View Refresh skipped for job {$this->importJobId}: " . $ve->getMessage());
            }

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
        
        // 2. Database Update (dedicated columns + metadata JSON)
        try {
            $job = ImportJob::find($this->importJobId);
            if ($job) {
                // Update dedicated columns for easy polling
                $job->status_etl = $status;
                $job->etl_progress = $progress;

                // Update metadata JSON for detailed logs
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
                // Volume REAL saja: Data Forecast sengaja tidak punya simpul, jadi tidak boleh
                // dihitung sebagai "unmapped" karena akan menghasilkan warning palsu.
                $realVolume = (float) DB::table('raw_mpd_data')
                    ->where('import_job_id', $this->importJobId)
                    ->where('is_forecast', false)
                    ->sum('total');

                $forecastVolume = (float) DB::table('raw_mpd_data')
                    ->where('import_job_id', $this->importJobId)
                    ->where('is_forecast', true)
                    ->sum('total');

                // Hanya hitung unmapped untuk data REAL yang punya simpul non-kosong
                $unmappedVolume = (float) DB::table('raw_mpd_data as r')
                    ->leftJoin('ref_transport_nodes as n1', 'r.kode_origin_simpul', '=', 'n1.code')
                    ->leftJoin('ref_transport_nodes as n2', 'r.kode_dest_simpul', '=', 'n2.code')
                    ->where('r.import_job_id', $this->importJobId)
                    ->where('r.is_forecast', false) // Hanya data REAL
                    ->where(function($q) {
                        $q->where(function($q2) {
                            // Origin simpul diisi tapi tidak ditemukan di referensi
                            $q2->where('r.kode_origin_simpul', '!=', '')
                               ->whereNull('n1.code');
                        })->orWhere(function($q2) {
                            // Dest simpul diisi tapi tidak ditemukan di referensi
                            $q2->where('r.kode_dest_simpul', '!=', '')
                               ->whereNull('n2.code');
                        });
                    })
                    ->sum('r.total');

                $stats['unmapped_volume'] = $unmappedVolume;
                $stats['forecast_volume'] = $forecastVolume;
                $stats['real_volume'] = $realVolume;
                $stats['mapped_volume'] = $realVolume - $unmappedVolume;
                $stats['success_rate'] = $realVolume > 0 
                    ? round(($stats['mapped_volume'] / $realVolume) * 100, 2) 
                    : 100;

                // Identifikasi node yang hilang (hanya dari data REAL)
                $missingOrigins = DB::table('raw_mpd_data as r')
                    ->leftJoin('ref_transport_nodes as n', 'r.kode_origin_simpul', '=', 'n.code')
                    ->where('r.import_job_id', $this->importJobId)
                    ->where('r.is_forecast', false)
                    ->where('r.kode_origin_simpul', '!=', '')
                    ->whereNull('n.code')
                    ->select('r.kode_origin_simpul as code', DB::raw('SUM(total) as t'))
                    ->groupBy('r.kode_origin_simpul')
                    ->orderByDesc('t')
                    ->take(5)
                    ->get();

                $stats['missing_nodes'] = $missingOrigins->map(fn($m) => ['code' => $m->code, 'vol' => $m->t])->toArray();

                $msg = "Integrity Check: " . $stats['success_rate'] . "% REAL data mapped. " .
                       number_format($unmappedVolume, 0) . " volume unmapped. " .
                       ($forecastVolume > 0 ? number_format($forecastVolume, 0) . " forecast volume (tanpa simpul, OK)." : '');
                $this->updateEtlStatus('processing', 82, $msg, $stats['success_rate'] < 90 ? 'warning' : 'info');

                // Write data_lost to dedicated column for display in history
                try {
                    $job = ImportJob::find($this->importJobId);
                    if ($job) {
                        $job->data_lost = (int) $unmappedVolume;
                        $job->save();
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to update data_lost: " . $e->getMessage());
                }
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
        // Temukan opsel apa saja yang terlibat di job ini untuk tanggal ini
        $opsels = DB::table('raw_mpd_data')
            ->where('import_job_id', $this->importJobId)
            ->where('tanggal', $date)
            ->distinct()
            ->pluck('opsel')
            ->toArray();

        // Temukan kategori apa saja yang terlibat di job ini untuk tanggal ini
        $kategoris = DB::table('raw_mpd_data')
            ->where('import_job_id', $this->importJobId)
            ->where('tanggal', $date)
            ->distinct()
            ->pluck('kategori')
            ->toArray();

        if (empty($opsels) || empty($kategoris)) return;

        // Kami memecah Query RAKSASA menjadi kepingan kecil (per opsel, per is_forecast, per kategori)
        // Ini adalah kunci agar VPS tidak memunculkan error "Killed" (Out of Memory/RAM Penuh)
        $forecastTypes = [true, false]; // true = forecast, false = real

        foreach ($opsels as $opsel) {
            foreach ($kategoris as $kategori) {
                foreach ($forecastTypes as $isForecast) {
                    
                    // Cek apakah data spesifik (opsel+kategori+forecast) ini ada di raw_mpd_data pada tanggal ini
                    $exists = DB::table('raw_mpd_data')
                        ->where('tanggal', $date)
                        ->where('opsel', $opsel)
                        ->where('kategori', $kategori)
                        ->where('is_forecast', $isForecast)
                        ->exists();

                    if (!$exists) continue;

                    // STEP 1: Delete existing spatial data for this specific combination to prevent duplicates
                    // Safer than ON CONFLICT for NULL values
                    DB::table('spatial_movements')
                        ->where('tanggal', $date)
                        ->where('opsel', $opsel)
                        ->where('kategori', $kategori)
                        ->where('is_forecast', $isForecast)
                        ->delete();

                    // STEP 2: Insert fresh aggregated data
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
                            n_origin.location as origin_location,
                            n_dest.location as dest_location,
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
                        WHERE r.tanggal = ?
                          AND r.opsel = ?
                          AND r.kategori = ?
                          AND r.is_forecast = ?
                        GROUP BY
                            r.tanggal, r.opsel, r.kategori,
                            r.kode_origin_kabupaten_kota, r.kode_dest_kabupaten_kota,
                            r.kode_origin_simpul, r.kode_dest_simpul,
                            r.kode_moda, r.is_forecast,
                            n_origin.location, n_dest.location
                    ";

                    DB::statement($sql, [$date, $opsel, $kategori, $isForecast]);
                }
            }
        }
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
