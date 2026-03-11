<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModaSeeder extends Seeder
{
    public function run(): void
    {
        $modes = config('mpd.transport_modes', []);

        $transportationModes = collect($modes)->map(fn (string $name, string $code) => [
            'code' => $code,
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->toArray();

        DB::table('ref_transport_modes')->upsert(
            $transportationModes,
            ['code'],
            ['name', 'updated_at']
        );
    }
}
