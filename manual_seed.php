<?php
define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Explicitly include Seeder file if autoload fails for some reason
require_once __DIR__.'/database/seeders/SpatialMovements2026Seeder.php';

use Database\Seeders\SpatialMovements2026Seeder;

echo "Running Manual Seeder...\n";

try {
    $seeder = new SpatialMovements2026Seeder();
    $seeder->run();
    echo "SUCCESS: Dummy data inserted for 13-30 Mar 2026.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
