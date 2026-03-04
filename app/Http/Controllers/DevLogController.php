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

    private function parseLogEntries(string $content, array $fixedLogs = []): array
    {
        $pattern = '/\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+(\w+)\.(\w+):\s+(.*?)(?=\n\[\d{4}-|\z)/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $entries = [];
        foreach (array_reverse($matches) as $match) {
            $timestamp = $match[1];
            $message = trim($match[4]);
            $id = md5($timestamp . $message);

            if (in_array($id, $fixedLogs)) {
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

        return array_slice($entries, 0, 200);
    }

    public function markFixed(Request $request)
    {
        if (! in_array(Auth::user()->role, ['su', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $id = $request->input('id');
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $fixedLogsPath = storage_path('logs/fixed_logs.json');
        $fixedLogs = [];
        if (File::exists($fixedLogsPath)) {
            $fixedLogs = json_decode(File::get($fixedLogsPath), true) ?? [];
        }

        if (!in_array($id, $fixedLogs)) {
            $fixedLogs[] = $id;
            File::put($fixedLogsPath, json_encode($fixedLogs));
        }

        return response()->json(['success' => true]);
    }
}
