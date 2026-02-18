<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ActivityLogController extends Controller
{
    /**
     * Halaman utama log aktivitas — dengan filter, search, dan caching
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Filter: Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'ilike', "%{$search}%")
                  ->orWhere('subject', 'ilike', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'ilike', "%{$search}%"));
            });
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter: Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date   . ' 23:59:59',
            ]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Pagination
        $perPage = in_array($request->input('per_page'), [10, 50, 100]) ? (int)$request->per_page : 10;
        $logs    = $query->latest()->paginate($perPage)->withQueryString();

        // Daftar user untuk filter dropdown — cache 5 menit
        $users = Cache::remember('activity_log:users', 300, fn () => User::orderBy('name')->get(['id', 'name']));

        return view('activity-log.index', compact('logs', 'users'));
    }

    /**
     * Export CSV
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'ilike', "%{$search}%")
                  ->orWhere('subject', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date   . ' 23:59:59',
            ]);
        }

        $logs     = $query->latest()->get();
        $filename = 'activity_log_' . date('Y-m-d_His') . '.csv';

        $callback = function () use ($logs) {
            $fp = fopen('php://output', 'w');
            fputcsv($fp, ['Waktu', 'User', 'Aktivitas', 'Target', 'Status', 'IP', 'User Agent']);

            foreach ($logs as $log) {
                fputcsv($fp, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->name ?? 'System',
                    $log->action,
                    $log->subject ?? '-',
                    $log->status,
                    $log->ip_address ?? '-',
                    $log->user_agent ?? '-',
                ]);
            }
            fclose($fp);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
