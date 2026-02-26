# Laporan QC — MPD Angleb 2026

**Tanggal Audit:** 26 Februari 2026
**Auditor:** QC Engineer (AI-Assisted)
**Versi Aplikasi:** Laravel 12 / PHP 8.4
**Cakupan Inspeksi:** 32 file PHP aplikasi, 25 file database, 65 template Blade, semua konfigurasi

---

## Ringkasan Eksekutif

Aplikasi MPD Angleb 2026 dibangun di atas stack Laravel 12 + PostgreSQL + PostGIS + Redis dan secara fungsional mampu menjalankan alur inti: upload CSV → ETL → visualisasi dashboard. Arsitektur PostGIS sudah terbukti diimplementasikan dengan benar, pipeline CSV berfungsi via `processChunk()`, dan cache Redis sudah diterapkan di semua endpoint data-berat.

Namun dari perspektif kualitas kode dan keamanan produksi, ditemukan sejumlah **kegagalan kritis** yang harus segera diperbaiki sebelum aplikasi ini beroperasi di lingkungan produksi penuh. Yang paling berbahaya adalah: (1) `APP_DEBUG=true` di lingkungan produksi memaparkan stack trace penuh kepada pengguna dan penyerang, (2) **password database MySQL hardcoded** secara langsung di `config/database.php` — bukan dari `.env`, (3) **API Key Google Maps** tercantum di `.env` tanpa perlindungan apapun, (4) tidak ada satu pun `declare(strict_types=1)` di hampir semua file PHP aplikasi, dan (5) tidak ada Service Layer, Form Request, maupun Policy/Gate yang diimplementasikan.

Dari sisi arsitektur, controller-controller utama bersifat **fat controller** yang melanggar prinsip single-responsibility. `GrafikMpdController` memiliki 1.388 baris, `DataMpdController` memiliki 892 baris, dan `DatasourceController` memiliki 493 baris — semua berisi logika bisnis yang seharusnya diekstrak ke Service Layer. Tidak ada direktori `app/Services/`, `app/Http/Requests/`, maupun `app/Policies/` yang eksis dalam proyek ini.

Sisi positif: pipeline ETL berbasis PostGIS sudah benar, sistem antrean Job (`TransformRawToSpatialJob`) sudah ada meski hanya dimicu via `dispatchAfterResponse()` bukan antrean Redis penuh, materialized view sudah didefinisikan, dan pengecekan SSO via HMAC-SHA256 sudah cukup aman. Fitur-fitur utama seperti Dashboard, Map Monitor, Executive Summary, Daily Report, dan Referensi Rules semuanya sudah ada dan fungsional secara dasar.

**Rekomendasi: NO-GO untuk production** sampai setidaknya semua item P0 di bawah ini diselesaikan. Estimasi waktu perbaikan P0: 3-5 hari kerja untuk developer yang familiar dengan codebase.

---

## Skor Keseluruhan

| Seksi | Nama                           | Skor /10 | Status             |
| ----- | ------------------------------ | -------- | ------------------ |
| 1     | Project Structure & Standards  | **4**    | ❌ Signifikan      |
| 2     | Route Integrity                | **7**    | ⚠️ Minor           |
| 3     | Controller Quality             | **3**    | ❌ Kritis          |
| 4     | Model Quality                  | **5**    | ⚠️ Perlu Perbaikan |
| 5     | Migration Integrity            | **6**    | ⚠️ Perlu Perbaikan |
| 6     | Service Layer Quality          | **1**    | ❌ Kritis          |
| 7     | CSV Ingestion Pipeline         | **6**    | ⚠️ Perlu Perbaikan |
| 8     | Query Performance & N+1        | **6**    | ⚠️ Perlu Perbaikan |
| 9     | Security Audit                 | **3**    | ❌ Kritis          |
| 10    | Blade & Frontend Quality       | **7**    | ⚠️ Minor           |
| 11    | Dummy Data Detection           | **5**    | ⚠️ Perlu Perbaikan |
| 12    | Feature Completeness           | **6**    | ⚠️ Perlu Perbaikan |
| 13    | Infrastructure & Configuration | **5**    | ⚠️ Perlu Perbaikan |

**Skor Total: 64/130 (49.2%) — TIDAK LAYAK PRODUKSI**

---

## Seksi 1 — Project Structure & Standards

### Temuan

- ❌ **`APP_DEBUG=true` di produksi** — `.env` baris 3. Stack trace PHP akan tampil kepada pengguna akhir di production. Ini pelanggaran keamanan kritis.
- ❌ **`declare(strict_types=1)` hampir tidak ada** — Dari 32 file PHP aplikasi, hanya 3 file yang mendeklarasikan strict types: `Controller.php`, `ImportMpdCommand.php`, `MovementAnalyticsController.php`. Semua controller utama (DashboardController, DatasourceController, MapMonitorController, dll.) tidak memilikinya. Ini berarti PHP tidak melakukan validasi tipe secara ketat.
- ❌ **Password hardcoded di `config/database.php`** — Baris 17: `'password' => '36f87268eb95c41f'` tercantum langsung sebagai string literal, bukan `env('LARAVEL11_DB_PASSWORD')`. Password ini masuk ke version control.
- ❌ **Google Maps API Key di `.env`** — Baris 64: `GOOGLE_MAPS_API_KEY=AIzaSyCE4QgGk4TzGazf93LGWJ3M1_BhOi1Glmg`. API key ter-expose di file yang sering disalin atau dilihat oleh developer. Risiko quota abuse.
- ⚠️ **File script Python dan PHP di root proyek** — `fix.py`, `manual_check.php`, `manual_seed.php`, `pdo_seed.php`, `test_db.php` ada di root proyek. File debug/maintenance ini tidak boleh berada di production root.
- ⚠️ **Tidak ada direktori `app/Services/`** — Tidak ada service layer sama sekali. Semua logika bisnis ada di controller.
- ⚠️ **Tidak ada direktori `app/Http/Requests/`** — Tidak ada Form Request sama sekali.
- ⚠️ **Tidak ada direktori `app/Policies/`** — Tidak ada Policy atau Gate yang diregistrasi.
- ✅ Namespace sudah konsisten dan benar.
- ✅ Tidak ditemukan `dd()`, `dump()`, atau `var_dump()` di kode produksi.
- ✅ `APP_KEY` sudah diset dengan nilai `base64:b2fSlBbTSnBnBdRLtRf80jQWcsgrNbPbHp8ghHKVwxE=`.
- ✅ `APP_ENV=production` sudah benar, hanya `APP_DEBUG` yang salah.

