<?php

namespace App\Http\Controllers;

use App\Models\Simpul;
use App\Models\SpatialMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KeynoteController extends Controller
{
    public function index()
    {
        $requirements = [
            // Executive Summary
            ['no' => 1, 'content' => 'Latar belakang kegiatan MPD', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 2, 'content' => 'Pendefinisian jumlah pergerakan dan jumlah unique subscriber pada MPD', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 3, 'content' => 'Hasil pengolahan data dari ketiga opsel untuk unique subscriber (Nasional)', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 4, 'content' => 'Puncak pergerakan berdasarkan MPD', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 5, 'content' => 'Jumlah orang/individu unique subscriber yang melakukan perjalanan', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 6, 'content' => 'Kontribusi setiap opsel terhadap total pergerakan MPD', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 7, 'content' => 'Kesimpulan sementara hasil pengumpulan dan pengolahan data MPD', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 8, 'content' => 'Perbandingan hasil pergerakan realisasi MPD dan hasil survei prakiraan', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 9, 'content' => 'Perbandingan hasil MPD tahun ini dengan tahun-tahun sebelumnya', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 10, 'content' => 'Hasil pengolahan data dari ketiga opsel untuk unique subscriber (Intra Jabodetabek dan Inter Jabodetabek)', 'group' => 'Executive Summary', 'route' => 'executive.summary'],
            ['no' => 11, 'content' => 'Jumlah orang/individu unique subscriber yang melakukan perjalanan (Intra Jabodetabek dan Inter Jabodetabek)', 'group' => 'Executive Summary', 'route' => 'executive.summary'],

            // Pergerakan Nasional
            ['no' => 12, 'content' => 'Latar belakang dan metodologi MPD', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.pergerakan'],
            ['no' => 13, 'content' => 'Persandingan pergerakan harian total berdasarkan masing-masing opsel', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.pergerakan'],
            ['no' => 14, 'content' => 'Akumulasi pergerakan harian dari seluruh opsel dan keterangan singkat terkait jumlah unique subscribernya', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.pergerakan'],
            ['no' => 15, 'content' => 'Pergerakan harian total (Pergerakan per hari dan orang per hari) + Tabel rincian jumlah pergerakan dan orang per hari', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.pergerakan'],
            ['no' => 16, 'content' => 'Persandingan pergerakan harian total berdasarkan masing-masing opsel (Pergerakan per hari dan orang per hari)', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.pergerakan'],
            ['no' => 17, 'content' => 'O-D Provinsi Asal (10 besar provinsi asal favorit Nasional) & Desire line', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.od-provinsi'],
            ['no' => 18, 'content' => 'O-D Provinsi Tujuan (10 besar provinsi tujuan favorit Nasional) & Desire line', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.od-provinsi'],
            ['no' => 19, 'content' => 'Top 10 Kab/Kota (10 besar kab/kota asal favorit nasional dan 10 besar kab/kota tujuan favorit nasional)', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.top-kabkota'],
            ['no' => 20, 'content' => 'Mode share (pemilihan moda transportasi) berdasarkan jumlah pergerakan dan jumlah orang', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.mode-share'],
            ['no' => 21, 'content' => 'Pergerakan harian berdasarkan mode share (pemilihan moda transportasi)', 'group' => 'Pergerakan Nasional', 'route' => 'grafik-mpd.nasional.mode-share'],

            // Pergerakan Jabodetabek
            ['no' => 22, 'content' => 'Persandingan pergerakan harian total berdasarkan masing-masing opsel (Intra Jabodetabek)', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.pergerakan-orang-opsel'],
            ['no' => 23, 'content' => 'Akumulasi pergerakan harian dari seluruh opsel dan keterangan singkat terkait jumlah unique subscribernya (Intra Jabodetabek)', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.pergerakan-orang-opsel'],
            ['no' => 24, 'content' => 'Pergerakan harian total (pergerakan per hari dan orang per hari) untuk Intra Jabodetabek', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.pergerakan-orang'],
            ['no' => 25, 'content' => 'Akumulasi pergerakan harian total berdasarkan masing-masing opsel (Intra Jabodetabek)', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.pergerakan-orang-opsel'],
            ['no' => 26, 'content' => 'Persandingan pergerakan harian total berdasarkan masing-masing opsel (Inter Jabodetabek)', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.pergerakan-orang-opsel'],
            ['no' => 27, 'content' => 'Akumulasi pergerakan harian dari seluruh opsel dan keterangan singkat terkait jumlah unique subscribernya (Inter Jabodetabek)', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.pergerakan-orang-opsel'],
            ['no' => 28, 'content' => 'Pergerakan harian total (pergerakan per hari dan orang per hari) untuk Inter Jabodetabek', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.pergerakan-orang'],
            ['no' => 29, 'content' => 'Akumulasi pergerakan harian total berdasarkan masing-masing opsel (Inter Jabodetabek)', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.pergerakan-orang-opsel'],
            ['no' => 30, 'content' => 'O-D Intra Jabodetabek (Top 10 kota/kab asal favorit Jabodetabek)', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.od-kabkota'],
            ['no' => 31, 'content' => 'O-D Intra Jabodetabek (Top 10 kota/kab tujuan favorit Jabodetabek)', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.od-kabkota'],
            ['no' => 32, 'content' => 'O-D Inter Jabodetabek (Top 10 Provinsi tujuan favorit dari Jabodetabek)', 'group' => 'Pergerakan Jabodetabek', 'route' => 'grafik-mpd.jabodetabek.od-kabkota'],

            // Kesimpulan and Rekomendasi
            ['no' => 33, 'content' => 'Kesimpulan Nasional', 'group' => 'Kesimpulan dan Rekomendasi', 'route' => 'executive.summary'],
            ['no' => 34, 'content' => 'Kesimpulan Jabodetabek', 'group' => 'Kesimpulan dan Rekomendasi', 'route' => 'executive.summary'],
            ['no' => 35, 'content' => 'Rekomendasi', 'group' => 'Kesimpulan dan Rekomendasi', 'route' => 'executive.summary'],

            // Tambahan
            ['no' => 36, 'content' => 'Simpul Transportasi Terpadat — Stasiun KA antar kota asal dan tujuan terpadat', 'group' => 'Substansi Tambahan', 'route' => 'grafik-mpd.nasional.simpul'],
            ['no' => 36, 'content' => 'Simpul Transportasi Terpadat — Stasiun KA regional asal dan tujuan terpadat', 'group' => 'Substansi Tambahan', 'route' => 'grafik-mpd.nasional.simpul'],
            ['no' => 36, 'content' => 'Simpul Transportasi Terpadat — Stasiun KA cepat asal dan tujuan terpadat', 'group' => 'Substansi Tambahan', 'route' => 'grafik-mpd.nasional.simpul'],
            ['no' => 36, 'content' => 'Simpul Transportasi Terpadat — Pelabuhan penyeberangan asal dan tujuan terpadat', 'group' => 'Substansi Tambahan', 'route' => 'grafik-mpd.nasional.simpul'],
            ['no' => 36, 'content' => 'Simpul Transportasi Terpadat — Pelabuhan laut asal dan tujuan terpadat', 'group' => 'Substansi Tambahan', 'route' => 'grafik-mpd.nasional.simpul'],
            ['no' => 36, 'content' => 'Simpul Transportasi Terpadat — Bandara asal dan tujuan terpadat', 'group' => 'Substansi Tambahan', 'route' => 'grafik-mpd.nasional.simpul'],
            ['no' => 36, 'content' => 'Simpul Transportasi Terpadat — Terminal asal dan tujuan terpadat', 'group' => 'Substansi Tambahan', 'route' => 'grafik-mpd.nasional.simpul'],
            ['no' => 36, 'content' => 'Simpul Transportasi Terpadat — O-D simpul pelabuhan', 'group' => 'Substansi Tambahan', 'route' => 'grafik-mpd.nasional.simpul'],
            ['no' => 37, 'content' => 'Netflow Pergerakan', 'group' => 'Substansi Tambahan', 'route' => 'map-monitor'],
        ];

        return view('keynote.index', compact('requirements'));
    }

    public function getData(Request $request)
    {
        try {
            // Periode (date range) support
            $startDate = $request->input('start_date', '2026-03-13');
            $endDate = $request->input('end_date', '2026-03-29');
            $opselFilter = $request->input('opsel', ''); // '', 'TSEL', 'IOH', 'XL'

            // Validate dates
            try {
                $startDate = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
                $endDate = \Carbon\Carbon::parse($endDate)->format('Y-m-d');
            } catch (\Throwable $e) {
                $startDate = '2026-03-13';
                $endDate = '2026-03-29';
            }

            // Ensure start <= end
            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            // Validate opsel
            $validOpsels = ['TSEL', 'IOH', 'XL'];
            if ($opselFilter && ! in_array($opselFilter, $validOpsels)) {
                $opselFilter = '';
            }

            // Cache key
            $cacheKey = "keynote:table:v1:{$startDate}:{$endDate}:{$opselFilter}";

            return Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $opselFilter) {

                // Fetch Simpuls for names
                $simpuls = Simpul::select('code', 'name')->get()->keyBy('code');

                // Query Data
                $query = SpatialMovement::whereBetween('tanggal', [$startDate, $endDate]);
                if ($opselFilter) {
                    $query->where('opsel', $opselFilter);
                }

                $raw = $query->select(
                    'kode_origin_simpul',
                    'is_forecast',
                    DB::raw('SUM(total) as total_volume')
                )
                    ->groupBy('kode_origin_simpul', 'is_forecast')
                    ->get();

                // Process Data
                $tableData = [];
                foreach ($raw as $row) {
                    $code = $row->kode_origin_simpul;
                    if (! isset($tableData[$code])) {
                        $tableData[$code] = [
                            'code' => $code,
                            'name' => $simpuls[$code]->name ?? $code,
                            'paparan' => 0,
                            'aktual' => 0,
                        ];
                    }
                    if ($row->is_forecast) {
                        $tableData[$code]['paparan'] = (int) $row->total_volume;
                    } else {
                        $tableData[$code]['aktual'] = (int) $row->total_volume;
                    }
                }

                // Sort by Aktual Desc
                $tableData = collect($tableData)->sortByDesc('aktual')->values()->toArray();

                // Build Summary
                $totalPaparan = array_sum(array_column($tableData, 'paparan'));
                $totalAktual = array_sum(array_column($tableData, 'aktual'));

                // Period Label
                $periodLabel = \Carbon\Carbon::parse($startDate)->format('d M Y');
                if ($startDate !== $endDate) {
                    $periodLabel .= ' — '.\Carbon\Carbon::parse($endDate)->format('d M Y');
                }

                return response()->json([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'period_label' => $periodLabel,
                    'opsel_filter' => $opselFilter ?: 'Semua Opsel',
                    'table_data' => $tableData,
                    'summary' => [
                        'total_paparan' => $totalPaparan,
                        'total_aktual' => $totalAktual,
                        'selisih' => $totalAktual - $totalPaparan,
                        'persen' => $totalPaparan > 0 ? round(($totalAktual / $totalPaparan) * 100, 1) : 0,
                    ],
                ]);
            });

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
