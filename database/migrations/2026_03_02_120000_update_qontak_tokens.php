<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update Qontak tokens (provided 2026-03-02)
        DB::table('app_settings')
            ->where('key', 'qontak_access_token')
            ->update([
                'value' => '4HK-hrW1uH3IOAWZ5X67CiHsqfX7HN4fCtiZe4yTHmQ',
                'updated_at' => now(),
            ]);

        DB::table('app_settings')
            ->where('key', 'qontak_refresh_token')
            ->update([
                'value' => 'VmX1cYhoTgxKvMjv_uiH7mRiUd3hEicH8gHrnXeFrOQ',
                'updated_at' => now(),
            ]);

        // Add message_template_id setting if not exists
        if (DB::table('app_settings')->where('key', 'qontak_message_template_id')->doesntExist()) {
            DB::table('app_settings')->insert([
                'key' => 'qontak_message_template_id',
                'value' => '',
                'label' => 'Qontak Message Template ID (UUID)',
                'group' => 'whatsapp',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('app_settings')
            ->where('key', 'qontak_message_template_id')
            ->delete();
    }
};
