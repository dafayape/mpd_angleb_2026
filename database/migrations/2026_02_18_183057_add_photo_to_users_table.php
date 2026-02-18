<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'laravel11_mysql';

    public function up(): void
    {
        Schema::connection('laravel11_mysql')->table('users', function (Blueprint $table) {
            if (!Schema::connection('laravel11_mysql')->hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('laravel11_mysql')->table('users', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