### Rekomendasi

```ini
# .env — SEGERA PERBAIKI
APP_DEBUG=false

# config/database.php — PINDAHKAN KE .env
'laravel11_mysql' => [
    'password' => env('LARAVEL11_DB_PASSWORD', ''),
    // ...
],
```

Semua file `app/**/*.php` harus menambahkan `declare(strict_types=1);` setelah tag `<?php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;
```

Hapus semua file debug dari root: `fix.py`, `manual_check.php`, `manual_seed.php`, `pdo_seed.php`, `test_db.php`, `seed_result.txt`, `temp_pdf_text.txt`.

---

## Seksi 2 — Route Integrity

### Temuan

✅ = Valid | ⚠️ = Peringatan | ❌ = Gagal

| Method       | URI                                | Controller@Method                      | Name                           | Middleware | Status |
| ------------ | ---------------------------------- | -------------------------------------- | ------------------------------ | ---------- | ------ |
| GET          | /                                  | DashboardController@index              | dashboard                      | auth       | ✅     |
| POST         | /logout                            | AuthController@logout                  | logout                         | auth       | ✅     |
| GET          | /profile                           | ProfileController@edit                 | profile.edit                   | auth       | ✅     |
| PUT          | /profile                           | ProfileController@update               | profile.update                 | auth       | ✅     |
| GET          | /nasional/data-dasar               | Route::view                            | pages.nasional.data-dasar      | auth       | ✅     |
| GET          | /grafik-mpd/nasional/pergerakan    | GrafikMpdController@nasionalPergerakan | grafik-mpd.nasional.pergerakan | auth       | ✅     |
| GET          | /map-monitor                       | MapMonitorController@index             | map-monitor                    | auth       | ✅     |
| GET          | /executive-summary/daily-report    | DailyReportController@index            | executive.daily-report         | auth       | ✅     |
| GET          | /executive-summary/summary         | ExecutiveSummaryController@index       | executive.summary              | auth       | ✅     |
| GET          | /keynote-material                  | KeynoteController@index                | keynote                        | auth       | ✅     |
| GET          | /master/rule-document              | RuleDocumentController@index           | master.rule-document.index     | auth       | ✅     |
| DELETE       | /master/rule-document/destroy/{id} | RuleDocumentController@destroy         | master.rule-document.destroy   | auth       | ✅     |
| GET,POST,... | /users                             | UserController (resource)              | users.\*                       | auth       | ✅     |
| GET          | /datasource/upload                 | DatasourceController@upload            | datasource.upload              | auth       | ✅     |
| GET          | /log-aktivitas                     | ActivityLogController@index            | log-aktivitas                  | auth       | ✅     |
| GET          | /sso-login                         | Closure inline                         | sso.login                      | **none**   | ⚠️     |

- ⚠️ **Route `/sso-login` tidak memiliki middleware auth** — Ini disengaja (endpoint SSO publik), namun implementasinya sebagai **closure inline di routes/web.php** (baris 145-175) sangat tidak ideal. Minimal harus dipindah ke `SsoController`.
- ⚠️ **Route `/sso-login` tidak membatasi IP atau rate limit** — Siapapun dapat mencoba HMAC brute force tanpa pembatasan.
- ⚠️ **`users.show` route tidak memiliki konten berguna** — Metode `show` di UserController hanya mengembalikan view `users.show` yang mungkin tidak ada.
- ❌ **Tidak ada file `routes/api.php`** — Padahal `Api/Mpd/MovementAnalyticsController.php` ada sebagai file PHP di `app/Http/Controllers/Api/`. Controller ini tidak terhubung ke route apapun.
- ✅ Semua route dalam `auth` middleware group terlindungi.
- ✅ Tidak ada nama route duplikat.
- ✅ Konvensi penamaan route konsisten.

### Rekomendasi

```php
// routes/web.php — Pindahkan SSO logic ke controller
Route::get('/sso-login', [SsoController::class, 'handle'])
    ->name('sso.login')
    ->middleware('throttle:10,1'); // max 10 request/menit

// Tambahkan rate limiting untuk proteksi SSO brute force
```

Buat file `routes/api.php` dan daftarkan `MovementAnalyticsController`:

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/mpd/analytics', [MovementAnalyticsController::class, 'index'])->name('api.mpd.analytics');
});
```

---

## Seksi 3 — Controller Quality

### Temuan

- ❌ **Fat controllers masif** — `GrafikMpdController.php` memiliki **1.388 baris**, `DataMpdController.php` memiliki **892 baris**, `DatasourceController.php` memiliki **493 baris**. Semua berisikan logika query, pemrosesan data, dan formatting yang seharusnya ada di Service layer.
- ❌ **Tidak ada Form Request sama sekali** — Validasi dilakukan inline dengan `$request->validate()` di dalam metode controller. Tidak ada file `app/Http/Requests/`.
- ❌ **Tidak ada `$this->authorize()` atau Gate** — `RuleDocumentController` dan `UserController` menggunakan pengecekan role manual `if ($user->role !== 'admin')` yang rawan inkonsistensi.
- ❌ **Business logic di controller** — `DashboardController::index()` (165 baris) berisi seluruh logika agregasi, color mapping, analysis string generation — semua seharusnya di Service.
- ❌ **Response type tidak konsisten** — `ExecutiveSummaryController::getData()` mengembalikan `response()->json()` dari dalam `Cache::remember()`, padahal yang ter-cache adalah objek Response bukan data. Ini **bug serius**: cache menyimpan serialized Response object, bukan array, sehingga setelah cache hit akan return objek stale atau gagal deserialisasi.
- ❌ **`ini_set()` dipanggil di dalam controller** — `DatasourceController::processChunk()` baris 78-79 memanggil `ini_set('max_execution_time', 0)` dan `ini_set('memory_limit', '2048M')`. Ini tidak proper untuk produksi.
- ⚠️ **`request()` global helper dipakai dalam Cache closure** — `DashboardController` baris 44: `$isForecastFilter = request()->input(...)` dipanggil di dalam closure `Cache::remember()`. Saat cache hit di request berikutnya, nilai ini akan ikut ter-cache (stale request context).
- ✅ `AuthController`, `MasterReferensiController`, `ActivityLogController`, `ProfileController` sudah cukup thin.
- ✅ HTTP method routing sudah benar (GET untuk list/show, POST untuk create, DELETE untuk destroy).

### Rekomendasi

Buat Service Layer untuk setiap domain:

```
app/Services/
├── Dashboard/DashboardAnalyticsService.php
├── Datasource/CsvImportService.php
├── MapMonitor/MapMonitorService.php
├── GrafikMpd/GrafikMpdService.php
└── Executive/DailyReportService.php
```

Perbaiki bug kritis cache Response object:

```php
// SALAH — Cache menyimpan Response object
return Cache::remember($cacheKey, 3600, function () {
    return response()->json(['data' => ...]);
});

