<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_mpd_data', function (Blueprint $table) {
            $table->string('kode_origin_provinsi', 2)->nullable()->change();
            $table->string('origin_provinsi', 50)->nullable()->change();
            $table->string('origin_kabupaten_kota', 50)->nullable()->change();
            $table->string('kode_dest_provinsi', 2)->nullable()->change();
            $table->string('dest_provinsi', 50)->nullable()->change();
            $table->string('dest_kabupaten_kota', 50)->nullable()->change();
            $table->string('origin_simpul', 50)->nullable()->change();
            $table->string('dest_simpul', 50)->nullable()->change();
            $table->string('moda', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('raw_mpd_data', function (Blueprint $table) {
            $table->string('kode_origin_provinsi', 2)->nullable(false)->change();
            $table->string('origin_provinsi', 50)->nullable(false)->change();
            $table->string('origin_kabupaten_kota', 50)->nullable(false)->change();
            $table->string('kode_dest_provinsi', 2)->nullable(false)->change();
            $table->string('dest_provinsi', 50)->nullable(false)->change();
            $table->string('dest_kabupaten_kota', 50)->nullable(false)->change();
            $table->string('origin_simpul', 50)->nullable(false)->change();
            $table->string('dest_simpul', 50)->nullable(false)->change();
            $table->string('moda', 50)->nullable(false)->change();
        });
    }
};
