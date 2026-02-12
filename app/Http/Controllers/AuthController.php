<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        $userId = Auth::id();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'    => $userId,
                'action'     => 'Logout',
                'subject'    => 'User logout dari Laravel 12',
                'status'     => 'Success',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // 1. Hapus session Laravel 11 dari MySQL (db_mpd.sessions)
        if ($userId) {
            try {
                DB::connection('laravel11_mysql')
                    ->table('sessions')
                    ->where('user_id', $userId)
                    ->delete();
            } catch (\Exception $e) {
                Log::warning('Gagal hapus session Laravel 11: ' . $e->getMessage());
            }
        }

        // 2. Logout dari Laravel 12 (clear auth + Redis session)
        Auth::guard('web')->logout();
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Forget semua cookies terkait session
        $sessionCookie = config('session.cookie'); // mpd_shared_session

        return redirect('https://mpdbkt.web.id/login')
            ->withCookie(cookie()->forget($sessionCookie))
            ->withCookie(cookie()->forget('remember_web_' . sha1('Illuminate\Auth\SessionGuard')));
    }
}