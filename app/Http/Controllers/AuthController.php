<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        // Logout — karena session shared via MySQL yang sama,
        // ini otomatis menghapus session untuk kedua app sekaligus.
        Auth::guard('web')->logout();
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('https://mpdbkt.web.id/login');
    }
}