<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        $userId = Auth::id();

        // Catat log SEBELUM session dihapus (non-blocking)
        try {
            ActivityLog::log('Logout', Auth::user()?->name, 'Success');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ActivityLog gagal: '.$e->getMessage());
        }

        if ($userId) {
            try {
                \Illuminate\Support\Facades\DB::connection('laravel11_mysql')
                    ->table('sessions')
                    ->where('user_id', $userId)
                    ->delete();
            } catch (\Exception $e) {
                Log::warning('Gagal hapus session Laravel 11: '.$e->getMessage());
            }
        }

        Auth::guard('web')->logout();
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $sessionCookie = config('session.cookie');

        return redirect('https://mpdbkt.web.id/login')
            ->withCookie(cookie()->forget($sessionCookie))
            ->withCookie(cookie()->forget('laravel_session'));
    }
}
