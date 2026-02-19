<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Simpul;
use App\Models\SpatialMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class KeynoteController extends Controller
{
    public function index()
    {
        return view('keynote.index');
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
            if ($opselFilter && !in_array($opselFilter, $validOpsels)) {
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
                    if (!isset($tableData[$code])) {
                        $tableData[$code] = [
                            'code' => $code, 
                            'name' => $simpuls[$code]->name ?? $code, 
                            'paparan' => 0, 
                            'aktual' => 0
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
                    $periodLabel .= ' — ' . \Carbon\Carbon::parse($endDate)->format('d M Y');
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
                        'persen' => $totalPaparan > 0 ? round(($totalAktual / $totalPaparan) * 100, 1) : 0
                    ]
                ]);
            });

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
