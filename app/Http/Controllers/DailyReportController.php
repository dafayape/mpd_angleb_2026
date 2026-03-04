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
        $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $isForecast, $opselFilter) {

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

            // --- B. JABODETABEK ---
            $jaboTotal = $applyOpsel(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('is_forecast', $isForecast)
                    ->where('kategori', 'PERGERAKAN')
                    ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
            )->sum('total');

            $jaboHighest = $applyOpsel(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('is_forecast', $isForecast)
                    ->where('kategori', 'PERGERAKAN')
                    ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
            )->select('tanggal', DB::raw('SUM(total) as daily_total'))
                ->groupBy('tanggal')
                ->orderByDesc('daily_total')
                ->first();

            // Formatted Dates
            Carbon::setLocale('id');
            $formattedStart = Carbon::parse($startDate)->isoFormat('D MMM YYYY');
            $formattedEnd = Carbon::parse($endDate)->isoFormat('D MMM YYYY');

            $nasionalHighestDate = $nasionalHighest
                ? Carbon::parse($nasionalHighest->tanggal)->isoFormat('dddd, D MMMM YYYY')
                : '-';
            $jaboHighestDate = $jaboHighest
                ? Carbon::parse($jaboHighest->tanggal)->isoFormat('dddd, D MMMM YYYY')
                : '-';

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_string' => "tgl {$formattedStart} s.d. {$formattedEnd}",
                'nasional_total' => $nasionalTotal,
                'nasional_highest_date' => $nasionalHighestDate,
                'nasional_highest_total' => $nasionalHighest ? $nasionalHighest->daily_total : 0,
                'jabo_total' => $jaboTotal,
                'jabo_highest_date' => $jaboHighestDate,
                'jabo_highest_total' => $jaboHighest ? $jaboHighest->daily_total : 0,
            ];
        });

        $data['kategori'] = $kategoriFilter;
        $data['opsel'] = $opselFilter;

        return view('executive.daily-report', $data);
    }

    /**
     * Send report via WhatsApp (Official Meta Cloud API)
     *
     * Meta API requires:
     *  - wa_cloud_phone_id
     *  - wa_cloud_template_name (approved WhatsApp template string)
     *  - to
     *
     * If template is not configured, falls back to building a wa.me link manual copy.
     */
    public function sendWhatsApp(Request $request)
    {
        try {
            $startDate = $request->input('start_date', '2026-03-13');
            $endDate = $request->input('end_date', '2026-03-30');
            $kategori = $request->input('kategori', 'REAL');
            $opsel = $request->input('opsel', 'ALL');

            // Build report text
            $reportText = $this->buildPlainText($startDate, $endDate, $kategori, $opsel);

            // Get settings from DB
            $settings = DB::table('app_settings')->pluck('value', 'key');
            $waNumbers = $settings->get('wa_recipients', '');
            $token = $settings->get('wa_cloud_token', '');
            $phoneId = $settings->get('wa_cloud_phone_id', '');
            $templateName = $settings->get('wa_cloud_template_name', '');

            if (empty($waNumbers)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor WhatsApp penerima belum dikonfigurasi. Silakan atur di menu Pengaturan.',
                ]);
            }

            // Check Meta Cloud API readiness
            $metaReady = ! empty($token) && ! empty($phoneId) && ! empty($templateName);

            if (! $metaReady) {
                // Fallback: return the text for manual sending
                $missing = [];
                if (empty($token)) {
                    $missing[] = 'Access Token';
                }
                if (empty($phoneId)) {
                    $missing[] = 'Phone Number ID';
                }
                if (empty($templateName)) {
                    $missing[] = 'Message Template Name';
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi WhatsApp Cloud API belum lengkap ('.implode(', ', $missing).'). Silakan lengkapi di menu Pengaturan, atau gunakan tombol Salin Teks untuk kirim manual.',
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
                    // Meta Cloud API Request
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
                                            'text' => $reportText,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]);

                    if ($response->successful()) {
                        $sent++;
                        Log::info("WA terkirim ke {$phone} via Meta API");
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

        $nasionalHighest = $applyOpsel(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)->where('kategori', 'PERGERAKAN')
        )->select('tanggal', DB::raw('SUM(total) as daily_total'))
            ->groupBy('tanggal')->orderByDesc('daily_total')->first();

        $jaboTotal = $applyOpsel(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)->where('kategori', 'PERGERAKAN')
                ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
        )->sum('total');

        $jaboHighest = $applyOpsel(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('is_forecast', $isForecast)->where('kategori', 'PERGERAKAN')
                ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
        )->select('tanggal', DB::raw('SUM(total) as daily_total'))
            ->groupBy('tanggal')->orderByDesc('daily_total')->first();

        Carbon::setLocale('id');
        $formattedStart = Carbon::parse($startDate)->isoFormat('D MMM YYYY');
        $formattedEnd = Carbon::parse($endDate)->isoFormat('D MMM YYYY');
        $periodStr = "tgl {$formattedStart} s.d. {$formattedEnd}";

        $nasHighDate = $nasionalHighest ? Carbon::parse($nasionalHighest->tanggal)->isoFormat('dddd, D MMMM YYYY') : '-';
        $jabHighDate = $jaboHighest ? Carbon::parse($jaboHighest->tanggal)->isoFormat('dddd, D MMMM YYYY') : '-';

        $nasTotal = number_format($nasionalTotal, 0, ',', '.');
        $nasHighVal = number_format($nasionalHighest ? $nasionalHighest->daily_total : 0, 0, ',', '.');
        $jabTotal = number_format($jaboTotal, 0, ',', '.');
        $jabHighVal = number_format($jaboHighest ? $jaboHighest->daily_total : 0, 0, ',', '.');

        $opselLabel = $opsel === 'ALL' ? '' : " (Opsel: {$opsel})";

        return "1. Periode Pematauan: *{$periodStr}*\n"
             ."A.\tPergerakan NASIONAL:\n"
             ."1. Total/akumulasi {$tipeTeks} pergerakan orang adalah sebanyak *{$nasTotal}* orang;\n"
             ."2. {$tipeTeksUc} pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari *{$nasHighDate}* sebanyak *{$nasHighVal}* orang.\n\n"
             ."B.\tPergerakan JABODETABEK:\n"
             ."1. Total/akumulasi {$tipeTeks} pergerakan orang adalah sebanyak *{$jabTotal}* orang;\n"
             ."2. {$tipeTeksUc} pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari *{$jabHighDate}* sebanyak *{$jabHighVal}* orang.";
    }
}
