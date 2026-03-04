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

        $fixedLogsPath = storage_path('logs/fixed_logs.json');
        $fixedLogs = File::exists($fixedLogsPath) ? json_decode(File::get($fixedLogsPath), true) ?? [] : [];

        if ($selectedFile && File::exists($filePath) && str_starts_with(realpath($filePath), realpath($logPath))) {
            $content = File::get($filePath);
            $fileSize = File::size($filePath);

            if ($fileSize > 2 * 1024 * 1024) {
                $content = $this->tailFile($filePath, 500);
            }

            $lines = $this->parseLogEntries($content, $fixedLogs);
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

    private function isErrorStillExist(string $message): bool
    {
        // Try to extract URL from the log string
        // Usually Laravel logs the URL somewhere or we can attempt to re-run the previous trace.
        // But logs are static text. A simpler approach for "auto-detecting if an error is fixed"
        // is to check if the exact error signature (File + Line) has been modified since the log timestamp.
        
        preg_match('/at (.*?\.php):(\d+)/', $message, $matches);
        if (count($matches) === 3) {
            $filePath = $matches[1];
            $lineNumber = (int) $matches[2];

            if (File::exists($filePath)) {
                // If the file was modified AFTER the error happened (we'll check timestamps later if needed, 
                // but just checking if the line of code changed is another heuristic).
                // Actually, the most robust way to auto-fix is to see if the file's modification time is newer
                // than the log entry's timestamp.
                return File::lastModified($filePath);
            }
        }
        return false;
    }

    private function parseLogEntries(string $content, array $fixedLogs = []): array
    {
        $pattern = '/\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+(\w+)\.(\w+):\s+(.*?)(?=\n\[\d{4}-|\z)/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $entries = [];
        $fixedLogsPath = storage_path('logs/fixed_logs.json');
        $newlyFixed = false;

        foreach (array_reverse($matches) as $match) {
            $timestamp = $match[1];
            $message = trim($match[4]);
            $id = md5($timestamp . $message);

            if (in_array($id, $fixedLogs)) {
                continue;
            }

            // Auto-detect if likely fixed by checking if the file mentioned in the error 
            // has been modified *after* the error occurred.
            $isFixed = false;
            $logTime = strtotime($timestamp);
            
            preg_match('/at (.*?\.php):(\d+)/', $message, $errMatches);
            if (count($errMatches) === 3) {
                $errFile = $errMatches[1];
                if (File::exists($errFile)) {
                    $fileModTime = File::lastModified($errFile);
                    if ($fileModTime > $logTime) {
                        $isFixed = true;
                    }
                }
            }

            if ($isFixed) {
                $fixedLogs[] = $id;
                $newlyFixed = true;
                continue;
            }

            $entries[] = [
                'id'        => $id,
                'timestamp' => $timestamp,
                'channel'   => $match[2],
                'level'     => strtoupper($match[3]),
                'message'   => $message,
            ];
        }

        if ($newlyFixed) {
            File::put($fixedLogsPath, json_encode(array_unique($fixedLogs)));
        }

        return array_slice($entries, 0, 200);
    }
}
