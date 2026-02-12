<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add is_forecast to raw_mpd_data
        DB::statement("ALTER TABLE raw_mpd_data ADD COLUMN is_forecast boolean NOT NULL DEFAULT false;");

        // Ensure raw_mpd_data has a unique constraint for upsert
        // Dropping any existing indexes that might conflict or be redundant (though none were strictly unique before)
        // Creating a unique index to support ON CONFLICT
        DB::statement("
            CREATE UNIQUE INDEX idx_raw_mpd_data_unique 
            ON raw_mpd_data (
                tanggal, opsel, kategori, 
                kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, 
                kode_origin_simpul, kode_dest_simpul, 
                kode_moda, is_forecast
            );
        ");

        // Add is_forecast to spatial_movements
        DB::statement("ALTER TABLE spatial_movements ADD COLUMN is_forecast boolean NOT NULL DEFAULT false;");

        // Update Unique Constraint on spatial_movements
        DB::statement("ALTER TABLE spatial_movements DROP CONSTRAINT spatial_movements_unique;");
        DB::statement("
            ALTER TABLE spatial_movements 
            ADD CONSTRAINT spatial_movements_unique UNIQUE (
                tanggal, opsel, kategori, 
                kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, 
                kode_origin_simpul, kode_dest_simpul, 
                kode_moda, is_forecast
            );
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE spatial_movements DROP CONSTRAINT spatial_movements_unique;");
        DB::statement("ALTER TABLE spatial_movements DROP COLUMN is_forecast;");
        DB::statement("
            ALTER TABLE spatial_movements 
            ADD CONSTRAINT spatial_movements_unique UNIQUE (
                tanggal, opsel, kategori, 
                kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, 
                kode_origin_simpul, kode_dest_simpul, 
                kode_moda
            );
        ");

        DB::statement("DROP INDEX IF EXISTS idx_raw_mpd_data_unique;");
        DB::statement("ALTER TABLE raw_mpd_data DROP COLUMN is_forecast;");
    }
};
