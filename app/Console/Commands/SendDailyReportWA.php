<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendDailyReportWA extends Command
{
    protected $signature = 'report:send-wa {--date= : Specific date (Y-m-d)}';
    protected $description = 'Send daily report via WhatsApp to configured recipients';

    public function handle()
    {
        $settings = DB::table('app_settings')
            ->where('group', 'whatsapp')
            ->pluck('value', 'key');

        // Check if auto-send is enabled
        if ($settings->get('wa_auto_send', '0') !== '1') {
            $this->warn('Auto-send is disabled. Enable it in Pengaturan.');
            return 0;
        }

        $token    = $settings->get('qontak_access_token', '');
        $numbers  = $settings->get('wa_recipients', '');
        $channelId = $settings->get('qontak_channel_id', '');

        if (empty($token) || empty($numbers)) {
            $this->error('Token atau nomor penerima belum dikonfigurasi.');
            return 1;
        }

        // Build report for today's scope (up to yesterday or specified date)
        $endDate   = $this->option('date') ?: Carbon::yesterday()->format('Y-m-d');
        $startDate = '2026-03-13';

        // Clamp to valid range
        if ($endDate > '2026-03-30') $endDate = '2026-03-30';
        if ($endDate < '2026-03-13') $endDate = '2026-03-13';

        $controller = app(\App\Http\Controllers\DailyReportController::class);
        $reportText = $this->callMethod($controller, 'buildPlainText', [$startDate, $endDate, 'REAL']);

        $recipients = array_filter(array_map('trim', explode(',', $numbers)));
        $sent = 0;

        foreach ($recipients as $number) {
            $phone = preg_replace('/[^0-9]/', '', $number);
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ])->post('https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct', [
                    'to_number'              => $phone,
                    'to_name'                => 'Penerima Laporan',
                    'channel_integration_id' => $channelId,
                    'body'                   => $reportText,
                ]);

                if ($response->successful()) {
                    $sent++;
                    $this->info("✅ Terkirim ke {$phone}");
                } else {
                    $this->warn("❌ Gagal ke {$phone}: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("❌ Error ke {$phone}: " . $e->getMessage());
            }
        }

        $this->info("Selesai. Terkirim: {$sent}/" . count($recipients));
        Log::info("Daily Report WA: Sent {$sent}/" . count($recipients));

        return 0;
    }

    private function callMethod($obj, $method, $args)
    {
        $ref = new \ReflectionMethod($obj, $method);
        $ref->setAccessible(true);
        return $ref->invoke($obj, ...$args);
    }
}
