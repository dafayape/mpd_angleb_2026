<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop materialized view that depends on opsel
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS mv_daily_summary;');

        // Fix opsel length to handle XLSMART
        DB::statement('ALTER TABLE spatial_movements ALTER COLUMN opsel TYPE varchar(10);');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN opsel TYPE varchar(10);');

        // Make simpul and moda nullable in raw_mpd_data
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN kode_origin_simpul DROP NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN origin_simpul DROP NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN kode_dest_simpul DROP NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN dest_simpul DROP NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN kode_moda DROP NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN moda DROP NOT NULL;');

        // Make simpul and moda nullable in spatial_movements
        DB::statement('ALTER TABLE spatial_movements ALTER COLUMN kode_origin_simpul DROP NOT NULL;');
        DB::statement('ALTER TABLE spatial_movements ALTER COLUMN kode_dest_simpul DROP NOT NULL;');
        DB::statement('ALTER TABLE spatial_movements ALTER COLUMN kode_moda DROP NOT NULL;');

        // Recreate the materialized view
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_daily_summary AS
            SELECT
                tanggal,
                kategori,
                opsel,
                is_forecast,
                SUM(total) as total_volume,
                COUNT(*) as record_count
            FROM spatial_movements
            GROUP BY tanggal, kategori, opsel, is_forecast
            ORDER BY tanggal, kategori, opsel
            WITH DATA;
        ");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS idx_mv_daily_summary ON mv_daily_summary (tanggal, kategori, opsel, is_forecast);");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop materialized view that depends on opsel
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS mv_daily_summary;');

        // Revert to NOT NULL
        DB::statement('ALTER TABLE spatial_movements ALTER COLUMN kode_origin_simpul SET NOT NULL;');
        DB::statement('ALTER TABLE spatial_movements ALTER COLUMN kode_dest_simpul SET NOT NULL;');
        DB::statement('ALTER TABLE spatial_movements ALTER COLUMN kode_moda SET NOT NULL;');

        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN kode_origin_simpul SET NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN origin_simpul SET NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN kode_dest_simpul SET NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN dest_simpul SET NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN kode_moda SET NOT NULL;');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN moda SET NOT NULL;');

        // Revert opsel length
        DB::statement('ALTER TABLE spatial_movements ALTER COLUMN opsel TYPE varchar(4);');
        DB::statement('ALTER TABLE raw_mpd_data ALTER COLUMN opsel TYPE varchar(4);');

        // Recreate the materialized view
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_daily_summary AS
            SELECT
                tanggal,
                kategori,
                opsel,
                is_forecast,
                SUM(total) as total_volume,
                COUNT(*) as record_count
            FROM spatial_movements
            GROUP BY tanggal, kategori, opsel, is_forecast
            ORDER BY tanggal, kategori, opsel
            WITH DATA;
        ");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS idx_mv_daily_summary ON mv_daily_summary (tanggal, kategori, opsel, is_forecast);");
    }
};
