<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Ubah tipe kolom kode_moda dari char(1) ke varchar(5)
 *
 * ALASAN:
 * Data Forecast dari penyedia data tidak memiliki kode simpul maupun kode moda.
 * PostgreSQL char(1) akan mengubah string kosong '' menjadi spasi ' ',
 * yang menyebabkan JOIN ke ref_transport_modes gagal (mismatch).
 * varchar(5) bisa menyimpan string kosong '' dengan benar.
 *
 * CATATAN:
 * Migration ini perlu dijalankan langsung di VPS production via SQL.
 * Lihat SQL di bawah atau jalankan: php artisan migrate
 */
return new class extends Migration {
    public function up(): void
    {
        // raw_mpd_data
        DB::statement("ALTER TABLE raw_mpd_data ALTER COLUMN kode_moda TYPE varchar(5);");

        // spatial_movements (partitioned table)
        DB::statement("ALTER TABLE spatial_movements ALTER COLUMN kode_moda TYPE varchar(5);");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE raw_mpd_data ALTER COLUMN kode_moda TYPE char(1);");
        DB::statement("ALTER TABLE spatial_movements ALTER COLUMN kode_moda TYPE char(1);");
    }
};
