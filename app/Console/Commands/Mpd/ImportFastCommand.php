<?php

namespace App\Console\Commands\Mpd;

use App\Actions\Mpd\ValidateCsvAction;
use App\Models\ImportJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportFastCommand extends Command
{
    /**
     * Nama dan argumen artisan command:
     * Format param: filepath, opsel (TSEL/IOH/XL), kategori (REAL/FORECAST), tanggal (YYYY-MM-DD)
     */
    protected $signature = 'mpd:import-fast 
                            {path : Path absolute menuju file CSV di server}
                            {--opsel= : Opsel data (TSEL, IOH, XL)}
                            {--kategori= : Kategori data (REAL, FORECAST)}
                            {--tanggal= : Tanggal data (YYYY-MM-DD)}';

    protected $description = 'Import kilat data MPD CSV langsung ke PostgreSQL via COPY command (Anti-Timeout)';

    public function handle()
    {
        $path = $this->argument('path');
        $opsel = strtoupper($this->option('opsel'));
        $kategori = strtoupper($this->option('kategori'));
        $tanggal = $this->option('tanggal');

        // 1. Validasi Input Dasar
        if (!file_exists($path)) {
            $this->error("❌ File tidak ditemukan di: {$path}");
            return Command::FAILURE;
        }
        if (!in_array($opsel, ['TSEL', 'IOH', 'XL'])) {
            $this->error("❌ Opsel tidak valid! Harus TSEL, IOH, atau XL.");
            return Command::FAILURE;
        }
        if (!in_array($kategori, ['REAL', 'FORECAST'])) {
            $this->error("❌ Kategori tidak valid! Harus REAL atau FORECAST.");
            return Command::FAILURE;
        }

        $this->info("🚀 Memulai proses Import Kilat (Jalur Tol PostgreSQL)");
        $this->line("File     : {$path}");
        $this->line("Identitas: {$opsel} | {$kategori} | {$tanggal}");
        $this->newLine();

        // 2. Buat Riwayat (ImportJob) agar muncul di Dashboard Web
        $filename = basename($path);
        
        $job = ImportJob::create([
            'filename' => $filename,
            'original_filename' => $filename . ' (CLI Fast Copier)',
            'opsel' => $opsel,
            'kategori' => $kategori,
            'tanggal_data' => $tanggal,
            'user_id' => 1, // Anggap dijalankan oleh Super Admin
            'status' => 'processing',
            'progress' => 0,
            'total_rows' => 0,
            'processed_rows' => 0,
        ]);

        $this->info("⏳ [1/4] Mencocokkan Integritas Data (Validasi CSV)...");
        
        // 3. Panggil Validasi (Persis seperti yang ada di Web)
        $validator = new ValidateCsvAction();
        $validationResult = $validator->execute($path, $opsel, $tanggal);

        if (!$validationResult['is_valid']) {
            $this->error("❌ GAGAL: File CSV cacat atau datanya tidak cocok (Mismatch Opsel/Tanggal)!");
            $job->update([
                'status' => 'validation_failed',
                'error_message' => 'Gagal validasi via Artisan. Cek struktur file/tanggal/opsel.'
            ]);
            return Command::FAILURE;
        }

        $this->info("✅ [2/4] Validasi Lolos! Memulai injeksi langsung ke Database...");

        // 4. Proses Inti: PostgreSQL Native COPY
        try {
            DB::disableQueryLog();
            $pdo = DB::connection()->getPdo();
            
            // Kolom di tabel raw_mpd_data. (Pastikan urutan ini cocok dengan susunan di dalam CSV)
            $dbColumns = "tanggal, opsel, kategori, kode_origin_provinsi, origin_provinsi, kode_origin_kabupaten_kota, origin_kabupaten_kota, kode_dest_provinsi, dest_provinsi, kode_dest_kabupaten_kota, dest_kabupaten_kota, kode_origin_simpul, origin_simpul, kode_dest_simpul, dest_simpul, kode_moda, moda, total";
            
            // Sintaks COPY native dari file csv murni, mengabaikan Header baris ke-1
            $query = "COPY raw_mpd_data ({$dbColumns}) FROM '{$path}' WITH (FORMAT csv, HEADER true, DELIMITER ';', ENCODING 'UTF8')";
            
            // Eksekusi: Sedot data! (Hanya butuh sekian detik)
            $pdo->exec($query);
            $this->info("✅ [3/4] Eksekusi COPY PostgreSQL Selesai (Sangat Cepat!).");

            // Karena pakai metode kilat, kita hitung ulang row yang sukses masuk berdasarkan identitasnya
            $totalInserted = DB::table('raw_mpd_data')
                ->where('tanggal', $tanggal)
                ->where('opsel', $opsel)
                ->where('kategori', $kategori)
                ->count();

            // 5. Update Status History ke "Completed" dengan jumlah baris aslinya
            $job->update([
                'status' => 'completed',
                'progress' => 100,
                'total_rows' => $totalInserted,
                'processed_rows' => $totalInserted,
            ]);

            $this->info("✅ [4/4] {$totalInserted} Baris berhasil masuk & History Web diupdate!");

            // 6. Jalankan Proses ETL Spasial secara Background (Mirroring dengan Web)
            \App\Jobs\Mpd\TransformRawToSpatialJob::dispatch($job->id);
            $this->info("🗺️  Job Transformasi Peta/Spasial sedang berjalan di background server.");

            $this->newLine();
            $this->info("🎉 IMPORT KILAT BERHASIL!");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("🔥 INSERT ERROR (Postgres COPY Aborted): " . $e->getMessage());
            Log::error("ImportFastCommand Failed: " . $e->getMessage());
            
            $job->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 250) // Simpan cuplikan error ke web
            ]);

            return Command::FAILURE;
        }
    }
}
