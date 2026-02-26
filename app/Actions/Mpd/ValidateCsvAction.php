<?php

namespace App\Actions\Mpd;

use Illuminate\Support\Facades\DB;

class ValidateCsvAction
{
    private array $expectedHeaders = [
        'TANGGAL', 'OPSEL', 'KATEGORI',
        'KODE_ORIGIN_PROVINSI', 'ORIGIN_PROVINSI',
        'KODE_ORIGIN_KABUPATEN_KOTA', 'ORIGIN_KABUPATEN_KOTA',
        'KODE_DEST_PROVINSI', 'DEST_PROVINSI',
        'KODE_DEST_KABUPATEN_KOTA', 'DEST_KABUPATEN_KOTA',
        'KODE_ORIGIN_SIMPUL', 'ORIGIN_SIMPUL',
        'KODE_DEST_SIMPUL', 'DEST_SIMPUL',
        'KODE_MODA', 'MODA', 'TOTAL',
    ];

    private const DELIMITER = ';';

    private const MAX_ERRORS_DISPLAYED = 50;

    private const VALID_OPSEL = ['TSEL', 'IOH', 'XL'];

    private const VALID_KATEGORI = ['REAL', 'FORECAST'];

    // Cached reference data (loaded once from DB)
    private array $validProvinces = [];

    private array $validCities = [];

    private array $validNodes = [];

    private array $validModes = [];

    /**
     * Validate a CSV file against the MPD schema and database reference tables.
     * Validates ALL rows, not just a sample.
     *
     * @param  string  $filePath  Absolute path to the CSV file
     * @return array Validation result with is_valid, header, rows, summary
     */
    public function execute(string $filePath): array
    {
        $handle = @fopen($filePath, 'r');
        if ($handle === false) {
            return [
                'is_valid' => false,
                'error' => 'Gagal membuka file CSV.',
            ];
        }

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            fseek($handle, 0);
        }

        // --- 1. Header Validation ---
        $headerLine = fgets($handle);
        if ($headerLine === false) {
            fclose($handle);

            return [
                'is_valid' => false,
                'error' => 'File CSV kosong.',
            ];
        }

        $headerResult = $this->validateHeader(trim(str_replace("\r", '', $headerLine)));

        // --- 2. Load reference data from DB (single query per table) ---
        $this->loadReferenceData();

        // --- 3. Validate ALL rows ---
        $rowErrors = [];
        $rowsChecked = 0;
        $totalErrorRows = 0;

        while (($line = fgets($handle)) !== false) {
            $line = trim(str_replace("\r", '', $line));
            if ($line === '') {
                continue;
            }

            $rowNum = $rowsChecked + 2; // +2: 1-based, skip header
            $cols = str_getcsv($line, self::DELIMITER);
            $issues = $this->validateRow($cols, $rowNum);

            if (! empty($issues)) {
                $totalErrorRows++;
                // Simpan detail error sampai batas MAX untuk UI
                if (count($rowErrors) < self::MAX_ERRORS_DISPLAYED) {
                    $rowErrors[] = [
                        'row' => $rowNum,
                        'issues' => $issues,
                    ];
                }
            }

            $rowsChecked++;
        }

        fclose($handle);

        // --- 4. Compile result ---
        $isValid = $headerResult['valid'] && $totalErrorRows === 0;

