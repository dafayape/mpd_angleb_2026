<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'Logout',
                'subject'    => 'User logout dari Laravel 12',
                'status'     => 'Success',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // 1. Logout dari Laravel 12 (clear auth session)
        Auth::logout();

        // 2. Invalidate session Laravel 12 & regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Redirect ke Laravel 11 SSO logout endpoint
        //    Route GET /sso-logout di Laravel 11 akan clear session dan redirect ke /login
        return redirect('https://mpdbkt.web.id/sso-logout');
    }
}