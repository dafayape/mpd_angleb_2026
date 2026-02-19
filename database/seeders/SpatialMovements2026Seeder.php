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
        // Random Total: 500k - 2M
        $total = rand(500000, 2000000);

        // Adjust Forecast slightly from Real to look realistic
        if ($isForecast) {
            $total = (int) ($total * (rand(90, 110) / 100));
        }

        // Random Valid Mode (Weighted for Mobil/Motor)
        $modes = ['I', 'J', 'I', 'J', 'I', 'J', 'A', 'B', 'C', 'H'];
        $selectedMode = $modes[array_rand($modes)];

        DB::table('spatial_movements')->insert([
            'tanggal' => $date,
            'opsel' => $opsel,
            'is_forecast' => $isForecast,
            'kategori' => 'DUMMY',
            'kode_origin_kabupaten_kota' => '3273', // Bandung (Valid)
            'kode_dest_kabupaten_kota' => '3171', // Jakpus (Valid)
            'kode_origin_simpul' => 'DUMMY_ORIGIN',
            'kode_dest_simpul' => 'DUMMY_DEST',
            'kode_moda' => $selectedMode, 
            'total' => $total,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
