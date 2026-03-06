<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengaturanController extends Controller
{
    public function index()
    {
        $settings = DB::table('app_settings')
            ->pluck('value', 'key');

        return view('pages.pengaturan.pengaturan', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $fields = [
            'wa_recipients',
            'wa_schedule_time',
            'wa_auto_send',
            'twilio_account_sid',
            'twilio_auth_token',
            'twilio_from_number',
            'twilio_content_sid',
        ];

        foreach ($fields as $key) {
            $value = $request->input($key, '');

            // Toggle checkbox (wa_auto_send)
            if ($key === 'wa_auto_send') {
                $value = $request->has('wa_auto_send') ? '1' : '0';
            }

            DB::table('app_settings')
                ->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'general', 'updated_at' => now()]
                );
        }

        // Log
        DB::table('activity_logs')->insert([
            'user_id' => Auth::id(),
            'action' => 'update_settings',
            'description' => 'Mengubah pengaturan WhatsApp',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('pengaturan')->with('success', 'Pengaturan berhasil disimpan!');
    }

    /**
     * Test send WhatsApp to verify configuration
     */
    public function testWhatsApp(Request $request)
    {
        try {
            $settings = DB::table('app_settings')
                ->pluck('value', 'key');

            $sid = $settings->get('twilio_account_sid', '');
            $token = $settings->get('twilio_auth_token', '');
            $fromNumber = $settings->get('twilio_from_number', '');
            $phone = $request->input('phone', '');

            if (empty($sid) || empty($token) || empty($fromNumber) || empty($phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Twilio SID, Auth Token, From Number, atau nomor tujuan belum diisi.',
                ]);
            }

            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (substr($phone, 0, 1) === '0') {
                $phone = '62'.substr($phone, 1);
            }

            // Build actual report for testing (defaulting to 13 March - 30 March, Real)
            $dailyCtrl = app(\App\Http\Controllers\DailyReportController::class);
            $refMethod = new \ReflectionMethod($dailyCtrl, 'buildPlainText');
            $refMethod->setAccessible(true);
            $testBodyText = $refMethod->invoke($dailyCtrl, '2026-03-13', '2026-03-30', 'REAL', 'ALL');

            // Hit Official Twilio API
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => 'whatsapp:+'.$phone,
                    'From' => $fromNumber,
                    'Body' => $testBodyText,
                ]);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->successful()
                    ? 'Pesan test berhasil dikirim ke +'.$phone
                    : 'Gagal: '.$response->body(),
                'status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }
    }
}
