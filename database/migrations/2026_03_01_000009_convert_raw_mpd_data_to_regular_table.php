<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop tabel lama (partitioned) dan buat ulang sebagai tabel biasa
        DB::statement("DROP TABLE IF EXISTS raw_mpd_data CASCADE;");

        DB::statement("
            CREATE TABLE raw_mpd_data (
                id bigserial PRIMARY KEY,
                import_job_id bigint NULL,
                tanggal date NOT NULL,
                opsel varchar(4) NOT NULL,
                kategori varchar(10) NOT NULL,
                kode_origin_provinsi char(2) NOT NULL,
                origin_provinsi varchar(50) NOT NULL,
                kode_origin_kabupaten_kota char(4) NOT NULL,
                origin_kabupaten_kota varchar(50) NOT NULL,
                kode_dest_provinsi char(2) NOT NULL,
                dest_provinsi varchar(50) NOT NULL,
                kode_dest_kabupaten_kota char(4) NOT NULL,
                dest_kabupaten_kota varchar(50) NOT NULL,
                kode_origin_simpul varchar(255) NOT NULL,
                origin_simpul varchar(50) NOT NULL,
                kode_dest_simpul varchar(255) NOT NULL,
                dest_simpul varchar(50) NOT NULL,
                kode_moda char(1) NOT NULL,
                moda varchar(50) NOT NULL,
                total integer NOT NULL,
                is_forecast boolean NOT NULL DEFAULT false,
                created_at timestamp(0) without time zone DEFAULT NULL,
                updated_at timestamp(0) without time zone DEFAULT NULL
            );
        ");

        // Indexes
        DB::statement("CREATE INDEX idx_raw_mpd_tanggal ON raw_mpd_data (tanggal);");
        DB::statement("CREATE INDEX idx_raw_mpd_opsel ON raw_mpd_data (opsel);");
        DB::statement("CREATE INDEX idx_raw_mpd_import_job ON raw_mpd_data (import_job_id);");
        DB::statement("CREATE INDEX idx_raw_mpd_origin_dest ON raw_mpd_data (kode_origin_kabupaten_kota, kode_dest_kabupaten_kota);");
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_mpd_data');
    }
};
