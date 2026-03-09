# Laporan Quality Control (QC) - Aplikasi MPD Angleb 2026

**Tanggal:** 9 Maret 2026
**Target Evaluasi:** Laravel 12, PHP 8.4, PostgreSQL 18 (PostGIS), Redis
**Metrik:** Zero Tolerance Policy
**Auditor:** OpenClaw QC Engineer

Berikut adalah temuan hasil audit kode sumber berdasarkan standar _enterprise-grade_ untuk proyek MPD Angleb 2026.

---

## 🛑 Temuan Kritis (Critical)

### 1. Model Mass-Assignment Vulnerability (Unscoped `$fillable`)

- **Lokasi:** `app/Models/User.php` (Baris 13-19)
- **Pelanggaran:** Array `$fillable` mengekspos kolom `role`. Hal ini memungkinkan serangan ekskalasi hak akses (Privilege Escalation) melalui _mass-assignment_.
- **Perbaikan Kode:**

```php
    // app/Models/User.php
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
    ];
    // Hapus 'role' dari fillable.
    // Penetapan role harus dilakukan secara eksplisit: $user->role = 'admin'; $user->save();
```

### 2. PostGIS Indexing Strategy Inefficient & Syntax Bypass

- **Lokasi:** `database/migrations/*_create_spatial_movements_table.php` (Baris 28-29)
- **Pelanggaran:** Menggunakan satu GiST index untuk dua kolom `(origin_location, dest_location)`. GiST tidak dapat mengindeks dua kolom geospasial secara bersamaan dengan efisien untuk operasi _bounding box_. Penggunaan `DB::statement` untuk tipe geografi melompati arsitektur Blueprint bawaan Laravel.
- **Perbaikan Kode:**

```php
    // Buat index secara terpisah
    DB::statement("CREATE INDEX idx_spat_mov_origin_gist ON spatial_movements USING GIST (origin_location);");
    DB::statement("CREATE INDEX idx_spat_mov_dest_gist ON spatial_movements USING GIST (dest_location);");
```

### 3. Endpoint Publik Tanpa Proteksi & Logika Fat Route (Broken Binding)

- **Lokasi:** `routes/web.php` (Baris 50-80)
- **Pelanggaran:** Endpoint `/sso-login` diimplementasikan langsung sebagai _closure_ (Fat Route) dengan validasi _inline_, serta tidak ada _rate limiting_ (`throttle`). Ini rentan terhadap _brute-force_ pada validasi SSO.
- **Perbaikan Kode:**

```php
    // routes/web.php
    Route::get('/sso-login', [\App\Http\Controllers\AuthController::class, 'ssoLogin'])
        ->middleware('throttle:10,1')
        ->name('sso.login');
```

---

## ⚠️ Peringatan (Warning)

### 4. Pelanggaran Single Responsibility Principle (SRP) & Optimasi Redis

- **Lokasi:** `app/Services/ExecutiveSummary/ExecutiveSummaryService.php` (Baris 12-50)
- **Pelanggaran:** Kelas God-Object. Menghitung `NasionalMetrics`, `PeakDay`, `OpselContribution` dalam satu servis besar. Menggunakan fasad `Cache::remember` standar tanpa deklarasi `store('redis')` atau _tags_, yang membuat _cache invalidation_ menjadi sulit saat volume data besar.
- **Perbaikan Kode:**

```php
    // app/Services/ExecutiveSummary/ExecutiveSummaryService.php
    public function getFullSummary(?string $opsel, string $dataType = 'real'): array
    {
        $dateKey = $this->getStartDate().'_'.$this->getEndDate();
        $key = "executive_summary:getFullSummary:{$dataType}:{$opsel}:all_v5:{$dateKey}";

        return Cache::store('redis')
            ->tags(['executive_summary', "opsel:{$opsel}"])
            ->remember($key, $this->cacheTtl(), fn () => $this->buildFullSummary($opsel, $dataType));
    }
    // Pisahkan logika ke Service yang lebih spesifik seperti `OpselContributionService`.
```

### 5. Fat Controller & Missing Authorization

- **Lokasi:** `app/Http/Controllers/DataMpdController.php` (Baris 16-36)
- **Pelanggaran:** Method `jabodetabekOdSimpul` melakukan manipulasi _Date Range_ `Carbon` secara langsung di _controller_ dan memanggil repositori/cache. Selain itu, tidak terdapat validasi otorisasi (`$this->authorize()` atau Gate).
- **Perbaikan Kode:**

```php
    // app/Http/Controllers/DataMpdController.php
    public function jabodetabekOdSimpul(Request $request, JabodetabekOdService $service)
    {
        // Missing Authorization
        Gate::authorize('view', Simpul::class);

        // Pindahkan komputasi Date ke Service
        $data = $service->getOdSimpulMatrix($request->all());

        return view('data-mpd.jabodetabek.od-simpul', $data);
    }
```

---

## 🟡 Minor (Minor)

### 6. Blade Authorization Directive (`@can`) Absen

- **Lokasi:** `resources/views/users/create.blade.php` (Baris 19-35)
- **Pelanggaran:** Tombol aksi dan formulir penambahan _user_ terekspos langsung tanpa pengecekan peran (role) di tingkat antarmuka. Meskipun backend memvalidasi (jika ada), UI tetap menampilkan opsi terlarang untuk pengguna non-Admin.
- **Perbaikan Kode:**

```blade
    {{-- Bungkus form atau tombol dengan direktif @can --}}
    @can('create', App\Models\User::class)
        <div class="card">
            <!-- Isi Form -->
        </div>
    @endcan
```

### 7. Missing Type Hints pada Model

- **Lokasi:** `app/Models/SpatialMovement.php`
- **Pelanggaran:** Relasi dan tipe balik (Return Type) untuk relasi ke tabel turunan (misal: node/simpul) tidak menggunakan _strict typing_ yang disyaratkan PHP 8.4 dan standar Laravel 12.

---

## 🎯 Kesimpulan & Verdict

Secara arsitektural, aplikasi ini sudah memiliki pemisahan komponen yang cukup baik dan penggunaan basis data spasial yang tepat. Namun, **kegagalan keamanan pada implementasi Model (`$fillable`), inefisiensi arsitektur indeks spasial, dan struktur "fat route" pada otentikasi SSO menciptakan risiko keamanan dan performa yang fatal.**

### Verdict Akhir: 🚨 NO-GO (Dilarang Deploy ke Production)

**Syarat Go-Live:** Selesaikan seluruh temuan berstatus **Kritis** dan **Peringatan** sebelum rilis _Production_. Lakukan _refactoring_ rute SSO segera.