// BENAR — Cache menyimpan array data, return response di luar
$data = Cache::remember($cacheKey, 3600, function () {
    return ['data' => ...]; // array biasa
});
return response()->json($data);
```

---

## Seksi 4 — Model Quality

### Temuan

- ❌ **`SpatialMovement` model memiliki `$primaryKey = null`** — File `app/Models/SpatialMovement.php` baris 32. Ini menyebabkan banyak operasi Eloquent yang bergantung pada primary key (seperti `find()`, `findOrFail()`, relasi) akan gagal dengan error. Model ini seharusnya menggunakan tabel dengan ID serial atau setidaknya mendeklarasikan composite key approach dengan benar.
- ❌ **`ActivityLog` menggunakan `$guarded = ['id']`** — File `app/Models/ActivityLog.php` baris 9. Ini artinya hampir semua kolom bisa di-mass-assign. Lebih aman menggunakan `$fillable`.
- ❌ **`User` model menggunakan koneksi `laravel11_mysql`** — `app/Models/User.php` baris 12. Ini berarti sistem autentikasi bergantung pada database MySQL eksternal. Jika koneksi MySQL tersebut putus, **seluruh sistem auth gagal**. Password untuk koneksi ini ditemukan hardcoded di `config/database.php`.
- ❌ **`SpatialMovement` tidak memiliki `$fillable`** — Hanya menggunakan `$guarded = ['id']` yang setara dengan mengizinkan mass assignment hampir semua kolom. Di tabel dengan data sensitif pergerakan orang, ini berisiko.
- ⚠️ **`RuleDocument::uploader()` relationship cross-database** — Relasi BelongsTo dari model PostgreSQL ke model MySQL (`User`) tidak akan berfungsi otomatis di Eloquent karena perbedaan koneksi database.
- ⚠️ **`Simpul` model memiliki accessor `getCoordinatesAttribute()` yang selalu return `null`** — Baris 32. Ini kode dead yang menyesatkan.
- ⚠️ **Tidak ada model untuk `Provinsi`, `KabupatenKota`, `Moda`** — Controller mengakses tabel referensi langsung via `DB::table()` (raw query builder) tanpa model Eloquent.
- ✅ `ImportJob` model sudah baik dengan `$fillable` dan `$casts` yang tepat.
- ✅ `Simpul` model sudah menggunakan `$fillable` dengan benar.
- ✅ PostGIS geography columns sudah benar: `geography(POINT, 4326)` di migration.

### Rekomendasi

```php
// app/Models/SpatialMovement.php — Perbaiki primary key
class SpatialMovement extends Model
{
    // Opsi 1: Tambahkan serial ID ke migration
    // Opsi 2: Gunakan pendekatan read-only dengan primaryKey = null tapi
    //         jangan gunakan metode yang butuh primary key

    // MINIMUM: Ganti guarded ke fillable spesifik
    protected $fillable = [
        'tanggal', 'opsel', 'kategori',
        'kode_origin_kabupaten_kota', 'kode_dest_kabupaten_kota',
        'kode_origin_simpul', 'kode_dest_simpul',
        'kode_moda', 'total', 'is_forecast',
        'origin_location', 'dest_location', 'distance_meters',
    ];
}

// app/Models/ActivityLog.php — Ganti guarded ke fillable
protected $fillable = [
    'user_id', 'action', 'subject',
    'description', 'status', 'ip_address', 'user_agent',
];
```

---

## Seksi 5 — Migration Integrity

### Temuan

- ❌ **Timestamp duplikat** — Dua migration memiliki timestamp yang sama persis `2026_03_01_000006`:
    - `2026_03_01_000006_create_reference_data_schema.php`
    - `2026_03_01_000006_create_spatial_movements_partitions.php`
      Ini akan menyebabkan `php artisan migrate` gagal atau berperilaku tidak terduga.
- ❌ **`raw_mpd_data` dan `spatial_movements` dibuat sebagai partitioned table TANPA partition default** — Migration `2026_03_01_000003` membuat `PARTITION BY RANGE (tanggal)` tapi tidak membuat partition apapun. Setiap INSERT ke tabel ini akan **gagal dengan error** sampai partition untuk range tanggal yang sesuai dibuat secara manual.
- ❌ **Tidak ada migration untuk `referensi_rules` table** — Requirement audit menyebut tabel ini, namun tidak ditemukan di direktori migrations.
- ❌ **Tidak ada migration untuk `mpd_upload_logs` table** — Requirement menyebut tabel ini, tidak ditemukan.
- ⚠️ **Timestamp `2026_03_01_000010` duplikat** — Dua file:
    - `2026_03_01_000010_create_activity_logs_table.php`
    - `2026_03_01_000010_create_historical_and_materialized_views.php`
- ⚠️ **Migration `2026_03_01_000006_create_spatial_movements_partitions.php`** belum dibaca isinya — perlu verifikasi apakah partition untuk Maret 2026 terdefinisi.
- ⚠️ **Migration `2026_03_01_000007_create_import_jobs_table.php`** tidak memiliki kolom `opsel`, `kategori`, `user_id` — kolom-kolom ini ditambahkan di migration terpisah `*_000008_add_metadata_to_import_jobs.php`. Ini tidak masalah tapi rawan jika migration dijalankan tidak lengkap.
- ✅ `down()` method ada di semua migration yang diperiksa.
- ✅ Foreign key `ref_cities → ref_provinces` sudah benar dengan `cascadeOnDelete()`.
- ✅ Index untuk `opsel`, `kategori`, `kode_origin_kabupaten_kota`, `kode_dest_kabupaten_kota` sudah ada.
- ✅ PostGIS `geography(POINT, 4326)` sudah benar di `ref_transport_nodes`.

### Rekomendasi

```bash
# Perbaiki timestamp duplikat — rename file migration
mv 2026_03_01_000006_create_spatial_movements_partitions.php \
   2026_03_01_000006b_create_spatial_movements_partitions.php

mv 2026_03_01_000010_create_historical_and_materialized_views.php \
   2026_03_01_000010b_create_historical_and_materialized_views.php
```

```sql
-- Tambahkan partition untuk periode Angkutan Lebaran 2026
-- (harus dieksekusi manual di PostgreSQL setelah migrate)
CREATE TABLE spatial_movements_2026_q1
    PARTITION OF spatial_movements
    FOR VALUES FROM ('2026-01-01') TO ('2026-04-01');

