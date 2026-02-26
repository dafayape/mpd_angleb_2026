<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ExecutiveSummary\ExecutiveSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExecutiveSummaryController extends Controller
{
    public function __construct(private readonly ExecutiveSummaryService $service) {}

    public function index()
    {
        return view('dashboard.index', [
            'periodStart' => ExecutiveSummaryService::PERIOD_START,
            'periodEnd' => ExecutiveSummaryService::PERIOD_END
        ]);
    }

    public function getData(Request $request): JsonResponse
    {
        $request->validate([
            'opsel' => 'nullable|in:TSEL,IOH,XL',
            'data_type' => 'nullable|in:real,forecast',
        ]);
        
        return response()->json(
            $this->service->getFullSummary($request->opsel, $request->data_type ?? 'real')
        );
    }
}
