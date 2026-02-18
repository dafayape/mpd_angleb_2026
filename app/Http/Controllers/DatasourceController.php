<?php

namespace App\Http\Controllers;

use App\Models\ImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $file->storeAs('mpd_uploads', $filename);

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

        Redis::set("mpd:import:{$job->id}:progress", json_encode([
            'percent' => 0,
            'rows'    => 0,
            'status'  => 'uploaded',
        ]));
        Redis::expire("mpd:import:{$job->id}:progress", 3600);

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
        $offset    = $request->input('offset', 0);
        $limit     = 5000;

        $job = ImportJob::find($historyId);

        if (!$job) {
            return response()->json(['status' => 'error', 'message' => 'Import job tidak ditemukan.'], 404);
        }

        $storagePath = 'mpd_uploads/' . $job->filename;

        if (!Storage::exists($storagePath)) {
            return response()->json(['status' => 'error', 'message' => 'File tidak ditemukan.'], 404);
        }

        $path = storage_path('app/private/' . $storagePath);

        if (!file_exists($path)) {
            $path = storage_path('app/' . $storagePath);
        }

        $handle = fopen($path, 'r');

        if (!$handle) {
            return response()->json(['status' => 'error', 'message' => 'Gagal membuka file.'], 500);
        }

        fseek($handle, $offset);

        if ($offset == 0) {
            fgetcsv($handle, 0, ';');
            $offset = ftell($handle);
        }

        $this->ensurePartition($job->tanggal_data);

        $batch = [];
        $rowsProcessed = 0;
        $isEof = false;
        $isForecast = ($job->kategori === 'FORECAST');
        $now = now()->toDateTimeString();

        while ($rowsProcessed < $limit) {
            $data = fgetcsv($handle, 0, ';');

            if ($data === false) {
                $isEof = true;
                break;
            }

            if (count($data) < 17) {
                continue;
            }

            $batch[] = [
                'tanggal'                    => $data[0],
                'opsel'                      => $data[1],
                'kategori'                   => $data[2],
                'kode_origin_provinsi'       => $data[3],
                'origin_provinsi'            => $data[4],
                'kode_origin_kabupaten_kota' => $data[5],
                'origin_kabupaten_kota'      => $data[6],
                'kode_dest_provinsi'         => $data[7],
                'dest_provinsi'              => $data[8],
                'kode_dest_kabupaten_kota'   => $data[9],
                'dest_kabupaten_kota'        => $data[10],
                'kode_origin_simpul'         => $data[11] ?: '',
                'origin_simpul'              => $data[12] ?: '',
                'kode_dest_simpul'           => $data[13] ?: '',
                'dest_simpul'                => $data[14] ?: '',
                'kode_moda'                  => $data[15] ?: '',
                'moda'                       => $data[16] ?? '',
                'total'                      => isset($data[17]) ? (int) $data[17] : 0,
                'is_forecast'                => $isForecast,
                'created_at'                 => $now,
                'updated_at'                 => $now,
            ];

            if (count($batch) >= 1000) {
                DB::table('raw_mpd_data')->insert($batch);
                $job->increment('processed_rows', count($batch));
                $batch = [];
            }

            $rowsProcessed++;
        }

        $newOffset = ftell($handle);
        fclose($handle);

        if (!empty($batch)) {
            DB::table('raw_mpd_data')->insert($batch);
            $job->increment('processed_rows', count($batch));
        }

        $fileSize = filesize($path);
        $percentage = $fileSize > 0 ? min(round(($newOffset / $fileSize) * 100), 99) : 100;

        if ($isEof) {
            $job->update([
                'status'     => 'completed',
                'progress'   => 100,
                'total_rows' => $job->processed_rows,
            ]);

            Redis::set("mpd:import:{$job->id}:progress", json_encode([
                'percent' => 100,
                'rows'    => $job->processed_rows,
                'status'  => 'completed',
            ]));
            Redis::expire("mpd:import:{$job->id}:progress", 300);

            Redis::del('mpd:summary:stats');

            return response()->json([
                'status'         => 'completed',
                'offset'         => $newOffset,
                'rows_processed' => $rowsProcessed,
                'percent'        => 100,
            ]);
        }

        $job->update(['status' => 'processing', 'progress' => $percentage]);

        Redis::set("mpd:import:{$job->id}:progress", json_encode([
            'percent' => $percentage,
            'rows'    => $job->processed_rows,
            'status'  => 'processing',
        ]));
        Redis::expire("mpd:import:{$job->id}:progress", 3600);

        return response()->json([
            'status'         => 'progress',
            'offset'         => $newOffset,
            'rows_processed' => $rowsProcessed,
            'percent'        => $percentage,
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

        if ($request->filled('is_forecast')) {
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

            $tanggalData = $job->tanggal_data;
            $opsel = $job->opsel;

            $deleted = 0;

            if ($tanggalData && $opsel) {
                $deleted = DB::table('raw_mpd_data')
                    ->where('tanggal', $tanggalData)
                    ->where('opsel', $opsel)
                    ->where('is_forecast', $job->kategori === 'FORECAST')
                    ->limit(25000)
                    ->delete();
            } else {
                $deleted = DB::table('raw_mpd_data')
                    ->whereRaw("ctid IN (SELECT ctid FROM raw_mpd_data LIMIT 25000)")
                    ->delete();
            }

            if ($deleted > 0) {
                return response()->json([
                    'status'  => 'progress',
                    'deleted' => $deleted,
                ]);
            }

            if (Storage::exists('mpd_uploads/' . $job->filename)) {
                Storage::delete('mpd_uploads/' . $job->filename);
            }

            $job->delete();

            Redis::del('mpd:summary:stats');

            return response()->json(['status' => 'completed', 'deleted' => 0]);
        } catch (\Exception $e) {
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
        $cached = Redis::get('mpd:summary:stats');

        if ($cached) {
            return json_decode($cached, true);
        }

        $stats = [
            'total_rows'    => (int) DB::table('raw_mpd_data')->count(),
            'total_uploads' => ImportJob::where('status', 'completed')->count(),
            'by_opsel'      => DB::table('raw_mpd_data')
                ->select('opsel', DB::raw('COUNT(*) as total'))
                ->groupBy('opsel')
                ->pluck('total', 'opsel')
                ->toArray(),
            'latest_date'   => DB::table('raw_mpd_data')->max('tanggal'),
        ];

        Redis::set('mpd:summary:stats', json_encode($stats));
        Redis::expire('mpd:summary:stats', 300);

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
}
