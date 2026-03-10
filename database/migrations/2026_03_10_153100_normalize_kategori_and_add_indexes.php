<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * QC Fix: Normalize kategori data to uppercase and add missing indexes.
 *
 * Issues addressed:
 * - K-1: Inconsistent kategori casing in DB causing some queries to use
 *         UPPER() which disables index usage, and others to miss lowercase rows.
 * - I-1: Missing index on kode_dest_kabupaten_kota (needed by Netflow queries).
 * - I-2: Missing index on kode_dest_simpul (needed by Substansi Simpul queries).
 * - I-3: Missing index on (kategori, tanggal) for queries that skip opsel column.
 */
return new class extends Migration {
    public function up(): void
    {
        // Step 1: Normalize all kategori values to uppercase
        DB::statement("UPDATE spatial_movements SET kategori = UPPER(kategori) WHERE kategori != UPPER(kategori);");

        // Step 3: Add missing indexes
        DB::statement("CREATE INDEX IF NOT EXISTS idx_sm_dest_kabkota ON spatial_movements (kode_dest_kabupaten_kota, tanggal);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_sm_dest_simpul ON spatial_movements (kode_dest_simpul, tanggal);");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_sm_kat_tanggal ON spatial_movements (kategori, tanggal);");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_sm_kat_tanggal;");
        DB::statement("DROP INDEX IF EXISTS idx_sm_dest_simpul;");
        DB::statement("DROP INDEX IF EXISTS idx_sm_dest_kabkota;");
        // Note: kategori normalization is not reversed as it's a data quality fix.
    }
};
