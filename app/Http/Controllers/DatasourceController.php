<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ImportJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'opsel'        => 'required|string|in:TSEL,IOH,XL',
            'kategori'     => 'required|string|in:REAL,FORECAST',
            'tanggal_data' => 'required|date',
            'file'         => 'required|file|mimes:csv,txt|max:1048576',
        ]);

        $file             = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        $filename         = time() . '_' . $originalFilename;

        $file->storeAs('mpd_uploads', $filename, 'local');

        $job = ImportJob::create([
            'filename'          => $filename,
            'original_filename' => $originalFilename,
            'opsel'             => $request->opsel,
            'kategori'          => $request->kategori,
            'tanggal_data'      => $request->tanggal_data,
            'user_id'           => Auth::id(),
            'status'            => 'uploaded',
            'progress'          => 0,
            'total_rows'        => 0,
            'processed_rows'    => 0,
            'metadata'          => ['file_size' => $file->getSize()],
        ]);

        // Catat log aktivitas
        ActivityLog::log('Upload CSV', $originalFilename, 'Success', "Opsel: {$request->opsel}, Kategori: {$request->kategori}");

        return response()->json([
            'status'     => 'success',
            'history_id' => $job->id,
            'message'    => 'File berhasil diupload.',
        ]);
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
        $offset    = (int) $request->input('offset', 0);
        $chunkSize = 5000;

        $job = ImportJob::find($historyId);
        if (!$job) {
            return response()->json(['status' => 'error', 'message' => 'Import job tidak ditemukan.'], 404);
        }

        // Resolve file path
        $path = $this->resolveFilePath('mpd_uploads/' . $job->filename);
        if (!$path) {
            $job->update(['status' => 'failed', 'error_message' => 'File tidak ditemukan di storage.']);
            return response()->json(['status' => 'error', 'message' => 'File tidak ditemukan di storage.'], 404);
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
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
        $isForecast   = ($job->kategori === 'FORECAST');
        $now          = now()->toDateTimeString();
        $batch        = [];
        $rowsInChunk  = 0;
        $rowsSkipped  = 0;
        $isEof        = false;
        $errors       = [];

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
            if (!$tanggal || !strtotime($tanggal)) {
                $rowsSkipped++;
                continue;
            }

            $batch[] = [
                'import_job_id'              => $job->id,
                'tanggal'                    => $tanggal,
                'opsel'                      => trim($cols[1]),
                'kategori'                   => trim($cols[2]),
                'kode_origin_provinsi'       => trim($cols[3]),
                'origin_provinsi'            => trim($cols[4]),
                'kode_origin_kabupaten_kota' => trim($cols[5]),
                'origin_kabupaten_kota'      => trim($cols[6]),
                'kode_dest_provinsi'         => trim($cols[7]),
                'dest_provinsi'              => trim($cols[8]),
                'kode_dest_kabupaten_kota'   => trim($cols[9]),
                'dest_kabupaten_kota'        => trim($cols[10]),
                'kode_origin_simpul'         => trim($cols[11] ?? ''),
                'origin_simpul'              => trim($cols[12] ?? ''),
                'kode_dest_simpul'           => trim($cols[13] ?? ''),
                'dest_simpul'                => trim($cols[14] ?? ''),
                'kode_moda'                  => trim($cols[15] ?? ''),
                'moda'                       => trim($cols[16] ?? ''),
                'total'                      => (int) trim($cols[17] ?? '0'),
                'is_forecast'                => $isForecast,
                'created_at'                 => $now,
                'updated_at'                 => $now,
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
        if (!empty($batch)) {
            $this->insertBatch($batch, $job, $errors);
        }

        $fileSize   = filesize($path);
        $percentage = $fileSize > 0 ? min(round(($newOffset / $fileSize) * 100), 99) : 100;

        $job->refresh();

        // Selesai (EOF)
        if ($isEof) {
            $finalStatus = empty($errors) ? 'completed' : 'completed_with_errors';
            $job->update([
                'status'        => $finalStatus,
                'progress'      => 100,
                'total_rows'    => $job->processed_rows,
                'error_message' => !empty($errors) ? implode(' | ', array_slice($errors, 0, 5)) : null,
            ]);

            return response()->json([
                'status'         => 'completed',
                'offset'         => $newOffset,
                'rows_processed' => $job->processed_rows,
                'rows_skipped'   => $rowsSkipped,
                'percent'        => 100,
                'errors'         => $errors,
            ]);
        }

        // Masih ada data (progress)
        $job->update(['status' => 'processing', 'progress' => $percentage]);

        return response()->json([
            'status'         => 'progress',
            'offset'         => $newOffset,
            'rows_processed' => $rowsInChunk,
            'rows_skipped'   => $rowsSkipped,
            'percent'        => $percentage,
            'errors'         => $errors,
        ]);
    }

    /**
     * Insert batch ke raw_mpd_data, fallback row-by-row jika batch gagal
     */
    private function insertBatch(array $batch, ImportJob $job, array &$errors): void
    {
        try {
            DB::table('raw_mpd_data')->insert($batch);
            $job->increment('processed_rows', count($batch));
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
            Log::error("Batch insert failed: " . $e->getMessage());

            // Fallback: insert satu per satu
            $saved = 0;
            foreach ($batch as $row) {
                try {
                    DB::table('raw_mpd_data')->insert($row);
                    $saved++;
                } catch (\Exception $rowErr) {
                    Log::warning("Row failed: " . $rowErr->getMessage());
                }
            }
            if ($saved > 0) {
                $job->increment('processed_rows', $saved);
            }
        }
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
        $summary   = $this->getSummary();

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

        $data = $query->paginate(50)->withQueryString();

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
            if (!$job) {
                return response()->json(['status' => 'completed', 'deleted' => 0]);
            }

            // Hapus data dari raw_mpd_data berdasarkan import_job_id
            $deleted = DB::table('raw_mpd_data')
                ->where('import_job_id', $job->id)
                ->take(25000)
                ->delete();

            // Masih ada baris yang perlu dihapus
            if ($deleted > 0) {
                return response()->json([
                    'status'  => 'progress',
                    'deleted' => $deleted,
                ]);
            }

            // Semua data terhapus → hapus file CSV dan record ImportJob
            $originalName = $job->original_filename ?? $job->filename;
            $filePath = 'mpd_uploads/' . $job->filename;
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            $job->delete();

            // Catat log aktivitas
            ActivityLog::log('Delete Import', $originalName, 'Success', "Import job #{$id} berhasil dihapus");

            return response()->json(['status' => 'completed', 'deleted' => 0]);
        } catch (\Exception $e) {
            Log::error("Delete error: " . $e->getMessage());
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
        try {
            return [
                'total_rows'    => (int) DB::table('raw_mpd_data')->count(),
                'total_uploads' => ImportJob::whereIn('status', ['completed', 'completed_with_errors'])->count(),
                'by_opsel'      => DB::table('raw_mpd_data')
                    ->select('opsel', DB::raw('COUNT(*) as total'))
                    ->groupBy('opsel')
                    ->pluck('total', 'opsel')
                    ->toArray(),
                'latest_date'   => DB::table('raw_mpd_data')->max('tanggal'),
            ];
        } catch (\Exception $e) {
            return ['total_rows' => 0, 'total_uploads' => 0, 'by_opsel' => [], 'latest_date' => null];
        }
    }

    /**
     * Resolve path file upload (cek beberapa kemungkinan lokasi storage)
     */
    private function resolveFilePath(string $storagePath): ?string
    {
        $paths = [
            Storage::disk('local')->path($storagePath),
            storage_path('app/private/' . $storagePath),
            storage_path('app/' . $storagePath),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        Log::error("File not found. Tried: " . implode(', ', $paths));
        return null;
    }
}
