<?php

namespace App\Console\Commands\Mpd;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckEtlCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mpd:check-etl';

    /**
     * The console command description.
     */
    protected $description = 'Cek integritas proses ETL antara Data Mentah (CSV) dan Data Spasial (Dashboard)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 Memeriksa Kelengkapan Data (Raw vs Spatial)...");

        $rawSums = DB::table('raw_mpd_data')
            ->selectRaw('tanggal, opsel, kategori, SUM(total) as raw_sum')
            ->groupBy('tanggal', 'opsel', 'kategori')
            ->get();

        $spatialSums = DB::table('spatial_movements')
            ->selectRaw('tanggal, opsel, kategori, SUM(total) as spatial_sum')
            ->groupBy('tanggal', 'opsel', 'kategori')
            ->get()
            ->keyBy(function ($item) {
                return $item->tanggal . '|' . $item->opsel . '|' . $item->kategori;
            });

        $report = [];
        $hasMissing = false;

        foreach ($rawSums as $raw) {
            $key = $raw->tanggal . '|' . $raw->opsel . '|' . $raw->kategori;
            $spatial = $spatialSums->get($key);
            $spatialTotal = $spatial ? (int) $spatial->spatial_sum : 0;
            $rawTotal = (int) $raw->raw_sum;
            
            if ($spatialTotal === 0) {
                $status = '❌ GAGAL (Belum di-ETL)';
                $hasMissing = true;
            } elseif ($rawTotal !== $spatialTotal) {
                $status = '⚠️ PARSIAL (Data hilang)';
                $hasMissing = true;
            } else {
                $status = '✅ SUKSES 100%';
            }
            
            $report[] = [
                'Tanggal' => $raw->tanggal,
                'Opsel' => $raw->opsel,
                'Kategori' => $raw->kategori,
                'Raw Total' => number_format($rawTotal),
                'Spatial Total' => number_format($spatialTotal),
                'Selisih (Missing)' => number_format($rawTotal - $spatialTotal),
                'Status' => $status
            ];
        }

        if ($rawSums->isEmpty()) {
            $this->warn("Tidak ada data raw ditemukan di database.");
        } else {
            $this->table(['Tanggal', 'Opsel', 'Kategori', 'Raw Total', 'Spatial Total', 'Missing', 'Status'], $report);
        }

        if ($hasMissing) {
            $this->newLine();
            $this->error("🚨 PERINGATAN: Ada data yang belum berhasil diolah ke Spatial Movements!");
            $this->warn("Terdapat Job ETL yang kemungkinan macet atau gagal.");
            
            // Cek Job nyangkut
            $pendingJobs = DB::table('jobs')
                ->where('payload', 'like', '%TransformRawToSpatialJob%')
                ->count();
                
            $failedJobs = DB::table('failed_jobs')
                ->where('payload', 'like', '%TransformRawToSpatialJob%')
                ->count();
                
            $this->info("Status Background Job ETL:");
            $this->line("- Mengantri / Berjalan : {$pendingJobs} job");
            $this->line("- Gagal (Failed)     : {$failedJobs} job");
            
            if ($failedJobs > 0) {
                $this->info("💡 Saran: Jalankan 'php artisan queue:retry all' lalu 'php artisan queue:work' untuk memproses ulang data yang gagal.");
            } elseif ($pendingJobs > 0) {
                $this->info("💡 Saran: Pastikan worker antrean berjalan (php artisan queue:work) agar proses segera dieksekusi.");
            }
        } else {
            $this->newLine();
            $this->info("🎉 LUAR BIASA! Semua data dari file CSV berhasil diproses dan sinkron dengan Dashboard.");
        }
        
        return Command::SUCCESS;
    }
}
