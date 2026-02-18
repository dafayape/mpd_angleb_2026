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
        
        $jabodetabekCodes = $this->getJabodetabekCodes();

        try {
            $matrix = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $jabodetabekCodes) {
                return $this->getOdSimpulData($startDate, $endDate, $jabodetabekCodes);
            });
        } catch (\Throwable $e) {
            // Redis/DB Fallback
            $matrix = $this->getOdSimpulData($startDate, $endDate, $jabodetabekCodes);
        }

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
        
        $jabodetabekCodes = $this->getJabodetabekCodes();
        
        try {
            $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate, $jabodetabekCodes) {
                return $this->getModeShareData($startDate, $endDate, $jabodetabekCodes);
            });
        } catch (\Throwable $e) {
            // Redis/DB Fallback
            $data = $this->getModeShareData($startDate, $endDate, $jabodetabekCodes);
        }

        return view('data-mpd.jabodetabek.mode-share', [
            'title' => 'Mode Share Jabodetabek',
            'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Mode Share'],
            'dates' => $dates,
            'movementMatrix' => $data['movement'],
            'peopleMatrix' => $data['people']
        ]);
    }

    private function getOdSimpulData($startDate, $endDate, $jabodetabekCodes)
    {
        // A. Get All Categories (Simpul) for Rows
        try {
            $categories = DB::table('ref_transport_nodes')
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->toArray();
        } catch (\Throwable $e) {
            $categories = [];
        }

        // Fallback if empty (ensure tables are never blank)
        if (empty($categories)) {
            $categories = ['Terminal', 'Stasiun', 'Bandara', 'Pelabuhan', 'Simpul Lainnya'];
        }

        // Initialize Pivot with All Categories (Empty Data)
        $pivot = [];
        foreach ($categories as $cat) {
            $pivot[$cat] = ['total' => 0];
        }

        // B. Query Movement Data
        try {
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

            // C. Merge Data
            foreach ($data as $row) {
                $cat = $row->kategori_simpul;
                $date = $row->tanggal;
                $vol = $row->total_volume;

                if (isset($pivot[$cat])) {
                    $pivot[$cat][$date] = $vol;
                    $pivot[$cat]['total'] += $vol;
                }
            }
        } catch (\Throwable $e) {
            // If DB Query fails, we return the initialized empty pivot
            \Illuminate\Support\Facades\Log::error('OD Simpul DB Error: ' . $e->getMessage());
        }

        return $pivot;
    }

    private function getModeShareData($startDate, $endDate, $jabodetabekCodes)
    {
        // A. Get All Modes for Rows
        try {
            $modes = DB::table('ref_transport_modes')
                ->orderBy('code')
                ->pluck('name')
                ->toArray();
        } catch (\Throwable $e) {
            $modes = [];
        }

        // Fallback if empty (ensure tables are never blank)
        if (empty($modes)) {
            $modes = [
                'Angkutan Jalan (Bus AKAP)', 'Angkutan Jalan (Bus AKDP)', 
                'Angkutan Kereta Api', 'Angkutan Laut', 'Angkutan Udara', 
                'Mobil Pribadi', 'Motor Pribadi'
            ];
        }

        // Initialize Pivot with All Modes
        $pivotMovement = [];
        $pivotPeople = [];

        foreach ($modes as $mode) {
            $pivotMovement[$mode] = ['total' => 0];
            $pivotPeople[$mode] = ['total' => 0];
        }

        // B. Query Movement Data
        try {
            $results = DB::table('spatial_movements as sm')
                ->join('ref_transport_modes as moda', 'sm.kode_moda', '=', 'moda.code')
                ->select(
                    'moda.name as moda_name',
                    'sm.tanggal',
                    DB::raw('SUM(sm.total) as total_volume')
                )
                ->whereBetween('sm.tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('sm.kode_origin_kabupaten_kota', $jabodetabekCodes)
                ->groupBy('moda.name', 'sm.tanggal')
                ->get();

            // C. Merge Data
            foreach ($results as $row) {
                $cat = $row->moda_name;
                $date = $row->tanggal;
                $vol = $row->total_volume;

                // PERGERAKAN (Movement)
                if (isset($pivotMovement[$cat])) {
                    $pivotMovement[$cat][$date] = $vol;
                    $pivotMovement[$cat]['total'] += $vol;
                }

                // ORANG (People)
                if (isset($pivotPeople[$cat])) {
                    $pivotPeople[$cat][$date] = $vol; // Simplified 1:1 for now
                    $pivotPeople[$cat]['total'] += $vol;
                }
            }
        } catch (\Throwable $e) {
             \Illuminate\Support\Facades\Log::error('Mode Share DB Error: ' . $e->getMessage());
        }

        return ['movement' => $pivotMovement, 'people' => $pivotPeople];
    }
}
