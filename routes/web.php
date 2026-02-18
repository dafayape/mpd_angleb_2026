<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('welcome');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    Route::get('/profile', function () {
        return view('placeholder', ['title' => 'Profil Pengguna', 'breadcrumb' => ['Dashboard', 'Profil']]);
    })->name('profile.edit');

    // =============================================
    // Grafik MPD - Nasional
    // =============================================
    Route::get('/grafik-mpd/nasional/pergerakan', function () {
        return view('placeholder', ['title' => 'Grafik MPD - Pergerakan (Nasional)', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Pergerakan']]);
    })->name('grafik-mpd.nasional.pergerakan');

    Route::get('/grafik-mpd/nasional/od-provinsi', function () {
        return view('placeholder', ['title' => 'Grafik MPD - O-D Provinsi (Nasional)', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'O-D Provinsi']]);
    })->name('grafik-mpd.nasional.od-provinsi');

    Route::get('/grafik-mpd/nasional/top-kabkota', function () {
        return view('placeholder', ['title' => 'Grafik MPD - Top Kabupaten/Kota (Nasional)', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Top Kabupaten/Kota']]);
    })->name('grafik-mpd.nasional.top-kabkota');

    Route::get('/grafik-mpd/nasional/mode-share', function () {
        return view('placeholder', ['title' => 'Grafik MPD - Mode Share (Nasional)', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Mode Share']]);
    })->name('grafik-mpd.nasional.mode-share');

    Route::get('/grafik-mpd/nasional/simpul', function () {
        return view('placeholder', ['title' => 'Grafik MPD - Simpul (Nasional)', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Simpul']]);
    })->name('grafik-mpd.nasional.simpul');

    // =============================================
    // Grafik MPD - Jabodetabek
    // =============================================
    Route::get('/grafik-mpd/jabodetabek/pergerakan-orang', function () {
        return view('placeholder', ['title' => 'Grafik MPD - Pergerakan & Orang (Jabodetabek)', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang']]);
    })->name('grafik-mpd.jabodetabek.pergerakan-orang');

    Route::get('/grafik-mpd/jabodetabek/pergerakan-orang-opsel', function () {
        return view('placeholder', ['title' => 'Grafik MPD - Pergerakan & Orang Opsel (Jabodetabek)', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang (Opsel)']]);
    })->name('grafik-mpd.jabodetabek.pergerakan-orang-opsel');

    Route::get('/grafik-mpd/jabodetabek/od-kabkota', function () {
        return view('placeholder', ['title' => 'Grafik MPD - O-D Kabupaten Kota (Jabodetabek)', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'O-D Kabupaten Kota']]);
    })->name('grafik-mpd.jabodetabek.od-kabkota');

    Route::get('/grafik-mpd/jabodetabek/mode-share', function () {
        return view('placeholder', ['title' => 'Grafik MPD - Mode Share (Jabodetabek)', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Mode Share']]);
    })->name('grafik-mpd.jabodetabek.mode-share');

    Route::get('/grafik-mpd/jabodetabek/simpul', function () {
        return view('placeholder', ['title' => 'Grafik MPD - Simpul (Jabodetabek)', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Simpul']]);
    })->name('grafik-mpd.jabodetabek.simpul');

    // =============================================
    // Data MPD Opsel - Nasional
    // =============================================
    Route::get('/data-mpd/nasional/pergerakan', function () {
        return view('placeholder', ['title' => 'Data MPD Opsel - Pergerakan (Nasional)', 'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'Pergerakan']]);
    })->name('data-mpd.nasional.pergerakan');

    Route::get('/data-mpd/nasional/mode-share', function () {
        return view('placeholder', ['title' => 'Data MPD Opsel - Mode Share (Nasional)', 'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'Mode Share']]);
    })->name('data-mpd.nasional.mode-share');

    Route::get('/data-mpd/nasional/od-simpul', function () {
        return view('placeholder', ['title' => 'Data MPD Opsel - O-D Simpul (Nasional)', 'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'O-D Simpul']]);
    })->name('data-mpd.nasional.od-simpul');

    // =============================================
    // Data MPD Opsel - Jabodetabek
    // =============================================
    Route::get('/data-mpd/jabodetabek/pergerakan', function () {
        return view('placeholder', ['title' => 'Data MPD Opsel - Pergerakan (Jabodetabek)', 'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Pergerakan']]);
    })->name('data-mpd.jabodetabek.pergerakan');

    Route::get('/data-mpd/jabodetabek/mode-share', function () {
        return view('placeholder', ['title' => 'Data MPD Opsel - Mode Share (Jabodetabek)', 'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Mode Share']]);
    })->name('data-mpd.jabodetabek.mode-share');

    Route::get('/data-mpd/jabodetabek/od-simpul', function () {
        return view('placeholder', ['title' => 'Data MPD Opsel - O-D Simpul (Jabodetabek)', 'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'O-D Simpul']]);
    })->name('data-mpd.jabodetabek.od-simpul');

    // =============================================
    // Map Monitor
    // =============================================
    Route::get('/map-monitor', function () {
        return view('placeholder', ['title' => 'Map Monitor', 'breadcrumb' => ['Dashboard', 'Map Monitor']]);
    })->name('map-monitor');

    // =============================================
    // Master - Referensi
    // =============================================
    Route::get('/master/referensi/provinsi', function () {
        return view('placeholder', ['title' => 'Referensi - Provinsi', 'breadcrumb' => ['Master', 'Referensi', 'Provinsi']]);
    })->name('master.referensi.provinsi');

    Route::get('/master/referensi/kabkota', function () {
        return view('placeholder', ['title' => 'Referensi - Kabupaten Kota', 'breadcrumb' => ['Master', 'Referensi', 'Kabupaten Kota']]);
    })->name('master.referensi.kabkota');

    Route::get('/master/referensi/simpul', function () {
        return view('placeholder', ['title' => 'Referensi - Simpul', 'breadcrumb' => ['Master', 'Referensi', 'Simpul']]);
    })->name('master.referensi.simpul');

    Route::get('/master/referensi/moda', function () {
        return view('placeholder', ['title' => 'Referensi - Moda', 'breadcrumb' => ['Master', 'Referensi', 'Moda']]);
    })->name('master.referensi.moda');

    // =============================================
    // Master - Pengguna
    // =============================================
    Route::get('/pengguna', function () {
        return view('placeholder', ['title' => 'Pengguna', 'breadcrumb' => ['Master', 'Pengguna']]);
    })->name('pengguna');

    // =============================================
    // Datasource
    // =============================================
    Route::get('/datasource/upload', function () {
        return view('placeholder', ['title' => 'Upload File (xlsx)', 'breadcrumb' => ['Datasource', 'Upload File']]);
    })->name('datasource.upload');

    Route::get('/datasource/history', function () {
        return view('placeholder', ['title' => 'History File Upload', 'breadcrumb' => ['Datasource', 'History File Upload']]);
    })->name('datasource.history');

    Route::get('/datasource/raw-data', function () {
        return view('placeholder', ['title' => 'View Raw Data', 'breadcrumb' => ['Datasource', 'Raw Data']]);
    })->name('datasource.raw-data');

    // =============================================
    // System & Monitoring
    // =============================================
    Route::get('/log-aktivitas', function () {
        return view('placeholder', ['title' => 'Log Aktivitas', 'breadcrumb' => ['System & Monitoring', 'Log Aktivitas']]);
    })->name('log-aktivitas');
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
