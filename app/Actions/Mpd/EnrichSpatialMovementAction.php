<?php

namespace App\Actions\Mpd;

use Illuminate\Support\Facades\DB;

class EnrichSpatialMovementAction
{
    public function execute(?string $processDate = null): void
    {
        $dateCondition = $processDate ? "WHERE r.tanggal = '{$processDate}'" : "";

        $query = "
            WITH enriched_data AS (
                SELECT
                    r.tanggal,
                    r.opsel,
                    r.kategori,
                    r.kode_origin_kabupaten_kota,
                    r.kode_dest_kabupaten_kota,
                    r.kode_origin_simpul,
                    r.kode_dest_simpul,
                    r.kode_moda,
                    r.is_forecast,
                    r.total,
                    o.location AS origin_location,
                    d.location AS dest_location,
                    ST_Distance(o.location, d.location) AS distance_meters,
                    NOW() as created_at,
                    NOW() as updated_at
                FROM raw_mpd_data r
                INNER JOIN ref_transport_nodes o ON r.kode_origin_simpul = o.code
                INNER JOIN ref_transport_nodes d ON r.kode_dest_simpul = d.code
                {$dateCondition}
            )
            INSERT INTO spatial_movements (
                tanggal, opsel, kategori, 
                kode_origin_kabupaten_kota, kode_dest_kabupaten_kota,
                kode_origin_simpul, kode_dest_simpul,
                kode_moda, is_forecast,
                total,
                origin_location, dest_location, distance_meters,
                created_at, updated_at
            )
            SELECT * FROM enriched_data
            ON CONFLICT (tanggal, opsel, kategori, kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, kode_origin_simpul, kode_dest_simpul, kode_moda, is_forecast)
            DO UPDATE SET
                total = EXCLUDED.total,
                origin_location = EXCLUDED.origin_location,
                dest_location = EXCLUDED.dest_location,
                distance_meters = EXCLUDED.distance_meters,
                updated_at = EXCLUDED.updated_at;
        ";

        DB::statement($query);
    }
}
