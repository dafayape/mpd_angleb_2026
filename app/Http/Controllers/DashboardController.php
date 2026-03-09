<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'startDate' => config('mpd.start_date', '2026-03-13'),
            'endDate' => config('mpd.end_date', '2026-03-30'),
        ]);
    }
}
