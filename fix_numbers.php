<?php

$pathsAndNumbers = [
    'resources/views/dashboard/index.blade.php' => '01',
    'resources/views/pages/nasional/data-dasar.blade.php' => '02',
    'resources/views/pages/nasional/pergerakan-harian.blade.php' => '03',
    'resources/views/pages/nasional/od.blade.php' => '04',
    'resources/views/pages/nasional/mode-share.blade.php' => '05',
    'resources/views/pages/jabodetabek/intra-pergerakan.blade.php' => '06',
    'resources/views/pages/jabodetabek/intra-od.blade.php' => '07',
    'resources/views/pages/jabodetabek/inter-pergerakan.blade.php' => '08',
    'resources/views/pages/jabodetabek/inter-od.blade.php' => '09',
    'resources/views/pages/substansi/netflow.blade.php' => '10',
    'resources/views/pages/kesimpulan/nasional.blade.php' => '11',
    'resources/views/pages/kesimpulan/jabodetabek.blade.php' => '12',
    'resources/views/pages/substansi/stasiun-ka-antar-kota.blade.php' => '13',
    'resources/views/pages/substansi/stasiun-ka-regional.blade.php' => '14',
    'resources/views/pages/substansi/stasiun-ka-cepat.blade.php' => '15',
    'resources/views/pages/substansi/pelabuhan-penyeberangan.blade.php' => '16',
    'resources/views/pages/substansi/pelabuhan-laut.blade.php' => '17',
    'resources/views/pages/substansi/bandara.blade.php' => '18',
    'resources/views/pages/substansi/terminal.blade.php' => '19',
    'resources/views/pages/substansi/od-simpul-pelabuhan.blade.php' => '20',
    'resources/views/pages/kesimpulan/rekomendasi.blade.php' => '21',
    'resources/views/map-monitor/index.blade.php' => '22',
    'resources/views/executive/daily-report.blade.php' => '23',
    'resources/views/master/referensi/provinsi.blade.php' => '24',
    'resources/views/master/referensi/kabkota.blade.php' => '25',
    'resources/views/master/referensi/simpul.blade.php' => '26',
    'resources/views/master/referensi/moda.blade.php' => '27',
    'resources/views/master/rule-document/index.blade.php' => '28',
    'resources/views/datasource/upload.blade.php' => '29',
    'resources/views/datasource/history.blade.php' => '30',
    'resources/views/datasource/raw.blade.php' => '31',
    'resources/views/datasource/summary.blade.php' => '32',
    'resources/views/users/index.blade.php' => '33',
    'resources/views/activity-log/index.blade.php' => '34',
    'resources/views/devlog/index.blade.php' => '35',
    'resources/views/pages/pengaturan/pengaturan.blade.php' => '36',
    'resources/views/keynote/index.blade.php' => '00',
];

$baseDir = '/home/dafayape/Documents/raw_bkt/mpd_angleb_2026/';

foreach ($pathsAndNumbers as $file => $number) {
    $fullPath = $baseDir . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $content = preg_replace("/'number'\s*=>\s*'\d+'/", "'number' => '$number'", $content);
        file_put_contents($fullPath, $content);
        echo "Updated $file to $number\n";
    } else {
        echo "NOT FOUND: $file\n";
    }
}
