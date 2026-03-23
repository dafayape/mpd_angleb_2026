<?php

namespace App\Jobs\Mpd;

use App\Actions\Mpd\ValidateCsvAction;
use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Background Import Job: Validates + Imports CSV → raw_mpd_data, then dispatches ETL.
 *
 * Alur lengkap dalam 1 job (tanpa HTTP request berulang):
 * 1. Validasi header CSV (cepat, <1 detik)
 * 2. Import seluruh file via streaming UPSERT (batch 10K rows, memory <64MB)
 * 3. Dispatch TransformRawToSpatialJob ke queue untuk ETL
 *
 * ShouldBeUnique: Hanya 1 import per import_job_id.
 */
class ProcessMpdImportJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 7200; // 2 jam max (file besar)

    public int $tries = 2;

    public array $backoff = [60, 300]; // retry: 1 min, 5 min

    public function __construct(
        private readonly int $importJobId
    ) {}

    public function uniqueId(): string
    {
        return 'import_'.$this->importJobId;
    }

    public function handle(): void
    {
        $job = ImportJob::find($this->importJobId);
        if (! $job) {
            Log::error("[Import] ImportJob #{$this->importJobId} not found");

            return;
        }

        $filePath = storage_path('app/mpd_uploads/'.$job->filename);
        if (! file_exists($filePath)) {
            $job->update([
                'status' => 'failed',
                'status_file' => 'failed',
                'error_message' => 'File tidak ditemukan di storage.',
            ]);
            Log::error("[Import] File not found: {$filePath}");

            return;
        }

        // ═══════════════════════════════════════════════════════
        // PHASE 1: Quick Header Validation (<1 detik)
        // ═══════════════════════════════════════════════════════
        $job->update(['status' => 'validating', 'status_file' => 'validating']);
        Log::info("[Import] Phase 1: Validating CSV for job #{$this->importJobId}");

        try {
            $validator = new ValidateCsvAction;
            $validationResult = $validator->execute($filePath, $job->opsel, $job->tanggal_data);

            if (! $validationResult['is_valid']) {
                $errorMsg = $validationResult['error'] ?? 'CSV validation failed.';
                if (isset($validationResult['header']) && ! $validationResult['header']['valid']) {
                    $errorMsg = 'Header error: '.($validationResult['header']['message'] ?? 'Invalid Format Header CSV.');
                } elseif (! empty($validationResult['opsel_mismatch'])) {
                    $errorMsg = 'OPSEL Mismatch: Data file berisi '.implode(', ', $validationResult['opsel_mismatch']['found_in_csv']).' tetapi dipilih '.$validationResult['opsel_mismatch']['selected'].'.';
                } elseif (! empty($validationResult['tanggal_mismatch'])) {
                    $errorMsg = 'Tanggal Mismatch: Data file berisi tanggal '.implode(', ', $validationResult['tanggal_mismatch']['found_in_csv']).' tetapi dipilih '.$validationResult['tanggal_mismatch']['selected'].'.';
                } elseif (! empty($validationResult['row_errors'])) {
                    $errorMsg = 'Ditemukan '.($validationResult['summary']['rows_with_errors'] ?? count($validationResult['row_errors'])).' baris error pada isi CSV.\\n';
                    foreach (array_slice($validationResult['row_errors'], 0, 10) as $err) {
                        $issueStrings = array_map(fn ($i) => ($i['field'] ? $i['field'].': ' : '').$i['detail'], $err['issues']);
                        $errorMsg .= 'Baris '.$err['row'].': '.implode(', ', $issueStrings).'\\n';
                    }
                    if (($validationResult['summary']['rows_with_errors'] ?? 0) > 10) {
                        $errorMsg .= '...(dan error baris lainnya)\\n';
                    }
                }

                $job->update([
                    'status' => 'validation_failed',
                    'status_file' => 'failed',
                    'error_message' => $errorMsg,
                    'metadata' => array_merge($job->metadata ?? [], ['validation' => $validationResult]),
                ]);
                Log::warning("[Import] Validation failed for job #{$this->importJobId}: {$errorMsg}");

                return;
            }

            // Simpan jumlah baris dari validasi
            $totalRows = $validationResult['summary']['total_data_rows'] ?? 0;
            $job->update([
                'status' => 'validated',
                'status_file' => 'validated',
                'total_rows' => $totalRows,
                'metadata' => array_merge($job->metadata ?? [], [
                    'validation' => [
                        'rows_checked' => $validationResult['summary']['rows_checked'] ?? 0,
                        'rows_with_errors' => $validationResult['summary']['rows_with_errors'] ?? 0,
                    ],
                ]),
            ]);
            Log::info("[Import] Validation OK: {$totalRows} rows for job #{$this->importJobId}");
        } catch (\Throwable $e) {
            $job->update([
                'status' => 'validation_failed',
                'status_file' => 'failed',
                'error_message' => 'Validation error: '.$e->getMessage(),
            ]);
            Log::error('[Import] Validation exception: '.$e->getMessage());
            throw $e;
        }

        // ═══════════════════════════════════════════════════════
        // PHASE 2: Streaming Import (batch 10K, memory <64MB)
        // ═══════════════════════════════════════════════════════
        $job->update(['status' => 'processing', 'status_file' => 'importing', 'progress' => 0]);
        Log::info("[Import] Phase 2: Importing CSV for job #{$this->importJobId}");

        $isForecast = $job->kategori === 'FORECAST';
        $delimiter = ';';
        $batchSize = 1000; // Dikurangi lagi untuk kestabilan parameter pdo
        $batch = [];
        $processedRows = 0;
        $skippedRows = 0;
        $timestamp = now();
        $totalRows = $job->total_rows ?: 1;

        $headers = [
            'TANGGAL', 'OPSEL', 'KATEGORI',
            'KODE_ORIGIN_PROVINSI', 'ORIGIN_PROVINSI',
            'KODE_ORIGIN_KABUPATEN_KOTA', 'ORIGIN_KABUPATEN_KOTA',
            'KODE_DEST_PROVINSI', 'DEST_PROVINSI',
            'KODE_DEST_KABUPATEN_KOTA', 'DEST_KABUPATEN_KOTA',
            'KODE_ORIGIN_SIMPUL', 'ORIGIN_SIMPUL',
            'KODE_DEST_SIMPUL', 'DEST_SIMPUL',
            'KODE_MODA', 'MODA', 'TOTAL',
        ];
        $expectedCount = count($headers);

        try {
            DB::statement('SET synchronous_commit TO OFF');
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \RuntimeException("Cannot open file: {$filePath}");
            }

            // Skip BOM
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                fseek($handle, 0);
            }

            // Skip header line
            fgets($handle);

            $rowsRead = 0;
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                $cols = str_getcsv($line, $delimiter);

                if (count($cols) < $expectedCount) {
                    $skippedRows++;
                    continue;
                }

                $rowsRead++;

                // Normalize Date for DB (must be YYYY-MM-DD)
                $rawTanggal = trim($cols[0] ?? '');
                $normalizedTanggal = str_replace('/', '-', $rawTanggal);
                $dbTanggal = date('Y-m-d', strtotime($normalizedTanggal));

                // ═══════════════════════════════════════════════════════
                // DATA LOGIC: REAL keep values, FORECAST must be empty ('')
                // ═══════════════════════════════════════════════════════
                $originSimpulCode = trim($cols[11] ?? '');
                $originSimpulName = trim($cols[12] ?? '');
                $destSimpulCode   = trim($cols[13] ?? '');
                $destSimpulName   = trim($cols[14] ?? '');
                $modaCode         = trim($cols[15] ?? '');
                $modaName         = trim($cols[16] ?? '');

                // Auto-Map anomali K ke A (Mobil) sesuai request (harden case dan quote jika ada)
                if (trim(strtoupper($modaCode), " \t\n\r\0\x0B\"'") === 'K') {
                    $modaCode = 'A';
                    $modaName = 'Mobil Pribadi';
                }

                // HANYA jika FORECAST maka kita paksa kosong. 
                // Jika REAL (is_forecast false), maka tetap gunakan nilai di atas.
                if ($isForecast) {
                    $originSimpulCode = '';
                    $originSimpulName = '';
                    $destSimpulCode   = '';
                    $destSimpulName   = '';
                    $modaCode         = '';
                    $modaName         = '';
                }

                // Sanitize Category: Ensure only ORANG or PERGERAKAN
                $dataKategori = strtoupper(trim($cols[2] ?? ''));
                if (!in_array($dataKategori, ['ORANG', 'PERGERAKAN'])) {
                    $dataKategori = 'PERGERAKAN';
                }



                // ═══════════════════════════════════════════════════════
                // DEDUPLICATION KEY (Standardized)
                // ═══════════════════════════════════════════════════════
                $key = "{$dbTanggal}|{$cols[1]}|{$dataKategori}|{$cols[5]}|{$cols[9]}|{$originSimpulCode}|{$destSimpulCode}|{$modaCode}|" . 
                       ($isForecast ? '1' : '0');

                if (isset($batch[$key])) {
                    $batch[$key]['total'] += (int) $cols[17];
                } else {
                    $batch[$key] = [
                        'tanggal' => $dbTanggal,
                        'opsel' => $cols[1],
                        'kategori' => $dataKategori, 
                        'kode_origin_provinsi' => $cols[3],
                        'origin_provinsi' => $cols[4],
                        'kode_origin_kabupaten_kota' => $cols[5],
                        'origin_kabupaten_kota' => $cols[6],
                        'kode_dest_provinsi' => $cols[7],
                        'dest_provinsi' => $cols[8],
                        'kode_dest_kabupaten_kota' => $cols[9],
                        'dest_kabupaten_kota' => $cols[10],
                        'kode_origin_simpul' => $originSimpulCode,
                        'origin_simpul' => $originSimpulName,
                        'kode_dest_simpul' => $destSimpulCode,
                        'dest_simpul' => $destSimpulName,
                        'kode_moda' => $modaCode,
                        'moda' => $modaName,
                        'total' => (int) $cols[17],
                        'is_forecast' => $isForecast,
                        'import_job_id' => $this->importJobId,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                if (count($batch) >= $batchSize) {
                    $this->upsertBatch(array_values($batch));
                    $processedRows += count($batch);
                    $batch = [];

                    // Update progress
                    $percent = min(99, (int) round(($rowsRead / $totalRows) * 100));
                    $job->update([
                        'processed_rows' => $rowsRead,
                        'skipped_rows' => $skippedRows,
                        'progress' => $percent,
                    ]);


                    if (memory_get_usage() > 64 * 1024 * 1024) {
                        gc_collect_cycles();
                    }
                }
            }

            // Flush remaining batch
            if (! empty($batch)) {
                $this->upsertBatch(array_values($batch));
                $processedRows += count($batch);
            }


            fclose($handle);

            // Final update: file import selesai
            $job->update([
                'status' => 'completed',
                'status_file' => 'completed',
                'progress' => 100,
                'total_rows' => $processedRows,
                'processed_rows' => $processedRows,
                'skipped_rows' => $skippedRows,
            ]);
            Log::info("[Import] Phase 2 complete: {$processedRows} rows imported, {$skippedRows} skipped for job #{$this->importJobId}");

        } catch (\Throwable $e) {
            $job->update([
                'status' => 'failed',
                'status_file' => 'failed',
                'processed_rows' => $processedRows,
                'skipped_rows' => $skippedRows,
                'error_message' => 'Import error: '.$e->getMessage(),
            ]);
            Log::error('[Import] Phase 2 failed: '.$e->getMessage());
            throw $e;
        }

        // ═══════════════════════════════════════════════════════
        // PHASE 3: Auto-dispatch ETL ke Queue
        // ═══════════════════════════════════════════════════════
        try {
            TransformRawToSpatialJob::dispatch($this->importJobId);
            $job->update(['status_etl' => 'queued']);
            Log::info("[Import] Phase 3: ETL dispatched to queue for job #{$this->importJobId}");
        } catch (\Throwable $e) {
            $job->update(['status_etl' => 'failed']);
            Log::error('[Import] ETL dispatch failed: '.$e->getMessage());
        }
    }

    /**
     * UPSERT batch ke raw_mpd_data.
     * Anti-duplikasi: jika unique key match, update total + updated_at.
     */
    private function upsertBatch(array $batch): void
    {
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
    }

    /**
     * Handle job failure — update ImportJob status.
     */
    public function failed(\Throwable $exception): void
    {
        $job = ImportJob::find($this->importJobId);
        if ($job) {
            $job->update([
                'status' => 'failed',
                'status_file' => 'failed',
                'error_message' => 'Fatal: '.$exception->getMessage(),
            ]);
        }
        Log::error("[Import] Job #{$this->importJobId} FAILED: ".$exception->getMessage());
    }
}
