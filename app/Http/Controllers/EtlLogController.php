<?php

namespace App\Http\Controllers;

use App\Jobs\Mpd\TransformRawToSpatialJob;
use App\Models\ImportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtlLogController extends Controller
{
    /**
     * Display the ETL Pipeline Log dashboard.
     */
    public function index(Request $request)
    {
        if (! in_array(Auth::user()->role, ['su', 'admin'])) {
            abort(403, 'Hanya admin yang dapat mengakses log ETL.');
        }

        $query = ImportJob::orderBy('created_at', 'desc');
        $jobs = $query->paginate(15);

        return view('etllog.index', compact('jobs'));
    }

    /**
     * Fetch the real-time status and logs for a specific ETL job.
     */
    public function status($id)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['su', 'admin'])) {
            return response()->json(['error' => 'Unauthorized or Session Expired'], 403);
        }

        $job = ImportJob::find($id);
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        $meta = $job->metadata ?? [];
        
        return response()->json([
            'etl_status'   => $meta['etl_status'] ?? 'pending',
            'etl_progress' => (int) ($meta['etl_progress'] ?? 0),
            'etl_logs'     => $meta['etl_logs'] ?? []
        ]);
    }

    /**
     * Retry a failed ETL job.
     */
    public function retry($id)
    {
        if (! in_array(Auth::user()->role, ['su', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $job = ImportJob::find($id);
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        $meta = $job->metadata ?? [];
        
        // Only allow retry if it's failed or if requested manually
        // We will reset the status to pending
        $meta['etl_status'] = 'pending';
        $meta['etl_progress'] = 0;
        $meta['etl_logs'] = [[
            'time' => now()->toDateTimeString(),
            'level' => 'INFO',
            'message' => 'ETL retry triggered manually by user.'
        ]];
        
        $job->metadata = $meta;
        $job->save();

        // Dispatch the job again
        try {
            TransformRawToSpatialJob::dispatchAfterResponse($job->id);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'ETL Job has been queued for retry.'
        ]);
    }
}
