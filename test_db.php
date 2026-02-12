<?php
$host = "127.0.0.1";
$port = "35432";
$dbname = "mpd_angleb_2026"; // <-- Pastiin nama db-nya bener
$user = "angleb";
$pass = "Makerdotindo2026"; // <-- Password angleb lu

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    // PDO constructor
    $pdo = new PDO($dsn, $user, $pass);
    
    if ($pdo) {
        echo "Connected to the $dbname database successfully!";
    }
} catch (PDOException $e) {
    echo "Connection Failed: " . $e->getMessage();
}
?>