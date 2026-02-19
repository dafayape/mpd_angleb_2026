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
        // Fetch valid codes once (static cache would be better but this is fine for seeded loop)
        static $simpulCodes = null;
        if (!$simpulCodes) {
            $simpulCodes = DB::table('ref_transport_nodes')->pluck('code')->toArray();
        }

        // Random Total: 500k - 2M
        $total = rand(500000, 2000000);

        // Adjust Forecast slightly from Real to look realistic
        if ($isForecast) {
            $total = (int) ($total * (rand(90, 110) / 100));
        }

        // Random Valid Mode (A-K) to match ModaSeeder
        // A=Bus AKAP, B=Bus AKDP, C=KA Antar, D=KC, E=KA Urban, F=Laut, G=Ferry, H=Udara, I=Mobil, J=Motor, K=Sepeda
        $modes = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        $selectedMode = $modes[array_rand($modes)];
        
        // Random Origin/Dest from Valid Codes
        $origin = $simpulCodes ? $simpulCodes[array_rand($simpulCodes)] : 'DUMMY_ORIGIN';
        $dest = $simpulCodes ? $simpulCodes[array_rand($simpulCodes)] : 'DUMMY_DEST';

        // Ensure distinct
        while($dest === $origin && count($simpulCodes) > 1) {
             $dest = $simpulCodes[array_rand($simpulCodes)];
        }

        DB::table('spatial_movements')->insert([
            'tanggal' => $date,
            'opsel' => $opsel,
            'is_forecast' => $isForecast,
            'kategori' => 'DUMMY',
            'kode_origin_kabupaten_kota' => '3273', // Bandung (Valid)
            'kode_dest_kabupaten_kota' => '3171', // Jakpus (Valid)
            'kode_origin_simpul' => $origin,
            'kode_dest_simpul' => $dest,
            'kode_moda' => $selectedMode, 
            'total' => $total,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