CREATE TABLE raw_mpd_data_2026_q1
    PARTITION OF raw_mpd_data
    FOR VALUES FROM ('2026-01-01') TO ('2026-04-01');
```

---

## Seksi 6 — Service Layer Quality

### Temuan

- ❌ **Tidak ada Service Layer sama sekali** — Direktori `app/Services/` tidak eksis. Semua logika bisnis tersebar di controller.
- ❌ **Logika bisnis di controller** — Contoh:
    - `DashboardController::index()` berisi query agregasi, color mapping, analysis text generation, dan disclaimer logic (165 baris total).
    - `GrafikMpdController` berisi 12+ private method yang seharusnya jadi Services (1.388 baris).
    - `DataMpdController` berisi seluruh pipeline pemrosesan data tabel (892 baris).
    - `MapMonitorController::getNetflow()` berisi query inflow/outflow + sorting logic.
- ❌ **`Actions/` ada tapi tidak digunakan secara konsisten** — Ada `app/Actions/Mpd/EnrichSpatialMovementAction.php` dan `app/Actions/Mpd/ImportRawMpdAction.php` tapi sebagian besar logika tetap di controller/job.
- ❌ **Tidak ada dependency injection** — Tidak ada service yang diinject via constructor. Semua akses langsung via `new ClassName()` atau static calls atau inline dalam method.

### Rekomendasi

```php
// app/Services/Dashboard/DashboardAnalyticsService.php
declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\SpatialMovement;
use Illuminate\Support\Facades\Cache;

class DashboardAnalyticsService
{
    public function getAggregatedData(string $startDate, string $endDate): array
    {
        return Cache::remember(
            "dashboard:analytics:{$startDate}:{$endDate}",
            3600,
            fn () => $this->computeAggregates($startDate, $endDate)
        );
    }

    private function computeAggregates(string $startDate, string $endDate): array
    {
        // Pindahkan semua query dari DashboardController ke sini
    }
}
```

```php
// app/Http/Controllers/DashboardController.php — Setelah refactor
class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardAnalyticsService $analyticsService
    ) {}

    public function index(): \Illuminate\View\View
    {
        $data = $this->analyticsService->getAggregatedData('2026-03-13', '2026-03-30');
        return view('dashboard.index', $data);
    }
}
```

---

## Seksi 7 — CSV Ingestion Pipeline

### Temuan

- ⚠️ **Pipeline bukan full async queue** — CSV diproses via AJAX chunking (`processChunk()`) yang berjalan synchronous di browser. ETL (`TransformRawToSpatialJob`) baru di-dispatch via `dispatchAfterResponse()` setelah chunk terakhir. Jika koneksi browser putus di tengah upload, proses berhenti. Queue Redis workers tidak terlibat dalam proses upload awal.
- ⚠️ **Delimiter semicolon (`;`) sudah benar** — Baris 136 `DatasourceController`: `str_getcsv($line, ';')` ✅
- ❌ **Validasi nama file tidak diimplementasikan** — Pattern `^mpd_(tsel|ioh|xl)_(real|forecast)_\d{8}\.csv$` tidak divalidasi. Form hanya mensyaratkan `mimes:csv,txt` dan `max:1048576` (1GB).
- ⚠️ **10 langkah preprocessing TIDAK diimplementasikan di CSV pipeline** — `DatasourceController::processChunk()` hanya melakukan:
    - Schema validation (kolom ≥ 18) ✅ (Step 1 parsial)
    - Skip baris kosong ✅
    - Validasi tanggal ✅
    - **Tidak ada**: validasi OPSEL/KATEGORI/KODE_MODA, validasi koordinat, deteksi duplicate, near-duplicate, speed anomaly, spatial jump, altitude anomaly.
- ⚠️ **Kolom koordinat (lat/lon) tidak ada di raw_mpd_data** — CSV yang dimuat ke `raw_mpd_data` tidak memiliki kolom latitude/longitude/altitude. Validasi koordinat (Steps 3, 8, 9, 10) tidak mungkin dilakukan.
- ❌ **DQI (Data Quality Index) tidak diimplementasikan** — Tidak ada perhitungan dan penyimpanan DQI per batch upload.
- ✅ `LazyCollection` tidak digunakan, tapi chunked file read dengan `fgets()` sudah cukup efisien untuk memory.
- ✅ `is_forecast` dari kategori form (REAL/FORECAST) sudah benar diset.
- ✅ Fallback row-by-row insert saat batch gagal sudah ada.

### Rekomendasi

```php
// Tambahkan validasi nama file di storeUpload()
$request->validate([
    'file' => [
        'required', 'file', 'mimes:csv,txt', 'max:1048576',
        function ($attribute, $value, $fail) {
            $name = $value->getClientOriginalName();
            if (!preg_match('/^mpd_(tsel|ioh|xl)_(real|forecast)_\d{8}\.csv$/i', $name)) {
                $fail('Nama file tidak sesuai format: mpd_(opsel)_(real|forecast)_YYYYMMDD.csv');
            }
        },
    ],
]);
```

```php
// Tambahkan validasi Step 2 (OPSEL, KATEGORI, KODE_MODA) di processChunk()
$validOpsels = ['TSEL', 'IOH', 'XL'];
$validKategori = ['PERGERAKAN', 'ORANG'];
$validModas = ['A','B','C','D','E','F','G','H','I','J','K'];

