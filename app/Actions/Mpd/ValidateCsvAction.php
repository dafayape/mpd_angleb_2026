<?php

namespace App\Actions\Mpd;

use Illuminate\Support\Facades\DB;

class ValidateCsvAction
{
    private const DELIMITER = ';';
    private const MAX_ERRORS_DISPLAYED = 50;
    private const VALID_OPSEL = ['TSEL', 'IOH', 'XL'];

    private const EXPECTED_HEADERS = [
        'TANGGAL', 'OPSEL', 'KATEGORI',
        'KODE_ORIGIN_PROVINSI', 'ORIGIN_PROVINSI',
        'KODE_ORIGIN_KABUPATEN_KOTA', 'ORIGIN_KABUPATEN_KOTA',
        'KODE_DEST_PROVINSI', 'DEST_PROVINSI',
        'KODE_DEST_KABUPATEN_KOTA', 'DEST_KABUPATEN_KOTA',
        'KODE_ORIGIN_SIMPUL', 'ORIGIN_SIMPUL',
        'KODE_DEST_SIMPUL', 'DEST_SIMPUL',
        'KODE_MODA', 'MODA', 'TOTAL',
    ];

    private const EXPECTED_COUNT = 18;

    // In-memory reference lookup (O(1) per check)
    private array $refProvinces = [];
    private array $refCities = [];
    private array $refNodes = [];
    private array $refModes = [];

    /**
     * Validate a CSV file against the MPD schema and database reference tables.
     *
     * @param  string       $filePath      Absolute path to the CSV file
     * @param  string|null  $selectedOpsel OPSEL yang dipilih di dropdown form (TSEL/IOH/XL)
     * @return array        Validation result
     */
    public function execute(string $filePath, ?string $selectedOpsel = null): array
    {
        $handle = @fopen($filePath, 'r');
        if ($handle === false) {
            return ['is_valid' => false, 'error' => 'Gagal membuka file CSV.'];
        }

        // Skip BOM (UTF-8 Byte Order Mark)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            fseek($handle, 0);
        }

        // ─── 1. HEADER VALIDATION ───
        $headerLine = fgets($handle);
        if ($headerLine === false) {
            fclose($handle);
            return ['is_valid' => false, 'error' => 'File CSV kosong.'];
        }

        $headerResult = $this->validateHeader(trim(str_replace("\r", '', $headerLine)));

        // Jika header tidak valid, tidak perlu lanjut cek baris
        if (! $headerResult['valid']) {
            fclose($handle);
            return [
                'is_valid' => false,
                'file' => ['name' => basename($filePath), 'total_data_rows' => 0, 'rows_checked' => 0],
                'header' => $headerResult,
                'row_errors' => [],
                'opsel_mismatch' => null,
                'summary' => [
                    'header_ok' => false,
                    'rows_checked' => 0,
                    'rows_with_errors' => 0,
                    'total_data_rows' => 0,
                    'errors_truncated' => false,
                ],
            ];
        }

        // ─── 2. LOAD REFERENCE DATA (sekali query, O(1) lookup) ───
        $this->loadReferenceData();

        // ─── 3. VALIDATE ALL ROWS ───
        $rowErrors = [];
        $rowsChecked = 0;
        $totalErrorRows = 0;
        $csvOpselValues = []; // Collect unique OPSEL values found in CSV

        while (($line = fgets($handle)) !== false) {
            $line = trim(str_replace("\r", '', $line));
            if ($line === '') {
                continue;
            }

            $rowNum = $rowsChecked + 2;
            $cols = str_getcsv($line, self::DELIMITER);
            $issues = $this->validateRow($cols, $rowNum);

            // Track unique OPSEL values efficiently
            if (count($cols) >= 2) {
                $opselVal = trim($cols[1]);
                if ($opselVal !== '' && ! isset($csvOpselValues[$opselVal])) {
                    $csvOpselValues[$opselVal] = true;
                }
            }

            if (! empty($issues)) {
                $totalErrorRows++;
                if (count($rowErrors) < self::MAX_ERRORS_DISPLAYED) {
                    $rowErrors[] = ['row' => $rowNum, 'issues' => $issues];
                }
            }

            $rowsChecked++;
        }

        fclose($handle);

        // ─── 4. OPSEL MISMATCH CHECK ───
        $opselMismatch = null;
        if ($selectedOpsel && ! empty($csvOpselValues)) {
            $csvOpsels = array_keys($csvOpselValues);
            $mismatched = array_filter($csvOpsels, fn ($o) => $o !== $selectedOpsel);
            if (! empty($mismatched)) {
                $opselMismatch = [
                    'selected' => $selectedOpsel,
                    'found_in_csv' => $csvOpsels,
                    'mismatched' => array_values($mismatched),
                ];
            }
        }

        // ─── 5. COMPILE RESULT ───
        $isValid = $totalErrorRows === 0 && $opselMismatch === null;

