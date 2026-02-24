<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * P3.2: Historical MPD Summary Table
 * Stores aggregated results from previous periods (Nataru 2025, Lebaran 2025, etc.)
 * for year-over-year comparison (Slide 9)
 *
 * P3.4: Materialized Views for heavy aggregations
 * Pre-computes daily totals by kategori, opsel, and region
 */
return new class extends Migration {
    public function up(): void
    {
        // Historical data table for year-over-year comparison
        DB::statement("
            CREATE TABLE IF NOT EXISTS historical_mpd_summary (
                id bigserial PRIMARY KEY,
                periode_nama varchar(100) NOT NULL,
                periode_mulai date NOT NULL,
                periode_selesai date NOT NULL,
                tahun integer NOT NULL,
                total_pergerakan_nasional bigint DEFAULT 0,
                total_orang_nasional bigint DEFAULT 0,
                total_pergerakan_jabodetabek bigint DEFAULT 0,
                total_orang_jabodetabek bigint DEFAULT 0,
                puncak_tanggal date NULL,
                puncak_volume bigint DEFAULT 0,
                metadata jsonb DEFAULT '{}',
                created_at timestamp(0) DEFAULT NOW(),
                updated_at timestamp(0) DEFAULT NOW()
            );
        ");

        DB::statement("CREATE INDEX idx_hist_mpd_tahun ON historical_mpd_summary (tahun);");
        DB::statement("CREATE INDEX idx_hist_mpd_periode ON historical_mpd_summary (periode_nama);");

        // Materialized View: Daily aggregated totals by kategori and opsel
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

        DB::statement("CREATE UNIQUE INDEX idx_mv_daily_summary ON mv_daily_summary (tanggal, kategori, opsel, is_forecast);");

        // Materialized View: Regional (Jabodetabek) daily summary
        DB::statement("
            CREATE MATERIALIZED VIEW IF NOT EXISTS mv_jabodetabek_daily AS
            SELECT
                tanggal,
                kategori,
                is_forecast,
                CASE
                    WHEN kode_origin_kabupaten_kota IN ('3171','3172','3173','3174','3175','3101','3201','3271','3276','3603','3671','3674','3216','3275')
                     AND kode_dest_kabupaten_kota IN ('3171','3172','3173','3174','3175','3101','3201','3271','3276','3603','3671','3674','3216','3275')
                    THEN 'INTRA'
                    ELSE 'INTER'
                END as flow_type,
                SUM(total) as total_volume
            FROM spatial_movements
            WHERE kode_origin_kabupaten_kota IN ('3171','3172','3173','3174','3175','3101','3201','3271','3276','3603','3671','3674','3216','3275')
               OR kode_dest_kabupaten_kota IN ('3171','3172','3173','3174','3175','3101','3201','3271','3276','3603','3671','3674','3216','3275')
            GROUP BY tanggal, kategori, is_forecast, flow_type
            ORDER BY tanggal
            WITH DATA;
        ");

        DB::statement("CREATE UNIQUE INDEX idx_mv_jabo_daily ON mv_jabodetabek_daily (tanggal, kategori, is_forecast, flow_type);");
    }

    public function down(): void
    {
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_jabodetabek_daily;");
        DB::statement("DROP MATERIALIZED VIEW IF EXISTS mv_daily_summary;");
        Schema::dropIfExists('historical_mpd_summary');
    }
};
