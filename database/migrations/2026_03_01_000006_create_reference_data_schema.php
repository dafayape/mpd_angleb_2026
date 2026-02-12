<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ref_provinces', function (Blueprint $table) {
            $table->string('code', 2)->primary();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('ref_cities', function (Blueprint $table) {
            $table->string('code', 4)->primary();
            $table->string('province_code', 2)->index();
            $table->string('name', 100);
            $table->timestamps();

            $table->foreign('province_code')->references('code')->on('ref_provinces')->cascadeOnDelete();
        });

        Schema::create('ref_transport_modes', function (Blueprint $table) {
            $table->char('code', 1)->primary();
            $table->string('name', 50);
            $table->timestamps();
        });

        Schema::create('ref_transport_nodes', function (Blueprint $table) {
            $table->string('code', 50)->primary();
            $table->string('name', 150);
            $table->string('category', 50)->index();
            $table->string('sub_category', 50)->nullable();
            $table->geography('location', 'POINT', 4326)->nullable();
            $table->timestamps();

            $table->spatialIndex('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_transport_nodes');
        Schema::dropIfExists('ref_transport_modes');
        Schema::dropIfExists('ref_cities');
        Schema::dropIfExists('ref_provinces');
    }
};
