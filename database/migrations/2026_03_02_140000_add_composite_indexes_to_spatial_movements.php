<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Composite B-tree index for common filter patterns
        // All major queries filter by kategori + opsel + is_forecast + tanggal
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_spatial_movements_filters
            ON spatial_movements (kategori, opsel, is_forecast, tanggal);
        ");

        // Index for origin simpul lookups (substansi, simpul transportasi pages)
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_spatial_movements_origin_simpul
            ON spatial_movements (kode_origin_simpul, tanggal);
        ");

        // Index for kabupaten/kota OD queries
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_spatial_movements_od_kabkota
            ON spatial_movements (kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, tanggal);
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_spatial_movements_filters;");
        DB::statement("DROP INDEX IF EXISTS idx_spatial_movements_origin_simpul;");
        DB::statement("DROP INDEX IF EXISTS idx_spatial_movements_od_kabkota;");
    }
};
