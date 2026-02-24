<?php

// 1. Read .env for credentials
$env = file_get_contents('.env');
preg_match('/DB_HOST=(.*)/', $env, $host);
preg_match('/DB_PORT=(.*)/', $env, $port);
preg_match('/DB_DATABASE=(.*)/', $env, $db);
preg_match('/DB_USERNAME=(.*)/', $env, $user);
preg_match('/DB_PASSWORD=(.*)/', $env, $pass);

$host = trim($host[1] ?? '127.0.0.1');
$port = trim($port[1] ?? '5432');
$db = trim($db[1] ?? 'mpd_angleb_2026');
$user = trim($user[1] ?? 'postgres');
$pass = trim($pass[1] ?? '');

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // 2. Clear Data
    $startDate = '2026-03-13';
    $endDate = '2026-03-30';

    $pdo->exec("DELETE FROM spatial_movements WHERE tanggal BETWEEN '$startDate' AND '$endDate'");

    // 3. Insert Dummy Data
    $stmt = $pdo->prepare("
        INSERT INTO spatial_movements (
            tanggal, opsel, is_forecast, kategori, 
            kode_origin_kabupaten_kota, kode_dest_kabupaten_kota, 
            kode_origin_simpul, kode_dest_simpul, kode_moda, 
            total, created_at, updated_at
        ) VALUES (
            :tanggal, :opsel, :is_forecast, 'DUMMY', 
            '0000', '0000', 
            'DUMMY_ORIGIN', 'DUMMY_DEST', 'X', 
            :total, NOW(), NOW()
        )
    ");

    $current = new DateTime($startDate);
    $end = new DateTime($endDate);
    $operators = ['XL', 'IOH', 'TSEL'];

    while ($current <= $end) {
        $dateStr = $current->format('Y-m-d');

        foreach ($operators as $op) {
            // Real
            $totalReal = rand(500000, 2000000);
            $stmt->execute([
                ':tanggal' => $dateStr,
                ':opsel' => $op,
                ':is_forecast' => 'false',
                ':total' => $totalReal,
            ]);

            // Forecast
            $totalForecast = (int) ($totalReal * (rand(90, 110) / 100));
            $stmt->execute([
                ':tanggal' => $dateStr,
                ':opsel' => $op,
                ':is_forecast' => 'true',
                ':total' => $totalForecast,
            ]);
        }
        $current->modify('+1 day');
    }

    file_put_contents('seed_result.txt', "SUCCESS: Inserted dummy data via PDO.\n");

} catch (PDOException $e) {
    file_put_contents('seed_result.txt', 'ERROR: '.$e->getMessage()."\n");
}
