<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class DevLogController extends Controller
{
    public function index(Request $request)
    {
        if (! in_array(Auth::user()->role, ['su', 'admin'])) {
            abort(403, 'Hanya admin yang dapat mengakses log developer.');
        }

        $logPath = storage_path('logs');
        $logFiles = collect(File::glob($logPath.'/laravel-*.log'))
            ->map(fn ($path) => basename($path))
            ->sortDesc()
            ->values();

        $selectedFile = $request->input('file', $logFiles->first());

        $lines = [];
        $filePath = $logPath.'/'.$selectedFile;

        if ($selectedFile && File::exists($filePath) && str_starts_with(realpath($filePath), realpath($logPath))) {
            $content = File::get($filePath);
            $fileSize = File::size($filePath);

            if ($fileSize > 2 * 1024 * 1024) {
                $content = $this->tailFile($filePath, 500);
            }

            $lines = $this->parseLogEntries($content);
        }

        return view('devlog.index', [
            'logFiles'     => $logFiles,
            'selectedFile' => $selectedFile,
            'lines'        => $lines,
        ]);
    }

    private function tailFile(string $path, int $lineCount): string
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $start = max(0, $totalLines - $lineCount);
        $file->seek($start);

        $output = '';
        while (! $file->eof()) {
            $output .= $file->current();
            $file->next();
        }

        return $output;
    }

    private function parseLogEntries(string $content): array
    {
        $pattern = '/\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+(\w+)\.(\w+):\s+(.*?)(?=\n\[\d{4}-|\z)/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $entries = [];
        foreach (array_reverse($matches) as $match) {
            $entries[] = [
                'timestamp' => $match[1],
                'channel'   => $match[2],
                'level'     => strtoupper($match[3]),
                'message'   => trim($match[4]),
            ];
        }

        return array_slice($entries, 0, 200);
    }
}
