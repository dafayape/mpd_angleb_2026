<?php

/**
 * Konfigurasi Koefisien Per-Opsel Per-Batch
 *
 * Koefisien ini ditetapkan oleh Kemenhub BKT berdasarkan batch pengiriman data.
 * Sistem akan otomatis memilih koefisien yang tepat berdasarkan `end_date` aktif
 * yang dikonfigurasi di `config/mpd.php`.
 *
 * Rumus: unique_subscriber_opsel = total_pergerakan_opsel / koefisien_opsel
 * Total = SUM(unique_subscriber per opsel)
 *
 * Riwayat Batch:
 * - Batch 1: H-8 s.d. H-5 (disampaikan 16 Maret 2026)
 * - Batch 2: H-8 s.d. H-1 (disampaikan 20 Maret 2026)
 * - Batch 3: H-8 s.d. H+4 (disampaikan 25 Maret 2026)
 * - Batch 4: H-8 s.d. H+8 (disampaikan 29 Maret 2026)
 */
return [

    /*
     | Tabel koefisien per-batch.
     | Sistem memilih batch berdasarkan end_date terkini (tanggal akhir periode).
     | Jika end_date tidak cocok, fallback ke batch terakhir yang tersedia.
     */
    'batches' => [

        'batch_1' => [
            'end_date' => '2026-03-16',
            'label' => 'Batch 1 (H-8 s.d. H-5)',
            'TSEL' => 1.1708,
            'IOH' => 1.1186,
            'XLSMART' => 1.41,
        ],

        'batch_2' => [
            'end_date' => '2026-03-20',
            'label' => 'Batch 2 (H-8 s.d. H-1)',
            'TSEL' => 1.4811,
            'IOH' => 1.71,
            'XLSMART' => 1.88,
        ],

        'batch_3' => [
            'end_date' => '2026-03-25',
            'label' => 'Batch 3 (H-8 s.d. H+4)',
            'TSEL' => 1.9523,
            'IOH' => 2.40,
            'XLSMART' => 2.25,
        ],
        'batch_4' => [
            'end_date' => '2026-03-29',
            'label' => 'Batch 4 (H-8 s.d. H+8)',
            // 'TSEL' => 2.4,
            'IOH' => 2.86,
            'XLSMART' => 2.8503,
        ],
    ],

];
