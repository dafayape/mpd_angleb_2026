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
    protected $signature = 'mpd:retry-etl {--opsel= : Filter spesifik operator (misal TSEL/IOH/XL)}';

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

        // Cari import_job_id yang jumlah raw != spatial ATAU belum ada sama sekali di spatial
        $query = DB::table('raw_mpd_data AS r')
            ->selectRaw('
                r.import_job_id,
                r.tanggal,
                r.opsel,
                r.kategori,
                MAX(i.original_filename) as filename
            ')
            ->leftJoin('spatial_movements AS s', function($join) {
                $join->on('r.tanggal', '=', 's.tanggal')
                     ->on('r.opsel', '=', 's.opsel')
                     ->on('r.kategori', '=', 's.kategori');
            })
            ->join('import_jobs AS i', 'r.import_job_id', '=', 'i.id')
            ->groupBy('r.import_job_id', 'r.tanggal', 'r.opsel', 'r.kategori')
            ->havingRaw('SUM(r.total) != COALESCE(SUM(s.total), 0) OR SUM(s.total) IS NULL');

        if ($opselFilter) {
            $query->where('r.opsel', $opselFilter);
        }

        $missingJobs = $query->get();

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

        if ($this->confirm('Apakah Anda ingin memasukkan ulang job-job di atas ke dalam antrean (Re-dispatch)?')) {
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
