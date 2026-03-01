<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DatasourceController;
use App\Http\Controllers\MasterReferensiController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// === TEMPORARY DEBUG ===
Route::any('/debug-route-info', function () {
    $routes = collect(\Illuminate\Support\Facades\Route::getRoutes()->get())->map(fn($route) => [
        'uri' => $route->uri(),
        'methods' => $route->methods(),
    ])->toArray();

    return response()->json([
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
        'REQUEST_URI'    => $_SERVER['REQUEST_URI'] ?? 'N/A',
        'laravel_path'   => request()->path(),
        'laravel_method' => request()->method(),
        'registered_routes' => $routes,
    ]);
});

// Jika Nginx membaca path '/' tetapi karena suatu alasan methods-nya error, ini akan menangkapnya paksa
Route::get('/tes-root', function() {
    return "TES ROOT WORKS!";
});
// === END DEBUG ===

// Login route — SSO dikelola oleh Laravel 11, redirect ke sana
Route::get('/login', fn () => redirect('https://mpdbkt.web.id/login'))->name('login');

Route::middleware(['auth'])->group(function () {
    // Kita biarkan route aslinya disembunyikan dulu untuk testing
    // Route::match(['get', 'head'], '/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', function() { return "VERIFIED GET WORKS!"; });
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Nasional
    Route::prefix('nasional')->name('pages.nasional.')->group(function () {
        Route::view('/data-dasar', 'pages.nasional.data-dasar')->name('data-dasar');
        Route::get('/pergerakan-harian', [\App\Http\Controllers\DataMpdController::class, 'nasionalPergerakanHarianPage'])->name('pergerakan-harian');
        Route::get('/od', [\App\Http\Controllers\DataMpdController::class, 'nasionalOdSimpul'])->name('od');
        Route::get('/mode-share', [\App\Http\Controllers\DataMpdController::class, 'nasionalModeSharePage'])->name('mode-share');
    });

    // Jabodetabek
    Route::prefix('jabodetabek')->name('pages.jabodetabek.')->group(function () {
        Route::get('/intra-pergerakan', [\App\Http\Controllers\DataMpdController::class, 'jabodetabekIntraPergerakanPage'])->name('intra-pergerakan');
        Route::get('/intra-od', [\App\Http\Controllers\DataMpdController::class, 'jabodetabekIntraOdPage'])->name('intra-od');
        Route::get('/inter-pergerakan', [\App\Http\Controllers\DataMpdController::class, 'jabodetabekInterPergerakanPage'])->name('inter-pergerakan');
        Route::get('/inter-od', [\App\Http\Controllers\DataMpdController::class, 'jabodetabekInterOdPage'])->name('inter-od');
    });

    // Substansi Tambahan
    Route::prefix('substansi')->name('pages.substansi.')->group(function () {
        Route::get('/stasiun-ka-antar-kota', fn(\Illuminate\Http\Request $r) => app(\App\Http\Controllers\DataMpdController::class)->substansiSimpulPage($r, 'stasiun-ka-antar-kota'))->name('stasiun-ka-antar-kota');
        Route::get('/pelabuhan-penyeberangan', fn(\Illuminate\Http\Request $r) => app(\App\Http\Controllers\DataMpdController::class)->substansiSimpulPage($r, 'pelabuhan-penyeberangan'))->name('pelabuhan-penyeberangan');
        Route::get('/pelabuhan-laut', fn(\Illuminate\Http\Request $r) => app(\App\Http\Controllers\DataMpdController::class)->substansiSimpulPage($r, 'pelabuhan-laut'))->name('pelabuhan-laut');
        Route::get('/bandara', fn(\Illuminate\Http\Request $r) => app(\App\Http\Controllers\DataMpdController::class)->substansiSimpulPage($r, 'bandara'))->name('bandara');
        Route::get('/terminal', fn(\Illuminate\Http\Request $r) => app(\App\Http\Controllers\DataMpdController::class)->substansiSimpulPage($r, 'terminal'))->name('terminal');
        Route::get('/od-simpul-pelabuhan', fn(\Illuminate\Http\Request $r) => app(\App\Http\Controllers\DataMpdController::class)->substansiSimpulPage($r, 'od-simpul-pelabuhan'))->name('od-simpul-pelabuhan');
        Route::get('/netflow', [\App\Http\Controllers\DataMpdController::class, 'substansiNetflowPage'])->name('netflow');
    });

    // Kesimpulan & Rekomendasi
    Route::prefix('kesimpulan')->name('pages.kesimpulan.')->group(function () {
        Route::get('/nasional', [\App\Http\Controllers\DataMpdController::class, 'kesimpulanNasionalPage'])->name('nasional');
        Route::get('/jabodetabek', [\App\Http\Controllers\DataMpdController::class, 'kesimpulanJabodetabekPage'])->name('jabodetabek');
        Route::get('/rekomendasi', [\App\Http\Controllers\DataMpdController::class, 'rekomendasiPage'])->name('rekomendasi');
    });

    // Executive Summary (New Pages)
    Route::view('/executive/daily-report-page', 'pages.executive.daily-report')->name('pages.executive.daily-report');

    // Pengaturan
    Route::get('/pengaturan', [\App\Http\Controllers\PengaturanController::class, 'index'])->name('pengaturan');
    Route::post('/pengaturan', [\App\Http\Controllers\PengaturanController::class, 'update'])->name('pengaturan.update');
    Route::post('/pengaturan/test-whatsapp', [\App\Http\Controllers\PengaturanController::class, 'testWhatsApp'])->name('pengaturan.test-wa');

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
        Route::post('/daily-report', [\App\Http\Controllers\DailyReportController::class, 'sendWhatsApp'])->name('daily-report.send-wa');

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
        Route::post('/upload/validate', [DatasourceController::class, 'validateCsv'])->name('validate');
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
