<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MapMonitorSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Simpul (Transport Nodes) - Jakarta & Surroundings
        $simpuls = [
            ['code' => 'S001', 'name' => 'Stasiun Gambir', 'category' => 'Stasiun', 'lat' => -6.1767, 'lng' => 106.8306],
            ['code' => 'S002', 'name' => 'Stasiun Pasar Senen', 'category' => 'Stasiun', 'lat' => -6.1751, 'lng' => 106.8456],
            ['code' => 'S003', 'name' => 'Bandara Soekarno-Hatta', 'category' => 'Bandara', 'lat' => -6.1275, 'lng' => 106.6537],
            ['code' => 'S004', 'name' => 'Terminal Pulo Gebang', 'category' => 'Terminal', 'lat' => -6.2104, 'lng' => 106.9532],
            ['code' => 'S005', 'name' => 'Pelabuhan Tanjung Priok', 'category' => 'Pelabuhan', 'lat' => -6.1086, 'lng' => 106.8837],
            ['code' => 'S006', 'name' => 'Stasiun Manggarai', 'category' => 'Stasiun', 'lat' => -6.2099, 'lng' => 106.8502],
            ['code' => 'S007', 'name' => 'Stasiun Tanah Abang', 'category' => 'Stasiun', 'lat' => -6.1865, 'lng' => 106.8115],
            ['code' => 'S008', 'name' => 'Terminal Kampung Rambutan', 'category' => 'Terminal', 'lat' => -6.3090, 'lng' => 106.8812],
        ];

        foreach ($simpuls as $simpul) {
            // Check if exists
            $exists = DB::table('ref_transport_nodes')->where('code', $simpul['code'])->exists();
            if (!$exists) {
                DB::statement("
                    INSERT INTO ref_transport_nodes (code, name, category, location, created_at, updated_at)
                    VALUES (?, ?, ?, ST_GeomFromText('POINT({$simpul['lng']} {$simpul['lat']})', 4326), NOW(), NOW())
                ", [$simpul['code'], $simpul['name'], $simpul['category']]);
            }
        }

        // 2. Seed Spatial Movements (Volume) - Today
        $date = Carbon::today()->format('Y-m-d');
        
        // Check if data already exists for today
        $exists_movement = DB::table('spatial_movements')->where('tanggal', $date)->exists();
        
        if (!$exists_movement) {
            foreach ($simpuls as $origin) {
                // Random volume between 1000 and 100000
                $volume = rand(1000, 100000);
                
                // Use raw insert because partition might affect Eloquent or simple Query Builder
                // Also need to insert geometry for origin/dest if table requires it (it does: origin_location, dest_location)
                
                // We fake destination (just pick another random simpul or same)
                // For density map, we aggregate by origin_simpul usually (outbound) or inbound.
                // Controller uses: groupBy('kode_origin_simpul') -> SUM(total)
                
                DB::statement("
                    INSERT INTO spatial_movements (
                        tanggal, opsel, kategori, 
                        kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, 
                        kode_origin_simpul, kode_dest_simpul, 
                        kode_moda, total, 
                        origin_location, dest_location, 
                        distance_meters, created_at, updated_at
                    ) VALUES (
                        ?, 'TSEL', 'JABODE', 
                        '3171', '3172', 
                        ?, 'S002', 
                        'K', ?, 
                        ST_GeomFromText('POINT({$origin['lng']} {$origin['lat']})', 4326), 
                        ST_GeomFromText('POINT(106.8456 -6.1751)', 4326), 
                        1000, NOW(), NOW()
                    )
                ", [$date, $origin['code'], $volume]);
            }
        }
    }
}
