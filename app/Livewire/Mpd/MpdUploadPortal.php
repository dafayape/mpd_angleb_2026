<?php

declare(strict_types=1);

namespace App\Livewire\Mpd;

use App\Jobs\Mpd\ProcessMpdImportJob;
use App\Models\ImportJob;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class MpdUploadPortal extends Component
{
    use WithFileUploads;

    public $file;
    public $opsel;
    public $type;
    public $date;
    public string $uploadStatus = '';

    protected array $rules = [
        'opsel' => 'required|in:TSEL,IOH,XL',
        'type' => 'required|in:REAL,FORECAST',
        'date' => 'required|date',
        'file' => 'required|file|mimes:csv,txt|max:1048576', // 1GB
    ];

    public function updatedFile(): void
    {
        $this->validateOnly('file');
    }

    public function import(): void
    {
        $this->validate();

        $this->uploadStatus = 'Initiating Upload...';

        try {
            $filename = 'mpd_' . strtolower($this->opsel) . '_' . strtolower($this->type) . '_' . str_replace('-', '', $this->date) . '.csv';

            // Store locally for processing
            $path = $this->file->storeAs('mpd_imports', $filename, 'local');
            $fullPath = Storage::disk('local')->path($path);

            $this->uploadStatus = 'Analyzing file structure...';

            // Pre-check: Calculate total rows immediately for UI feedback
            $lineCount = (int) exec("wc -l '$fullPath'");

            // Create tracking record
            $jobRecord = ImportJob::create([
                'filename' => $filename,
                'status' => 'pending',
                'total_rows' => $lineCount,
                'metadata' => [
                    'opsel' => $this->opsel,
                    'type' => $this->type,
                    'date' => $this->date
                ]
            ]);

            // Dispatch Job
            ProcessMpdImportJob::dispatch(
                $jobRecord->id,
                $fullPath,
                $this->type === 'FORECAST',
                $this->opsel,
                $this->date
            );

            $this->uploadStatus = 'Queued successfully. Total rows: ' . number_format($lineCount);
            $this->reset(['file']);
            $this->dispatch('import-started');

        } catch (\Exception $e) {
            $this->uploadStatus = 'Error: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.mpd.mpd-upload-portal', [
            'recentJobs' => ImportJob::latest()->take(10)->get()
        ]);
    }
}
