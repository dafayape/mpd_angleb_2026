<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimpulSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('resources/data/master_simpul.csv');
        $handle = fopen($csvPath, 'r');

        $headers = fgetcsv($handle, 0, ';');

        $nodeBatch = [];
        $batchSize = 500;
        $timestamp = now();

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowData = array_combine($headers, $row);

            $latitude = trim($rowData['LATITUDE'] ?? '');
            $longitude = trim($rowData['LONGITUDE'] ?? '');

            if ($latitude === '' || $longitude === '' || !is_numeric($latitude) || !is_numeric($longitude)) {
                continue;
            }

            $nodeBatch[] = [
                'code' => trim($rowData['KODE_SIMPUL']),
                'name' => trim($rowData['NAMA_SIMPUL']),
                'category' => trim($rowData['KATEGORI_SIMPUL']),
                'sub_category' => trim($rowData['SUB_KATEGORI_SIMPUL'] ?? null),
                'location' => DB::raw("ST_GeographyFromText('SRID=4326;POINT({$longitude} {$latitude})')"),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if (count($nodeBatch) >= $batchSize) {
                DB::table('ref_transport_nodes')->upsert(
                    $nodeBatch,
                    ['code'],
                    ['name', 'category', 'sub_category', 'location', 'updated_at']
                );
                $nodeBatch = [];
            }
        }

        if (!empty($nodeBatch)) {
            DB::table('ref_transport_nodes')->upsert(
                $nodeBatch,
                ['code'],
                ['name', 'category', 'sub_category', 'location', 'updated_at']
            );
        }

        fclose($handle);
    }
}
