<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {

    Route::get('/', fn () => view('welcome'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', fn () => view('placeholder', ['title' => 'Profil Pengguna', 'breadcrumb' => ['Dashboard', 'Profil']]))->name('profile.edit');

    Route::get('/grafik-mpd/nasional/pergerakan', fn () => view('placeholder', ['title' => 'Pergerakan', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Pergerakan']]))->name('grafik-mpd.nasional.pergerakan');
    Route::get('/grafik-mpd/nasional/od-provinsi', fn () => view('placeholder', ['title' => 'O-D Provinsi', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'O-D Provinsi']]))->name('grafik-mpd.nasional.od-provinsi');
    Route::get('/grafik-mpd/nasional/top-kabkota', fn () => view('placeholder', ['title' => 'Top Kabupaten/Kota', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Top Kabupaten/Kota']]))->name('grafik-mpd.nasional.top-kabkota');
    Route::get('/grafik-mpd/nasional/mode-share', fn () => view('placeholder', ['title' => 'Mode Share', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Mode Share']]))->name('grafik-mpd.nasional.mode-share');
    Route::get('/grafik-mpd/nasional/simpul', fn () => view('placeholder', ['title' => 'Simpul', 'breadcrumb' => ['Grafik MPD', 'Nasional', 'Simpul']]))->name('grafik-mpd.nasional.simpul');

    Route::get('/grafik-mpd/jabodetabek/pergerakan-orang', fn () => view('placeholder', ['title' => 'Pergerakan & Orang', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang']]))->name('grafik-mpd.jabodetabek.pergerakan-orang');
    Route::get('/grafik-mpd/jabodetabek/pergerakan-orang-opsel', fn () => view('placeholder', ['title' => 'Pergerakan & Orang (Opsel)', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Pergerakan & Orang (Opsel)']]))->name('grafik-mpd.jabodetabek.pergerakan-orang-opsel');
    Route::get('/grafik-mpd/jabodetabek/od-kabkota', fn () => view('placeholder', ['title' => 'O-D Kabupaten Kota', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'O-D Kabupaten Kota']]))->name('grafik-mpd.jabodetabek.od-kabkota');
    Route::get('/grafik-mpd/jabodetabek/mode-share', fn () => view('placeholder', ['title' => 'Mode Share', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Mode Share']]))->name('grafik-mpd.jabodetabek.mode-share');
    Route::get('/grafik-mpd/jabodetabek/simpul', fn () => view('placeholder', ['title' => 'Simpul', 'breadcrumb' => ['Grafik MPD', 'Jabodetabek', 'Simpul']]))->name('grafik-mpd.jabodetabek.simpul');

    Route::get('/data-mpd/nasional/pergerakan', fn () => view('placeholder', ['title' => 'Pergerakan', 'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'Pergerakan']]))->name('data-mpd.nasional.pergerakan');
    Route::get('/data-mpd/nasional/mode-share', fn () => view('placeholder', ['title' => 'Mode Share', 'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'Mode Share']]))->name('data-mpd.nasional.mode-share');
    Route::get('/data-mpd/nasional/od-simpul', fn () => view('placeholder', ['title' => 'O-D Simpul', 'breadcrumb' => ['Data MPD Opsel', 'Nasional', 'O-D Simpul']]))->name('data-mpd.nasional.od-simpul');

    Route::get('/data-mpd/jabodetabek/pergerakan', fn () => view('placeholder', ['title' => 'Pergerakan', 'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Pergerakan']]))->name('data-mpd.jabodetabek.pergerakan');
    Route::get('/data-mpd/jabodetabek/mode-share', fn () => view('placeholder', ['title' => 'Mode Share', 'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'Mode Share']]))->name('data-mpd.jabodetabek.mode-share');
    Route::get('/data-mpd/jabodetabek/od-simpul', fn () => view('placeholder', ['title' => 'O-D Simpul', 'breadcrumb' => ['Data MPD Opsel', 'Jabodetabek', 'O-D Simpul']]))->name('data-mpd.jabodetabek.od-simpul');

    Route::get('/map-monitor', fn () => view('placeholder', ['title' => 'Map Monitor', 'breadcrumb' => ['Dashboard', 'Map Monitor']]))->name('map-monitor');

    Route::get('/master/referensi/provinsi', fn () => view('placeholder', ['title' => 'Provinsi', 'breadcrumb' => ['Master', 'Referensi', 'Provinsi']]))->name('master.referensi.provinsi');
    Route::get('/master/referensi/kabkota', fn () => view('placeholder', ['title' => 'Kabupaten Kota', 'breadcrumb' => ['Master', 'Referensi', 'Kabupaten Kota']]))->name('master.referensi.kabkota');
    Route::get('/master/referensi/simpul', fn () => view('placeholder', ['title' => 'Simpul', 'breadcrumb' => ['Master', 'Referensi', 'Simpul']]))->name('master.referensi.simpul');
    Route::get('/master/referensi/moda', fn () => view('placeholder', ['title' => 'Moda', 'breadcrumb' => ['Master', 'Referensi', 'Moda']]))->name('master.referensi.moda');
    Route::resource('users', UserController::class);
    Route::get('/pengguna', fn () => redirect()->route('users.index'))->name('pengguna');

    Route::get('/datasource/upload', fn () => view('placeholder', ['title' => 'Upload File (xlsx)', 'breadcrumb' => ['Datasource', 'Upload File']]))->name('datasource.upload');
    Route::get('/datasource/history', fn () => view('placeholder', ['title' => 'History File Upload', 'breadcrumb' => ['Datasource', 'History File Upload']]))->name('datasource.history');
    Route::get('/datasource/raw-data', fn () => view('placeholder', ['title' => 'View Raw Data', 'breadcrumb' => ['Datasource', 'Raw Data']]))->name('datasource.raw-data');

    Route::get('/log-aktivitas', fn () => view('placeholder', ['title' => 'Log Aktivitas', 'breadcrumb' => ['System & Monitoring', 'Log Aktivitas']]))->name('log-aktivitas');
});

Route::get('/sso-login', function (Request $request) {
    $userId    = $request->query('user_id');
    $timestamp = $request->query('timestamp');
    $signature = $request->query('signature');

    if (!$userId || !$timestamp || !$signature) {
        abort(403, 'Parameter SSO tidak lengkap.');
    }

    if (abs(time() - $timestamp) > 600) {
        abort(403, 'Tautan SSO sudah kedaluwarsa.');
    }

    $expected = hash_hmac('sha256', $userId . '|' . $timestamp, config('app.key'));

    if (!hash_equals($expected, $signature)) {
        abort(403, 'Tanda tangan SSO tidak valid.');
    }

    Auth::loginUsingId($userId);
    $request->session()->regenerate();

    return redirect('/');
})->name('sso.login');
