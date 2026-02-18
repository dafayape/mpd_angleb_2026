<?php

namespace App\Http\Controllers;

use App\Models\ImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class DatasourceController extends Controller
{
    public function upload()
    {
        return view('datasource.upload');
    }

    public function storeUpload(Request $request)
    {
        $request->validate([
            'opsel'        => 'required|string|in:TSEL,IOH,XL',
            'kategori'     => 'required|string|in:REAL,FORECAST',
            'tanggal_data' => 'required|date',
            'file'         => 'required|file|mimes:csv,txt|max:1048576',
        ]);

        if (!$request->hasFile('file')) {
            return response()->json(['status' => 'error', 'message' => 'File tidak ditemukan.'], 400);
        }

        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        $filename = time() . '_' . $originalFilename;
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

        try {
            Redis::set("mpd:import:{$job->id}:progress", json_encode([
                'percent' => 0, 'rows' => 0, 'status' => 'uploaded',
            ]));
            Redis::expire("mpd:import:{$job->id}:progress", 3600);
        } catch (\Exception $e) {
            Log::warning("Redis not available: " . $e->getMessage());
        }

        return response()->json([
            'status'     => 'success',
            'history_id' => $job->id,
            'message'    => 'File berhasil diupload. Memulai pemrosesan...',
            'file_size'  => $file->getSize(),
        ]);
    }

    public function processChunk(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');
        DB::disableQueryLog();

        $historyId = $request->input('history_id');
        $offset    = (int) $request->input('offset', 0);
        $limit     = 5000;

        $job = ImportJob::find($historyId);

        if (!$job) {
            return response()->json(['status' => 'error', 'message' => 'Import job tidak ditemukan.'], 404);
        }

        $storagePath = 'mpd_uploads/' . $job->filename;
        $path = $this->resolveFilePath($storagePath);

        if (!$path) {
            return response()->json([
                'status'  => 'error',
                'message' => 'File tidak ditemukan di storage. Path: ' . $storagePath,
            ], 404);
        }

        $handle = fopen($path, 'r');

        if (!$handle) {
            return response()->json(['status' => 'error', 'message' => 'Gagal membuka file: ' . $path], 500);
        }

        // Handle BOM (Byte Order Mark) if present
        if ($offset === 0) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                fseek($handle, 0); // Not a BOM, rewind
            }
            // Read and skip header line
            $headerLine = fgets($handle);
            Log::info("CSV Header: " . trim($headerLine));
        } else {
            fseek($handle, $offset);
        }

        // Check if is_forecast column exists in raw_mpd_data
        $hasIsForecast = $this->tableHasColumn('raw_mpd_data', 'is_forecast');

        $createdPartitions = [];
        $batch = [];
        $rowsProcessed = 0;
        $rowsSkipped = 0;
        $isEof = false;
        $isForecast = ($job->kategori === 'FORECAST');
        $now = now()->toDateTimeString();
        $errors = [];

        while ($rowsProcessed < $limit) {
            $line = fgets($handle);

            if ($line === false) {
                $isEof = true;
                break;
            }

            // Strip \r\n and trim whitespace
            $line = trim(str_replace(["\r\n", "\r"], "\n", $line));
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $data = str_getcsv($line, ';');

            if (count($data) < 18) {
                $rowsSkipped++;
                if ($rowsSkipped <= 5) {
                    Log::warning("Skipped row (only " . count($data) . " columns): " . substr($line, 0, 200));
                }
                continue;
            }

            $tanggal = trim($data[0]);

            if (!$tanggal || !strtotime($tanggal)) {
                $rowsSkipped++;
                Log::warning("Skipped row (invalid date): " . substr($line, 0, 100));
                continue;
            }

            // Ensure partition exists for this month
            $monthKey = date('Y_m', strtotime($tanggal));
            if (!isset($createdPartitions[$monthKey])) {
                try {
                    $this->ensurePartition($tanggal);
                    $createdPartitions[$monthKey] = true;
                    Log::info("Partition created/verified: raw_mpd_data_{$monthKey}");
                } catch (\Exception $e) {
                    $errors[] = "Partition error for {$monthKey}: " . $e->getMessage();
                    Log::error("Partition creation failed for {$tanggal}: " . $e->getMessage());
                    $createdPartitions[$monthKey] = true; // Don't retry
                }
            }

            $row = [
                'tanggal'                    => $tanggal,
                'opsel'                      => trim($data[1]),
                'kategori'                   => trim($data[2]),
                'kode_origin_provinsi'       => trim($data[3]),
                'origin_provinsi'            => trim($data[4]),
                'kode_origin_kabupaten_kota' => trim($data[5]),
                'origin_kabupaten_kota'      => trim($data[6]),
                'kode_dest_provinsi'         => trim($data[7]),
                'dest_provinsi'              => trim($data[8]),
                'kode_dest_kabupaten_kota'   => trim($data[9]),
                'dest_kabupaten_kota'        => trim($data[10]),
                'kode_origin_simpul'         => trim($data[11] ?? ''),
                'origin_simpul'              => trim($data[12] ?? ''),
                'kode_dest_simpul'           => trim($data[13] ?? ''),
                'dest_simpul'                => trim($data[14] ?? ''),
                'kode_moda'                  => trim($data[15] ?? ''),
                'moda'                       => trim($data[16] ?? ''),
                'total'                      => isset($data[17]) ? (int) trim($data[17]) : 0,
                'created_at'                 => $now,
                'updated_at'                 => $now,
            ];

            // Only include is_forecast if column exists
            if ($hasIsForecast) {
                $row['is_forecast'] = $isForecast;
            }

            $batch[] = $row;

            if (count($batch) >= 1000) {
                try {
                    DB::table('raw_mpd_data')->insert($batch);
                    $job->increment('processed_rows', count($batch));
                } catch (\Exception $e) {
                    $errorMsg = "Batch insert error at row ~{$rowsProcessed}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error($errorMsg);

                    // Try inserting one by one to find problematic rows
                    $savedCount = 0;
                    foreach ($batch as $singleRow) {
                        try {
                            DB::table('raw_mpd_data')->insert($singleRow);
                            $savedCount++;
                        } catch (\Exception $rowErr) {
                            Log::warning("Single row insert failed: " . $rowErr->getMessage() . " | Row: " . json_encode(array_slice($singleRow, 0, 5)));
                        }
                    }
                    if ($savedCount > 0) {
                        $job->increment('processed_rows', $savedCount);
                    }
                }
                $batch = [];
            }

            $rowsProcessed++;
        }

        $newOffset = ftell($handle);
        fclose($handle);

        // Insert remaining rows
        if (!empty($batch)) {
            try {
                DB::table('raw_mpd_data')->insert($batch);
                $job->increment('processed_rows', count($batch));
            } catch (\Exception $e) {
                $errorMsg = "Final batch insert error: " . $e->getMessage();
                $errors[] = $errorMsg;
                Log::error($errorMsg);

                // Fallback: one by one
                $savedCount = 0;
                foreach ($batch as $singleRow) {
                    try {
                        DB::table('raw_mpd_data')->insert($singleRow);
                        $savedCount++;
                    } catch (\Exception $rowErr) {
                        Log::warning("Single row insert failed: " . $rowErr->getMessage());
                    }
                }
                if ($savedCount > 0) {
                    $job->increment('processed_rows', $savedCount);
                }
            }
        }

        $fileSize = filesize($path);
        $percentage = $fileSize > 0 ? min(round(($newOffset / $fileSize) * 100), 99) : 100;

        $job->refresh();

        if ($isEof) {
            $finalStatus = empty($errors) ? 'completed' : 'completed_with_errors';
            $job->update([
                'status'        => $finalStatus,
                'progress'      => 100,
                'total_rows'    => $job->processed_rows,
                'error_message' => !empty($errors) ? implode(' | ', array_slice($errors, 0, 5)) : null,
            ]);

            try {
                Redis::set("mpd:import:{$job->id}:progress", json_encode([
                    'percent' => 100, 'rows' => $job->processed_rows, 'status' => $finalStatus,
                ]));
                Redis::expire("mpd:import:{$job->id}:progress", 300);
                Redis::del('mpd:summary:stats');
            } catch (\Exception $e) {
                // Redis not available, continue
            }

            return response()->json([
                'status'         => 'completed',
                'offset'         => $newOffset,
                'rows_processed' => $job->processed_rows,
                'rows_skipped'   => $rowsSkipped,
                'percent'        => 100,
                'errors'         => $errors,
            ]);
        }

        $job->update(['status' => 'processing', 'progress' => $percentage]);

        try {
            Redis::set("mpd:import:{$job->id}:progress", json_encode([
                'percent' => $percentage, 'rows' => $job->processed_rows, 'status' => 'processing',
            ]));
            Redis::expire("mpd:import:{$job->id}:progress", 3600);
        } catch (\Exception $e) {
            // Redis not available, continue
        }

        return response()->json([
            'status'         => 'progress',
            'offset'         => $newOffset,
            'rows_processed' => $rowsProcessed,
            'rows_skipped'   => $rowsSkipped,
            'percent'        => $percentage,
            'errors'         => $errors,
        ]);
    }

    public function history(Request $request)
    {
        $query = ImportJob::orderBy('created_at', 'desc');

        if ($request->filled('opsel')) {
            $query->where('opsel', $request->opsel);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_data', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('tanggal_data', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('tanggal_data', '<=', $request->end_date);
        }

        $histories = $query->paginate(10)->withQueryString();

        $summary = $this->getCachedSummary();

        return view('datasource.history', compact('histories', 'summary'));
    }

    public function rawData(Request $request)
    {
        $query = DB::table('raw_mpd_data')->orderBy('tanggal', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->where('tanggal', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->where('tanggal', '<=', $request->end_date);
        }

        if ($request->filled('opsel')) {
            $query->where('opsel', $request->opsel);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $hasIsForecast = $this->tableHasColumn('raw_mpd_data', 'is_forecast');
        if ($hasIsForecast && $request->filled('is_forecast')) {
            $query->where('is_forecast', $request->is_forecast === '1');
        }

        $data = $query->paginate(50)->withQueryString();

        return view('datasource.raw', compact('data'));
    }

    public function destroyChunk($id)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        try {
            $job = ImportJob::find($id);

            if (!$job) {
                return response()->json(['status' => 'completed', 'deleted' => 0]);
            }

            $tanggalData = $job->tanggal_data ? \Carbon\Carbon::parse($job->tanggal_data)->format('Y-m-d') : null;
            $opsel = $job->opsel;

            $deleted = 0;
            $hasIsForecast = $this->tableHasColumn('raw_mpd_data', 'is_forecast');

            if ($tanggalData && $opsel) {
                if ($hasIsForecast) {
                    $isForecast = ($job->kategori === 'FORECAST');
                    $deleted = DB::affectingStatement("
                        DELETE FROM raw_mpd_data
                        WHERE ctid IN (
                            SELECT ctid FROM raw_mpd_data
                            WHERE tanggal = ?
                              AND opsel = ?
                              AND is_forecast = ?
                            LIMIT 25000
                        )
                    ", [$tanggalData, $opsel, $isForecast]);
                } else {
                    $deleted = DB::affectingStatement("
                        DELETE FROM raw_mpd_data
                        WHERE ctid IN (
                            SELECT ctid FROM raw_mpd_data
                            WHERE tanggal = ?
                              AND opsel = ?
                            LIMIT 25000
                        )
                    ", [$tanggalData, $opsel]);
                }
            } else {
                $deleted = DB::affectingStatement("
                    DELETE FROM raw_mpd_data
                    WHERE ctid IN (
                        SELECT ctid FROM raw_mpd_data LIMIT 25000
                    )
                ");
            }

            if ($deleted > 0) {
                return response()->json([
                    'status'  => 'progress',
                    'deleted' => $deleted,
                ]);
            }

            if (Storage::disk('local')->exists('mpd_uploads/' . $job->filename)) {
                Storage::disk('local')->delete('mpd_uploads/' . $job->filename);
            }

            $job->delete();

            try {
                Redis::del('mpd:summary:stats');
            } catch (\Exception $e) {
                // Redis not available
            }

            return response()->json(['status' => 'completed', 'deleted' => 0]);
        } catch (\Exception $e) {
            Log::error("Delete chunk error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function summary()
    {
        $stats = $this->getCachedSummary();

        return response()->json($stats);
    }

    private function getCachedSummary(): array
    {
        try {
            $cached = Redis::get('mpd:summary:stats');
            if ($cached) {
                return json_decode($cached, true);
            }
        } catch (\Exception $e) {
            // Redis not available, calculate directly
        }

        $stats = [
            'total_rows'    => (int) DB::table('raw_mpd_data')->count(),
            'total_uploads' => ImportJob::where('status', 'completed')->orWhere('status', 'completed_with_errors')->count(),
            'by_opsel'      => DB::table('raw_mpd_data')
                ->select('opsel', DB::raw('COUNT(*) as total'))
                ->groupBy('opsel')
                ->pluck('total', 'opsel')
                ->toArray(),
            'latest_date'   => DB::table('raw_mpd_data')->max('tanggal'),
        ];

        try {
            Redis::set('mpd:summary:stats', json_encode($stats));
            Redis::expire('mpd:summary:stats', 300);
        } catch (\Exception $e) {
            // Redis not available
        }

        return $stats;
    }

    private function ensurePartition(string $date): void
    {
        $month = date('Y_m', strtotime($date));
        $startOfMonth = date('Y-m-01', strtotime($date));
        $startOfNext = date('Y-m-01', strtotime($startOfMonth . ' +1 month'));
        $partitionName = "raw_mpd_data_{$month}";

        DB::statement("
            CREATE TABLE IF NOT EXISTS {$partitionName}
            PARTITION OF raw_mpd_data
            FOR VALUES FROM ('{$startOfMonth}') TO ('{$startOfNext}')
        ");
    }

    /**
     * Check if a table has a specific column
     */
    private function tableHasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = "{$table}.{$column}";

        if (!isset($cache[$key])) {
            try {
                $cache[$key] = DB::selectOne("
                    SELECT EXISTS (
                        SELECT 1 FROM information_schema.columns
                        WHERE table_name = ? AND column_name = ?
                    ) as exists
                ", [$table, $column])->exists;
            } catch (\Exception $e) {
                $cache[$key] = false;
            }
        }

        return $cache[$key];
    }

    private function resolveFilePath(string $storagePath): ?string
    {
        $candidates = [
            Storage::disk('local')->path($storagePath),
            storage_path('app/private/' . $storagePath),
            storage_path('app/' . $storagePath),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                Log::info("Resolved file path: {$path}");
                return $path;
            }
        }

        Log::error("File not found. Tried: " . implode(', ', $candidates));
        return null;
    }
}
