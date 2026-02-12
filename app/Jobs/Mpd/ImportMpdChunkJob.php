<?php

namespace App\Jobs\Mpd;

use App\Actions\Mpd\ImportRawMpdAction;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportMpdChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function __construct(
        public int $importJobId,
        public string $filePath,
        public int $startByte,
        public int $endByte,
        public bool $isForecast
    ) {
    }

    public function handle(ImportRawMpdAction $action): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $action->execute(
            path: $this->filePath,
            isForecast: $this->isForecast,
            onProgress: null,
            startByte: $this->startByte,
            endByte: $this->endByte,
            jobId: (string) $this->importJobId
        );
    }
}
