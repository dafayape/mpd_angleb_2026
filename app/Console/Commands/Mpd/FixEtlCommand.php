<?php

namespace App\Console\Commands\Mpd;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Command untuk memperbaiki semua ETL yang GAGAL/PARSIAL.
 *
 * Berbeda dengan retry-etl (yang dispatch ke queue), command ini:
 * 1. Menjalankan ETL secara SINKRON & BERURUTAN per kombinasi (tanggal/opsel/kategori)
 * 2. Menampilkan progress realtime
 * 3. Memperbarui status di tabel import_jobs agar dashboard sinkron
 * 4. Refresh Materialized Views & Cache SEKALI di akhir (lebih efisien)
 * 5. Verifikasi akhir untuk memastikan 100%
 *
 * SQL yang digunakan IDENTIK dengan TransformRawToSpatialJob::processDateBatch()
 */
class FixEtlCommand extends Command
{
    protected $signature = 'mpd:fix-etl {--dry-run : Tampilkan yang perlu diperbaiki tanpa eksekusi}';

    protected $description = 'Memperbaiki semua ETL yang GAGAL/PARSIAL secara berurutan hingga SUKSES 100%';

    public function handle()
    {
        $startTime = microtime(true);

        // ══════════════════════════════════════════════
        //  FASE 1: ANALISIS
        // ══════════════════════════════════════════════
        $this->newLine();
        $this->info("═══════════════════════════════════════════════════════════");
        $this->info("  🔍 FASE 1: Menganalisis Selisih Data Raw vs Spatial");
        $this->info("═══════════════════════════════════════════════════════════");

        $mismatched = $this->findMismatchedCombinations();

        if ($mismatched->isEmpty()) {
            $this->newLine();
            $this->info("🎉 Semua data sudah SUKSES 100%! Tidak ada yang perlu diperbaiki.");
            return Command::SUCCESS;
        }

        $gagal = $mismatched->where('status', 'GAGAL')->count();
        $parsial = $mismatched->where('status', 'PARSIAL')->count();

        $this->newLine();
        $this->line("  📊 Ditemukan {$mismatched->count()} kombinasi bermasalah:");
        $this->line("     ❌ GAGAL (belum ETL)     : {$gagal} kombinasi");
        $this->line("     ⚠️  PARSIAL (data hilang)  : {$parsial} kombinasi");

        $tableData = $mismatched->map(fn($m, $i) => [
            'No' => $i + 1,
            'Tanggal' => $m['tanggal'],
            'Opsel' => $m['opsel'],
            'Kategori' => $m['kategori'],
            'Raw' => number_format($m['raw_total']),
            'Spatial' => number_format($m['spatial_total']),
            'Missing' => number_format($m['raw_total'] - $m['spatial_total']),
            'Status' => $m['status'] === 'GAGAL' ? '❌ GAGAL' : '⚠️ PARSIAL',
        ])->toArray();

        $this->table(['No', 'Tanggal', 'Opsel', 'Kategori', 'Raw', 'Spatial', 'Missing', 'Status'], $tableData);

        if ($this->option('dry-run')) {
            $this->warn("🏁 Mode --dry-run aktif. Tidak ada eksekusi.");
            return Command::SUCCESS;
        }

        // ══════════════════════════════════════════════
        //  FASE 2: ETL ULANG BERURUTAN
        // ══════════════════════════════════════════════
        $this->newLine();
        $this->info("═══════════════════════════════════════════════════════════");
        $this->info("  🚀 FASE 2: Menjalankan ETL Ulang Berurutan");
        $this->info("═══════════════════════════════════════════════════════════");

        $total = $mismatched->count();
        $completed = 0;
        $failed = 0;

        foreach ($mismatched as $index => $item) {
            $num = $index + 1;
            $label = "{$item['tanggal']} | {$item['opsel']} | {$item['kategori']}";

            $this->newLine();
            $this->line("──────────────────────────────────────────────────────────");
            $this->info("  ⚙️  [{$num}/{$total}] Memproses: {$label}");

            $jobStart = microtime(true);

            try {
                $this->processEtlForCombination($item['tanggal'], $item['opsel'], $item['kategori']);

                // Verify immediately
                $rawNow = (int) DB::table('raw_mpd_data')
                    ->where('tanggal', $item['tanggal'])
                    ->where('opsel', $item['opsel'])
                    ->where('kategori', $item['kategori'])
                    ->sum('total');

                $spatialNow = (int) DB::table('spatial_movements')
                    ->where('tanggal', $item['tanggal'])
                    ->where('opsel', $item['opsel'])
                    ->where('kategori', $item['kategori'])
                    ->sum('total');

                $elapsed = round(microtime(true) - $jobStart, 1);

                if ($rawNow === $spatialNow) {
                    $this->info("  ✅ SUKSES — Raw: " . number_format($rawNow) . " = Spatial: " . number_format($spatialNow) . " ({$elapsed}s)");
                    
                    // Update ImportJob table so UI reflecting "Completed"
                    DB::table('import_jobs')
                        ->where('tanggal_data', $item['tanggal'])
                        ->where('opsel', $item['opsel'])
                        ->where('kategori', $item['kategori'])
                        ->update([
                            'status_etl' => 'completed',
                            'etl_progress' => 100,
                            'updated_at' => now()
                        ]);

                    $completed++;
                } else {
                    $diff = $rawNow - $spatialNow;
                    $this->warn("  ⚠️ Selisih — Raw: " . number_format($rawNow) . " | Spatial: " . number_format($spatialNow) . " | Δ " . number_format($diff) . " ({$elapsed}s)");
                    
                    // Even if partial, mark as processing/partial for UI
                    DB::table('import_jobs')
                        ->where('tanggal_data', $item['tanggal'])
                        ->where('opsel', $item['opsel'])
                        ->where('kategori', $item['kategori'])
                        ->update([
                            'status_etl' => 'processing',
                            'etl_progress' => $rawNow > 0 ? (int)(($spatialNow/$rawNow)*100) : 0,
                            'updated_at' => now()
                        ]);

                    $completed++;
                }
            } catch (\Throwable $e) {
                $elapsed = round(microtime(true) - $jobStart, 1);
                $this->error("  ❌ ERROR ({$elapsed}s): " . $e->getMessage());
                Log::error("FixETL failed for {$label}: " . $e->getMessage());

                DB::table('import_jobs')
                    ->where('tanggal_data', $item['tanggal'])
                    ->where('opsel', $item['opsel'])
                    ->where('kategori', $item['kategori'])
                    ->update([
                        'status_etl' => 'failed',
                        'updated_at' => now()
                    ]);

                $failed++;
            }

            // Progress bar
            $progress = round(($num / $total) * 100);
            $totalElapsed = round(microtime(true) - $startTime);
            $filled = (int) ($progress / 2.5);
            $bar = str_repeat('█', $filled) . str_repeat('░', 40 - $filled);
            $this->line("  [{$bar}] {$progress}% | ⏱️ {$totalElapsed}s");
        }

        // ══════════════════════════════════════════════
        //  FASE 3: REFRESH MV & CACHE (SEKALI)
        // ══════════════════════════════════════════════
        $this->newLine();
        $this->info("═══════════════════════════════════════════════════════════");
        $this->info("  🔄 FASE 3: Menyegarkan Materialized Views & Cache");
        $this->info("═══════════════════════════════════════════════════════════");

        try {
            $this->line("  ↻ Refreshing mv_daily_summary...");
            DB::statement("REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_summary;");
            $this->info("  ✅ mv_daily_summary selesai");
        } catch (\Throwable $e) {
            $this->warn("  ⚠️ mv_daily_summary gagal: " . $e->getMessage());
        }

        try {
            $this->line("  ↻ Refreshing mv_jabodetabek_daily...");
            DB::statement("REFRESH MATERIALIZED VIEW CONCURRENTLY mv_jabodetabek_daily;");
            $this->info("  ✅ mv_jabodetabek_daily selesai");
        } catch (\Throwable $e) {
            $this->warn("  ⚠️ mv_jabodetabek_daily gagal: " . $e->getMessage());
        }

        try {
            Cache::flush();
            $this->info("  ✅ Cache di-flush");
        } catch (\Throwable $e) {
            $this->warn("  ⚠️ Cache flush gagal: " . $e->getMessage());
        }

        // ══════════════════════════════════════════════
        //  FASE 4: VERIFIKASI AKHIR
        // ══════════════════════════════════════════════
        $this->newLine();
        $this->info("═══════════════════════════════════════════════════════════");
        $this->info("  🔍 FASE 4: Verifikasi Akhir");
        $this->info("═══════════════════════════════════════════════════════════");

        $remaining = $this->findMismatchedCombinations();

        $totalElapsed = round(microtime(true) - $startTime);
        $minutes = floor($totalElapsed / 60);
        $seconds = $totalElapsed % 60;

        $this->newLine();
        $this->info("═══════════════════════════════════════════════════════════");
        $this->info("  📋 LAPORAN AKHIR");
        $this->info("═══════════════════════════════════════════════════════════");
        $this->line("  ⏱️  Total Waktu         : {$minutes}m {$seconds}s");
        $this->line("  📦 Kombinasi Diproses   : {$completed}");
        $this->line("  ❌ Kombinasi Error      : {$failed}");
        $this->line("  🎯 Awal Bermasalah      : {$total}");
        $this->line("  ⚠️  Masih Bermasalah     : " . $remaining->count());

        if ($remaining->isEmpty()) {
            $this->newLine();
            $this->info("  🎉 SEMPURNA! Semua data Raw ↔ Spatial sudah sinkron 100%!");
            $this->info("  Dashboard siap digunakan.");
        } else {
            $this->newLine();
            $this->error("  ⚠️ Masih ada " . $remaining->count() . " kombinasi belum sinkron.");
            $remainTable = $remaining->map(fn($m) => [
                'Tanggal' => $m['tanggal'],
                'Opsel' => $m['opsel'],
                'Kategori' => $m['kategori'],
                'Missing' => number_format($m['raw_total'] - $m['spatial_total']),
            ])->toArray();
            $this->table(['Tanggal', 'Opsel', 'Kategori', 'Missing'], $remainTable);
        }

        return $remaining->isEmpty() ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Mencari semua kombinasi (tanggal, opsel, kategori) yang belum sinkron.
     */
    private function findMismatchedCombinations()
    {
        $rawSums = DB::table('raw_mpd_data')
            ->selectRaw('tanggal, opsel, kategori, SUM(total) as raw_sum')
            ->groupBy('tanggal', 'opsel', 'kategori')
            ->get();

        $spatialSums = DB::table('spatial_movements')
            ->selectRaw('tanggal, opsel, kategori, SUM(total) as spatial_sum')
            ->groupBy('tanggal', 'opsel', 'kategori')
            ->get()
            ->keyBy(fn($item) => $item->tanggal . '|' . $item->opsel . '|' . $item->kategori);

        $mismatched = collect();

        foreach ($rawSums as $raw) {
            $key = $raw->tanggal . '|' . $raw->opsel . '|' . $raw->kategori;
            $spatial = $spatialSums->get($key);
            $spatialTotal = $spatial ? (int) $spatial->spatial_sum : 0;
            $rawTotal = (int) $raw->raw_sum;

            if ($rawTotal !== $spatialTotal) {
                $mismatched->push([
                    'tanggal' => $raw->tanggal,
                    'opsel' => $raw->opsel,
                    'kategori' => $raw->kategori,
                    'raw_total' => $rawTotal,
                    'spatial_total' => $spatialTotal,
                    'status' => $spatialTotal === 0 ? 'GAGAL' : 'PARSIAL',
                ]);
            }
        }

        return $mismatched->sortBy(['tanggal', 'opsel', 'kategori'])->values();
    }

    /**
     * Menjalankan SQL ETL yang IDENTIK dengan TransformRawToSpatialJob::processDateBatch()
     * untuk kombinasi (tanggal, opsel, kategori) tertentu.
     */
    private function processEtlForCombination(string $tanggal, string $opsel, string $kategori): void
    {
        foreach ([true, false] as $isForecast) {
            $exists = DB::table('raw_mpd_data')
                ->where('tanggal', $tanggal)
                ->where('opsel', $opsel)
                ->where('kategori', $kategori)
                ->where('is_forecast', $isForecast)
                ->exists();

            if (!$exists) continue;

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
                ON CONFLICT (tanggal, opsel, kategori, kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, kode_origin_simpul, kode_dest_simpul, kode_moda, is_forecast)
                DO UPDATE SET
                    total = EXCLUDED.total,
                    origin_location = EXCLUDED.origin_location,
                    dest_location = EXCLUDED.dest_location,
                    distance_meters = EXCLUDED.distance_meters,
                    updated_at = NOW()
            ";

            DB::statement($sql, [$tanggal, $opsel, $kategori, $isForecast]);

            // Calculate Data Lost (Unmapped Nodes) if it's REAL data
            if (!$isForecast) {
                $unmapped = (int) DB::table('raw_mpd_data as r')
                    ->leftJoin('ref_transport_nodes as n1', 'r.kode_origin_simpul', '=', 'n1.code')
                    ->leftJoin('ref_transport_nodes as n2', 'r.kode_dest_simpul', '=', 'n2.code')
                    ->where('r.tanggal', $tanggal)
                    ->where('r.opsel', $opsel)
                    ->where('r.kategori', $kategori)
                    ->where('r.is_forecast', false)
                    ->where(function ($q) {
                        $q->where(function ($q2) {
                            $q2->where('r.kode_origin_simpul', '!=', '')->whereNull('n1.code');
                        })->orWhere(function ($q2) {
                            $q2->where('r.kode_dest_simpul', '!=', '')->whereNull('n2.code');
                        });
                    })->sum('r.total');

                DB::table('import_jobs')
                    ->where('tanggal_data', $tanggal)
                    ->where('opsel', $opsel)
                    ->where('kategori', $kategori)
                    ->update(['data_lost' => $unmapped]);
            }
        }
    }
}
