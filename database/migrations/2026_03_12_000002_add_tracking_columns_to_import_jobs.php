<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom tracking yang lebih detail ke import_jobs:
 * - file_size: ukuran file CSV dalam bytes
 * - status_file: status proses import file (pending/validating/importing/completed/failed)
 * - status_etl: status proses ETL (pending/processing/completed/failed)
 * - etl_progress: persentase progress ETL (0-100)
 * - skipped_rows: jumlah baris yang di-skip karena format error
 * - data_lost: volume data yang hilang di ETL karena unmapped nodes
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->bigInteger('file_size')->default(0)->after('metadata');
            $table->string('status_file', 20)->default('pending')->after('file_size');
            $table->string('status_etl', 20)->default('pending')->after('status_file');
            $table->integer('etl_progress')->default(0)->after('status_etl');
            $table->integer('skipped_rows')->default(0)->after('processed_rows');
            $table->integer('data_lost')->default(0)->after('skipped_rows');
        });
    }

    public function down(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'file_size', 'status_file', 'status_etl',
                'etl_progress', 'skipped_rows', 'data_lost',
            ]);
        });
    }
};
