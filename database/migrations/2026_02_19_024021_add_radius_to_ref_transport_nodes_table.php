<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ref_transport_nodes', function (Blueprint $table) {
            $table->integer('radius')->nullable()->after('location')->comment('Radius visualisasi dalam meter (LTS)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_transport_nodes', function (Blueprint $table) {
            $table->dropColumn('radius');
        });
    }
};
