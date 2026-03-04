<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $token = $settings->get('wa_cloud_token', '');
        $phoneId = $settings->get('wa_cloud_phone_id', '');
        $templateName = $settings->get('wa_cloud_template_name', '');
        $numbers = $settings->get('wa_recipients', '');

        if (empty($token) || empty($numbers)) {
            $this->error('Token atau nomor penerima belum dikonfigurasi.');

            return 1;
        }

        if (empty($phoneId) || empty($templateName)) {
            $this->error('Phone Number ID atau Message Template Name belum dikonfigurasi.');

            return 1;
        }

        // Build report for today's scope (up to yesterday or specified date)
        $endDate = $this->option('date') ?: Carbon::yesterday()->format('Y-m-d');
        $startDate = '2026-03-13';

        // Clamp to valid range
        if ($endDate > '2026-03-30') {
            $endDate = '2026-03-30';
        }
        if ($endDate < '2026-03-13') {
            $endDate = '2026-03-13';
        }

        $controller = app(\App\Http\Controllers\DailyReportController::class);
        $reportText = $this->callMethod($controller, 'buildPlainText', [$startDate, $endDate, 'REAL']);

        $recipients = array_filter(array_map('trim', explode(',', $numbers)));
        $sent = 0;

        foreach ($recipients as $number) {
            $phone = preg_replace('/[^0-9]/', '', $number);
            if (substr($phone, 0, 1) === '0') {
                $phone = '62'.substr($phone, 1);
            }

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'template',
                    'template' => [
                        'name' => $templateName,
                        'language' => ['code' => 'id'],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    [
                                        'type' => 'text',
                                        'text' => $reportText
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $sent++;
                    $this->info("✅ Terkirim ke {$phone}");
                } else {
                    $this->warn("❌ Gagal ke {$phone}: ".$response->body());
                }
            } catch (\Exception $e) {
                $this->error("❌ Error ke {$phone}: ".$e->getMessage());
            }
        }

        $this->info("Selesai. Terkirim: {$sent}/".count($recipients));
        Log::info("Daily Report WA: Sent {$sent}/".count($recipients));

        return 0;
    }

    private function callMethod($obj, $method, $args)
    {
        $ref = new \ReflectionMethod($obj, $method);
        $ref->setAccessible(true);

        return $ref->invoke($obj, ...$args);
    }
}
