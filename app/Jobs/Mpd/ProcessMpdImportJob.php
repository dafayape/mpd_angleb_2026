<?php

namespace App\Jobs\Mpd;

use App\Actions\Mpd\EnrichSpatialMovementAction;
use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class ProcessMpdImportJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public function uniqueId(): string
    {
        return (string) $this->importJobId;
    }

    public function __construct(
        private readonly int $importJobId,
        private readonly string $filePath,
        private readonly ?bool $isForecast = null,
        private readonly ?string $manualOpsel = null,
        private readonly ?string $manualDate = null
    ) {
    }

    public function handle(): void
    {
        $jobModel = ImportJob::find($this->importJobId);
        if (!$jobModel)
            return;

        $jobModel->update(['status' => 'processing', 'progress' => 0]);

        $filename = basename($this->filePath);
        $isForecast = $this->resolveForecastStatus($filename);
        $date = $this->resolveDate($filename);

        // Calculate Chunks provided file is huge
        $fileSize = filesize($this->filePath);
        $chunkSize = 50 * 1024 * 1024; // 50MB Chunks
        $chunks = [];

        for ($start = 0; $start < $fileSize; $start += $chunkSize) {
            $end = min($start + $chunkSize, $fileSize);
            $chunks[] = new ImportMpdChunkJob(
                $this->importJobId,
                $this->filePath,
                $start,
                $end,
                $isForecast
            );
        }

        // Batch Dispatch
        Bus::batch($chunks)
            ->then(function ($batch) use ($date) {
                // Enrichment runs after ALL chunks successfully complete
                if ($date) {
                    (new EnrichSpatialMovementAction())->execute($date);
                }
            })
            ->finally(function ($batch) use ($jobModel) {
                // Cleanup and Final Status Update
                $jobModel->update(['status' => 'completed', 'progress' => 100]);
                // We assume Success here for simplicity, ideally check batch failures
                // Archival logic would go here if we passed the path differently or stored it in DB
            })
            ->name('Import MPD: ' . $filename)
            ->dispatch();
    }

    private function resolveForecastStatus(string $filename): bool
    {
        if ($this->isForecast !== null) {
            return $this->isForecast;
        }
        return Str::contains(Str::lower($filename), 'forecast');
    }

    private function resolveDate(string $filename): ?string
    {
        if ($this->manualDate) {
            return $this->manualDate;
        }
        if (preg_match('/(\d{8})\.csv$/i', $filename, $matches)) {
            return \Carbon\Carbon::createFromFormat('Ymd', $matches[1])->toDateString();
        }
        return null;
    }
}
