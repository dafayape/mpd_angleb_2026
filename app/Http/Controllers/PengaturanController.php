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
            'wa_cloud_token',
            'wa_cloud_phone_id',
            'wa_cloud_template_name',
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
            'user_id'     => Auth::id(),
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

            $token   = $settings->get('wa_cloud_token', '');
            $phoneId   = $settings->get('wa_cloud_phone_id', '');
            $phone   = $request->input('phone', '');

            if (empty($token) || empty($phoneId) || empty($phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token, Phone ID, atau nomor WhatsApp tujuan belum diisi.'
                ]);
            }

            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }

            // Hit Official Meta WhatsApp Cloud API
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ])->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => 'text',
                'text' => [
                    'body' => '✅ Test koneksi dari *Sistem MPD Angleb 2026* menggunakan Meta Cloud API berhasil!'
                ]
            ]);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->successful()
                    ? 'Pesan test berhasil dikirim ke ' . $phone
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
