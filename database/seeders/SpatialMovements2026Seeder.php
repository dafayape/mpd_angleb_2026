<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SpatialMovements2026Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = Carbon::create(2026, 3, 13);
        $endDate = Carbon::create(2026, 3, 29);

        // Clear existing data for this range to prevent duplicates/unique constraint violations
        DB::table('spatial_movements')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->delete();

        $operators = ['XL', 'IOH', 'TSEL'];
        $curr = $startDate->copy();

        while ($curr->lte($endDate)) {
            $date = $curr->format('Y-m-d');
            
            foreach ($operators as $opsel) {
                // 1. Create REAL Data
                $this->insertDummyRecord($date, $opsel, false);

                // 2. Create FORECAST Data
                $this->insertDummyRecord($date, $opsel, true);
            }

            $curr->addDay();
        }
    }

    private function insertDummyRecord($date, $opsel, $isForecast)
    {
        // Fetch Node Categories & Cities from DB (Cached static)
        static $nodes = null;
        static $cities = null; 

        if (!$nodes || !$cities) {
            $rawNodes = DB::table('ref_transport_nodes')->select('code', 'name')->get();
            $nodes = [
                'AIR' => [],
                'SEA' => [], // Includes Ferry
                'RAIL' => [], // GEN
                'LAND' => []  // If any specific, otherwise reuse RAIL/GEN or assume Terminals use specific codes
            ];
            foreach ($rawNodes as $node) {
                if (str_starts_with($node->code, 'AIR-')) $nodes['AIR'][] = $node->code;
                elseif (str_starts_with($node->code, 'SEA-')) $nodes['SEA'][] = $node->code;
                elseif (str_starts_with($node->code, 'GEN-')) $nodes['RAIL'][] = $node->code;
                else $nodes['LAND'][] = $node->code; // Fallback
            }

            $cities = DB::table('ref_cities')->pluck('code')->toArray();
        }

        // 2. Define Mode Groups
        // Air: H
        // Sea: F (Laut), G (Ferry)
        // Rail: C (Antar), D (KC), E (Urban)
        // Land: A (AKAP), B (AKDP), I, J, K
        $modeGroups = [
            'AIR' => ['H'],
            'SEA' => ['F', 'G'],
            'RAIL' => ['C', 'D', 'E'],
            'LAND' => ['A', 'B', 'I', 'J', 'K']
        ];

        // 3. Pick a Category Weighted by Probability
        // Land > Commuter Rail > Sea > Air
        $category = 'LAND';
        $rand = rand(1, 100);
        if ($rand <= 10) $category = 'AIR';      // 10%
        elseif ($rand <= 25) $category = 'SEA';  // 15%
        elseif ($rand <= 50) $category = 'RAIL'; // 25%
        else $category = 'LAND';                 // 50%

        // 4. Select Mode
        $selectedMode = $modeGroups[$category][array_rand($modeGroups[$category])];

        // 5. Select Origin/Dest based on Category
        $pool = $nodes[$category];
        if (empty($pool)) {
           // Fallback for Land if empty: Use Rail nodes (common aggregation)
           if ($category === 'LAND' && !empty($nodes['RAIL'])) $pool = $nodes['RAIL'];
           else return; // Skip if no nodes
        }

        $origin = $pool[array_rand($pool)];
        $dest = $pool[array_rand($pool)];
        while($dest === $origin && count($pool) > 1) {
             $dest = $pool[array_rand($pool)];
        }
        
        // 6. Select Origin/Dest City (Random from DB)
        $originCity = !empty($cities) ? $cities[array_rand($cities)] : '3273';
        $destCity = !empty($cities) ? $cities[array_rand($cities)] : '3171';
        while($destCity === $originCity && count($cities) > 1) {
            $destCity = $cities[array_rand($cities)];
        }

        // 7. Generate Volume
        // Rail/Land > Sea > Air
        $baseVolume = match($category) {
            'LAND' => rand(500000, 3000000),
            'RAIL' => rand(100000, 1000000),
            'SEA'  => rand(5000, 50000),
            'AIR'  => rand(1000, 20000),
        };
        
        $total = $baseVolume;
        if ($isForecast) {
            $total = (int) ($total * (rand(95, 105) / 100)); // tight forecast
        }

        DB::table('spatial_movements')->insert([
            'tanggal' => $date,
            'opsel' => $opsel,
            'is_forecast' => $isForecast,
            'kategori' => 'REAL_PATTERN_SIMULATION', // Marker for "Real-like" data
            'kode_origin_kabupaten_kota' => $originCity, // Real City
            'kode_dest_kabupaten_kota' => $destCity,   // Real City
            'kode_origin_simpul' => $origin,
            'kode_dest_simpul' => $dest,
            'kode_moda' => $selectedMode, 
            'total' => $total,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
