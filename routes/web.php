<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DatasourceController;
use App\Http\Controllers\MasterReferensiController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Nasional
    Route::prefix('nasional')->name('pages.nasional.')->group(function () {
        Route::view('/data-dasar', 'pages.nasional.data-dasar')->name('data-dasar');
        Route::view('/pergerakan-harian', 'pages.nasional.pergerakan-harian')->name('pergerakan-harian');
        Route::view('/od', 'pages.nasional.od')->name('od');
        Route::view('/mode-share', 'pages.nasional.mode-share')->name('mode-share');
    });

    // Jabodetabek
    Route::prefix('jabodetabek')->name('pages.jabodetabek.')->group(function () {
        Route::view('/intra-pergerakan', 'pages.jabodetabek.intra-pergerakan')->name('intra-pergerakan');
        Route::view('/intra-od', 'pages.jabodetabek.intra-od')->name('intra-od');
        Route::view('/inter-pergerakan', 'pages.jabodetabek.inter-pergerakan')->name('inter-pergerakan');
        Route::view('/inter-od', 'pages.jabodetabek.inter-od')->name('inter-od');
    });

    // Substansi Tambahan
    Route::prefix('substansi')->name('pages.substansi.')->group(function () {
        Route::view('/stasiun-ka-antar-kota', 'pages.substansi.stasiun-ka-antar-kota')->name('stasiun-ka-antar-kota');
        Route::view('/stasiun-ka-regional', 'pages.substansi.stasiun-ka-regional')->name('stasiun-ka-regional');
        Route::view('/stasiun-ka-cepat', 'pages.substansi.stasiun-ka-cepat')->name('stasiun-ka-cepat');
        Route::view('/pelabuhan-penyeberangan', 'pages.substansi.pelabuhan-penyeberangan')->name('pelabuhan-penyeberangan');
        Route::view('/pelabuhan-laut', 'pages.substansi.pelabuhan-laut')->name('pelabuhan-laut');
        Route::view('/bandara', 'pages.substansi.bandara')->name('bandara');
        Route::view('/terminal', 'pages.substansi.terminal')->name('terminal');
        Route::view('/od-simpul-pelabuhan', 'pages.substansi.od-simpul-pelabuhan')->name('od-simpul-pelabuhan');
        Route::view('/netflow', 'pages.substansi.netflow')->name('netflow');
    });

    // Kesimpulan & Rekomendasi
    Route::prefix('kesimpulan')->name('pages.kesimpulan.')->group(function () {
        Route::view('/nasional', 'pages.kesimpulan.nasional')->name('nasional');
        Route::view('/jabodetabek', 'pages.kesimpulan.jabodetabek')->name('jabodetabek');
        Route::view('/rekomendasi', 'pages.kesimpulan.rekomendasi')->name('rekomendasi');
    });

    // Executive Summary (New Pages)
    Route::view('/executive/daily-report-page', 'pages.executive.daily-report')->name('pages.executive.daily-report');

    // Pengaturan
    Route::view('/pengaturan', 'pages.pengaturan.pengaturan')->name('pengaturan');

    // Grafik MPD Routes
    Route::controller(\App\Http\Controllers\GrafikMpdController::class)->group(function () {
        // Nasional
        Route::get('/grafik-mpd/nasional/pergerakan', 'nasionalPergerakan')->name('grafik-mpd.nasional.pergerakan');
        Route::get('/grafik-mpd/nasional/od-provinsi', 'nasionalOdProvinsi')->name('grafik-mpd.nasional.od-provinsi');
        Route::get('/grafik-mpd/nasional/top-kabkota', 'nasionalTopKabkota')->name('grafik-mpd.nasional.top-kabkota');
        Route::get('/grafik-mpd/nasional/mode-share', 'nasionalModeShare')->name('grafik-mpd.nasional.mode-share');
        Route::get('/grafik-mpd/nasional/simpul', 'nasionalSimpul')->name('grafik-mpd.nasional.simpul');

        // Jabodetabek
        Route::get('/grafik-mpd/jabodetabek/pergerakan-orang', 'jabodetabekPergerakanOrang')->name('grafik-mpd.jabodetabek.pergerakan-orang');
        Route::get('/grafik-mpd/jabodetabek/pergerakan-orang-opsel', 'jabodetabekPergerakanOrangOpsel')->name('grafik-mpd.jabodetabek.pergerakan-orang-opsel');
        Route::get('/grafik-mpd/jabodetabek/od-kabkota', 'jabodetabekOdKabkota')->name('grafik-mpd.jabodetabek.od-kabkota');
        Route::get('/grafik-mpd/jabodetabek/mode-share', 'jabodetabekModeShare')->name('grafik-mpd.jabodetabek.mode-share');
        Route::get('/grafik-mpd/jabodetabek/simpul', 'jabodetabekSimpul')->name('grafik-mpd.jabodetabek.simpul');
    });

    Route::get('/data-mpd/nasional/pergerakan', [\App\Http\Controllers\DataMpdController::class, 'nasionalPergerakan'])->name('data-mpd.nasional.pergerakan');
    Route::get('/data-mpd/nasional/mode-share', [\App\Http\Controllers\DataMpdController::class, 'nasionalModeShare'])->name('data-mpd.nasional.mode-share');
    Route::get('/data-mpd/nasional/od-simpul', [\App\Http\Controllers\DataMpdController::class, 'nasionalOdSimpul'])->name('data-mpd.nasional.od-simpul');

    Route::get('/data-mpd/jabodetabek/pergerakan', [\App\Http\Controllers\DataMpdController::class, 'jabodetabekPergerakan'])->name('data-mpd.jabodetabek.pergerakan');
    Route::get('/data-mpd/jabodetabek/pergerakan-orang', [\App\Http\Controllers\DataMpdController::class, 'jabodetabekPergerakanOrang'])->name('data-mpd.jabodetabek.pergerakan-orang');
    Route::get('/data-mpd/jabodetabek/mode-share', [\App\Http\Controllers\DataMpdController::class, 'jabodetabekModeShare'])->name('data-mpd.jabodetabek.mode-share');
    Route::get('/data-mpd/jabodetabek/od-simpul', [\App\Http\Controllers\DataMpdController::class, 'jabodetabekOdSimpul'])->name('data-mpd.jabodetabek.od-simpul');

    // Map Monitor
    Route::get('/map-monitor', [\App\Http\Controllers\MapMonitorController::class, 'index'])->name('map-monitor');
    Route::get('/map-monitor/data', [\App\Http\Controllers\MapMonitorController::class, 'getData'])->name('map-monitor.data');
    Route::get('/map-monitor/search-simpul', [\App\Http\Controllers\MapMonitorController::class, 'searchSimpul'])->name('map-monitor.search-simpul');
    Route::get('/map-monitor/netflow', [\App\Http\Controllers\MapMonitorController::class, 'getNetflow'])->name('map-monitor.netflow');

    // Executive Summary
    Route::prefix('executive-summary')->name('executive.')->group(function () {
        Route::get('/daily-report', [\App\Http\Controllers\DailyReportController::class, 'index'])->name('daily-report');

        Route::get('/summary', [\App\Http\Controllers\ExecutiveSummaryController::class, 'index'])->name('summary');
        Route::get('/summary/data', [\App\Http\Controllers\ExecutiveSummaryController::class, 'getData'])->name('summary.data');
    });

    // Keynote Material (Restored)
    Route::get('/keynote-material', [\App\Http\Controllers\KeynoteController::class, 'index'])->name('keynote');
    Route::get('/keynote-material/data', [\App\Http\Controllers\KeynoteController::class, 'getData'])->name('keynote.data');

    // Master Referensi — data dari database (seeder)
    Route::prefix('master/referensi')->name('master.referensi.')->group(function () {
        Route::get('/provinsi', [MasterReferensiController::class, 'provinsi'])->name('provinsi');
        Route::get('/kabkota', [MasterReferensiController::class, 'kabkota'])->name('kabkota');
        Route::get('/simpul', [MasterReferensiController::class, 'simpul'])->name('simpul');
        Route::get('/moda', [MasterReferensiController::class, 'moda'])->name('moda');
    });

    // Rule Document Management
    Route::prefix('master/rule-document')->name('master.rule-document.')->group(function () {
        Route::get('/', [\App\Http\Controllers\RuleDocumentController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\RuleDocumentController::class, 'store'])->name('store');
        Route::get('/download/{id}', [\App\Http\Controllers\RuleDocumentController::class, 'download'])->name('download');
        Route::get('/preview/{id}', [\App\Http\Controllers\RuleDocumentController::class, 'preview'])->name('preview');
        Route::delete('/destroy/{id}', [\App\Http\Controllers\RuleDocumentController::class, 'destroy'])->name('destroy');
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

    // Log Developer
    Route::get('/devlog', [\App\Http\Controllers\DevLogController::class, 'index'])->name('devlog');
});

Route::get('/sso-login', function (Request $request) {
    $userId = $request->query('user_id');
    $timestamp = $request->query('timestamp');
    $signature = $request->query('signature');

    if (! $userId || ! $timestamp || ! $signature) {
        abort(403, 'Parameter SSO tidak lengkap.');
    }

    if (abs(time() - $timestamp) > 600) {
        abort(403, 'Tautan SSO sudah kedaluwarsa.');
    }

    $expected = hash_hmac('sha256', $userId.'|'.$timestamp, config('app.key'));

    if (! hash_equals($expected, $signature)) {
        abort(403, 'Tanda tangan SSO tidak valid.');
    }

    Auth::loginUsingId($userId);
    $request->session()->regenerate();

    // Catat login ke activity_logs (non-blocking: tabel mungkin belum di-migrate)
    try {
        \App\Models\ActivityLog::log('Login SSO', Auth::user()?->name, 'Success');
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('ActivityLog gagal: '.$e->getMessage());
    }

    return redirect('/');
})->name('sso.login');
