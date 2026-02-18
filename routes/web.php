<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DatasourceController;
use App\Http\Controllers\MasterReferensiController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {

    Route::get('/', fn () => view('welcome'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/', fn () => view('welcome'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

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

    Route::get('/map-monitor', [\App\Http\Controllers\MapMonitorController::class, 'index'])->name('map-monitor');
    Route::get('/map-monitor/data', [\App\Http\Controllers\MapMonitorController::class, 'getData'])->name('map-monitor.data');

    // Master Referensi — data dari database (seeder)
    Route::prefix('master/referensi')->name('master.referensi.')->group(function () {
        Route::get('/provinsi', [MasterReferensiController::class, 'provinsi'])->name('provinsi');
        Route::get('/kabkota', [MasterReferensiController::class, 'kabkota'])->name('kabkota');
        Route::get('/simpul', [MasterReferensiController::class, 'simpul'])->name('simpul');
        Route::get('/moda', [MasterReferensiController::class, 'moda'])->name('moda');
    });
    Route::resource('users', UserController::class);
    Route::get('/pengguna', fn () => redirect()->route('users.index'))->name('pengguna');

    Route::prefix('datasource')->name('datasource.')->group(function () {
        Route::get('/upload', [DatasourceController::class, 'upload'])->name('upload');
        Route::post('/upload', [DatasourceController::class, 'storeUpload'])->name('store');
        Route::post('/upload/process-chunk', [DatasourceController::class, 'processChunk'])->name('process-chunk');
        Route::get('/history', [DatasourceController::class, 'history'])->name('history');
        Route::post('/history/{id}/delete-chunk', [DatasourceController::class, 'destroyChunk'])->name('destroy-chunk');
        Route::get('/raw-data', [DatasourceController::class, 'rawData'])->name('raw-data');
        Route::get('/summary', [DatasourceController::class, 'summary'])->name('summary');
    });

    // Log Aktivitas
    Route::get('/log-aktivitas', [ActivityLogController::class, 'index'])->name('log-aktivitas');
    Route::get('/log-aktivitas/export', [ActivityLogController::class, 'export'])->name('log-aktivitas.export');
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

    // Catat login ke activity_logs
    \App\Models\ActivityLog::log('Login SSO', Auth::user()?->name, 'Success');

    return redirect('/');
})->name('sso.login');
