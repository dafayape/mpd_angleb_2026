<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModaSeeder extends Seeder
{
    public function run(): void
    {
        $transportationModes = [
            ['code' => 'A', 'name' => 'Angkutan Jalan (Bus AKAP)', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'B', 'name' => 'Angkutan Jalan (Bus AKDP)', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'C', 'name' => 'Angkutan Kereta Api Antarkota', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'D', 'name' => 'Angkutan Kereta Api KCJB', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'E', 'name' => 'Angkutan Kereta Api Perkotaan', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'F', 'name' => 'Angkutan Laut', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'G', 'name' => 'Angkutan Penyeberangan', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'H', 'name' => 'Angkutan Udara', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'I', 'name' => 'Mobil Pribadi', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'J', 'name' => 'Motor Pribadi', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'K', 'name' => 'Sepeda', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('ref_transport_modes')->upsert(
            $transportationModes,
            ['code'],
            ['name', 'updated_at']
        );
    }
}
