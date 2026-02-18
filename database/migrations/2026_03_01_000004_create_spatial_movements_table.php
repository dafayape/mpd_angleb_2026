<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    // Pastikan extension ada di awal
    DB::statement("CREATE EXTENSION IF NOT EXISTS postgis;");

    DB::statement("
        CREATE TABLE spatial_movements (
            tanggal date NOT NULL,
            opsel varchar(4) NOT NULL,
            kategori varchar(10) NOT NULL,
            kode_origin_kabupaten_kota char(4) NOT NULL,
            kode_dest_kabupaten_kota char(4) NOT NULL,
            kode_origin_simpul varchar(255) NOT NULL,
            kode_dest_simpul varchar(255) NOT NULL,
            kode_moda char(1) NOT NULL,
            total integer NOT NULL,
            -- TAMBAHKAN 'public.' DI SINI
            origin_location public.geography(POINT, 4326),
            dest_location public.geography(POINT, 4326),
            distance_meters double precision,
            created_at timestamp(0) without time zone DEFAULT NULL,
            updated_at timestamp(0) without time zone DEFAULT NULL,
            CONSTRAINT spatial_movements_unique UNIQUE (tanggal, opsel, kategori, kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, kode_origin_simpul, kode_dest_simpul, kode_moda)
        ) PARTITION BY RANGE (tanggal);
    ");

    DB::statement("CREATE INDEX idx_spatial_movements_tanggal_brin ON spatial_movements USING BRIN (tanggal);");
    DB::statement("CREATE INDEX idx_spatial_movements_spatial ON spatial_movements USING GIST (origin_location, dest_location);");
}

    public function down(): void
    {
        Schema::dropIfExists('spatial_movements');
    }
};