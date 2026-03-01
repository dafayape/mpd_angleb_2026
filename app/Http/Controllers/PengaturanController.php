<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengaturanController extends Controller
{
    public function index()
    {
        $settings = DB::table('app_settings')
            ->where('group', 'whatsapp')
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
            'qontak_access_token',
            'qontak_refresh_token',
            'qontak_channel_id',
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
                    ['value' => $value, 'updated_at' => now()]
                );
        }

        // Log
        DB::table('activity_logs')->insert([
            'user_id'     => auth()->id(),
            'action'      => 'update_settings',
            'description' => 'Mengubah pengaturan WhatsApp',
            'created_at'  => now(),
            'updated_at'  => now(),
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
                ->where('group', 'whatsapp')
                ->pluck('value', 'key');

            $token   = $settings->get('qontak_access_token', '');
            $phone   = $request->input('phone', '');

            if (empty($token) || empty($phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token atau nomor WhatsApp belum diisi.'
                ]);
            }

            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ])->post('https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct', [
                'to_number' => $phone,
                'to_name'   => 'Test',
                'body'      => '✅ Test koneksi WhatsApp dari *Sistem MPD Angleb 2026* berhasil!',
            ]);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->successful()
                    ? 'Pesan test berhasil dikirim!'
                    : 'Gagal: ' . $response->body(),
                'status'  => $response->status(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
