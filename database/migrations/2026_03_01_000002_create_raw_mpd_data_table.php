<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            CREATE TABLE raw_mpd_data (
                tanggal date NOT NULL,
                opsel varchar(4) NOT NULL CHECK (opsel IN ('TSEL', 'IOH', 'XL')),
                kategori varchar(10) NOT NULL CHECK (kategori IN ('ORANG', 'PERGERAKAN')),
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
                created_at timestamp(0) without time zone DEFAULT NULL,
                updated_at timestamp(0) without time zone DEFAULT NULL
            ) PARTITION BY RANGE (tanggal);
        ");

        DB::statement("CREATE INDEX idx_raw_mpd_data_tanggal_brin ON raw_mpd_data USING BRIN (tanggal);");
        DB::statement("CREATE INDEX idx_raw_mpd_data_opsel ON raw_mpd_data (opsel);");
        DB::statement("CREATE INDEX idx_raw_mpd_data_origin_dest ON raw_mpd_data (kode_origin_kabupaten_kota, kode_dest_kabupaten_kota);");
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_mpd_data');
    }
};
