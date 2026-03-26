<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $minDate = config('mpd.start_date');
        $maxDate = config('mpd.end_date');

        $startDate = $request->input('start_date', $minDate);
        $endDate = $request->input('end_date', $maxDate);

        // Enforce Date Limits Server-Side
        $startDate = max($minDate, min($maxDate, $startDate));
        $endDate = max($minDate, min($maxDate, $endDate));

        $kategoriFilter = $request->input('kategori', 'REAL');
        $isForecast = ($kategoriFilter === 'FORECAST');
        $opselFilter = $request->input('opsel', 'ALL');

        // Cache data for report
        $cacheKey = "dailyreport:text:v4:{$startDate}:{$endDate}:{$isForecast}:{$opselFilter}";
        $data = Cache::remember($cacheKey, config('mpd.cache_ttl.data_page', 21600), function () use ($startDate, $endDate, $isForecast, $opselFilter, $kategoriFilter) {

            // Jabodetabek codes
            $jabodetabekCodes = config('mpd.jabodetabek_codes');

            // Opsel filter helper
            $applyOpsel = function ($query) use ($opselFilter) {
                if ($opselFilter !== 'ALL') {
                    $query->where('opsel', $opselFilter);
                }

                return $query;
            };

            // --- A. NASIONAL ---
            $nasionalTotal = $applyOpsel(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('is_forecast', $isForecast)
                    ->where('kategori', 'PERGERAKAN')
            )->sum('total');

            $nasionalHighest = $applyOpsel(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('is_forecast', $isForecast)
                    ->where('kategori', 'PERGERAKAN')
            )->select('tanggal', DB::raw('SUM(total) as daily_total'))
                ->groupBy('tanggal')
                ->orderByDesc('daily_total')
                ->first();

            $nasionalUnique = $applyOpsel(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('is_forecast', $isForecast)
                    ->where('kategori', 'ORANG')
            )->sum('total');

            // --- Top 5 Provinsi Asal ---
            $top5Asal = $applyOpsel(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('is_forecast', $isForecast)
                    ->where('kategori', 'PERGERAKAN')
            )->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_origin_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
             ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
             ->groupBy('ref_provinces.name')
             ->orderByDesc('total')
             ->limit(5)
             ->get();

            // --- Top 5 Provinsi Tujuan ---
            $top5Tujuan = $applyOpsel(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('is_forecast', $isForecast)
                    ->where('kategori', 'PERGERAKAN')
            )->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_dest_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
             ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
             ->groupBy('ref_provinces.name')
             ->orderByDesc('total')
             ->limit(5)
             ->get();

            // Formatted Dates
            Carbon::setLocale('id');
            $formattedStart = Carbon::parse($startDate)->isoFormat('D MMMM YYYY');
            $formattedEnd = Carbon::parse($endDate)->isoFormat('D MMMM YYYY');
            $formattedEndWithDay = Carbon::parse($endDate)->isoFormat('dddd, D MMMM YYYY');

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_string' => "tgl {$formattedStart} s.d. {$formattedEnd}",
                'kategori' => $kategoriFilter,
                'opsel' => $opselFilter,
                'formatted_start' => $formattedStart,
                'formatted_end' => $formattedEnd,
                'formatted_end_day' => $formattedEndWithDay,
                'nasional_total' => $nasionalTotal,
                'nasional_unique' => $nasionalUnique,
                'top5_asal' => $top5Asal,
                'top5_tujuan' => $top5Tujuan,
            ];
        });

        $data['kategori'] = $kategoriFilter;
        $data['opsel'] = $opselFilter;

        return view('executive.daily-report', $data);
    }

    /**
     * Send report via WhatsApp (Official Twilio API)
     *
     * Twilio API requires:
     *  - twilio_account_sid
     *  - twilio_auth_token
     *  - twilio_from_number
     *  - twilio_content_sid (approved WhatsApp template string)
     *  - to
     *
     * If template is not configured, falls back to building a wa.me link manual copy.
     */
    public function sendWhatsApp(Request $request)
    {
        try {
            $startDate = $request->input('start_date', config('mpd.start_date', '2026-03-13'));
            $endDate = $request->input('end_date', config('mpd.end_date', '2026-03-29'));
            $kategori = $request->input('kategori', 'REAL');
            $opsel = $request->input('opsel', 'ALL');

            // Build report text
            $reportText = $this->buildPlainText($startDate, $endDate, $kategori, $opsel);

            // Get settings from DB
            $settings = DB::table('app_settings')->pluck('value', 'key');
            $waNumbers = $settings->get('wa_recipients', '');
            $sid = $settings->get('twilio_account_sid', '');
            $token = $settings->get('twilio_auth_token', '');
            $fromNumber = $settings->get('twilio_from_number', '');
            $contentSid = $settings->get('twilio_content_sid', '');

            if (empty($waNumbers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor WhatsApp penerima belum dikonfigurasi. Silakan atur di menu Pengaturan.',
                ]);
            }

            // Check Twilio API readiness
            $twilioReady = ! empty($sid) && ! empty($token) && ! empty($fromNumber);

            if (! $twilioReady) {
                // Fallback: return the text for manual sending
                $missing = [];
                if (empty($sid) || empty($token)) {
                    $missing[] = 'Account SID / Auth Token';
                }
                if (empty($fromNumber)) {
                    $missing[] = 'Twilio From Number';
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi Twilio API belum lengkap ('.implode(', ', $missing).'). Silakan lengkapi di menu Pengaturan, atau gunakan tombol Salin Teks untuk kirim manual.',
                    'fallback' => true,
                    'report_text' => $reportText,
                ]);
            }

            $recipients = array_filter(array_map('trim', explode(',', $waNumbers)));
            $sent = 0;
            $errors = [];

            foreach ($recipients as $number) {
                $phone = preg_replace('/[^0-9]/', '', $number);
                if (substr($phone, 0, 1) === '0') {
                    $phone = '62'.substr($phone, 1);
                }

                try {
                    // Twilio API Request Custom Message
                    $response = Http::withBasicAuth($sid, $token)
                        ->asForm()
                        ->timeout(30)
                        ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                            'To' => 'whatsapp:+' . $phone,
                            'From' => $fromNumber,
                            'Body' => $reportText,
                        ]);

                    if ($response->successful()) {
                        $sent++;
                        Log::info("WA terkirim ke {$phone} via Twilio API");
                    } else {
                        $errBody = $response->body();
                        $errors[] = $phone.': '.$errBody;
                        Log::warning("WA gagal ke {$phone}: {$errBody}");
                    }
                } catch (\Exception $e) {
                    $errors[] = $phone.': '.$e->getMessage();
                    Log::error("WA exception ke {$phone}: ".$e->getMessage());
                }
            }

            // Log activity
            if (Auth::check()) {
                DB::table('activity_logs')->insert([
                    'user_id' => Auth::id(),
                    'action' => 'send_daily_report_wa',
                    'description' => 'Kirim Daily Report WA ke '.count($recipients)." nomor. Berhasil: {$sent}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($sent > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil mengirim ke {$sent} dari ".count($recipients).' penerima.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim. '.implode('; ', array_slice($errors, 0, 2)),
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp Send Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Build plain text report (WhatsApp markdown format)
     */
    public function buildPlainText($startDate, $endDate, $kategori, $opsel = 'ALL')
    {
        $isForecast = ($kategori === 'FORECAST');
        $tipeTeks = $isForecast ? 'prediksi' : 'realisasi';
        $tipeTeksUc = $isForecast ? 'Prediksi' : 'Realisasi';

        $jabodetabekCodes = [
            '3171', '3172', '3173', '3174', '3175', '3101',
            '3201', '3271', '3276',
            '3603', '3671', '3674',
            '3216', '3275',
        ];

        $applyOpsel = function ($query) use ($opsel) {
            if ($opsel !== 'ALL') {
                $query->where('opsel', $opsel);
            }

            return $query;
        };

        $nasionalTotal = $applyOpsel(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)->where('kategori', 'PERGERAKAN')
        )->sum('total');

        $nasionalUnique = $applyOpsel(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)->where('kategori', 'ORANG')
        )->sum('total');

        $top5Asal = $applyOpsel(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)->where('kategori', 'PERGERAKAN')
        )->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_origin_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
         ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
         ->groupBy('ref_provinces.name')->orderByDesc('total')->limit(5)->get();

        $top5Tujuan = $applyOpsel(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)->where('kategori', 'PERGERAKAN')
        )->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_dest_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
         ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
         ->groupBy('ref_provinces.name')->orderByDesc('total')->limit(5)->get();

        Carbon::setLocale('id');
        $formattedStart = Carbon::parse($startDate)->isoFormat('D MMMM YYYY');
        $formattedEnd = Carbon::parse($endDate)->isoFormat('D MMMM YYYY');
        $formattedEndDay = Carbon::parse($endDate)->isoFormat('dddd, D MMMM YYYY');
        
        $nasTotal = number_format($nasionalTotal, 0, ',', '.');
        $nasUnique = number_format($nasionalUnique, 0, ',', '.');

        $str = "Yth. Bapak Kepala Badan Kebijakan Transportasi\n\n";
        $str .= "Izin melaporkan, berdasarkan hasil pemantauan sementara pergerakan orang dengan menggunakan MPD dari 3 Operator Seluler (Tsel, Indosat & XLSmart), dengan ini kami laporkan perolehan data MPD tersebut dengan posisi hari {$formattedEndDay} (akumulasi data {$tipeTeks} dari tgl {$formattedStart} s.d. {$formattedEnd}), sbb:\n\n";
        $str .= "A.  Jumlah total Nasional sebesar {$nasTotal} pergerakan dengan jumlah unique subscriber sebesar {$nasUnique} orang\n\n";
        $str .= "B.  Top 5 Provinsi Asal dan Tujuan, sbb:\n\n";
        $str .= "1.  Provinsi Asal\n";
        
        $letters = ['a', 'b', 'c', 'd', 'e'];
        foreach ($top5Asal as $idx => $item) {
            $ltr = $letters[$idx] ?? 'a';
            $val = number_format($item->total, 0, ',', '.');
            $str .= "{$ltr}.  {$item->nama_provinsi} sebesar {$val} pergerakan;\n";
        }

        $str .= "\n2.  Provinsi Tujuan\n";
        foreach ($top5Tujuan as $idx => $item) {
            $ltr = $letters[$idx] ?? 'a';
            $val = number_format($item->total, 0, ',', '.');
            $str .= "{$ltr}.  {$item->nama_provinsi} sebesar {$val} pergerakan;\n";
        }

        $str .= "\nDemikian kami sampaikan, atas perkenan dan arahan Bapak Kepala Badan Kebijakan Transportasi diucapkan terima kasih.\n\n";
        $str .= "Hormat kami,\nKapusjak LLAT\nM. Arief Affandi";

        return $str;
    }
}
