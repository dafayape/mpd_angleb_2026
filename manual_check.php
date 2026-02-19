<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Direct PDO Check
$env = file_get_contents('.env');
preg_match('/DB_HOST=(.*)/', $env, $host);
preg_match('/DB_PORT=(.*)/', $env, $port);
preg_match('/DB_DATABASE=(.*)/', $env, $db);
preg_match('/DB_USERNAME=(.*)/', $env, $user);
preg_match('/DB_PASSWORD=(.*)/', $env, $pass);

$host = trim($host[1] ?? '127.0.0.1');
$port = trim($port[1] ?? '5432');
$db   = trim($db[1] ?? 'mpd_angleb_2026');
$user = trim($user[1] ?? 'postgres');
$pass = trim($pass[1] ?? '');

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    echo "Connecting to DB...\n";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "Counting Simpul...\n";
    $count = $pdo->query("SELECT COUNT(*) FROM ref_transport_nodes")->fetchColumn();
    echo "Simpul Count: $count\n";
    
    $row = $pdo->query("SELECT code, name, location FROM ref_transport_nodes WHERE location IS NOT NULL LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Sample: {$row['name']} ({$row['code']})\n";
    } else {
        echo "Sample: None found with location.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