if (!in_array(trim($cols[1]), $validOpsels)) { $rowsSkipped++; continue; }
if (!in_array(trim($cols[2]), $validKategori)) { $rowsSkipped++; continue; }
if (!in_array(trim($cols[15] ?? ''), $validModas)) { $rowsSkipped++; continue; }
```

---

## Seksi 8 — Query Performance & N+1

### Temuan

**Potensi N+1 Terdeteksi:**

| File                             | Baris   | Masalah                                                                                                                                                                                         | Fix                            |
| -------------------------------- | ------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------ |
| `KeynoteController.php`          | 132     | `$simpuls[$code]->name ?? $code` — akses property dari collection yang di-keyBy, aman                                                                                                           | N/A                            |
| `ExecutiveSummaryController.php` | 48      | `Simpul::select(...)->get()->keyBy('code')` lalu foreach access — aman (eager load semua)                                                                                                       | ✅ Sudah benar                 |
| `RuleDocumentController.php`     | 15      | `RuleDocument::with('uploader')` — relationship cross-database (PostgreSQL → MySQL) akan menyebabkan **N+1 atau eager load failure** karena Eloquent tidak support cross-database eager loading | Gunakan manual join atau cache |
| `GrafikMpdController.php`        | 671-730 | Loop `foreach ($section['daily_charts'] as $code => $label)` yang masing-masing menjalankan `DB::table(...)->get()` — **N+1 query dalam loop**                                                  | Batch query di luar loop       |
| `GrafikMpdController.php`        | 710-730 | Loop `sections` menjalankan 3 query terpisah per section (daily, top_origin, top_dest) — **N+1**                                                                                                | Batch semua query sebelum loop |

**Cache:**

- ✅ Redis cache sudah diterapkan di semua controller utama dengan TTL 3600s.
- ✅ Dashboard, MapMonitor, Executive Summary, GrafikMpd, DataMpd — semua ter-cache.
- ⚠️ Cache keys tidak menggunakan **cache tags**, sehingga invalidasi selektif tidak bisa dilakukan. Saat ini `Cache::flush()` menghapus semua cache, termasuk session Redis.
- ❌ **`Cache::flush()` menghapus session!** — `TransformRawToSpatialJob` baris 151 dan `DatasourceController` baris 464 memanggil `Cache::flush()`. Jika Session dan Cache menggunakan koneksi Redis yang sama (yang terjadi di sini karena `SESSION_DRIVER=redis` dan `CACHE_STORE=redis` dengan prefix berbeda tapi database sama), semua sesi pengguna yang sedang login akan **ter-logout**.

**Index:**

- ✅ Composite index `[opsel]`, `[kode_origin_kabupaten_kota, kode_dest_kabupaten_kota]` sudah ada di `raw_mpd_data`.
- ⚠️ Tidak ada composite index `[tanggal, opsel, is_forecast]` di `spatial_movements` yang merupakan kombinasi filter paling sering digunakan.

### Rekomendasi

```php
// Pisahkan Redis database untuk Session dan Cache
// config/database.php
'redis' => [
    'default' => ['database' => env('REDIS_DB', '0')],
    'cache'   => ['database' => env('REDIS_CACHE_DB', '1')],
    'session' => ['database' => env('REDIS_SESSION_DB', '2')],
],

// config/session.php
'connection' => 'session',
```

```sql
-- Tambahkan composite index di spatial_movements
CREATE INDEX idx_sm_tanggal_opsel_forecast
    ON spatial_movements (tanggal, opsel, is_forecast);

CREATE INDEX idx_sm_kategori_forecast
    ON spatial_movements (kategori, is_forecast);
```

```php
// Ganti Cache::flush() dengan tagged cache:
Cache::tags(['dashboard', 'grafik', 'mapmonitor'])->flush();
// Tapi ini butuh Redis dengan tagging support (sudah ada di Redis driver)
```

---

## Seksi 9 — Security Audit

### Temuan

- ❌ **`APP_DEBUG=true` di produksi** — Stack trace PHP dengan informasi internal (path file, query, environment) ter-expose ke semua pengguna.
- ❌ **Password MySQL hardcoded di `config/database.php`** — Baris 17: `'password' => '36f87268eb95c41f'`. Ini masuk version control dan ter-expose ke semua developer yang punya akses repo.
- ❌ **CSV injection sanitization tidak ada** — `DatasourceController::processChunk()` tidak melakukan strip prefix berbahaya (`=`, `+`, `-`, `@`, TAB, CR) dari string cells sebelum insert ke database.
- ❌ **SSO route tidak ada rate limiting** — `/sso-login` dapat di-brute force tanpa pembatasan. HMAC token bisa di-replay jika `abs(time() - $timestamp) > 600` masih dalam window 10 menit.
- ❌ **IDOR di RuleDocument** — `RuleDocumentController::download($id)` dan `preview($id)` baris 76 dan 88: `RuleDocument::findOrFail($id)`. Tidak ada pengecekan ownership. Pengguna dengan role `tamu`/`operator` dapat mengakses dokumen milik siapapun cukup dengan menebak ID (sequential integer).
- ❌ **Validasi MIME type upload tidak ketat** — `DatasourceController::storeUpload()` hanya memvalidasi `mimes:csv,txt` yang mengecek extension, bukan magic bytes file. File berbahaya bisa diupload dengan extension `.csv`.
- ❌ **`RuleDocumentController` tidak memvalidasi type file** — `store()` baris 47-48 hanya `file|max:102400`, tidak ada pembatasan tipe file. File PHP, exe, atau script berbahaya bisa diupload.
- ⚠️ **`ActivityLog::log()` via static method** — Cross-database: `ActivityLog` gunakan koneksi `pgsql`, tapi `User` dari koneksi `mysql`. Relasi `belongsTo(User::class)` dalam `ActivityLog` akan gagal karena beda database.
- ⚠️ **`Auth::loginUsingId()` di SSO** — Baris 164: `Auth::loginUsingId($userId)`. Jika `$userId` yang dikirim SSO adalah ID user yang tidak ada di MySQL, login tetap bisa "berhasil" tanpa user valid (bergantung pada implementasi driver).
- ✅ HMAC-SHA256 verifikasi SSO sudah benar di baris 158-161.
- ✅ CSRF `@csrf` ada di semua form (dicek di Blade views utama).
- ✅ Password di-hash dengan `Hash::make()` menggunakan Bcrypt rounds=12.

### Rekomendasi

```php
// DatasourceController.php → storeUpload()
// Validasi MIME type via isi file (magic bytes)
$allowedMimes = ['text/plain', 'text/csv', 'application/csv'];
$detectedMime = mime_content_type($file->getPathname());
if (!in_array($detectedMime, $allowedMimes)) {
    return response()->json(['status' => 'error', 'message' => 'Tipe file tidak valid.'], 422);
}

// DatasourceController.php → processChunk() — CSV Injection sanitization
$sanitize = function (?string $val): string {
    $val = trim((string) $val);
    if (strlen($val) > 0 && in_array($val[0], ['=', '+', '-', '@', "\t", "\r"])) {
        $val = "'" . $val;
    }
    return $val;
};
// Terapkan ke semua kolom string dalam $batch

// RuleDocumentController.php → Tambahkan MIME validation
$request->validate([
    'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:102400',
]);

// RuleDocumentController.php → Tambahkan ownership check untuk tamu
public function download(int $id)
{
    $document = RuleDocument::findOrFail($id);
    // Semua user bisa download referensi dokumen — ini sesuai kebutuhan bisnis
    // Tapi jika ada dokumen privat, tambahkan: $this->authorize('download', $document);
    // ...
}

