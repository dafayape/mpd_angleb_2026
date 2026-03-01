<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', '2026-03-13');
        $endDate   = $request->input('end_date', '2026-03-30');

        // Enforce Date Limits Server-Side (13 Mar 2026 - 30 Mar 2026)
        if ($startDate < '2026-03-13') $startDate = '2026-03-13';
        if ($startDate > '2026-03-30') $startDate = '2026-03-30';
        if ($endDate < '2026-03-13')   $endDate   = '2026-03-13';
        if ($endDate > '2026-03-30')   $endDate   = '2026-03-30';

        $kategoriFilter = $request->input('kategori', 'REAL');
        $isForecast     = ($kategoriFilter === 'FORECAST');
        $opselFilter    = $request->input('opsel', 'ALL');

        // Cache data for report
        $cacheKey = "dailyreport:text:v4:{$startDate}:{$endDate}:{$isForecast}:{$opselFilter}";
        $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $isForecast, $opselFilter) {

            // Jabodetabek codes
            $jabodetabekCodes = [
                '3171','3172','3173','3174','3175','3101', // DKI
                '3201','3271', // Bogor
                '3276',        // Depok
                '3603','3671','3674', // Tangerang
                '3216','3275'  // Bekasi
            ];

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
            $formattedEnd   = Carbon::parse($endDate)->isoFormat('D MMM YYYY');

            $nasionalHighestDate = $nasionalHighest
                ? Carbon::parse($nasionalHighest->tanggal)->isoFormat('dddd, D MMMM YYYY')
                : '-';
            $jaboHighestDate = $jaboHighest
                ? Carbon::parse($jaboHighest->tanggal)->isoFormat('dddd, D MMMM YYYY')
                : '-';

            return [
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'period_string'         => "tgl {$formattedStart} s.d. {$formattedEnd}",
                'nasional_total'        => $nasionalTotal,
                'nasional_highest_date' => $nasionalHighestDate,
                'nasional_highest_total'=> $nasionalHighest ? $nasionalHighest->daily_total : 0,
                'jabo_total'            => $jaboTotal,
                'jabo_highest_date'     => $jaboHighestDate,
                'jabo_highest_total'    => $jaboHighest ? $jaboHighest->daily_total : 0,
            ];
        });

        $data['kategori'] = $kategoriFilter;
        $data['opsel']    = $opselFilter;

        return view('executive.daily-report', $data);
    }

    /**
     * Send report via WhatsApp (Qontak API)
     */
    public function sendWhatsApp(Request $request)
    {
        try {
            $startDate = $request->input('start_date', '2026-03-13');
            $endDate   = $request->input('end_date', '2026-03-30');
            $kategori  = $request->input('kategori', 'REAL');
            $opsel     = $request->input('opsel', 'ALL');

            // Build report text
            $reportText = $this->buildPlainText($startDate, $endDate, $kategori, $opsel);

            // Get settings from DB
            $settings = DB::table('app_settings')->pluck('value', 'key');
            $waNumbers = $settings->get('wa_recipients', '');
            $token     = $settings->get('qontak_access_token', '');

            if (empty($waNumbers) || empty($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor WhatsApp atau token Qontak belum dikonfigurasi. Silakan atur di menu Pengaturan.'
                ]);
            }

            $recipients = array_filter(array_map('trim', explode(',', $waNumbers)));
            $sent = 0;
            $errors = [];

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
                        'channel_integration_id' => $settings->get('qontak_channel_id', ''),
                        'body'                   => $reportText,
                    ]);

                    if ($response->successful()) {
                        $sent++;
                    } else {
                        $errors[] = $phone . ': ' . $response->body();
                    }
                } catch (\Exception $e) {
                    $errors[] = $phone . ': ' . $e->getMessage();
                }
            }

            // Log activity
            if (auth()->check()) {
                DB::table('activity_logs')->insert([
                    'user_id'    => auth()->id(),
                    'action'     => 'send_daily_report_wa',
                    'description'=> "Kirim Daily Report WA ke " . count($recipients) . " nomor. Berhasil: {$sent}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($sent > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil mengirim ke {$sent} dari " . count($recipients) . " penerima."
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim. ' . implode('; ', array_slice($errors, 0, 2))
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp Send Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Build plain text report (WhatsApp markdown format)
     */
    public function buildPlainText($startDate, $endDate, $kategori, $opsel = 'ALL')
    {
        $isForecast = ($kategori === 'FORECAST');
        $tipeTeks   = $isForecast ? 'prediksi' : 'realisasi';
        $tipeTeksUc = $isForecast ? 'Prediksi' : 'Realisasi';

        $jabodetabekCodes = [
            '3171','3172','3173','3174','3175','3101',
            '3201','3271','3276',
            '3603','3671','3674',
            '3216','3275'
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
        $formattedEnd   = Carbon::parse($endDate)->isoFormat('D MMM YYYY');
        $periodStr      = "tgl {$formattedStart} s.d. {$formattedEnd}";

        $nasHighDate = $nasionalHighest ? Carbon::parse($nasionalHighest->tanggal)->isoFormat('dddd, D MMMM YYYY') : '-';
        $jabHighDate = $jaboHighest ? Carbon::parse($jaboHighest->tanggal)->isoFormat('dddd, D MMMM YYYY') : '-';

        $nasTotal    = number_format($nasionalTotal, 0, ',', '.');
        $nasHighVal  = number_format($nasionalHighest ? $nasionalHighest->daily_total : 0, 0, ',', '.');
        $jabTotal    = number_format($jaboTotal, 0, ',', '.');
        $jabHighVal  = number_format($jaboHighest ? $jaboHighest->daily_total : 0, 0, ',', '.');

        $opselLabel = $opsel === 'ALL' ? '' : " (Opsel: {$opsel})";

        return "Yth. *Bapak Kepala Badan Kebijakan Transportasi*\n\n"
             . "Dengan hormat, izin melaporkan perkembangan pemantauan pergerakan orang pada periode Angleb 2026 "
             . "dengan menggunakan _Mobile Positioning Data_ (MPD){$opselLabel} posisi dari *{$periodStr}* sebagai berikut:\n\n"
             . "A.\tPergerakan NASIONAL:\n"
             . "1. Total/akumulasi {$tipeTeks} pergerakan orang adalah sebanyak *{$nasTotal}* orang;\n"
             . "2. {$tipeTeksUc} pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari *{$nasHighDate}* sebanyak *{$nasHighVal}* orang.\n\n"
             . "B.\tPergerakan JABODETABEK:\n"
             . "1. Total/akumulasi {$tipeTeks} pergerakan orang adalah sebanyak *{$jabTotal}* orang;\n"
             . "2. {$tipeTeksUc} pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari *{$jabHighDate}* sebanyak *{$jabHighVal}* orang.\n\n"
             . "Demikian disampaikan dan mohon arahannya.\n\n"
             . "Terima kasih.";
    }
}
