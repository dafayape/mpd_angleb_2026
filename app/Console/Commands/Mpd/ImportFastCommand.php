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
        if (!in_array($opsel, ['TSEL', 'IOH', 'XLSMART'])) {
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
            'original_filename' => $filename,
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

        // 4. Proses Inti: PostgreSQL Native COPY via Temporary Table
        try {
            DB::disableQueryLog();
            $pdo = DB::connection()->getPdo();
            
            $tempTable = "temp_csv_" . $job->id;
            
            // a. Prepare Temp Table
            $pdo->exec("
                CREATE TEMPORARY TABLE {$tempTable} (
                    tanggal DATE, opsel VARCHAR, kategori VARCHAR,
                    kode_origin_provinsi VARCHAR, origin_provinsi VARCHAR,
                    kode_origin_kabupaten_kota VARCHAR, origin_kabupaten_kota VARCHAR,
                    kode_dest_provinsi VARCHAR, dest_provinsi VARCHAR,
                    kode_dest_kabupaten_kota VARCHAR, dest_kabupaten_kota VARCHAR,
                    kode_origin_simpul VARCHAR, origin_simpul VARCHAR,
                    kode_dest_simpul VARCHAR, dest_simpul VARCHAR,
                    kode_moda VARCHAR, moda VARCHAR, total INTEGER
                ) ON COMMIT PRESERVE ROWS;
            ");

            // b. COPY Cepat langsung ke Temp Table (Sedot File Murni)
            $dbColumns = "tanggal, opsel, kategori, kode_origin_provinsi, origin_provinsi, kode_origin_kabupaten_kota, origin_kabupaten_kota, kode_dest_provinsi, dest_provinsi, kode_dest_kabupaten_kota, dest_kabupaten_kota, kode_origin_simpul, origin_simpul, kode_dest_simpul, dest_simpul, kode_moda, moda, total";
            $query = "COPY {$tempTable} ({$dbColumns}) FROM '{$path}' WITH (FORMAT csv, HEADER true, DELIMITER ';', ENCODING 'UTF8')";
            $pdo->exec($query);

            // c. Migrasi Internal dari Temp ke Tabel Asli + deduplikasi + logic forecast
            $isForecastStr = ($kategori === 'FORECAST') ? 'true' : 'false';
            
            // Kolom target unik untuk ON CONFLICT
            $uniqueColumns = "tanggal, opsel, kategori, kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, kode_origin_simpul, kode_dest_simpul, kode_moda, is_forecast";
            
            $insertQuery = "
                INSERT INTO raw_mpd_data (
                    import_job_id, is_forecast, created_at, updated_at, 
                    tanggal, opsel, kategori, 
                    kode_origin_provinsi, origin_provinsi, 
                    kode_origin_kabupaten_kota, origin_kabupaten_kota, 
                    kode_dest_provinsi, dest_provinsi, 
                    kode_dest_kabupaten_kota, dest_kabupaten_kota, 
                    kode_origin_simpul, origin_simpul, 
                    kode_dest_simpul, dest_simpul, 
                    kode_moda, moda, total
                )
                SELECT 
                    {$job->id}, {$isForecastStr}, NOW(), NOW(),
                    tanggal, opsel, kategori, 
                    MAX(kode_origin_provinsi), MAX(origin_provinsi), 
                    kode_origin_kabupaten_kota, MAX(origin_kabupaten_kota), 
                    MAX(kode_dest_provinsi), MAX(dest_provinsi), 
                    kode_dest_kabupaten_kota, MAX(dest_kabupaten_kota), 
                    -- HANYA paksa kosong ('') jika Kategori adalah FORECAST
                    CASE WHEN {$isForecastStr} THEN '' ELSE kode_origin_simpul END,
                    CASE WHEN {$isForecastStr} THEN '' ELSE MAX(origin_simpul) END,
                    CASE WHEN {$isForecastStr} THEN '' ELSE kode_dest_simpul END,
                    CASE WHEN {$isForecastStr} THEN '' ELSE MAX(dest_simpul) END,
                    CASE WHEN {$isForecastStr} THEN '' ELSE kode_moda END,
                    CASE WHEN {$isForecastStr} THEN '' ELSE MAX(moda) END,
                    SUM(total)
                FROM {$tempTable}
                GROUP BY 
                    tanggal, opsel, kategori, 
                    kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, 
                    -- Kolom unik harus konsisten dengan SELECT agar GROUP BY jalan
                    CASE WHEN {$isForecastStr} THEN '' ELSE kode_origin_simpul END,
                    CASE WHEN {$isForecastStr} THEN '' ELSE kode_dest_simpul END,
                    CASE WHEN {$isForecastStr} THEN '' ELSE kode_moda END
                ON CONFLICT ({$uniqueColumns}) 
                DO UPDATE SET 
                    total = raw_mpd_data.total + EXCLUDED.total,
                    import_job_id = EXCLUDED.import_job_id,
                    updated_at = NOW()
            ";
            
            $insertedRows = $pdo->exec($insertQuery);
            $this->info("✅ [3/4] Eksekusi Copy & Deduplikasi PostgreSQL Selesai.");

            // 5. Update Status History ke "Completed" dengan jumlah baris aslinya
            $job->update([
                'status' => 'completed',
                'progress' => 100,
                'total_rows' => $insertedRows,
                'processed_rows' => $insertedRows,
            ]);

            $this->info("✅ [4/4] {$insertedRows} Baris berhasil masuk & History Web diupdate!");
            
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