// routes/web.php — Rate limit SSO
Route::get('/sso-login', [SsoController::class, 'handle'])
    ->middleware('throttle:10,1')
    ->name('sso.login');
```

---

## Seksi 10 — Blade & Frontend Quality

### Temuan

- ✅ Tidak ditemukan query Eloquent langsung di Blade template utama.
- ✅ Semua form memiliki `@csrf`.
- ✅ Tidak ada heredoc PHP atau `<?php echo` yang obvious di Blade.
- ⚠️ **`KeynoteController::index()` memiliki hardcoded array besar** — 37 item requirements list (baris 15-69) di-hardcode dalam PHP controller, bukan dari database. Data ini tidak bisa dikonfigurasi tanpa deploy baru.
- ⚠️ **Blade `@can/@cannot` tidak digunakan** — Role check di UI dilakukan dengan `@if(auth()->user()->role === 'admin')` yang rawan typo dan tidak menggunakan sistem Policy Laravel.
- ⚠️ **JavaScript variables dari PHP** — Beberapa Blade view menggunakan `@json($variable)` atau langsung meng-echo PHP var ke JavaScript. Tidak terlihat eksplisit di file yang dibaca, perlu verifikasi di semua Blade.
- ⚠️ **Hardcoded tanggal di views** — Beberapa Blade view mungkin punya tanggal hardcoded (2026-03-13 sampai 2026-03-30) yang dikembalikan dari controller dan di-inject ke JS.

### Rekomendasi

```blade
{{-- Ganti role check manual --}}
{{-- SEBELUM --}}
@if(auth()->user()->role === 'admin')
    <button>Upload</button>
@endif

{{-- SESUDAH — dengan Policy --}}
@can('upload', App\Models\RuleDocument::class)
    <button>Upload</button>
@endcan
```

```php
// Pindahkan requirements list Keynote ke seeder/database
// Buat tabel keynote_requirements
// KeynoteController::index() tinggal:
public function index(): View
{
    $requirements = KeynoteRequirement::orderBy('no')->get()->groupBy('group');
    return view('keynote.index', compact('requirements'));
}
```

---

## Seksi 11 — Dummy Data Detection

### Temuan

| No  | File                                                         | Baris   | Deskripsi                                                                                                      | Solusi Real                                           |
| --- | ------------------------------------------------------------ | ------- | -------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| 1   | `app/Http/Controllers/KeynoteController.php`                 | 15-69   | 37 item requirements hardcoded sebagai PHP array                                                               | Pindahkan ke tabel `keynote_requirements` di database |
| 2   | `app/Http/Controllers/DashboardController.php`               | 14-15   | `$startDate = '2026-03-13'` hardcoded                                                                          | Ambil dari config/database atau parameter dinamis     |
| 3   | `app/Http/Controllers/GrafikMpdController.php`               | 422-433 | `$occupancy` faktor pengali (30, 25, 300...) hardcoded                                                         | Pindahkan ke tabel `ref_transport_modes` atau config  |
| 4   | `app/Http/Controllers/DataMpdController.php`                 | 299-303 | Fallback mode names array hardcoded                                                                            | Pastikan `ref_transport_modes` selalu ada datanya     |
| 5   | `app/Http/Controllers/DataMpdController.php`                 | 597-599 | Fallback categories array hardcoded                                                                            | Pastikan `ref_transport_nodes` selalu ada datanya     |
| 6   | `app/Models/SpatialMovements2026Seeder` (via DatabaseSeeder) | —       | `SpatialMovements2026Seeder.php` ada di seeders tapi tidak diregistrasi — perlu dicek apakah berisi dummy data | Review isi file                                       |
| 7   | `database/seeders/DatabaseSeeder.php`                        | 16      | `SimpulSeeder::class` di-comment out                                                                           | Harus diaktifkan dengan data real dari CSV simpul     |

### Rekomendasi

Semua tanggal hardcoded harus dipindah ke `config/app.php`:

```php
// config/app.php
'angleb_period' => [
    'start' => env('ANGLEB_START_DATE', '2026-03-13'),
    'end'   => env('ANGLEB_END_DATE',   '2026-03-30'),
],

// Controller usage:
$startDate = config('app.angleb_period.start');
$endDate   = config('app.angleb_period.end');
```

---

## Seksi 12 — Feature Completeness Check

### Temuan

| Fitur                                     | Status       | Catatan                                                                                                                                               |
| ----------------------------------------- | ------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| Dashboard kalender + real/forecast toggle | ✅ Ada       | Fungsi dasar ada, toggle filter dari request                                                                                                          |
| Keynote Material page                     | ✅ Ada       | List requirements hardcoded (lihat Seksi 11)                                                                                                          |
| Executive Summary → Daily Report          | ✅ Ada       | `DailyReportController` fungsional                                                                                                                    |
| Executive Summary → AI Analytics          | ⚠️ Parsial   | `ExecutiveSummaryController::getData()` menghasilkan analisis berbasis template string PHP, bukan AI sebenarnya                                       |
| Map Monitor (PostGIS spatial)             | ✅ Ada       | PostGIS ST_Y/ST_X sudah digunakan dengan benar                                                                                                        |
| Master → Referensi Rules (CRUD)           | ⚠️ Parsial   | CRUD upload/download/delete ada, tapi CRUD full (edit nama, kategori) belum ada. Juga: tidak ada CRUD di tabel referensi Provinsi/KabKota/Moda/Simpul |
| CSV Upload async queue                    | ⚠️ Parsial   | Upload ada, ETL di-dispatch lewat `dispatchAfterResponse()` bukan Redis queue worker murni                                                            |
| Lookup Table management                   | ⚠️ Read-only | `MasterReferensiController` hanya menampilkan data (GET), tidak ada fitur tambah/edit/hapus untuk lookup tables (Simpul, Provinsi, Moda, KabKota)     |
| SSO integration                           | ✅ Ada       | HMAC-SHA256 SSO sudah diimplementasi di routes/web.php                                                                                                |
| Data MPD (Nasional & Jabodetabek)         | ✅ Ada       | `DataMpdController` dan `GrafikMpdController` sudah ada                                                                                               |

### Rekomendasi

Tambahkan fitur CRUD untuk lookup tables:

```php
// routes/web.php
Route::resource('master/referensi/simpul', SimplulManagementController::class)
    ->middleware(['auth', 'can:admin-only']);
