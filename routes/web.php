<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/sso-login', function (Request $request) {
    $userId = $request->query('user_id');
    $timestamp = $request->query('timestamp');
    $signature = $request->query('signature');

    if (!$userId || !$timestamp || !$signature) {
        abort(403, 'Missing SSO parameters.');
    }

    if (abs(time() - $timestamp) > 600) {
        abort(403, 'SSO link expired.');
    }

    $expectedSignature = hash_hmac('sha256', $userId . '|' . $timestamp, config('app.key'));
    
    if (!hash_equals($expectedSignature, $signature)) {
        abort(403, 'Invalid SSO signature.');
    }

    Auth::loginUsingId($userId);
    
    $request->session()->regenerate();

    return redirect('/');
})->name('sso.login');
