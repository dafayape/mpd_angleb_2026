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
            ->pluck('value', 'key');

        // Check if auto-send is enabled
        if ($settings->get('wa_auto_send', '0') !== '1') {
            $this->warn('Auto-send is disabled. Enable it in Pengaturan.');

            return 0;
        }

        $sid = $settings->get('twilio_account_sid', '');
        $token = $settings->get('twilio_auth_token', '');
        $fromNumber = $settings->get('twilio_from_number', '');
        $contentSid = $settings->get('twilio_content_sid', '');
        $numbers = $settings->get('wa_recipients', '');

        if (empty($sid) || empty($token) || empty($numbers)) {
            $this->error('Twilio Credentials atau nomor penerima belum dikonfigurasi.');

            return 1;
        }

        if (empty($fromNumber) || empty($contentSid)) {
            $this->error('From Number atau Content SID belum dikonfigurasi.');

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
                $response = Http::withBasicAuth($sid, $token)
                    ->asForm()
                    ->timeout(30)
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                        'To' => 'whatsapp:+'.$phone,
                        'From' => $fromNumber,
                        'ContentSid' => $contentSid,
                        'ContentVariables' => json_encode(['1' => $reportText]),
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