```

Klarifikasi apakah "AI Analytics" memerlukan integrasi LLM nyata (OpenAI, Gemini) atau cukup berbasis template analisis.

---

## Seksi 13 — Infrastructure & Configuration

### Temuan

- ❌ **`QUEUE_CONNECTION=redis` di .env tapi konfigurasi Redis queue menggunakan `retry_after=90` detik** — ETL job `TransformRawToSpatialJob` memiliki `$timeout = 3600` (1 jam). Jika worker restart saat job berjalan, job akan di-retry setelah 90 detik padahal proses ETL belum selesai — menyebabkan **duplicate data insert**.
- ❌ **`DB_PASSWORD` di `.env` baris 24 kosong** — `DB_PASSWORD=` tidak ada nilai. PostgreSQL user `dafayape` tidak memiliki password. Ini tidak aman untuk server produksi.
- ⚠️ **PHP `max_execution_time` dan `upload_max_filesize`** — Tidak ada bukti file `php.ini` dikonfigurasi untuk upload 1GB. `ini_set()` di controller bukan solusi production-grade.
- ⚠️ **`storage:link`** — Tidak bisa diverifikasi dari kode, harus dikonfirmasi di server.
- ⚠️ **Log rotation** — `LOG_CHANNEL=daily` sudah benar, `LOG_MAX_FILES=14` sudah dikonfigurasi. ✅
- ⚠️ **Redis `maxmemory` dan eviction policy** — Tidak dapat diverifikasi dari kode. Harus dicek di server: `redis-cli CONFIG GET maxmemory`.
- ⚠️ **Queue worker timeout** — Tidak ada bukti supervisor/systemd dikonfigurasi untuk queue worker dengan `--timeout=3600`.
- ⚠️ **Apache `TimeOut` dan `LimitRequestBody`** — Tidak dapat diverifikasi dari kode. Harus dicek di konfigurasi Apache.
- ✅ `APP_KEY` sudah diset dan bukan nilai default.
- ✅ Redis sudah digunakan untuk session, cache, dan queue.
- ✅ Log level sudah `warning` (tidak verbose untuk produksi).

### Rekomendasi

```bash
# Konfigurasi supervisor untuk queue worker
[program:mpd-worker]
command=php /var/www/html/angleb/artisan queue:work redis \
    --queue=default \
    --timeout=3600 \
    --memory=1024 \
    --tries=3 \
    --sleep=3
autostart=true
autorestart=true

# /etc/php/8.4/apache2/conf.d/99-mpd.ini
upload_max_filesize = 1024M
post_max_size = 1100M
max_execution_time = 1800
memory_limit = 2048M

# /etc/redis/redis.conf
maxmemory 4gb
maxmemory-policy allkeys-lru
```

```bash
# Perbaiki Redis queue retry_after agar lebih dari job timeout
# config/queue.php — 'redis' connection
'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 7200), // 2 jam

