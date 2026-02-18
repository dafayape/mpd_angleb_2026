<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->string('original_filename')->nullable()->after('filename');
            $table->string('opsel', 4)->nullable()->after('original_filename');
            $table->string('kategori', 10)->nullable()->after('opsel');
            $table->date('tanggal_data')->nullable()->after('kategori');
            $table->unsignedBigInteger('user_id')->nullable()->after('tanggal_data');

            $table->index('opsel');
            $table->index('kategori');
            $table->index('tanggal_data');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->dropIndex(['opsel']);
            $table->dropIndex(['kategori']);
            $table->dropIndex(['tanggal_data']);
            $table->dropIndex(['user_id']);
            $table->dropColumn(['original_filename', 'opsel', 'kategori', 'tanggal_data', 'user_id']);
        });
    }
};
