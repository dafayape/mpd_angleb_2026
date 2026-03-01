<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily Report WhatsApp Auto-Send
Schedule::command('report:send-wa')->dailyAt(
    rescue(fn () => DB::table('app_settings')->where('key', 'wa_schedule_time')->value('value') ?? '08:00', '08:00')
)->withoutOverlapping()->runInBackground();