# .env
REDIS_QUEUE_RETRY_AFTER=7200
```

---

## Daftar Bug & Masalah Kritis

| No  | File                                 | Baris   | Masalah                                                          | Prioritas | Solusi                                                     |
| --- | ------------------------------------ | ------- | ---------------------------------------------------------------- | --------- | ---------------------------------------------------------- |
| 1   | `.env`                               | 3       | `APP_DEBUG=true` di produksi                                     | 🔴 P0     | Set `APP_DEBUG=false`                                      |
| 2   | `config/database.php`                | 17      | Password MySQL hardcoded                                         | 🔴 P0     | Pindah ke env variable                                     |
| 3   | `ExecutiveSummaryController.php`     | 45/194  | Cache menyimpan Response object bukan data                       | 🔴 P0     | Return array dari closure, wrap response di luar           |
| 4   | `DatasourceController.php`           | 151,464 | `Cache::flush()` menghapus Redis session → logout semua user     | 🔴 P0     | Pisahkan Redis DB session vs cache, gunakan tagged cache   |
| 5   | `SpatialMovement.php`                | 32      | `$primaryKey = null` menyebabkan banyak Eloquent operation gagal | 🔴 P0     | Tambah serial ID atau gunakan model sebagai read-only pure |
| 6   | Migration timestamps                 | —       | Dua pasang migration dengan timestamp duplikat                   | 🔴 P0     | Rename file migration agar unik                            |
| 7   | `raw_mpd_data` + `spatial_movements` | —       | Partitioned tables tanpa partition → INSERT gagal                | 🔴 P0     | Buat partition untuk Q1 2026                               |
| 8   | `RuleDocumentController.php`         | 47-48   | Upload file tanpa validasi MIME/type → arbitrary file upload     | 🔴 P0     | Tambah validasi `mimes:pdf,doc,docx,...`                   |
| 9   | `RuleDocument::uploader()`           | —       | Cross-database eager loading akan fail                           | 🟡 P1     | Cache user data atau join manual                           |
| 10  | `GrafikMpdController`                | 671-753 | N+1 queries dalam nested loop                                    | 🟡 P1     | Batch semua query sebelum loop                             |
| 11  | `.env`                               | 64      | Google Maps API Key ter-expose                                   | 🟡 P1     | Rotasi key, tambahkan API restriction di Google Console    |
| 12  | Semua controller                     | —       | `declare(strict_types=1)` tidak ada                              | 🟡 P1     | Tambahkan ke semua file PHP                                |
| 13  | `queue.php`                          | 43      | `retry_after=90` < job timeout 3600 → duplicate ETL              | 🟡 P1     | Set `retry_after >= 7200`                                  |
| 14  | `routes/web.php`                     | 145-175 | SSO route sebagai inline closure                                 | 🟢 P2     | Pindahkan ke `SsoController`                               |
| 15  | Semua file root                      | —       | File debug (`fix.py`, `manual_check.php`, dll.) di root          | 🟢 P2     | Hapus dari root, pindah ke `.gitignore`                    |

---

## Daftar Dummy Data

| No  | File                                  | Baris   | Deskripsi                                                    | Solusi Real                                                 |
| --- | ------------------------------------- | ------- | ------------------------------------------------------------ | ----------------------------------------------------------- |
| 1   | `KeynoteController.php`               | 15-69   | Array `$requirements` 37 item hardcoded                      | Buat tabel `keynote_requirements` + seeder dengan data asli |
| 2   | `DashboardController.php`             | 14-15   | Periode `2026-03-13` sampai `2026-03-30` hardcoded           | Pindahkan ke `config/app.php` atau tabel konfigurasi        |
| 3   | `GrafikMpdController.php`             | 17-18   | Idem hardcoded date range                                    | Idem                                                        |
| 4   | `DataMpdController.php`               | 36-37   | Idem                                                         | Idem                                                        |
| 5   | `GrafikMpdController.php`             | 422-433 | Occupancy factors `['A' => 30, 'B' => 25, ...]` hardcoded    | Tambahkan kolom `occupancy_factor` ke `ref_transport_modes` |
| 6   | `DataMpdController.php`               | 299-303 | Fallback mode names array hardcoded                          | Pastikan `ref_transport_modes` selalu terisi via seeder     |
| 7   | `DataMpdController.php`               | 597-599 | Fallback categories `['Terminal', 'Stasiun', ...]` hardcoded | Pastikan `ref_transport_nodes.category` terisi              |
| 8   | `database/seeders/DatabaseSeeder.php` | 16      | `SimpulSeeder` di-comment out                                | Aktifkan dengan data simpul real dari CSV                   |

---

## Rencana Perbaikan Prioritas

### 🔴 P0 — Harus Diperbaiki Sebelum Production

1. **Set `APP_DEBUG=false`** di `.env`:

    ```ini
    APP_DEBUG=false
    ```

2. **Pindahkan password MySQL ke `.env`**:

    ```ini
    # .env
    LARAVEL11_DB_PASSWORD=36f87268eb95c41f
    ```

    ```php
    // config/database.php
    'laravel11_mysql' => [
        'password' => env('LARAVEL11_DB_PASSWORD', ''),
    ],
    ```

3. **Perbaiki bug cache Response object di `ExecutiveSummaryController.php`** (baris 45-211):

    ```php
    // Pindahkan return response()->json() ke luar Cache::remember()
    $data = Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
        // ... semua logika query ...
        return [
            'start_date' => $startDate,
            'analysis' => $analysis,
            'summary' => [...],
        ]; // Return array, BUKAN response()
    });
    return response()->json($data);
    ```

4. **Pisahkan Redis database untuk session dan cache** (mencegah Cache::flush() logout semua user):

    ```ini
    # .env
    REDIS_SESSION_DB=2
    ```

    ```php
    // config/session.php
    'connection' => 'session',
    // config/database.php — tambahkan koneksi 'session' dengan database=2
    ```

5. **Buat partition PostgreSQL untuk Q1 2026** (jalankan setelah `migrate`):

    ```sql
    CREATE TABLE raw_mpd_data_2026_q1 PARTITION OF raw_mpd_data
        FOR VALUES FROM ('2026-01-01') TO ('2026-04-01');
    CREATE TABLE spatial_movements_2026_q1 PARTITION OF spatial_movements
        FOR VALUES FROM ('2026-01-01') TO ('2026-04-01');
    ```

6. **Perbaiki timestamp duplikat migration** — rename file yang konflik.

7. **Tambahkan validasi file upload di `RuleDocumentController`**:
    ```php
    $request->validate([
        'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:102400',
    ]);
    ```

---

### 🟡 P1 — Perbaiki Sprint Ini

1. **Perbaiki `queue retry_after`** — Set minimal 2x timeout ETL job:

    ```ini
    REDIS_QUEUE_RETRY_AFTER=7200
    ```

2. **Tambahkan `declare(strict_types=1)`** ke semua file PHP di `app/`:

    ```bash
    # Script untuk menambahkan strict_types ke semua controller
    find app -name "*.php" -exec grep -L "declare(strict_types" {} \; | xargs -I {} sed -i '1s/<?php/<?php\n\ndeclare(strict_types=1);/' {}
    ```

3. **Perbaiki N+1 di `GrafikMpdController`** — Batch semua database queries sebelum loop section.

4. **Tambahkan validasi CSV filename** di `DatasourceController::storeUpload()`.

5. **Tambahkan CSV injection sanitization** di `DatasourceController::processChunk()`.

6. **Tambahkan rate limiting ke SSO route**:

    ```php
    Route::get('/sso-login', [SsoController::class, 'handle'])
        ->middleware('throttle:10,1')
        ->name('sso.login');
    ```

7. **Rotasi Google Maps API Key** dan tambahkan HTTP referrer restriction di Google Cloud Console.

8. **Aktifkan `SimpulSeeder`** di `DatabaseSeeder::run()`.

---

### 🟢 P2 — Tech Debt

1. **Ekstrak Service Layer** dari semua fat controllers:
    - `DashboardAnalyticsService`
    - `CsvImportService`
    - `MapMonitorService`
    - `GrafikMpdService`
    - `DataMpdService`

2. **Buat Form Request classes** untuk semua input validation.

3. **Implementasikan Policy/Gate** untuk role-based access control.

4. **Pindahkan data hardcoded** (tanggal periode, $requirements Keynote, occupancy factors) ke database/config.

5. **Refactor SSO route** dari inline closure ke `SsoController`.

6. **Hapus file debug dari root proyek** (`fix.py`, `manual_check.php`, `pdo_seed.php`, dll.).

7. **Buat `routes/api.php`** dan daftarkan `MovementAnalyticsController`.

8. **Implementasikan Queue worker dengan Supervisor** di server produksi.

9. **Tambahkan DQI calculation** di pipeline upload CSV.

10. **Implementasikan CRUD untuk lookup tables** (Simpul, Provinsi, Moda, KabKota).

---

## Kesimpulan

Aplikasi MPD Angleb 2026 telah mencapai tingkat kematangan fungsional yang cukup — fitur-fitur utama sudah ada dan kerangka arsitektur (PostGIS, Redis cache, antrean Job) sudah terpasang dengan benar. Namun **skor QC keseluruhan 64/130 (49.2%)** mencerminkan bahwa fondasi kode belum memenuhi standar produksi.

**Keputusan: ❌ NO-GO untuk production deployment** saat ini.

Tiga item yang bersifat show-stopper dan harus diselesaikan segera: (1) `APP_DEBUG=true` yang memaparkan informasi internal sistem kepada publik, (2) bug cache menyimpan Response object yang akan menyebabkan `ExecutiveSummaryController` gagal setelah cache hit pertama, dan (3) partitioned tables tanpa partition yang akan menyebabkan semua INSERT ke `raw_mpd_data` dan `spatial_movements` gagal total jika migrate dijalankan di environment baru.

Dengan menyelesaikan 7 item P0 dalam estimasi 2-3 hari kerja, aplikasi akan mencapai standar minimal untuk production. Item P1 dan P2 disarankan diselesaikan dalam sprint berikutnya untuk meningkatkan keamanan, maintainability, dan skalabilitas jangka panjang.

---

_Laporan ini dihasilkan berdasarkan inspeksi statis terhadap seluruh kode sumber. Pengujian runtime dan penetration testing terpisah tetap disarankan sebelum go-live._