        return [
            'is_valid' => $isValid,
            'file' => [
                'name' => basename($filePath),
                'total_data_rows' => $rowsChecked,
                'rows_checked' => $rowsChecked,
            ],
            'header' => $headerResult,
            'row_errors' => $rowErrors,
            'opsel_mismatch' => $opselMismatch,
            'summary' => [
                'header_ok' => true,
                'rows_checked' => $rowsChecked,
                'rows_with_errors' => $totalErrorRows,
                'total_data_rows' => $rowsChecked,
                'errors_truncated' => $totalErrorRows > self::MAX_ERRORS_DISPLAYED,
            ],
        ];
    }

    /**
     * Validate header: exact 18 columns, exact names, exact order.
     */
    private function validateHeader(string $headerLine): array
    {
        $actualCols = array_map('trim', explode(self::DELIMITER, $headerLine));

        $missing = array_values(array_diff(self::EXPECTED_HEADERS, $actualCols));
        $extra = array_values(array_diff($actualCols, self::EXPECTED_HEADERS));
        $valid = empty($missing) && empty($extra) && count($actualCols) === self::EXPECTED_COUNT;

        $orderCorrect = $valid;
        if ($valid) {
            for ($i = 0; $i < self::EXPECTED_COUNT; $i++) {
                if ($actualCols[$i] !== self::EXPECTED_HEADERS[$i]) {
                    $orderCorrect = false;
                    break;
                }
            }
        }

        return [
            'valid' => $valid && $orderCorrect,
            'expected_count' => self::EXPECTED_COUNT,
            'actual_count' => count($actualCols),
            'missing' => $missing,
            'extra' => $extra,
            'order_correct' => $orderCorrect,
        ];
    }

    /**
     * Validate a single data row against schema + database references.
     */
    private function validateRow(array $cols, int $rowNum): array
    {
        $issues = [];

        // ─── Column count ───
        if (count($cols) !== self::EXPECTED_COUNT) {
            return [['field' => null, 'type' => 'COLUMN_COUNT', 'detail' => 'Diharapkan ' . self::EXPECTED_COUNT . ' kolom, ditemukan ' . count($cols)]];
        }

        // ─── TANGGAL (index 0): YYYY-MM-DD ───
        $tanggal = trim($cols[0]);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || ! strtotime($tanggal)) {
            $issues[] = ['field' => 'TANGGAL', 'type' => 'INVALID_DATE', 'detail' => "Format harus YYYY-MM-DD, ditemukan: \"{$tanggal}\""];
        }

        // ─── OPSEL (index 1): wajib, harus TSEL/IOH/XL ───
        $opsel = trim($cols[1]);
        if ($opsel === '') {
            $issues[] = ['field' => 'OPSEL', 'type' => 'EMPTY_REQUIRED', 'detail' => 'OPSEL tidak boleh kosong'];
        } elseif (! in_array($opsel, self::VALID_OPSEL, true)) {
            $issues[] = ['field' => 'OPSEL', 'type' => 'INVALID_VALUE', 'detail' => "Harus TSEL/IOH/XL, ditemukan: \"{$opsel}\""];
        }

        // ─── KATEGORI (index 2): wajib terisi (nilainya bebas, misal PERGERAKAN) ───
        if (trim($cols[2]) === '') {
            $issues[] = ['field' => 'KATEGORI', 'type' => 'EMPTY_REQUIRED', 'detail' => 'KATEGORI tidak boleh kosong'];
        }

        // ─── TOTAL (index 17): numerik >= 0 ───
        $total = trim($cols[17]);
        if (! is_numeric($total) || (int) $total < 0) {
            $issues[] = ['field' => 'TOTAL', 'type' => 'INVALID_NUMERIC', 'detail' => "TOTAL harus angka >= 0, ditemukan: \"{$total}\""];
        }

        // ─── DATABASE REFERENCE CHECKS (by column index for speed) ───

        // KODE_ORIGIN_PROVINSI (3) & KODE_DEST_PROVINSI (7) → ref_provinces
        foreach ([3 => 'KODE_ORIGIN_PROVINSI', 7 => 'KODE_DEST_PROVINSI'] as $idx => $field) {
            $val = trim($cols[$idx]);
            if ($val !== '' && ! isset($this->refProvinces[$val])) {
                $issues[] = ['field' => $field, 'type' => 'REF_NOT_FOUND', 'detail' => "Kode provinsi \"{$val}\" tidak terdaftar di database"];
            }
        }

        // KODE_ORIGIN_KABUPATEN_KOTA (5) & KODE_DEST_KABUPATEN_KOTA (9) → ref_cities
        foreach ([5 => 'KODE_ORIGIN_KABUPATEN_KOTA', 9 => 'KODE_DEST_KABUPATEN_KOTA'] as $idx => $field) {
            $val = trim($cols[$idx]);
            if ($val !== '' && ! isset($this->refCities[$val])) {
                $issues[] = ['field' => $field, 'type' => 'REF_NOT_FOUND', 'detail' => "Kode kab/kota \"{$val}\" tidak terdaftar di database"];
            }
        }

        // KODE_ORIGIN_SIMPUL (11) & KODE_DEST_SIMPUL (13) → ref_transport_nodes
        foreach ([11 => 'KODE_ORIGIN_SIMPUL', 13 => 'KODE_DEST_SIMPUL'] as $idx => $field) {
            $val = trim($cols[$idx]);
            if ($val !== '' && ! isset($this->refNodes[$val])) {
                $issues[] = ['field' => $field, 'type' => 'REF_NOT_FOUND', 'detail' => "Kode simpul \"{$val}\" tidak terdaftar di database"];
            }
        }

        // KODE_MODA (15) → ref_transport_modes
        $modaVal = trim($cols[15]);
        if ($modaVal !== '' && ! isset($this->refModes[$modaVal])) {
            $issues[] = ['field' => 'KODE_MODA', 'type' => 'REF_NOT_FOUND', 'detail' => "Kode moda \"{$modaVal}\" tidak terdaftar di database"];
        }

        return $issues;
    }

    /**
     * Load all reference codes into flipped arrays for O(1) isset() lookups.
     */
    private function loadReferenceData(): void
    {
        $this->refProvinces = array_flip(DB::table('ref_provinces')->pluck('code')->toArray());
        $this->refCities = array_flip(DB::table('ref_cities')->pluck('code')->toArray());
        $this->refNodes = array_flip(DB::table('ref_transport_nodes')->pluck('code')->toArray());
        $this->refModes = array_flip(DB::table('ref_transport_modes')->pluck('code')->toArray());
    }
}
