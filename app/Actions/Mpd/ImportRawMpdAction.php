<?php

namespace App\Actions\Mpd;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;

class ImportRawMpdAction
{
    public function execute(
        string $path,
        bool $isForecast = false,
        ?callable $onProgress = null,
        int $startByte = 0,
        ?int $endByte = null,
        string $jobId = ''
    ): void {
        DB::statement('SET synchronous_commit TO OFF');

        $handle = @fopen($path, 'r');
        if ($handle === false)
            die("Unable to open stream: {$path}");

        // optimize stream
        stream_set_chunk_size($handle, 8192);

        // Seek to start position
        if ($startByte > 0) {
            fseek($handle, $startByte);
            // If we seeked to the middle of the file, we likely landed in the middle of a line.
            // Discard the partial line (the previous chunk handled it).
            fgets($handle);
        } else {
            // First chunk: skip header
            fgetcsv($handle, 0, ';');
        }

        // Headers for mapping (Hardcoded or passed, assuming standard structure from SOP if seeking mid-file)
        // Since we might skip header in chunks > 0, we define standard headers based on SOP.
        $headers = [
            'TANGGAL',
            'OPSEL',
            'KATEGORI',
            'KODE_ORIGIN_PROVINSI',
            'ORIGIN_PROVINSI',
            'KODE_ORIGIN_KABUPATEN_KOTA',
            'ORIGIN_KABUPATEN_KOTA',
            'KODE_DEST_PROVINSI',
            'DEST_PROVINSI',
            'KODE_DEST_KABUPATEN_KOTA',
            'DEST_KABUPATEN_KOTA',
            'KODE_ORIGIN_SIMPUL',
            'ORIGIN_SIMPUL',
            'KODE_DEST_SIMPUL',
            'DEST_SIMPUL',
            'KODE_MODA',
            'MODA',
            'TOTAL'
        ];

        $batch = [];
        $batchSize = 10000;
        $timestamp = now();
        $rowCount = 0;
        $currentByte = ftell($handle);

        try {
            DB::beginTransaction();

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                // Check if we passed the endByte
                $currentByte = ftell($handle);
                if ($endByte !== null && $currentByte > $endByte) {
                    // We process this last row to finish the line, then stop.
                    // Actually fgetcsv reads the full line, so we are good.
                    // But we should check BEFORE adding if we are WAY past? 
                    // Standard logic: Process until ftell > endByte. 
                    // The overlaps are handled by "Discard partial line at start" logic of NEXT chunk.
                }

                if (count($row) !== count($headers)) {
                    continue; // Skip malformed
                }

                $data = array_combine($headers, $row);

                $batch[] = [
                    'tanggal' => $data['TANGGAL'],
                    'opsel' => $data['OPSEL'],
                    'kategori' => $data['KATEGORI'],
                    'kode_origin_kabupaten_kota' => $data['KODE_ORIGIN_KABUPATEN_KOTA'],
                    'kode_dest_kabupaten_kota' => $data['KODE_DEST_KABUPATEN_KOTA'],
                    'kode_origin_simpul' => $data['KODE_ORIGIN_SIMPUL'],
                    'kode_dest_simpul' => $data['KODE_DEST_SIMPUL'],
                    'kode_moda' => $data['KODE_MODA'],
                    'total' => (int) $data['TOTAL'],
                    'is_forecast' => $isForecast,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                    // We only store necessary columns for analytics/enrichment to save space
                ];

                if (count($batch) >= $batchSize) {
                    $this->upsertBatch($batch);
                    $rowCount += count($batch);

                    if ($onProgress) {
                        $onProgress($rowCount);
                    } elseif ($jobId) {
                        // Optimistic update via Redis Pipeline every 10k
                        Redis::pipeline(function ($pipe) use ($jobId, $rowCount) {
                            $pipe->incrby("import_job:{$jobId}:processed", 10000);
                        });
                    }

                    $batch = [];

                    if (memory_get_usage() > 64 * 1024 * 1024) {
                        gc_collect_cycles();
                    }
                }

                if ($endByte !== null && $currentByte > $endByte) {
                    break;
                }
            }

            if (!empty($batch)) {
                $this->upsertBatch($batch);
                $rowCount += count($batch);
                if ($jobId) {
                    Redis::incrby("import_job:{$jobId}:processed", count($batch));
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }
    }

    private function upsertBatch(array $batch): void
    {
        // Using DB::table for raw speed, bypassing Models
        DB::table('raw_mpd_data')->upsert(
            $batch,
            [
                'tanggal',
                'opsel',
                'kategori',
                'kode_origin_kabupaten_kota',
                'kode_dest_kabupaten_kota',
                'kode_origin_simpul',
                'kode_dest_simpul',
                'kode_moda',
                'is_forecast'
            ],
            ['total', 'updated_at']
        );
    }
}
