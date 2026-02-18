<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Drop foreign key constraint because User model is on MySQL (cross-database)
            $table->dropForeign(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // Restore foreign key constraint (only works if users table is in same DB)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
