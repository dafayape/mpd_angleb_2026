<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Periode Angkutan Lebaran 2026
    |--------------------------------------------------------------------------
    |
    | Tanggal awal dan akhir periode pemantauan pergerakan masyarakat.
    | Digunakan sebagai default di seluruh controller dan view.
    |
    */

    'start_date' => env('MPD_START_DATE', '2026-03-13'),
    'end_date' => env('MPD_END_DATE', '2026-03-29'),

    /*
    |--------------------------------------------------------------------------
    | Gemini AI API Key
    |--------------------------------------------------------------------------
    */

    'gemini_api_key' => env('GEMINI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Jabodetabek City Codes
    |--------------------------------------------------------------------------
    */

    'jabodetabek_codes' => [
        '3171', '3172', '3173', '3174', '3175', '3101', // DKI Jakarta
        '3201', '3271', // Bogor
        '3276',         // Depok
        '3603', '3671', '3674', // Tangerang
        '3216', '3275', // Bekasi
    ],

    /*
    |--------------------------------------------------------------------------
    | Referensi Moda Transportasi (A-J)
    |--------------------------------------------------------------------------
    |
    | Single source of truth untuk kode dan nama moda transportasi.
    | Digunakan oleh seluruh controller, view, dan seeder.
    |
    */

    'transport_modes' => [
        'A' => 'Mobil',
        'B' => 'Motor',
        'C' => 'Bus AKAP',
        'D' => 'Bus AKDP',
        'E' => 'Kereta Api Antarkota',
        'F' => 'Kereta Cepat Jakarta Bandung (KCJB)',
        'G' => 'Kereta Api Perkotaan',
        'H' => 'Pesawat Udara',
        'I' => 'Kapal Laut',
        'J' => 'Kapal Penyeberangan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Occupancy Factors (Rata-rata penumpang per kendaraan)
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk konversi Orang -> Pergerakan (vehicle units).
    |
    */

    'occupancy_factors' => [
        'A' => 3.5,  // Mobil
        'B' => 1.5,  // Motor
        'C' => 30,   // Bus AKAP
        'D' => 25,   // Bus AKDP
        'E' => 300,  // Kereta Api Antarkota
        'F' => 600,  // KCJB
        'G' => 100,  // Kereta Api Perkotaan
        'H' => 100,  // Pesawat Udara
        'I' => 200,  // Kapal Laut
        'J' => 50,   // Kapal Penyeberangan
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => [
        'dashboard' => 86400,      // 24 jam
        'data_page' => 86400,      // 24 jam
        'grafik' => 86400,         // 24 jam
        'ai_rekomendasi' => 86400, // 24 jam
    ],

    /*
    |--------------------------------------------------------------------------
    | Historical Baselines
    |--------------------------------------------------------------------------
    */

    'historical_baselines' => [
        '2025_orang' => 115197227,
    ],

];