        return [
            'is_valid' => $isValid,
            'file' => [
                'name' => basename($filePath),
                'total_data_rows' => $rowsChecked,
                'rows_checked' => $rowsChecked,
            ],
            'header' => $headerResult,
            'row_errors' => $rowErrors,
            'summary' => [
                'header_ok' => $headerResult['valid'],
                'rows_checked' => $rowsChecked,
                'rows_with_errors' => $totalErrorRows,
                'total_data_rows' => $rowsChecked,
                'errors_truncated' => $totalErrorRows > self::MAX_ERRORS_DISPLAYED,
            ],
        ];
    }

    /**
     * Validate the header line against expected columns.
     */
    private function validateHeader(string $headerLine): array
    {
        $actualCols = array_map('trim', explode(self::DELIMITER, $headerLine));

        $missing = array_values(array_diff($this->expectedHeaders, $actualCols));
        $extra = array_values(array_diff($actualCols, $this->expectedHeaders));

        $valid = empty($missing) && empty($extra) && count($actualCols) === count($this->expectedHeaders);

        // Check column order
        $orderCorrect = $valid;
        if ($valid) {
            foreach ($this->expectedHeaders as $i => $expected) {
                if ($actualCols[$i] !== $expected) {
                    $orderCorrect = false;
                    break;
                }
            }
        }

        return [
            'valid' => $valid && $orderCorrect,
            'expected_count' => count($this->expectedHeaders),
            'actual_count' => count($actualCols),
            'missing' => $missing,
            'extra' => $extra,
            'order_correct' => $orderCorrect,
        ];
    }

    /**
     * Validate a single data row.
     */
    private function validateRow(array $cols, int $rowNum): array
    {
        $issues = [];

        // Column count check
        if (count($cols) !== count($this->expectedHeaders)) {
            $issues[] = [
                'field' => null,
                'type' => 'COLUMN_COUNT',
                'detail' => 'Diharapkan '.count($this->expectedHeaders).' kolom, ditemukan '.count($cols),
            ];

            return $issues; // Can't validate further if column count is wrong
        }

        $data = array_combine($this->expectedHeaders, $cols);

        // TANGGAL: format YYYY-MM-DD dan valid date
        $tanggal = trim($data['TANGGAL']);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || ! strtotime($tanggal)) {
            $issues[] = [
                'field' => 'TANGGAL',
                'type' => 'INVALID_DATE',
                'detail' => "Format harus YYYY-MM-DD, ditemukan: \"{$tanggal}\"",
            ];
        }

        // OPSEL: wajib dan harus salah satu dari TSEL/IOH/XL
        $opsel = trim($data['OPSEL']);
        if ($opsel === '') {
            $issues[] = [
                'field' => 'OPSEL',
                'type' => 'EMPTY_REQUIRED',
                'detail' => 'OPSEL tidak boleh kosong',
            ];
        } elseif (! in_array($opsel, self::VALID_OPSEL, true)) {
            $issues[] = [
                'field' => 'OPSEL',
                'type' => 'INVALID_VALUE',
                'detail' => "Harus salah satu: TSEL, IOH, XL. Ditemukan: \"{$opsel}\"",
            ];
        }

        // KATEGORI: wajib dan harus REAL/FORECAST
        $kategori = trim($data['KATEGORI']);
        if ($kategori === '') {
            $issues[] = [
                'field' => 'KATEGORI',
                'type' => 'EMPTY_REQUIRED',
                'detail' => 'KATEGORI tidak boleh kosong',
            ];
        } elseif (! in_array($kategori, self::VALID_KATEGORI, true)) {
            $issues[] = [
                'field' => 'KATEGORI',
                'type' => 'INVALID_VALUE',
                'detail' => "Harus salah satu: REAL, FORECAST. Ditemukan: \"{$kategori}\"",
            ];
        }

        // TOTAL: harus numerik >= 0
        $total = trim($data['TOTAL']);
        if (! is_numeric($total) || (int) $total < 0) {
            $issues[] = [
                'field' => 'TOTAL',
                'type' => 'INVALID_NUMERIC',
                'detail' => "TOTAL harus angka >= 0, ditemukan: \"{$total}\"",
            ];
        }

        // --- Database Reference Checks ---

        // KODE_ORIGIN_PROVINSI & KODE_DEST_PROVINSI → ref_provinces
        foreach (['KODE_ORIGIN_PROVINSI', 'KODE_DEST_PROVINSI'] as $field) {
            $val = trim($data[$field]);
            if ($val !== '' && ! isset($this->validProvinces[$val])) {
                $issues[] = [
                    'field' => $field,
                    'type' => 'REF_NOT_FOUND',
                    'detail' => "Kode provinsi \"{$val}\" tidak terdaftar di ref_provinces",
                ];
            }
        }

        // KODE_ORIGIN_KABUPATEN_KOTA & KODE_DEST_KABUPATEN_KOTA → ref_cities
        foreach (['KODE_ORIGIN_KABUPATEN_KOTA', 'KODE_DEST_KABUPATEN_KOTA'] as $field) {
            $val = trim($data[$field]);
            if ($val !== '' && ! isset($this->validCities[$val])) {
                $issues[] = [
                    'field' => $field,
                    'type' => 'REF_NOT_FOUND',
                    'detail' => "Kode kabupaten/kota \"{$val}\" tidak terdaftar di ref_cities",
                ];
            }
        }

        // KODE_ORIGIN_SIMPUL & KODE_DEST_SIMPUL → ref_transport_nodes
        foreach (['KODE_ORIGIN_SIMPUL', 'KODE_DEST_SIMPUL'] as $field) {
            $val = trim($data[$field]);
            if ($val !== '' && ! isset($this->validNodes[$val])) {
                $issues[] = [
                    'field' => $field,
                    'type' => 'REF_NOT_FOUND',
                    'detail' => "Kode simpul \"{$val}\" tidak terdaftar di ref_transport_nodes",
                ];
            }
        }

        // KODE_MODA → ref_transport_modes
        $modaVal = trim($data['KODE_MODA']);
        if ($modaVal !== '' && ! isset($this->validModes[$modaVal])) {
            $issues[] = [
                'field' => 'KODE_MODA',
                'type' => 'REF_NOT_FOUND',
                'detail' => "Kode moda \"{$modaVal}\" tidak terdaftar di ref_transport_modes",
            ];
        }

        return $issues;
    }

    /**
     * Load all reference codes from database into memory for fast lookup.
     * Uses flip for O(1) isset() checks instead of in_array().
     */
    private function loadReferenceData(): void
    {
        $this->validProvinces = array_flip(
            DB::table('ref_provinces')->pluck('code')->toArray()
        );

        $this->validCities = array_flip(
            DB::table('ref_cities')->pluck('code')->toArray()
        );

        $this->validNodes = array_flip(
            DB::table('ref_transport_nodes')->pluck('code')->toArray()
        );

        $this->validModes = array_flip(
            DB::table('ref_transport_modes')->pluck('code')->toArray()
        );
    }
}
