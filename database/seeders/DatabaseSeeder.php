<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Referensi data — urutan penting (provinces dulu karena cities FK ke provinces)
        $this->call([
            ProvinceSeeder::class,
            CitySeeder::class,
            ModaSeeder::class,
            // SimpulSeeder::class, // Uncomment jika data simpul sudah siap + PostGIS installed
        ]);
    }
}
