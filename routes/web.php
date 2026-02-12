<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Route yang butuh autentikasi - Redirect ke login jika belum login
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/**
 * SSO LOGIN HANDOFF
 * Menerima user_id & signature dari Laravel 11.
 * Verifikasi signature (shared APP_KEY).
 * Login user (create Redis session).
 * Redirect ke dashboard.
 */
Route::get('/sso-login', function (Request $request) {
    $userId = $request->query('user_id');
    $timestamp = $request->query('timestamp');
    $signature = $request->query('signature');

    if (!$userId || !$timestamp || !$signature) {
        abort(403, 'Missing SSO parameters.');
    }

    // Verify timestamp (valid for 10 minutes to be safe with clock skew)
    if (abs(time() - $timestamp) > 600) {
        abort(403, 'SSO link expired.');
    }

    // Verify signature
    $expectedSignature = hash_hmac('sha256', $userId . '|' . $timestamp, config('app.key'));
    
    // Use hash_equals to prevent timing attacks
    if (!hash_equals($expectedSignature, $signature)) {
        abort(403, 'Invalid SSO signature.');
    }

    // Login user by ID (using connections defined in User model)
    Auth::loginUsingId($userId);
    
    // Regenerate session ID for security
    $request->session()->regenerate();

    return redirect('/');
})->name('sso.login');

// Debug Route
Route::get('/check-session', function () {
    return response()->json([
        'laravel_version' => app()->version(),
        'auth_check' => \Illuminate\Support\Facades\Auth::check(),
        'user' => \Illuminate\Support\Facades\Auth::user(),
        'session_driver' => config('session.driver'),
        'session_cookie' => config('session.cookie'),
        'db_config_l11' => [
            'host' => config('database.connections.laravel11_mysql.host'),
            'port' => config('database.connections.laravel11_mysql.port'),
            'database' => config('database.connections.laravel11_mysql.database'),
            'username' => config('database.connections.laravel11_mysql.username'),
            'has_password' => filled(config('database.connections.laravel11_mysql.password')),
        ],
    ]);
});
