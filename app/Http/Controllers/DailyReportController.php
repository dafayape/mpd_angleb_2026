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

        $kategoriFilter = $request->input('kategori', 'COMBINED');
        $isForecast = ($kategoriFilter === 'FORECAST');
        $opselFilter = $request->input('opsel', 'ALL');

        // Cache data for report
        $cacheKey = "dailyreport:text:v7_sapu_bersih:{$startDate}:{$endDate}:{$kategoriFilter}:{$opselFilter}";
        $data = Cache::remember($cacheKey, config('mpd.cache_ttl.data_page', 21600), function () use ($startDate, $endDate, $isForecast, $opselFilter, $kategoriFilter) {

            // Jabodetabek codes
            $jabodetabekCodes = config('mpd.jabodetabek_codes');

            // Opsel & Type filter helper
            $applyFilters = function ($query) use ($opselFilter, $kategoriFilter) {
                if ($opselFilter !== 'ALL') {
                    $query->where('spatial_movements.opsel', $opselFilter);
                }

                if ($kategoriFilter === 'COMBINED') {
                    $query->where(function ($q) {
                        $q->where(function ($realQ) {
                            $realQ->where('spatial_movements.is_forecast', false)
                                  ->whereNotIn(DB::raw('DATE(spatial_movements.tanggal)'), ['2026-03-27', '2026-03-28', '2026-03-29']);
                        })->orWhere(function ($forecastQ) {
                            $forecastQ->where('spatial_movements.is_forecast', true)
                                      ->where(function ($cond) {
                                          $cond->whereIn(DB::raw('DATE(spatial_movements.tanggal)'), ['2026-03-27', '2026-03-28', '2026-03-29'])
                                               ->orWhereNotExists(function ($exists) {
                                                   $exists->select(DB::raw(1))
                                                          ->from('spatial_movements as sm2')
                                                          ->whereColumn(DB::raw('DATE(sm2.tanggal)'), DB::raw('DATE(spatial_movements.tanggal)'))
                                                          ->whereColumn('sm2.opsel', 'spatial_movements.opsel')
                                                          ->where('sm2.kategori', '!=', 'ORANG')
                                                          ->where('sm2.is_forecast', false);
                                               });
                                      });
                        });
                    });
                } elseif ($kategoriFilter === 'FORECAST') {
                    $query->where('spatial_movements.is_forecast', true);
                } else {
                    $query->where('spatial_movements.is_forecast', false);
                }

                return $query;
            };

            // --- A. NASIONAL ---
            $nasionalTotal = $applyFilters(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', '!=', 'ORANG')
            )->sum('total');

            // Hitung unique subscriber per-opsel menggunakan koefisien per-batch
            // (Konsisten dengan sistem koefisien di DataMpdController)
            $pergerakanPerOpsel = $applyFilters(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', '!=', 'ORANG')
            )->select('opsel', DB::raw('SUM(total) as total_pergerakan'))
             ->groupBy('opsel')
             ->pluck('total_pergerakan', 'opsel');

            // Pilih batch koefisien berdasarkan end_date atau final override
            $finalKoef = config('mpd_koefisien.final');
            if (!empty($finalKoef) && is_array($finalKoef)) {
                $selectedBatch = $finalKoef;
            } else {
                $batches = config('mpd_koefisien.batches', []);
                $selectedBatch = null;
                foreach ($batches as $batch) {
                    if (isset($batch['end_date']) && $batch['end_date'] >= $endDate) {
                        $selectedBatch = $batch;
                        break;
                    }
                }
                if (! $selectedBatch && ! empty($batches)) {
                    $selectedBatch = end($batches);
                }
            }

            $opselKoefMap = ['TSEL' => 'TSEL', 'IOH' => 'IOH', 'XLSMART' => 'XLSMART'];
            $nasionalUnique = 0;
            foreach ($opselKoefMap as $opselKey => $configKey) {
                $pergerakan = (float) ($pergerakanPerOpsel[$opselKey] ?? 0);
                $koefisien = (float) ($selectedBatch[$configKey] ?? 1.0);
                $nasionalUnique += $koefisien > 0 ? round($pergerakan / $koefisien) : 0;
            }

            $jaboTotal = $applyFilters(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', '!=', 'ORANG')
                    ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
            )->sum('total');

            // --- Top 5 Provinsi Asal ---
            $top5Asal = $applyFilters(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', '!=', 'ORANG')
            )->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_origin_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
             ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
             ->groupBy('ref_provinces.name')
             ->orderByDesc('total')
             ->limit(5)
             ->get();

            // --- Top 5 Provinsi Tujuan ---
            $top5Tujuan = $applyFilters(
                \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', '!=', 'ORANG')
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
            $formattedEndWithDay = Carbon::now()->isoFormat('dddd, D MMMM YYYY');

            $hariRaya = Carbon::parse('2026-03-21');
            $diffStart = (int) $hariRaya->diffInDays(Carbon::parse($startDate), false);
            $diffEnd = (int) $hariRaya->diffInDays(Carbon::parse($endDate), false);
            
            $formatHDay = function($diff) {
                if ($diff < 0) return 'H' . $diff;
                if ($diff > 0) return 'H+' . $diff;
                return 'Hari Raya';
            };

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_string' => "tgl {$formattedStart} s.d. {$formattedEnd}",
                'kategori' => $kategoriFilter,
                'opsel' => $opselFilter,
                'formatted_start' => $formattedStart,
                'formatted_end' => $formattedEnd,
                'formatted_end_day' => $formattedEndWithDay,
                'h_start' => $formatHDay($diffStart),
                'h_end' => $formatHDay($diffEnd),
                'nasional_total' => $nasionalTotal,
                'nasional_unique' => $nasionalUnique,
                'jabo_total' => $jaboTotal,
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
        $tipeTeks = $kategori === 'FORECAST' ? 'prediksi' : ($kategori === 'COMBINED' ? 'gabungan real & prediksi' : 'realisasi');

        $applyFilters = function ($query) use ($opsel, $kategori) {
            if ($opsel !== 'ALL') {
                $query->where('spatial_movements.opsel', $opsel);
            }

            if ($kategori === 'COMBINED') {
                $query->where(function ($q) {
                    $q->where(function ($realQ) {
                        $realQ->where('spatial_movements.is_forecast', false)
                              ->whereNotIn(DB::raw('DATE(spatial_movements.tanggal)'), ['2026-03-27', '2026-03-28', '2026-03-29']);
                    })->orWhere(function ($forecastQ) {
                        $forecastQ->where('spatial_movements.is_forecast', true)
                                  ->where(function ($cond) {
                                      $cond->whereIn(DB::raw('DATE(spatial_movements.tanggal)'), ['2026-03-27', '2026-03-28', '2026-03-29'])
                                           ->orWhereNotExists(function ($exists) {
                                               $exists->select(DB::raw(1))
                                                      ->from('spatial_movements as sm2')
                                                      ->whereColumn(DB::raw('DATE(sm2.tanggal)'), DB::raw('DATE(spatial_movements.tanggal)'))
                                                      ->whereColumn('sm2.opsel', 'spatial_movements.opsel')
                                                      ->where('sm2.kategori', '!=', 'ORANG')
                                                      ->where('sm2.is_forecast', false);
                                           });
                                  });
                    });
                });
            } elseif ($kategori === 'FORECAST') {
                $query->where('spatial_movements.is_forecast', true);
            } else {
                $query->where('spatial_movements.is_forecast', false);
            }

            return $query;
        };

        $nasionalTotal = $applyFilters(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('kategori', '!=', 'ORANG')
        )->sum('total');

        // Hitung unique subscriber per-opsel menggunakan koefisien per-batch
        $pergerakanPerOpsel = $applyFilters(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('kategori', '!=', 'ORANG')
        )->select('opsel', DB::raw('SUM(total) as total_pergerakan'))
         ->groupBy('opsel')
         ->pluck('total_pergerakan', 'opsel');

        $finalKoef = config('mpd_koefisien.final');
        if (!empty($finalKoef) && is_array($finalKoef)) {
            $selectedBatch = $finalKoef;
        } else {
            $batches = config('mpd_koefisien.batches', []);
            $selectedBatch = null;
            foreach ($batches as $batch) {
                if (isset($batch['end_date']) && $batch['end_date'] >= $endDate) {
                    $selectedBatch = $batch;
                    break;
                }
            }
            if (! $selectedBatch && ! empty($batches)) {
                $selectedBatch = end($batches);
            }
        }

        $nasionalUnique = 0;
        foreach (['TSEL', 'IOH', 'XLSMART'] as $opselKey) {
            $pergerakan = (float) ($pergerakanPerOpsel[$opselKey] ?? 0);
            $koefisien = (float) ($selectedBatch[$opselKey] ?? 1.0);
            $nasionalUnique += $koefisien > 0 ? round($pergerakan / $koefisien) : 0;
        }

        $top5Asal = $applyFilters(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('kategori', '!=', 'ORANG')
        )->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_origin_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
         ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
         ->groupBy('ref_provinces.name')->orderByDesc('total')->limit(5)->get();

        $top5Tujuan = $applyFilters(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])
                ->where('kategori', '!=', 'ORANG')
        )->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_dest_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
         ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
         ->groupBy('ref_provinces.name')->orderByDesc('total')->limit(5)->get();

        Carbon::setLocale('id');
        $formattedStart = Carbon::parse($startDate)->isoFormat('D MMMM YYYY');
        $formattedEnd = Carbon::parse($endDate)->isoFormat('D MMMM YYYY');
        $formattedEndDay = Carbon::now()->isoFormat('dddd, D MMMM YYYY');
        
        $hariRaya = Carbon::parse('2026-03-21');
        $formatHDay = function($diff) {
            if ($diff < 0) return 'H' . $diff;
            if ($diff > 0) return 'H+' . $diff;
            return 'Hari Raya';
        };
        $hStart = $formatHDay((int) $hariRaya->diffInDays(Carbon::parse($startDate), false));
        $hEnd = $formatHDay((int) $hariRaya->diffInDays(Carbon::parse($endDate), false));
        
        $nasTotal = number_format($nasionalTotal, 0, ',', '.');
        $nasUnique = number_format($nasionalUnique, 0, ',', '.');

        $str = "Yth. Bapak Kepala Badan Kebijakan Transportasi,\n\n";
        $str .= "Izin melaporkan, berdasarkan hasil pemantauan sementara pergerakan orang menggunakan Mobile Positioning Data (MPD) dari 3 operator seluler (Telkomsel, Indosat, dan XLSmart), bersama ini kami sampaikan capaian data MPD per {$formattedEndDay}.\n\n";
        $str .= "Data tersebut merupakan akumulasi {$tipeTeks} periode {$formattedStart} s.d. {$formattedEnd} ({$hStart} s.d. {$hEnd}). Secara nasional tercatat sebanyak {$nasUnique} orang melakukan perjalanan di periode tersebut.\n\n";
        $str .= "Demikian kami sampaikan. Atas perkenan dan arahan Bapak, kami ucapkan terima kasih.";

        return $str;
    }
}
