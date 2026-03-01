<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // Seed default settings
        DB::table('app_settings')->insert([
            [
                'key'        => 'wa_recipients',
                'value'      => '',
                'label'      => 'Nomor WhatsApp Penerima (pisahkan dengan koma)',
                'group'      => 'whatsapp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'wa_schedule_time',
                'value'      => '08:00',
                'label'      => 'Jam Kirim Otomatis (format HH:mm)',
                'group'      => 'whatsapp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'wa_auto_send',
                'value'      => '0',
                'label'      => 'Auto Kirim Harian',
                'group'      => 'whatsapp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'qontak_access_token',
                'value'      => '4HK-hrW1uH3IOAWZ5X67CiHsqfX7HN4fCtiZe4yTHmQ',
                'label'      => 'Qontak Access Token',
                'group'      => 'whatsapp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'qontak_refresh_token',
                'value'      => 'VmX1cYhoTgxKvMjv_uiH7mRiUd3hEicH8gHrnXeFrOQ',
                'label'      => 'Qontak Refresh Token',
                'group'      => 'whatsapp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'qontak_channel_id',
                'value'      => '',
                'label'      => 'Qontak Channel Integration ID',
                'group'      => 'whatsapp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
