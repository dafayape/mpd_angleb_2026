<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Buat partisi bulanan untuk spatial_movements.
     * Tabel utama sudah PARTITION BY RANGE (tanggal).
     * Partisi harus dibuat sebelum INSERT bisa dilakukan.
     */
    public function up(): void
    {
        // Partition Maret 2026 (range: 2026-03-01 inclusive to 2026-04-01 exclusive)
        DB::statement("
            CREATE TABLE IF NOT EXISTS spatial_movements_2026_03
            PARTITION OF spatial_movements
            FOR VALUES FROM ('2026-03-01') TO ('2026-04-01');
        ");

        // Partition April 2026 (untuk jaga-jaga jika ada data overflow)
        DB::statement("
            CREATE TABLE IF NOT EXISTS spatial_movements_2026_04
            PARTITION OF spatial_movements
            FOR VALUES FROM ('2026-04-01') TO ('2026-05-01');
        ");

        // Default partition untuk data di luar range yang didefinisikan
        DB::statement("
            CREATE TABLE IF NOT EXISTS spatial_movements_default
            PARTITION OF spatial_movements
            DEFAULT;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS spatial_movements_default;");
        DB::statement("DROP TABLE IF EXISTS spatial_movements_2026_04;");
        DB::statement("DROP TABLE IF EXISTS spatial_movements_2026_03;");
    }
};
