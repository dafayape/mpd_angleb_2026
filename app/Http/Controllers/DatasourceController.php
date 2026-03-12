<?php

namespace App\Http\Controllers;

use App\Actions\Mpd\ValidateCsvAction;
use App\Models\ActivityLog;
use App\Models\ImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DatasourceController extends Controller
{
    /**
     * Form upload CSV
     */
    public function upload()
    {
        return view('datasource.upload');
    }

    /**
     * Step 1: Upload file CSV, simpan ke storage, buat ImportJob record
     */
    public function storeUpload(Request $request)
    {
        $request->validate([
            'opsel' => 'required|string|in:TSEL,IOH,XLSMART',
            'kategori' => 'required|string|in:REAL,FORECAST',
            'tanggal_data' => 'required|date',
            'file' => 'required|file|mimes:csv,txt|max:1048576',
        ]);

        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        $filename = time().'_'.$originalFilename;
        $fileSize = $file->getSize();

        $file->move(storage_path('app/mpd_uploads'), $filename);
        $fullPath = storage_path('app/mpd_uploads/'.$filename);

        // Hitung total baris dengan cepat (tanpa membaca isi, hanya hitung newline)
        $totalRows = 0;
        $handle = @fopen($fullPath, 'r');
        if ($handle) {
            while (fgets($handle) !== false) {
                $totalRows++;
            }
            fclose($handle);
            $totalRows = max(0, $totalRows - 1); // minus header
        }

        $job = ImportJob::create([
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'opsel' => $request->opsel,
            'kategori' => $request->kategori,
            'tanggal_data' => $request->tanggal_data,
            'user_id' => Auth::id(),
            'status' => 'queued',
            'status_file' => 'queued',
            'status_etl' => 'pending',
            'etl_progress' => 0,
            'progress' => 0,
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'skipped_rows' => 0,
            'data_lost' => 0,
            'file_size' => $fileSize,
            'metadata' => ['file_size' => $fileSize, 'total_rows_estimated' => $totalRows],
        ]);

        // ═══════════════════════════════════════════════════════
        // Dispatch ke Background Queue: Validate → Import → ETL
        // Seluruh proses jalan di background, browser bisa ditutup.
        // ═══════════════════════════════════════════════════════
        \App\Jobs\Mpd\ProcessMpdImportJob::dispatch($job->id);
        Log::info("[Upload] File '{$originalFilename}' ({$totalRows} rows) dispatched to queue. Job #{$job->id}");

        // Catat log aktivitas (non-blocking)
        try {
            ActivityLog::log('Upload CSV', $originalFilename, 'Success', "Opsel: {$request->opsel}, Kategori: {$request->kategori}, Rows: {$totalRows}");
        } catch (\Throwable $e) {
            Log::warning('ActivityLog gagal: '.$e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'history_id' => $job->id,
            'total_rows' => $totalRows,
            'message' => "File berhasil diterima ({$totalRows} baris). Proses validasi + import berjalan di background.",
            'redirect' => route('datasource.history'),
        ]);
    }

    /**
     * Step 1.5: Validasi CSV sebelum import — cek header, tipe data, dan kode referensi
     */
    public function validateCsv(Request $request)
    {
        $historyId = $request->input('history_id');
        $job = ImportJob::find($historyId);

        if (! $job) {
            return response()->json(['is_valid' => false, 'error' => 'Import job tidak ditemukan.'], 404);
        }

        $path = $this->resolveFilePath('mpd_uploads/'.$job->filename);
        if (! $path) {
            return response()->json(['is_valid' => false, 'error' => 'File tidak ditemukan di storage.'], 404);
        }

        $result = (new ValidateCsvAction())->execute($path, $job->opsel, $job->tanggal_data);

        // Update job status based on validation result
        if (! $result['is_valid']) {
            $job->update(['status' => 'validation_failed']);
        } else {
            $job->update(['status' => 'validated']);
        }

        return response()->json($result);
    }

    /**
     * Step 2: Proses CSV per chunk via AJAX
     * - Parse baris CSV (delimiter ;)
     * - Insert langsung ke raw_mpd_data dengan import_job_id
     * - is_forecast dari pilihan REAL / FORECAST di form
     */
    public function processChunk(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        DB::disableQueryLog();

        $historyId = $request->input('history_id');
        $offset = (int) $request->input('offset', 0);
        $chunkSize = 5000;

        $job = ImportJob::find($historyId);
        if (! $job) {
            return response()->json(['status' => 'error', 'message' => 'Import job tidak ditemukan.'], 404);
        }

        // Resolve file path
        $path = $this->resolveFilePath('mpd_uploads/'.$job->filename);
        if (! $path) {
            $job->update(['status' => 'failed', 'error_message' => 'File tidak ditemukan di storage.']);

            return response()->json(['status' => 'error', 'message' => 'File tidak ditemukan di storage.'], 404);
        }

        $handle = fopen($path, 'r');
        if (! $handle) {
            return response()->json(['status' => 'error', 'message' => 'Gagal membuka file.'], 500);
        }

        // Offset 0: skip BOM + header
        if ($offset === 0) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                fseek($handle, 0);
            }
            fgets($handle); // skip header
        } else {
            fseek($handle, $offset);
        }

        // is_forecast: REAL = false, FORECAST = true
        $isForecast = ($job->kategori === 'FORECAST');
        $now = now()->toDateTimeString();
        $batch = [];
        $rowsInChunk = 0;
        $rowsSkipped = 0;
        $isEof = false;
        $errors = [];

        while ($rowsInChunk < $chunkSize) {
            $line = fgets($handle);
            if ($line === false) {
                $isEof = true;
                break;
            }

            $line = trim(str_replace("\r", '', $line));
            if ($line === '') {
                continue;
            }

            $cols = str_getcsv($line, ';');
            if (count($cols) < 18) {
                $rowsSkipped++;

                continue;
            }

            $tanggal = trim($cols[0]);
            if (! $tanggal || ! strtotime($tanggal)) {
                $rowsSkipped++;

                continue;
            }

            $batch[] = [
                'import_job_id' => $job->id,
                'tanggal' => $tanggal,
                'opsel' => trim($cols[1]),
                'kategori' => trim($cols[2]),
                'kode_origin_provinsi' => trim($cols[3]),
                'origin_provinsi' => trim($cols[4]),
                'kode_origin_kabupaten_kota' => trim($cols[5]),
                'origin_kabupaten_kota' => trim($cols[6]),
                'kode_dest_provinsi' => trim($cols[7]),
                'dest_provinsi' => trim($cols[8]),
                'kode_dest_kabupaten_kota' => trim($cols[9]),
                'dest_kabupaten_kota' => trim($cols[10]),
                'kode_origin_simpul' => trim($cols[11] ?? ''),
                'origin_simpul' => trim($cols[12] ?? ''),
                'kode_dest_simpul' => trim($cols[13] ?? ''),
                'dest_simpul' => trim($cols[14] ?? ''),
                'kode_moda' => trim($cols[15] ?? ''),
                'moda' => trim($cols[16] ?? ''),
                'total' => (int) trim($cols[17] ?? '0'),
                'is_forecast' => $isForecast,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Flush batch setiap 1000 baris
            if (count($batch) >= 1000) {
                $this->insertBatch($batch, $job, $errors);
                $batch = [];
            }

            $rowsInChunk++;
        }

        $newOffset = ftell($handle);
        fclose($handle);

        // Insert sisa batch
        if (! empty($batch)) {
            $this->insertBatch($batch, $job, $errors);
        }

        $fileSize = filesize($path);
        $percentage = $fileSize > 0 ? min(round(($newOffset / $fileSize) * 100), 99) : 100;

        $job->refresh();

        // Selesai (EOF)
        if ($isEof) {
            $finalStatus = empty($errors) ? 'completed' : 'completed_with_errors';
            $job->update([
                'status' => $finalStatus,
                'status_file' => $finalStatus,
                'progress' => 100,
                'total_rows' => $job->processed_rows,
                'skipped_rows' => DB::raw("skipped_rows + {$rowsSkipped}"),
                'error_message' => ! empty($errors) ? implode(' | ', array_slice($errors, 0, 5)) : null,
            ]);

            // Auto-trigger ETL via Queue (background worker)
            try {
                \App\Jobs\Mpd\TransformRawToSpatialJob::dispatch($job->id);
                $job->update(['status_etl' => 'queued']);
                Log::info("[Import] ETL dispatched to queue for import_job_id: {$job->id}");
            } catch (\Throwable $etlErr) {
                Log::error('[Import] ETL dispatch failed: '.$etlErr->getMessage());
                $job->update(['status_etl' => 'failed']);
            }

            return response()->json([
                'status' => 'completed',
                'offset' => $newOffset,
                'rows_processed' => $job->processed_rows,
                'rows_skipped' => $rowsSkipped,
                'percent' => 100,
                'errors' => $errors,
                'etl_dispatched' => true,
            ]);
        }

        // Masih ada data (progress)
        $job->update([
            'status' => 'processing',
            'status_file' => 'importing',
            'progress' => $percentage,
            'skipped_rows' => DB::raw("skipped_rows + {$rowsSkipped}"),
        ]);

        return response()->json([
            'status' => 'progress',
            'offset' => $newOffset,
            'rows_processed' => $rowsInChunk,
            'rows_skipped' => $rowsSkipped,
            'percent' => $percentage,
            'errors' => $errors,
        ]);
    }

    /**
     * Insert batch ke raw_mpd_data, fallback row-by-row jika batch gagal
     */
    private function insertBatch(array $batch, ImportJob $job, array &$errors): void
    {
        try {
            // UPSERT: Jika data duplikat (unique key match), update total + updated_at
            // Ini mencegah data ganda jika file yang sama di-upload ulang
            DB::table('raw_mpd_data')->upsert(
                $batch,
                [
                    'tanggal', 'opsel', 'kategori',
                    'kode_origin_kabupaten_kota', 'kode_dest_kabupaten_kota',
                    'kode_origin_simpul', 'kode_dest_simpul',
                    'kode_moda', 'is_forecast',
                ],
                ['total', 'import_job_id', 'updated_at']
            );
            $job->increment('processed_rows', count($batch));
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
            Log::error('Batch upsert failed: '.$e->getMessage());

            // Fallback: upsert satu per satu
            $saved = 0;
            foreach ($batch as $row) {
                try {
                    DB::table('raw_mpd_data')->upsert(
                        [$row],
                        [
                            'tanggal', 'opsel', 'kategori',
                            'kode_origin_kabupaten_kota', 'kode_dest_kabupaten_kota',
                            'kode_origin_simpul', 'kode_dest_simpul',
                            'kode_moda', 'is_forecast',
                        ],
                        ['total', 'import_job_id', 'updated_at']
                    );
                    $saved++;
                } catch (\Exception $rowErr) {
                    Log::warning('Row upsert failed: '.$rowErr->getMessage());
                }
            }
            if ($saved > 0) {
                $job->increment('processed_rows', $saved);
            }
        }
    }

    /**
     * API: Polling ETL status untuk auto-refresh di halaman History.
     * Mengembalikan status ETL semua job yang belum selesai (untuk update progress bar tanpa reload).
     */
    public function etlStatus()
    {
        $activeJobs = ImportJob::where(function ($q) {
                // ETL aktif (queued, processing)
                $q->whereNotIn('status_etl', ['completed', 'pending', 'failed'])
                // ATAU file masih diproses (queued, validating, importing)
                  ->orWhereIn('status_file', ['queued', 'validating', 'importing']);
            })
            ->select('id', 'status_file', 'status_etl', 'etl_progress', 'progress', 'processed_rows', 'total_rows', 'skipped_rows', 'data_lost')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'jobs' => $activeJobs,
            'has_active' => $activeJobs->isNotEmpty(),
        ]);
    }

    /**
     * Halaman history import
     */
    public function history(Request $request)
    {
        $query = ImportJob::orderBy('created_at', 'desc');

        if ($request->filled('opsel')) {
            $query->where('opsel', $request->opsel);
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_data', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_data', '<=', $request->end_date);
        }

        $histories = $query->paginate(10)->withQueryString();
        $summary = $this->getSummary();

        return view('datasource.history', compact('histories', 'summary'));
    }

    /**
     * Halaman raw data
     */
    public function rawData(Request $request)
    {
        $query = DB::table('raw_mpd_data')->orderBy('tanggal', 'desc');

        if ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }
        if ($request->filled('opsel')) {
            $query->where('opsel', $request->opsel);
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('is_forecast') && $request->is_forecast !== '') {
            $query->where('is_forecast', $request->is_forecast === '1');
        }

        $data = $query->simplePaginate(50)->withQueryString();

        return view('datasource.raw', compact('data'));
    }

    /**
     * Hapus data import — DELETE dari raw_mpd_data berdasarkan import_job_id
     * Chunked delete 25000 baris per request supaya tidak timeout
     */
    public function destroyChunk($id)
    {
        ini_set('max_execution_time', 0);

        try {
            $job = ImportJob::find($id);
            if (! $job) {
                return response()->json(['status' => 'completed', 'deleted' => 0]);
            }

            // STEP 1: Hapus spatial_movements yang terkait (idempotent, aman dipanggil tiap chunk)
            // Harus dilakukan SEBELUM raw_mpd_data dihapus, karena butuh JOIN ke raw data
            $this->deleteSpatialMovements($job->id);

            // STEP 2: Hapus data dari raw_mpd_data berdasarkan import_job_id (chunked)
            $deleted = DB::table('raw_mpd_data')
                ->where('import_job_id', $job->id)
                ->take(25000)
                ->delete();

            // Masih ada baris yang perlu dihapus
            if ($deleted > 0) {
                return response()->json([
                    'status' => 'progress',
                    'deleted' => $deleted,
                ]);
            }

            // Semua data terhapus → hapus file CSV dan record ImportJob
            $originalName = $job->original_filename ?? $job->filename;
            $filePath = 'mpd_uploads/'.$job->filename;
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            $job->delete();

            // STEP 3: Refresh materialized views & invalidate cache
            $this->refreshAfterDelete();

            // Catat log aktivitas
            ActivityLog::log('Delete Import', $originalName, 'Success', "Import job #{$id} berhasil dihapus (raw + spatial)");

            return response()->json(['status' => 'completed', 'deleted' => 0]);
        } catch (\Exception $e) {
            Log::error('Delete error: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Summary statistics
     */
    public function summary()
    {
        return response()->json($this->getSummary());
    }

    /**
     * Summary statistik raw_mpd_data
     */
    private function getSummary(): array
    {
        $cacheKey = 'datasource:summary:v3';

        try {
            return Cache::remember($cacheKey, 60, function () {
                return [
                    'total_rows' => (int) ImportJob::whereIn('status', ['completed', 'completed_with_errors'])->sum('processed_rows'),
                    'total_uploads' => ImportJob::whereIn('status', ['completed', 'completed_with_errors'])->count(),
                    'by_opsel' => ImportJob::whereIn('status', ['completed', 'completed_with_errors'])
                        ->select('opsel', DB::raw('SUM(processed_rows) as total'))
                        ->groupBy('opsel')
                        ->pluck('total', 'opsel')
                        ->toArray(),
                    'latest_date' => ImportJob::whereIn('status', ['completed', 'completed_with_errors'])->max('tanggal_data'),
                ];
            });
        } catch (\Throwable $e) {
            return ['total_rows' => 0, 'total_uploads' => 0, 'by_opsel' => [], 'latest_date' => null];
        }
    }

    /**
     * Hapus spatial_movements yang terkait dengan import_job_id.
     * Menggunakan PostgreSQL DELETE...USING untuk JOIN ke raw_mpd_data
     * sebelum raw data dihapus.
     *
     * Idempotent: aman dipanggil berulang (no-op jika sudah kosong).
     */
    private function deleteSpatialMovements(int $importJobId): void
    {
        try {
            $job = ImportJob::find($importJobId);
            if (!$job || !$job->tanggal_data || !$job->opsel || !$job->kategori) {
                return;
            }

            $tanggalDate = \Carbon\Carbon::parse($job->tanggal_data)->format('Y-m-d');
            
            $deleted = DB::table('spatial_movements')
                ->where('tanggal', $tanggalDate)
                ->where('opsel', $job->opsel)
                ->where('kategori', $job->kategori)
                ->delete();

            if ($deleted > 0) {
                Log::info("[Delete] Removed {$deleted} spatial_movements rows for import_job_id={$importJobId} (Date: {$tanggalDate}, Opsel: {$job->opsel})");
            }
        } catch (\Throwable $e) {
            Log::error("[Delete] Failed to delete spatial_movements for import_job_id={$importJobId}: ".$e->getMessage());
        }
    }

    /**
     * Refresh materialized views dan invalidate cache setelah delete.
     * Mirror logic dari TransformRawToSpatialJob.
     */
    private function refreshAfterDelete(): void
    {
        // Refresh materialized views
        try {
            DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY mv_daily_summary;');
            DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY mv_jabodetabek_daily;');
            Log::info('[Delete] Materialized views refreshed.');
        } catch (\Throwable $e) {
            Log::warning('[Delete] Materialized view refresh skipped: '.$e->getMessage());
        }

        // Flush seluruh cache (setara php artisan cache:clear)
        try {
            Cache::flush();
            Log::info('[Delete] Cache flushed (all keys cleared).');
        } catch (\Throwable $e) {
            Log::warning('[Delete] Cache flush failed: '.$e->getMessage());
        }
    }

    /**
     * Resolve path file upload (cek beberapa kemungkinan lokasi storage)
     */
    private function resolveFilePath(string $storagePath): ?string
    {
        $paths = [
            Storage::disk('local')->path($storagePath),
            storage_path('app/private/'.$storagePath),
            storage_path('app/'.$storagePath),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        Log::error('File not found. Tried: '.implode(', ', $paths));

        return null;
    }
}
