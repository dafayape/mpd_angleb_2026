<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Traits\MpdHelpers;

class DailyReportController extends Controller
{
    use MpdHelpers;

    public function index(Request $request)
    {
        $minDate = config('mpd.start_date', '2026-03-13');
        $maxDate = config('mpd.end_date', '2026-03-29');

        $startDate = $request->input('start_date', $minDate);
        $endDate = $request->input('end_date', $maxDate);

        // Enforce Date Limits Server-Side
        $startDate = max($minDate, min($maxDate, $startDate));
        $endDate = max($minDate, min($maxDate, $endDate));

        $kategoriFilter = $request->input('kategori', 'COMBINED');
        $opselFilter = $request->input('opsel', 'ALL');

        // Cache data for report - Bumped to v10 for Method 360
        $cacheKey = "dailyreport:text:v11_override_combined:{$startDate}:{$endDate}:{$kategoriFilter}:{$opselFilter}";
        $data = Cache::remember($cacheKey, config('mpd.cache_ttl.data_page', 21600), function () use ($startDate, $endDate, $opselFilter, $kategoriFilter) {

            $jabodetabekCodes = config('mpd.jabodetabek_codes');
            $forceForecastDates = ['2026-03-27', '2026-03-28', '2026-03-29'];

            $buildBaseQuery = function (bool $isForecastFlag) use ($startDate, $endDate, $opselFilter) {
                $q = DB::table('spatial_movements')
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->where('kategori', '!=', 'ORANG')
                    ->where('is_forecast', $isForecastFlag);
                if ($opselFilter !== 'ALL') {
                    $q->where('opsel', $opselFilter);
                }
                return $q;
            };

            $indexByDateOpsel = function ($rows) {
                $map = [];
                foreach ($rows as $r) {
                    $op = $this->normalizeOpsel($r->opsel);
                    $map[$r->date_val][$op] = ($map[$r->date_val][$op] ?? 0) + (float) $r->total;
                }
                return $map;
            };

            // Koefisien
            $finalKoef = config('mpd_koefisien.final', []);
            $koefBatches = config('mpd_koefisien.batches', []);
            $selectedBatch = !empty($finalKoef) ? $finalKoef : (end($koefBatches) ?: []);

            $nasionalTotal  = 0;
            $nasionalUnique = 0;

            if ($kategoriFilter === 'COMBINED') {
                $realDailyRows = $buildBaseQuery(false)
                    ->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', DB::raw('SUM(total) as total'))
                    ->groupBy(DB::raw('DATE(tanggal)'), 'opsel')
                    ->get();
                $forecastDailyRows = $buildBaseQuery(true)
                    ->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', DB::raw('SUM(total) as total'))
                    ->groupBy(DB::raw('DATE(tanggal)'), 'opsel')
                    ->get();

                $realMap     = $indexByDateOpsel($realDailyRows);
                $forecastMap = $indexByDateOpsel($forecastDailyRows);

                $allDates = array_unique(array_merge(array_keys($realMap), array_keys($forecastMap)));
                
                $uniqueSumFloat = 0;
                foreach ($allDates as $d) {
                    $isForced = in_array($d, $forceForecastDates);
                    $allOpsels = array_unique(array_merge(
                        array_keys($realMap[$d] ?? []),
                        array_keys($forecastMap[$d] ?? [])
                    ));
                    foreach ($allOpsels as $op) {
                        if ($op === 'OTHER') continue;
                        $rVol = $realMap[$d][$op] ?? 0;
                        $fVol = $forecastMap[$d][$op] ?? 0;
                        $vol  = ($rVol > 0 && !$isForced) ? $rVol : $fVol;
                        
                        if ($vol > 0) {
                            $koef = (float) ($selectedBatch[$op] ?? 1.0);
                            $nasionalTotal += $vol;
                            $uniqueSumFloat += ($vol / $koef);
                        }
                    }
                }
                $nasionalUnique = (int) round($uniqueSumFloat);

                // Override Alternatif 3 BKT: angka resmi posko angleb 2026
                if ($kategoriFilter === 'COMBINED') {
                    $nasionalUnique = 147551770;
                }

                // Jabodetabek
                $realJaboRows = $buildBaseQuery(false)
                    ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                    ->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', DB::raw('SUM(total) as total'))
                    ->groupBy(DB::raw('DATE(tanggal)'), 'opsel')
                    ->get();
                $forecastJaboRows = $buildBaseQuery(true)
                    ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                    ->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', DB::raw('SUM(total) as total'))
                    ->groupBy(DB::raw('DATE(tanggal)'), 'opsel')
                    ->get();
                $realJaboMap     = $indexByDateOpsel($realJaboRows);
                $forecastJaboMap = $indexByDateOpsel($forecastJaboRows);

                $allJaboDates = array_unique(array_merge(array_keys($realJaboMap), array_keys($forecastJaboMap)));
                $jaboTotal = 0;
                foreach ($allJaboDates as $d) {
                    $isForced = in_array($d, $forceForecastDates);
                    $ops = array_unique(array_merge(array_keys($realJaboMap[$d] ?? []), array_keys($forecastJaboMap[$d] ?? [])));
                    foreach ($ops as $op) {
                        if ($op === 'OTHER') continue;
                        $rV = $realJaboMap[$d][$op] ?? 0;
                        $fV = $forecastJaboMap[$d][$op] ?? 0;
                        $jaboTotal += ($rV > 0 && !$isForced) ? $rV : $fV;
                    }
                }

                // Top 5 Provinces
                $top5Asal = DB::table('spatial_movements')
                    ->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_origin_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
                    ->whereBetween('spatial_movements.tanggal', [$startDate, $endDate])
                    ->where('spatial_movements.kategori', '!=', 'ORANG')
                    ->where(function ($q) use ($forceForecastDates) {
                        $q->where(function ($rQ) use ($forceForecastDates) {
                            $rQ->where('is_forecast', false)
                               ->whereNotIn(DB::raw('DATE(spatial_movements.tanggal)'), $forceForecastDates);
                        })->orWhere(function ($fQ) use ($forceForecastDates) {
                            $fQ->where('is_forecast', true)
                               ->whereIn(DB::raw('DATE(spatial_movements.tanggal)'), $forceForecastDates);
                        });
                    })
                    ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
                    ->groupBy('ref_provinces.name')->orderByDesc('total')->limit(5)->get();

                $top5Tujuan = DB::table('spatial_movements')
                    ->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_dest_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
                    ->whereBetween('spatial_movements.tanggal', [$startDate, $endDate])
                    ->where('spatial_movements.kategori', '!=', 'ORANG')
                    ->where(function ($q) use ($forceForecastDates) {
                        $q->where(function ($rQ) use ($forceForecastDates) {
                            $rQ->where('is_forecast', false)
                               ->whereNotIn(DB::raw('DATE(spatial_movements.tanggal)'), $forceForecastDates);
                        })->orWhere(function ($fQ) use ($forceForecastDates) {
                            $fQ->where('is_forecast', true)
                               ->whereIn(DB::raw('DATE(spatial_movements.tanggal)'), $forceForecastDates);
                        });
                    })
                    ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
                    ->groupBy('ref_provinces.name')->orderByDesc('total')->limit(5)->get();

            } else {
                $isForecastFlag = ($kategoriFilter === 'FORECAST');
                $baseQ = $buildBaseQuery($isForecastFlag);

                $nasionalTotal = (clone $baseQ)->sum('total');

                $dailyStats = (clone $baseQ)
                    ->select(DB::raw('DATE(tanggal) as date_val'), 'opsel', DB::raw('SUM(total) as total'))
                    ->groupBy(DB::raw('DATE(tanggal)'), 'opsel')
                    ->get();

                $uniqueSumFloat = 0;
                foreach ($dailyStats as $stat) {
                    $op   = $this->normalizeOpsel($stat->opsel);
                    $vol  = (float) $stat->total;
                    $koef = (float) ($selectedBatch[$op] ?? 1.0);
                    $uniqueSumFloat += ($vol / $koef);
                }
                $nasionalUnique = (int) round($uniqueSumFloat);

                $jaboTotal = (clone $baseQ)
                    ->whereIn('kode_origin_kabupaten_kota', $jabodetabekCodes)
                    ->sum('total');

                $top5Asal = (clone $baseQ)
                    ->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_origin_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
                    ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
                    ->groupBy('ref_provinces.name')->orderByDesc('total')->limit(5)->get();

                $top5Tujuan = (clone $baseQ)
                    ->join('ref_provinces', DB::raw('SUBSTRING(spatial_movements.kode_dest_kabupaten_kota, 1, 2)'), '=', 'ref_provinces.code')
                    ->select('ref_provinces.name as nama_provinsi', DB::raw('SUM(spatial_movements.total) as total'))
                    ->groupBy('ref_provinces.name')->orderByDesc('total')->limit(5)->get();
            }

            Carbon::setLocale('id');
            $formattedStart      = Carbon::parse($startDate)->isoFormat('D MMMM YYYY');
            $formattedEnd        = Carbon::parse($endDate)->isoFormat('D MMMM YYYY');
            $formattedEndWithDay = Carbon::now()->isoFormat('dddd, D MMMM YYYY');

            $hariRaya  = Carbon::parse('2026-03-21');
            $diffStart = (int) $hariRaya->diffInDays(Carbon::parse($startDate), false);
            $diffEnd   = (int) $hariRaya->diffInDays(Carbon::parse($endDate), false);

            $formatHDay = function ($diff) {
                if ($diff < 0) return 'H' . $diff;
                if ($diff > 0) return 'H+' . $diff;
                return 'Hari Raya';
            };

            return [
                'start_date'        => $startDate,
                'end_date'          => $endDate,
                'period_string'     => "tgl {$formattedStart} s.d. {$formattedEnd}",
                'kategori'          => $kategoriFilter,
                'opsel'             => $opselFilter,
                'formatted_start'   => $formattedStart,
                'formatted_end'     => $formattedEnd,
                'formatted_end_day' => $formattedEndWithDay,
                'h_start'           => $formatHDay($diffStart),
                'h_end'             => $formatHDay($diffEnd),
                'nasional_total'    => $nasionalTotal,
                'nasional_unique'   => $nasionalUnique,
                'jabo_total'        => $jaboTotal,
                'top5_asal'         => $top5Asal,
                'top5_tujuan'       => $top5Tujuan,
            ];
        });

        $data['kategori'] = $kategoriFilter;
        $data['opsel'] = $opselFilter;

        return view('executive.daily-report', $data);
    }

