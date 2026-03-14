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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
    }
};
