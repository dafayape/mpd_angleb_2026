<?php

namespace App\Console\Commands\Mpd;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\Mpd\TransformRawToSpatialJob;

class RetryMissingEtlCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mpd:retry-etl {--opsel= : Filter spesifik operator (misal TSEL/IOH/XL)} {--force : Paksa jalankan tanpa prompt konfirmasi}';

    /**
     * The console command description.
     */
    protected $description = 'Menjalankan ulang (re-dispatch) job ETL untuk file yang belum masuk / gagal sebagian di Spatial Movements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $opselFilter = $this->option('opsel') ? strtoupper($this->option('opsel')) : null;
        
        $this->info("🔍 Mendeteksi Data Mentah (Raw) yang belum masuk (missing) di Dashboard (Spatial)...");

        // 1. Ambil agregat RAW (Cepat karena ringan)
        $rawQuery = DB::table('raw_mpd_data')
            ->selectRaw('tanggal, opsel, kategori, SUM(total) as raw_sum, MAX(import_job_id) as import_job_id')
            ->groupBy('tanggal', 'opsel', 'kategori');
            
        if ($opselFilter) {
            $rawQuery->where('opsel', $opselFilter);
        }
        $rawSums = $rawQuery->get();

        // 2. Ambil agregat SPATIAL (Cepat karena ringan)
        $spatialQuery = DB::table('spatial_movements')
            ->selectRaw('tanggal, opsel, kategori, SUM(total) as spatial_sum')
            ->groupBy('tanggal', 'opsel', 'kategori');
            
        if ($opselFilter) {
            $spatialQuery->where('opsel', $opselFilter);
        }
        $spatialSums = $spatialQuery->get()->keyBy(function ($item) {
            return $item->tanggal . '|' . $item->opsel . '|' . $item->kategori;
        });

        // 3. Cocokkan di Memory PHP (Sangat cepat < 2 detik)
        $missingJobs = collect();
        $importJobIdsToFetch = [];

        foreach ($rawSums as $raw) {
            $key = $raw->tanggal . '|' . $raw->opsel . '|' . $raw->kategori;
            $spatial = $spatialSums->get($key);
            $spatialTotal = $spatial ? (int) $spatial->spatial_sum : 0;
            $rawTotal = (int) $raw->raw_sum;
            
            if ($rawTotal !== $spatialTotal) {
                $importJobIdsToFetch[] = $raw->import_job_id;
            }
        }

        if (empty($importJobIdsToFetch)) {
            $this->info("✨ Keren! Tidak ditemukan data yang selisih/hilang. Semuanya sudah sinkron.");
            return Command::SUCCESS;
        }

        // 4. Tarik metadata job dari tabel import_jobs berdasarkan ID yang tertangkap selisih
        $importJobIdsToFetch = array_unique($importJobIdsToFetch);
        $missingJobs = DB::table('raw_mpd_data as r')
            ->join('import_jobs as i', 'r.import_job_id', '=', 'i.id')
            ->selectRaw('r.import_job_id, r.tanggal, r.opsel, r.kategori, MAX(i.original_filename) as filename')
            ->whereIn('r.import_job_id', $importJobIdsToFetch)
            ->groupBy('r.import_job_id', 'r.tanggal', 'r.opsel', 'r.kategori')
            ->get();

        if ($missingJobs->isEmpty()) {
            $this->info("✨ Keren! Tidak ditemukan data yang selisih/hilang. Semuanya sudah sinkron.");
            return Command::SUCCESS;
        }

        $this->warn("🚨 Ditemukan " . $missingJobs->count() . " Import Job yang wajib di-ETL Ulang:");

        $tableData = [];
        $uniqueJobIds = [];

        foreach ($missingJobs as $job) {
            $tableData[] = [
                'Job ID' => $job->import_job_id,
                'Tanggal' => $job->tanggal,
                'Opsel' => $job->opsel,
                'Kategori' => $job->kategori,
                'File' => $job->filename
            ];
            $uniqueJobIds[] = $job->import_job_id;
        }

        $this->table(['ID Job', 'Tanggal', 'Opsel', 'Kategori', 'Nama File Asli'], $tableData);

        if ($this->option('force') || $this->confirm('Apakah Anda ingin memasukkan ulang job-job di atas ke dalam antrean (Re-dispatch)?')) {
            $uniqueJobIds = array_unique($uniqueJobIds);
            $this->info("⚙️  Memproses Re-dispatching " . count($uniqueJobIds) . " Job...");

            foreach ($uniqueJobIds as $jid) {
                TransformRawToSpatialJob::dispatch((int) $jid);
                $this->line("✅ Job {$jid} berhasil dilempar ke antrean background.");
            }

            $this->newLine();
            $this->info("🚀 Berhasil! Saat ini server sedang mengerjakannya di belakang layar.");
            $this->warn("PASTIKAN Anda menjalankan 'php artisan queue:work' di server agar antrean ini jalan!");
        } else {
            $this->info("❌ Aksi dibatalkan.");
        }

        return Command::SUCCESS;
    }
}