    public function sendWhatsApp(Request $request)
    {
        try {
            $startDate = $request->input('start_date', config('mpd.start_date', '2026-03-13'));
            $endDate = $request->input('end_date', config('mpd.end_date', '2026-03-29'));
            $kategori = $request->input('kategori', 'REAL');
            $opsel = $request->input('opsel', 'ALL');

            $reportText = $this->buildPlainText($startDate, $endDate, $kategori, $opsel);

            $settings = DB::table('app_settings')->pluck('value', 'key');
            $waNumbers = $settings->get('wa_recipients', '');
            $sid = $settings->get('twilio_account_sid', '');
            $token = $settings->get('twilio_auth_token', '');
            $fromNumber = $settings->get('twilio_from_number', '');

            if (empty($waNumbers)) {
                return response()->json(['success' => false, 'message' => 'Nomor WhatsApp belum diatur.']);
            }

            if (empty($sid) || empty($token) || empty($fromNumber)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Twilio API belum lengkap.', 
                    'fallback' => true, 
                    'report_text' => $reportText
                ]);
            }

            $recipients = array_filter(array_map('trim', explode(',', $waNumbers)));
            $sent = 0;
            foreach ($recipients as $number) {
                $phone = preg_replace('/[^0-9]/', '', $number);
                if (substr($phone, 0, 1) === '0') $phone = '62'.substr($phone, 1);

                $response = Http::withBasicAuth($sid, $token)->asForm()->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => 'whatsapp:+' . $phone,
                    'From' => $fromNumber,
                    'Body' => $reportText,
                ]);

                if ($response->successful()) $sent++;
            }

            return response()->json(['success' => true, 'message' => "Terkirim ke {$sent} penerima."]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
        }
    }

    public function buildPlainText($startDate, $endDate, $kategori, $opsel = 'ALL')
    {
        $tipeTeks = $kategori === 'FORECAST' ? 'prediksi' : ($kategori === 'COMBINED' ? 'gabungan real & prediksi' : 'realisasi');

        $applyFilters = function ($query) use ($opsel, $kategori) {
            if ($opsel !== 'ALL') $query->where('spatial_movements.opsel', $opsel);

            if ($kategori === 'COMBINED') {
                $query->where(function ($q) {
                    $q->where(function ($realQ) {
                        $realQ->where('spatial_movements.is_forecast', false)
                              ->whereNotIn(DB::raw('DATE(spatial_movements.tanggal)'), ['2026-03-27', '2026-03-28', '2026-03-29']);
                    })->orWhere(function ($forecastQ) {
                        $forecastQ->where('spatial_movements.is_forecast', true);
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
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])->where('kategori', '!=', 'ORANG')
        )->sum('total');

        $pergerakanPerOpsel = $applyFilters(
            \App\Models\SpatialMovement::whereBetween('tanggal', [$startDate, $endDate])->where('kategori', '!=', 'ORANG')
        )->select('opsel', DB::raw('SUM(total) as total_pergerakan'))->groupBy('opsel')->pluck('total_pergerakan', 'opsel');

        $finalKoef = config('mpd_koefisien.final', []);
        $koefBatches = config('mpd_koefisien.batches', []);
        $selectedBatch = !empty($finalKoef) ? $finalKoef : (end($koefBatches) ?: []);

        $uniqueSumFloat = 0;
        foreach (['TSEL', 'IOH', 'XLSMART'] as $opKey) {
            $vol = (float) ($pergerakanPerOpsel[$opKey] ?? 0);
            $koef = (float) ($selectedBatch[$opKey] ?? 1.0);
            $uniqueSumFloat += ($vol / $koef);
        }
        $nasionalUnique = (int) round($uniqueSumFloat);

        // Override Alternatif 3 BKT: angka resmi posko angleb 2026
        if ($kategori === 'COMBINED') {
            $nasionalUnique = 147551770;
        }

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
        
        $nasUnique = number_format($nasionalUnique, 0, ',', '.');

        $str = "Yth. Bapak Kepala Badan Kebijakan Transportasi,\n\n";
        $str .= "Izin melaporkan, berdasarkan hasil pemantauan sementara pergerakan orang menggunakan MPD dari 3 operator seluler, bersama ini kami sampaikan capaian data MPD per {$formattedEndDay}.\n\n";
        $str .= "Data tersebut merupakan akumulasi {$tipeTeks} periode {$formattedStart} s.d. {$formattedEnd} ({$hStart} s.d. {$hEnd}). Secara nasional tercatat sebanyak {$nasUnique} orang melakukan perjalanan di periode tersebut.\n\n";
        $str .= "Demikian kami sampaikan. Atas perkenan dan arahan Bapak, kami ucapkan terima kasih.";

        return $str;
    }
}
