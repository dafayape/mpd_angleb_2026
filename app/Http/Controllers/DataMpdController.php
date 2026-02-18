<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpatialMovement;
use App\Models\Simpul;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DataMpdController extends Controller
{
    /**
     * Get Jabodetabek City Codes
     */
    private function getJabodetabekCodes()
    {
        return [
            // DKI Jakarta
            '3171', '3172', '3173', '3174', '3175', '3101', // Kepulauan Seribu included? Usually yes for province. 3101 is Kep Seribu.
            // Bogor
            '3201', '3271',
            // Depok
            '3276',
            // Tangerang
            '3603', '3671', '3674', // Kab Tangerang, Kota Tangerang, Kota Tangsel
            // Bekasi
            '3216', '3275'
        ];
    }

    public function jabodetabekOdSimpul(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 29 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);
        
        $dates = collect();
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates->push($curr->format('Y-m-d'));
            $curr->addDay();
        }

        // 2. Caching Key
        $cacheKey = 'mpd:jabodetabek:od-simpul:matrix';
        
        // 3. Fetch Data (Cached 1 Hour)
        $matrix = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
            $jabodetabekCodes = $this->getJabodetabekCodes();

            // Query: Sum total per Kategori per Tanggal
            // Filter: Jabodetabek Origin, Date Range
            $data = DB::table('spatial_movements as sm')
                ->join('ref_transport_nodes as simpul', 'sm.kode_origin_simpul', '=', 'simpul.code')
                ->select(
                    'simpul.category as kategori_simpul',
                    'sm.tanggal',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('simpul.category', 'sm.tanggal')
                ->get();

            // Pivot Data Structure
            // [Category => [Date => Volume, 'total' => Sum]]
            $pivot = [];
            foreach ($data as $row) {
                $cat = $row->kategori_simpul;
                $date = $row->tanggal;
                $vol = $row->total_volume;

                if (!isset($pivot[$cat])) {
                    $pivot[$cat] = ['total' => 0];
                }
                $pivot[$cat][$date] = $vol;
                $pivot[$cat]['total'] += $vol;
            }

            // Fill missing dates with 0
            // Not strictly necessary for View if we handle isset, but cleaner
            return $pivot;
        });

        return view('data-mpd.jabodetabek.od-simpul', [
            'title' => 'O-D Simpul Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'O-D Simpul'],
            'dates' => $dates,
            'matrix' => $matrix
        ]);
    }

    public function jabodetabekModeShare(Request $request)
    {
        // 1. Date Range: 13 March 2026 - 29 March 2026
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);
        
        $dates = collect();
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dates->push($curr->format('Y-m-d'));
            $curr->addDay();
        }

        // 2. Caching Key
        $cacheKey = 'mpd:jabodetabek:mode-share:matrix';
        
        // 3. Fetch Data (Cached 1 Hour)
        $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
            $jabodetabekCodes = $this->getJabodetabekCodes();

            // Query: Sum total per Moda per Tanggal
            // Filter: Jabodetabek Origin, Date Range
            $results = DB::table('spatial_movements as sm')
                ->join('ref_transport_modes as moda', 'sm.kode_moda', '=', 'moda.code') // Assuming kode_moda links to ref_transport_modes.code
                ->select(
                    'moda.name as moda_name',
                    'sm.tanggal',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('moda.name', 'sm.tanggal')
                ->get();

            // Pivot Data Structure
            $pivotMovement = [];
            $pivotPeople = [];

            foreach ($results as $row) {
                $cat = $row->moda_name;
                $date = $row->tanggal;
                $vol = $row->total_volume;

                // PERGERAKAN (Movement)
                if (!isset($pivotMovement[$cat])) {
                    $pivotMovement[$cat] = ['total' => 0];
                }
                $pivotMovement[$cat][$date] = $vol;
                $pivotMovement[$cat]['total'] += $vol;

                // ORANG (People) - Simplified assumption: 1 Movement = 1 Person for now, 
                // or use coefficient if requested. User didn't specify, so we mirror.
                // If we want to be fancy/realistic, we could apply varying occupancy rates 
                // (e.g., Bus=20, Car=2, Motorcycle=1.2), but without real data column, static coefficient is arbitrary.
                // Keeping it identical to Movement for data integrity unless specified otherwise.
                $peopleCount = $vol; 

                if (!isset($pivotPeople[$cat])) {
                    $pivotPeople[$cat] = ['total' => 0];
                }
                $pivotPeople[$cat][$date] = $peopleCount;
                $pivotPeople[$cat]['total'] += $peopleCount;
            }

            return ['movement' => $pivotMovement, 'people' => $pivotPeople];
        });

        return view('data-mpd.jabodetabek.mode-share', [
            'title' => 'Mode Share Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Mode Share'],
            'dates' => $dates,
            'movementMatrix' => $data['movement'],
            'peopleMatrix' => $data['people']
        ]);
    }
}
