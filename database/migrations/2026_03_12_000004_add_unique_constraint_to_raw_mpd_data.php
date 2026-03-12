<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add unique constraint for all columns used in upsert ON CONFLICT
        DB::statement('
            CREATE UNIQUE INDEX idx_raw_mpd_data_upsert_unique 
            ON raw_mpd_data (
                tanggal, 
                opsel, 
                kategori, 
                kode_origin_kabupaten_kota, 
                kode_dest_kabupaten_kota, 
                kode_origin_simpul, 
                kode_dest_simpul, 
                kode_moda, 
                is_forecast
            );
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_raw_mpd_data_upsert_unique;');
    }
};
