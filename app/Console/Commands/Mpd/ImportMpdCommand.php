<?php

declare(strict_types=1);

namespace App\Console\Commands\Mpd;

use App\Actions\Mpd\ImportRawMpdAction;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ImportMpdCommand extends Command
{
    protected $signature = 'mpd:import {path : The path to the CSV file} {--forecast : Force import as forecast data}';

    protected $description = 'Import raw MPD data from a CSV file with auto-detection for Forecast vs Real';

    public function handle(ImportRawMpdAction $importAction): int
    {
        $path = $this->argument('path');
        $filename = basename($path);

        $isForecast = (bool) $this->option('forecast');

        if (!$isForecast) {
            if (Str::contains(Str::lower($filename), 'forecast')) {
                $isForecast = true;
                $this->info("Auto-detected FORECAST data from filename.");
            } elseif (Str::contains(Str::lower($filename), 'real')) {
                $isForecast = false;
                $this->info("Auto-detected REAL data from filename.");
            } else {
                $this->warn("Could not detect type from filename. Defaulting to REAL data.");
            }
        } else {
            $this->info("Forcing FORECAST mode via flag.");
        }

        $this->info("Starting import from: {$path}");
        $this->info("Mode: " . ($isForecast ? 'FORECAST' : 'REAL'));

        try {
            $totalLines = $this->countLines($path);
            $bar = $this->output->createProgressBar($totalLines);
            $bar->start();

            $importAction->execute(
                path: $path,
                isForecast: $isForecast,
                onProgress: function ($progress) use ($bar) {
                    $bar->setProgress($progress);
                }
            );

            $bar->finish();
            $this->newLine();
            $this->info('Import completed successfully.');

            return CommandAlias::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return CommandAlias::FAILURE;
        }
    }

    private function countLines(string $path): int
    {
        $lines = 0;
        $handle = @fopen($path, "r");
        if ($handle === false)
            return 0;

        while (!feof($handle)) {
            $line = fgets($handle, 4096);
            $lines += substr_count($line, PHP_EOL);
        }
        fclose($handle);
        return $lines - 1;
    }
}
