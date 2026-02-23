@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Dashboard Nasional</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li>
                        <li class="breadcrumb-item active">Nasional</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome & Profile -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary bg-soft shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="text-primary">
                                <h5 class="text-primary">Selamat datang kembali!</h5>
                                <p class="mb-1">{{ Auth::user()->name }}</p>
                                <p class="mb-0 text-muted small">{{ Auth::user()->email }} | {{ Auth::user()->role }}</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-end d-none d-md-block">
                            <img src="assets/images/profile-img.png" alt="" class="img-fluid"
                                style="max-height: 60px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Highlights Angleb 2026 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-info bg-soft border-0">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <h5 class="text-info fw-bold mb-2"><i class="bx bx-info-circle me-2"></i>Highlight Survei
                                Angkutan Lebaran 2026</h5>
                            <p class="mb-0 text-dark">
                                Dalam rangka persiapan dan pelaksanaan layanan Angkutan Lebaran (Angleb) tahun 2026,
                                Badan Kebijakan Transportasi melaksanakan survei online pada periode <strong>15 – 30 Januari
                                    2026</strong>
                                untuk mengidentifikasi preferensi dan mengukur persepsi masyarakat yang akan melakukan
                                perjalanan.
                                Survei tersebut bekerja sama dengan <strong>LAPI ITB, BPS, dan Kementerian Komdigi</strong>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latar Belakang & Metodologi -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0 text-primary">Latar Belakang Survei</h6>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted text-justify">
                        Periode Lebaran menjadi salah satu momen dengan tingkat mobilitas tinggi di Indonesia,
                        biasanya terjadi lonjakan signifikan pergerakan antarkota maupun lokal perkotaan.
                        Lebaran tahun 2026 diperkirakan jatuh pada tanggal <strong>21 dan 22 Maret 2026</strong>
                        sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal <strong>19 Maret 2026</strong>.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0 text-primary">Metodologi Survei</h6>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted text-justify">
                        Survei prakiraan pergerakan masyarakat pada periode Lebaran 2026 ini dilaksanakan di <strong>38
                            Provinsi</strong>
                        di Indonesia dengan total <strong>55.585 responden</strong>. Penentuan jumlah sampel menggunakan
                        rumus
                        <em>Isaac & Michael</em> dengan <em>margin of error</em> sebesar <strong>0,44%</strong>.
                        Pengumpulan data dilakukan melalui survei online menggunakan platform e-survei.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Faktor & Historical Chart -->
    <div class="row g-4 mb-4">
        <!-- Faktor-Faktor -->
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0 text-primary">Faktor-Faktor Yang Mempengaruhi</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-start border-4 border-primary">
                                <h6 class="fw-bold text-dark">Faktor Sosial & Budaya</h6>
                                <p class="small text-muted mb-0">Tradisi tahunan Masyarakat Indonesia untuk mudik dan
                                    silaturahmi pada masa libur lebaran.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-start border-4 border-success">
                                <h6 class="fw-bold text-dark">Faktor Ekonomi</h6>
                                <p class="small text-muted mb-0">Adanya tunjangan hari raya (THR) atau bonus karyawan yang
                                    mendorong konsumsi dan mobilitas masyarakat.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-start border-4 border-info">
                                <h6 class="fw-bold text-dark">Faktor Pariwisata</h6>
                                <p class="small text-muted mb-0">Destinasi wisata domestik unggulan seperti Bali,
                                    Yogyakarta, Bandung, Semarang, Labuan Bajo, dll.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded h-100 border-start border-4 border-warning">
                                <h6 class="fw-bold text-dark">Faktor Libur Panjang</h6>
                                <p class="small text-muted mb-0">Adanya libur sekolah, libur hari raya Nyepi, dan Hari Raya
                                    Idul Fitri, serta kebijakan cuti bersama.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Tren Historis -->
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0 text-primary">Tren Historis Pergerakan (2024/2025)</h6>
                </div>
                <div class="card-body">
                    <div id="chartTrenHistoris" style="width:100%; height:300px;"></div>
                    <div class="alert alert-light mt-2 mb-0 p-2">
                        <p class="small mb-0 text-muted">
                            Prakiraan 2026 (<strong>143.9 Jt</strong>) turun sekitar <strong>1,75%</strong> dari Prakiraan
                            2025 (<strong>146.4 Jt</strong>). Realisasi MPD 2025 sebesar <strong>154.6 Jt</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay Kalender -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0 text-primary">Penarikan Mobile Positioning Data Angkutan Lebaran 2026</h6>
                </div>
                <div class="card-body overflow-auto">
                    {{-- Simple Custom Calendar Visualization --}}
                    <div style="min-width: 800px;">
                        <h6 class="text-center fw-bold mb-3">MARET 2026</h6>
                        <div class="d-flex border text-center font-size-12">
                            <!-- Header Days -->
                            @php
                                $days = [
                                    ['tgl' => 13, 'hari' => 'Jumat', 'h' => 'H-8', 'bg' => ''],
                                    ['tgl' => 14, 'hari' => 'Sabtu', 'h' => 'H-7', 'bg' => 'bg-danger text-white'],
                                    ['tgl' => 15, 'hari' => 'Minggu', 'h' => 'H-6', 'bg' => 'bg-danger text-white'],
                                    ['tgl' => 16, 'hari' => 'Senin', 'h' => 'H-5', 'bg' => 'bg-warning bg-soft'],
                                    ['tgl' => 17, 'hari' => 'Selasa', 'h' => 'H-4', 'bg' => 'bg-warning bg-soft'],
                                    ['tgl' => 18, 'hari' => 'Rabu', 'h' => 'H-3', 'bg' => 'bg-warning'],
                                    ['tgl' => 19, 'hari' => 'Kamis', 'h' => 'H-2', 'bg' => 'bg-success text-white'],
                                    ['tgl' => 20, 'hari' => 'Jumat', 'h' => 'H-1', 'bg' => 'bg-warning'],
                                    ['tgl' => 21, 'hari' => 'Sabtu', 'h' => 'H', 'bg' => 'bg-success text-white'],
                                    ['tgl' => 22, 'hari' => 'Minggu', 'h' => 'H+1', 'bg' => 'bg-success text-white'],
                                    ['tgl' => 23, 'hari' => 'Senin', 'h' => 'H+2', 'bg' => 'bg-warning'],
                                    ['tgl' => 24, 'hari' => 'Selasa', 'h' => 'H+3', 'bg' => 'bg-warning'],
                                    ['tgl' => 25, 'hari' => 'Rabu', 'h' => 'H+4', 'bg' => ''],
                                    ['tgl' => 26, 'hari' => 'Kamis', 'h' => 'H+5', 'bg' => ''],
                                    ['tgl' => 27, 'hari' => 'Jumat', 'h' => 'H+6', 'bg' => ''],
                                    ['tgl' => 28, 'hari' => 'Sabtu', 'h' => 'H+7', 'bg' => 'bg-danger text-white'],
                                    ['tgl' => 29, 'hari' => 'Minggu', 'h' => 'H+8', 'bg' => 'bg-danger text-white'],
                                    ['tgl' => 30, 'hari' => 'Senin', 'h' => 'H+9', 'bg' => ''],
                                ];
                            @endphp

                            @foreach ($days as $d)
                                <div class="flex-fill border-end" style="width: {{ 100 / 18 }}%;">
                                    <div class="p-1 bg-light fw-bold text-uppercase" style="font-size: 10px;">
                                        {{ $d['hari'] }}</div>
                                    <div class="p-2 fw-bold fs-5 {{ $d['bg'] }} position-relative">
                                        {{ $d['tgl'] }}
                                    </div>
                                    <div class="p-1 small border-top bg-light" style="font-size: 10px;">{{ $d['h'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Annotations --}}
                        <div class="position-relative mt-2" style="height: 125px;">
                            <!-- Row 1: Top Annotations -->
                            <div class="position-relative w-100" style="height: 35px; margin-top: 10px;">
                                <!-- Potensi WFA 1 -->
                                <div class="position-absolute d-flex flex-column align-items-center"
                                    style="left: {{ (3 / 18) * 100 }}%; width: {{ (2 / 18) * 100 }}%; top: 0;">
                                    <div class="w-100"
                                        style="border-top: 1.5px solid #f1b44c; border-left: 1.5px solid #f1b44c; border-right: 1.5px solid #f1b44c; height: 6px;">
                                    </div>
                                    <div class="text-center rounded mt-1 shadow-sm text-dark px-2 text-nowrap"
                                        style="background-color: #fef4e4; padding: 3px 0; font-size: 8px; font-weight: bold; border: 1px solid #f1b44c;">
                                        POTENSI PENERAPAN WFA</div>
                                </div>

                                <!-- Nyepi -->
                                <div class="position-absolute d-flex flex-column align-items-center"
                                    style="left: {{ (6 / 18) * 100 }}%; width: {{ (1 / 18) * 100 }}%; top: 0;">
                                    <div class="text-center bg-success text-white rounded shadow-sm px-2 text-nowrap"
                                        style="padding: 3px 0; font-size: 8px; font-weight: bold; margin-top: 5px;">HARI
                                        RAYA NYEPI</div>
                                </div>

                                <!-- Hari Raya Lebaran -->
                                <div class="position-absolute d-flex flex-column align-items-center"
                                    style="left: {{ (8 / 18) * 100 }}%; width: {{ (2 / 18) * 100 }}%; top: 0;">
                                    <div class="w-100"
                                        style="border-top: 1.5px solid #34c38f; border-left: 1.5px solid #34c38f; border-right: 1.5px solid #34c38f; height: 6px;">
                                    </div>
                                    <div class="text-center bg-success text-white rounded mt-1 shadow-sm px-2 text-nowrap"
                                        style="padding: 3px 0; font-size: 8px; font-weight: bold;">HARI RAYA LEBARAN</div>
                                </div>

                                <!-- Potensi WFA 2 -->
                                <div class="position-absolute d-flex flex-column align-items-center"
                                    style="left: {{ (12 / 18) * 100 }}%; width: {{ (3 / 18) * 100 }}%; top: 0;">
                                    <div class="w-100"
                                        style="border-top: 1.5px solid #f1b44c; border-left: 1.5px solid #f1b44c; border-right: 1.5px solid #f1b44c; height: 6px;">
                                    </div>
                                    <div class="text-center rounded mt-1 shadow-sm text-dark px-2 text-nowrap"
                                        style="background-color: #fef4e4; padding: 3px 0; font-size: 8px; font-weight: bold; border: 1px solid #f1b44c;">
                                        POTENSI PENERAPAN WFA</div>
                                </div>
                            </div>

                            <!-- Row 2: Middle Annotations -->
                            <div class="position-relative w-100" style="height: 35px;">
                                <!-- Cuti Nyepi -->
                                <div class="position-absolute d-flex flex-column align-items-center"
                                    style="left: {{ (5 / 18) * 100 }}%; width: {{ (1 / 18) * 100 }}%; top: 0;">
                                    <div class="w-100"
                                        style="border-top: 1.5px solid #f1b44c; border-left: 1.5px solid #f1b44c; border-right: 1.5px solid #f1b44c; height: 6px;">
                                    </div>
                                    <div class="text-center bg-warning text-dark rounded shadow-sm mt-1 px-3 text-nowrap"
                                        style="padding: 3px 0; font-size: 8px; font-weight: bold;">CUTI BERSAMA NYEPI</div>
                                </div>

                                <!-- Cuti Lebaran -->
                                <div class="position-absolute d-flex flex-column align-items-center"
                                    style="left: {{ (7 / 18) * 100 }}%; width: {{ (5 / 18) * 100 }}%; top: 0;">
                                    <div class="w-100"
                                        style="border-top: 1.5px solid #f1b44c; border-left: 1.5px solid #f1b44c; border-right: 1.5px solid #f1b44c; height: 6px;">
                                    </div>
                                    <div class="text-center bg-warning text-dark rounded shadow-sm mt-1 px-4 text-nowrap"
                                        style="padding: 3px 0; font-size: 8px; font-weight: bold;">CUTI BERSAMA LEBARAN
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Libur Sekolah & Posko -->
                            <div class="position-relative w-100" style="height: 45px; margin-top: 5px;">
                                <!-- Libur Sekolah JABODETABEK -->
                                <div class="position-absolute d-flex flex-column"
                                    style="left: {{ (3 / 18) * 100 }}%; width: {{ (15 / 18) * 100 }}%; top: 0;">
                                    <div class="w-100"
                                        style="border-top: 1.5px solid #8e959c; border-left: 1.5px solid #8e959c; border-right: 1.5px solid #8e959c; height: 6px;">
                                    </div>
                                    <div class="text-center w-100 fw-bold border"
                                        style="background-color: #e2e3e5; color: #495057; font-size: 9px; padding: 3px 0; margin-top: -1px; border-color: #adb5bd !important;">
                                        LIBUR SEKOLAH JABODETABEK
                                    </div>
                                </div>

                                <!-- Posko Bar -->
                                <div class="position-absolute d-flex flex-column"
                                    style="left: 0; width: 100%; top: 25px;">
                                    <div class="w-100"
                                        style="border-left: 1.5px solid #0d6efd; border-right: 1.5px solid #0d6efd; height: 6px;">
                                    </div>
                                    <div class="text-center text-white fw-bold shadow-sm"
                                        style="background: #2389f4; padding: 5px 0; font-size: 10px; width: 100%; border-radius: 4px; border: 1px solid #0d6efd; margin-top: -1px;">
                                        PELAKSANAAN POSKO ANGLEB 2026
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Row 1: Status Survey -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-primary bg-soft text-primary font-size-18"><i
                                    class="bx bx-copy-alt"></i></span>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-truncate font-size-12 mb-1 text-muted">TOTAL RESPONDEN</p>
                            <h5 class="mb-0">{{ number_format($responden ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-info bg-soft text-info font-size-18"><i
                                    class="bx bx-archive-in"></i></span>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-truncate font-size-12 mb-1 text-muted">SUBMITTED</p>
                            <h5 class="mb-0">{{ number_format($responden_submit ?? 0) }}</h5>
                            <small
                                class="text-muted">{{ $responden > 0 ? number_format(($responden_submit / $responden) * 100, 1) : 0 }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-warning bg-soft text-warning font-size-18"><i
                                    class="bx bx-purchase-tag-alt"></i></span>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-truncate font-size-12 mb-1 text-muted">DRAFT</p>
                            <h5 class="mb-0">{{ number_format($responden_draft ?? 0) }}</h5>
                            <small
                                class="text-muted">{{ $responden > 0 ? number_format(($responden_draft / $responden) * 100, 1) : 0 }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Validation Status -->
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-success bg-soft text-success font-size-18"><i
                                    class="bx bx-check-double"></i></span>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-truncate font-size-12 mb-1 text-muted">VALID SUBMIT</p>
                            <h5 class="mb-0">{{ number_format($valid_data ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-danger bg-soft text-danger font-size-18"><i
                                    class="bx bx-error"></i></span>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-truncate font-size-12 mb-1 text-muted">INVALID RESPONDEN</p>
                            <h5 class="mb-0">{{ number_format($invalid_data ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title rounded-circle bg-danger bg-soft text-danger font-size-18"><i
                                    class="bx bx-error"></i></span>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-truncate font-size-12 mb-1 text-muted">INVALID SUBMIT</p>
                            <h5 class="mb-0">{{ number_format($invalid_submit ?? 0) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-soft-secondary d-flex align-items-center" role="alert">
                <i class="mdi mdi-information-outline me-2 font-size-20"></i>
                <div>
                    Infografis berikut menampilkan data valid hasil survey.
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4 mb-4">
        <!-- Left Column: Demographics -->
        <div class="col-xl-4 col-md-12">
            <!-- Table Rekap -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="card-title mb-0">Rekapitulasi Kategori</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 font-size-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Kategori</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rekap_responden as $row)
                                    <tr>
                                        <td>{{ $row['kategori'] }}</td>
                                        <td class="text-center">{{ $row['unit'] }}</td>
                                        <td class="text-end">{{ $row['total'] }}</td>
                                        <td class="text-end">{{ $row['percent'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Gender Chart -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Rasio Jenis Kelamin</h6>
                </div>
                <div class="card-body">
                    <div id="chartGender" style="width:100%; height:200px;">
                        <div class="d-flex justify-content-center align-items-center h-100"><i
                                class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>
                    </div>
                    <div class="row text-center mt-3 g-0 border rounded overflow-hidden">
                        <div class="col-6 p-2 border-end bg-light">
                            <h5 class="mb-0 text-primary">
                                {{ round(($responden_laki_laki / ($responden_laki_laki + $responden_perempuan)) * 100, 1) }}%
                            </h5>
                            <small>Laki-laki</small>
                        </div>
                        <div class="col-6 p-2 bg-light">
                            <h5 class="mb-0 text-success">
                                {{ round(($responden_perempuan / ($responden_laki_laki + $responden_perempuan)) * 100, 1) }}%
                            </h5>
                            <small>Perempuan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Charts -->
        <div class="col-xl-8 col-md-12">
            <div class="row g-4">
                <!-- Age Distribution -->
                <div class="col-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h6 class="card-title mb-0">Karakteristik Usia & Gender</h6>
                        </div>
                        <div class="card-body">
                            <div id="rentangUsia" style="width:100%; height:350px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Education & Income -->
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h6 class="card-title mb-0">Jenjang Pendidikan</h6>
                        </div>
                        <div class="card-body">
                            <div id="chartJenjangPendidikan" style="width:100%; height:300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h6 class="card-title mb-0">Tingkat Penghasilan</h6>
                        </div>
                        <div class="card-body">
                            <div id="chartPenghasilan" style="width:100%; height:300px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profession & Travel Plans -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Profesi Responden</h6>
                </div>
                <div class="card-body">
                    <div id="profesiResponden" style="width:100%; height:400px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Alasan Bepergian</h6>
                </div>
                <div class="card-body">
                    <div id="chartAlasanBepergian" style="width:100%; height:400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Travel Intent & Analysis -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Intensi Perjalanan</h6>
                </div>
                <div class="card-body">
                    <?php
                    $total_all = $travel_plan_grouped->sum('total') ?? 1;
                    $persen_only = $travel_plan_grouped->map(function ($item) use ($total_all) {
                        return [
                            'plan_group' => $item->plan_group,
                            'persen' => round(($item->total / $total_all) * 100, 2),
                        ];
                    });
                    $total_all2 = $travel_plan_perjalanan->sum('total') ?? 1;
                    $persen_only2 = $travel_plan_perjalanan->map(function ($item) use ($total_all2) {
                        return [
                            'plan_group' => $item->plan_group,
                            'persen' => round(($item->total / $total_all2) * 100, 2),
                        ];
                    });
                    ?>
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <div id="intensiPerjalanan1" style="width:100%; height:300px;"></div>
                            <div class="row text-center mt-3">
                                <div class="col-6 border-end">
                                    <h4 class="text-primary mb-1">{{ $persen_only[0]['persen'] ?? 0 }}%</h4>
                                    <small>Berencana Perjalanan</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-danger mb-1">{{ $persen_only[1]['persen'] ?? 0 }}%</h4>
                                    <small>Tidak Berencana</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div id="intensiPerjalanan2" style="width:100%; height:300px;"></div>
                            <div class="alert alert-light mt-3">
                                <p class="mb-0 small">
                                    Dari <strong>{{ $persen_only[0]['persen'] ?? 0 }}%</strong> responden yang akan
                                    melakukan perjalanan:
                                    @foreach ($persen_only2 as $row)
                                        @if ($row['plan_group'] == 'd. Tidak akan bepergian ke luar kota')
                                            @continue
                                        @endif
                                        <br>• <strong>{{ $row['persen'] }}%</strong> {{ $row['plan_group'] }}
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sankey Chart & Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Peta Pergerakan Antar Provinsi (Sankey)</h6>
                    <!-- Filter placeholder functionality preserved but hidden/styled if needed -->
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3 bg-light p-2 rounded">
                        <!-- Filters preserved -->
                        <div class="col-md-2 d-none">
                            <select id="filterYear" class="form-select form-select-sm">
                                <option value="">Tahun</option>
                                @foreach ($years as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-none">
                            <select id="filterMonth" class="form-select form-select-sm">
                                <option value="">Bulan</option>
                                @foreach ($months as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <select id="filterFrom" class="form-select form-select-sm">
                                <option value="">Semua Provinsi Asal</option>
                                @foreach ($provinces as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <select id="filterTo" class="form-select form-select-sm">
                                <option value="">Semua Provinsi Tujuan</option>
                                @foreach ($provinces as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button id="btnFilter" class="btn btn-primary btn-sm w-100">Filter</button>
                        </div>
                    </div>
                    <div id="sankeyChart" style="width:100%; min-height:800px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Cities & Mode Share -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">10 Kota Tujuan Terfavorit</h6>
                </div>
                <div class="card-body">
                    <div id="provinsiTujuanFavorit10" style="width:100%; height:500px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Mode Share Keberangkatan</h6>
                </div>
                <div class="card-body">
                    <div id="modeShareChart" style="width:100%; height:500px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost & Timing -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Perkiraan Biaya vs Jumlah Orang</h6>
                </div>
                <div class="card-body">
                    <div id="biayaChart" style="width:100%; height:400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Hari & Jam Keberangkatan</h6>
                </div>
                <div class="card-body">
                    <div id="chartHariPergi" style="width:100%; height:400px; margin-bottom: 20px;"></div>
                    <div id="chartJamPergi" style="width:100%; height:400px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Hari & Jam Kepulangan</h6>
                </div>
                <div class="card-body">
                    <div id="chartHariBalik" style="width:100%; height:400px; margin-bottom: 20px;"></div>
                    <div id="chartJamBalik" style="width:100%; height:400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Preferensi Waktu Perjalanan (Heatmap/Detail)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div id="hariJamChart" style="width:100%; height:500px;"></div>
                        </div>
                        <div class="col-lg-6">
                            <div id="hariJamChart2" style="width:100%; height:500px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Province Travel Plans -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Rencana Liburan Per Provinsi</h6>
                </div>
                <div class="card-body">
                    <div id="stackedBarChartTravel" style="width:100%; min-height:1000px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Routes & Tickets -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Pilihan Rute (Mobil & Motor)</h6>
                </div>
                <div class="card-body">
                    <div id="chartRuteMobil" style="height: 350px; margin-bottom: 20px;"></div>
                    <div id="chartRuteMotor" style="height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Kepemilikan & Pembelian Tiket</h6>
                </div>
                <div class="card-body">
                    <div id="tiketGroupChart" style="height: 350px; width: 100%;"></div>
                    <div id="tiketWaktuPembelian" style="height: 350px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transport Nodes (Simpul) -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Simpul Transportasi Terpadat</h6>
                </div>
                <div class="card-body">
                    <!-- Bandara -->
                    <h6 class="text-muted border-bottom pb-2 mb-3">Bandara</h6>
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div id="simpulBandaraAsal" style="height: 400px;"></div>
                        </div>
                        <div class="col-lg-6">
                            <div id="simpulBandaraTujuan" style="height: 400px;"></div>
                        </div>
                    </div>

                    <!-- Pelabuhan -->
                    <h6 class="text-muted border-bottom pb-2 mb-3">Pelabuhan</h6>
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div id="simpulPelabuhanAsal" style="height: 400px;"></div>
                        </div>
                        <div class="col-lg-6">
                            <div id="simpulPelabuhanTujuan" style="height: 400px;"></div>
                        </div>
                    </div>

                    <!-- Stasiun -->
                    <h6 class="text-muted border-bottom pb-2 mb-3">Stasiun</h6>
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div id="simpulStasiunAsal" style="height: 400px;"></div>
                        </div>
                        <div class="col-lg-6">
                            <div id="simpulStasiunTujuan" style="height: 400px;"></div>
                        </div>
                    </div>

                    <!-- Tol -->
                    <h6 class="text-muted border-bottom pb-2 mb-3">Ruas Tol</h6>
                    <div class="row">
                        <div class="col-12">
                            <div id="simpulRuasTolAsal" style="height: 400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Link Source -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">Sumber Akses Link Survey</h6>
                </div>
                <div class="card-body">
                    <div id="sumberLinkChart" style="height: 400px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <br>
    <br>
@endsection

@push('js')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-sankey@0.12.3/dist/d3-sankey.min.js"></script>

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const laki = {{ $responden_laki_laki }};
            const perempuan = {{ $responden_perempuan }};
            const total = laki + perempuan;

            Highcharts.chart('chartGender', {
                chart: {
                    type: 'bar',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: ['Rasio']
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Jumlah Responden'
                    },
                    stackLabels: {
                        enabled: true,
                        formatter: function() {
                            return Highcharts.numberFormat(this.total, 0);
                        },
                        style: {
                            fontWeight: 'bold',
                            color: 'gray'
                        }
                    },
                    labels: {
                        formatter: function() {
                            return Highcharts.numberFormat(this.value, 0); // tanpa 'k'
                        }
                    }
                },
                legend: {
                    reversed: true,
                    itemStyle: {
                        fontSize: '12px',
                        fontWeight: 'normal'
                    }
                },
                plotOptions: {
                    series: {
                        stacking: 'normal',
                        dataLabels: {
                            enabled: true,
                            formatter: function() {
                                const percent = (this.y / total * 100).toFixed(1);
                                return `${Highcharts.numberFormat(this.y, 0)} (${percent}%)`;
                            },
                            style: {
                                fontSize: '11px',
                                textOutline: 'none'
                            }
                        }
                    }
                },
                exporting: {
                    enabled: true
                },
                credits: {
                    enabled: false
                },
                series: [{
                        name: 'Perempuan',
                        data: [perempuan],
                        color: '#5FD834'
                    },
                    {
                        name: 'Laki-laki',
                        data: [laki],
                        color: '#00A1FE'
                    }
                ]
            });
        });
    </script>
@endpush




@push('scripts')
    <script>
        Highcharts.chart('profesiResponden', {
            chart: {
                type: 'pie',
                backgroundColor: 'transparent'
            },
            title: {
                text: 'PROFESI<br>RESPONDEN',
                align: 'center',
                verticalAlign: 'middle',
                y: 10,
                style: {
                    color: '#999',
                    fontWeight: 'bold',
                    fontSize: '16px'
                }
            },
            tooltip: {
                pointFormat: '<b>{point.y} ({point.percentage:.2f}%)</b>',
                style: {
                    fontSize: '11px'
                }
            },
            plotOptions: {
                pie: {
                    innerSize: '70%',
                    borderWidth: 0,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        connectorColor: '#ccc',
                        connectorPadding: 2,
                        distance: 20,
                        useHTML: true,
                        formatter: function() {
                            const index = this.point.index;
                            const alphabet = String.fromCharCode(97 + index); // 97 = 'a'
                            // Tampilkan nama + jumlah + persen
                            return `<b>${this.point.name}</b><br>
                            ${this.point.y} (${Highcharts.numberFormat(this.point.percentage, 2)}%)`;
                        },
                        style: {
                            fontSize: '10px',
                            color: '#333',
                            textOutline: 'none'
                        }
                    }
                }
            },
            exporting: {
                enabled: true,
                filename: 'profesi_responden',
                buttons: {
                    contextButton: {
                        menuItems: [
                            'viewFullscreen', 'printChart', 'separator',
                            'downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG',
                            'separator', 'downloadCSV', 'downloadXLS'
                        ]
                    }
                },
                chartOptions: {
                    title: {
                        text: 'PROFESI RESPONDEN'
                    },
                    backgroundColor: 'transparent'
                } // '#ffffff' }
            },
            credits: {
                enabled: false
            },
            colors: [
                '#C4D8EA', '#285E90', '#A5A5A5', '#f1948a', '#48c9b0', '#f5b041', '#85929e', '#3ea99f',
                '#7fb3d5', '#c39bd3', '#b2babb', '#d4b26a', '#d7bde2', '#f0b27a', '#f4cc70', '#7cc576',
                '#a569bd',

                '#fad7a0', '#76d7c4'
            ],
            series: [{
                name: 'Jumlah',
                colorByPoint: true,
                data: @json($profile_responden['profesi'] ?? [])
            }]
        });
    </script>
@endpush

@push('scripts')


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Data dari Laravel
                const categories = @json($jenjang_pendidikan->pluck('name'));
                const data = @json($jenjang_pendidikan->pluck('y'));
                const colors = ['#00A1FE', '#5FD834', '#F7B500', '#FF6B6B', '#9B59B6']; // warna tiap bar
                const total = data.reduce((a, b) => a + b, 0);

                const seriesData = data.map((y, i) => ({
                    name: categories[i],
                    y: y,
                    persen: +(y / total * 100).toFixed(1),
                    color: colors[i % colors.length]
                }));

                Highcharts.chart('chartJenjangPendidikan', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: null,
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#333'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: 'Jenjang Pendidikan'
                        },
                        labels: {
                            rotation: 0,
                            style: {
                                fontSize: '12px',
                                color: '#555'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Responden'
                        },
                        labels: {
                            style: {
                                fontSize: '12px',
                                color: '#555'
                            }
                        }
                    },
                    tooltip: {
                        formatter: function() {
                            return `<b>${this.point.name}</b><br/>
                        Jumlah: <b>${this.point.y}</b><br/>
                        Persentase: <b>${this.point.persen}%</b>`;
                        }
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    return `${this.point.y} (${this.point.persen}%)`;
                                },
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            },
                            borderRadius: 3
                        }
                    },
                    series: [{
                        name: 'Jumlah Responden',
                        data: seriesData,
                        showInLegend: false
                    }],
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });

            });
        </script>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Data dari Laravel
                const categories = @json($penghasilan->pluck('name'));
                const data = @json($penghasilan->pluck('y'));
                const colors = ['#00A1FE', '#5FD834', '#F7B500', '#FF6B6B', '#9B59B6']; // warna tiap bar
                const total = data.reduce((a, b) => a + b, 0);

                const seriesData = data.map((y, i) => ({
                    name: categories[i],
                    y: y,
                    persen: +(y / total * 100).toFixed(1),
                    color: colors[i % colors.length]
                }));

                Highcharts.chart('chartPenghasilan', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: null,
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#333'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: 'Jenjang Pendidikan'
                        },
                        labels: {
                            rotation: 0,
                            style: {
                                fontSize: '12px',
                                color: '#555'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Responden'
                        },
                        labels: {
                            style: {
                                fontSize: '12px',
                                color: '#555'
                            }
                        }
                    },
                    tooltip: {
                        formatter: function() {
                            return `<b>${this.point.name}</b><br/>
                        Jumlah: <b>${this.point.y}</b><br/>
                        Persentase: <b>${this.point.persen}%</b>`;
                        }
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    return `${this.point.y} (${this.point.persen}%)`;
                                },
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            },
                            borderRadius: 3
                        }
                    },
                    series: [{
                        name: 'Jumlah Responden',
                        data: seriesData,
                        showInLegend: false
                    }],
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });

            });
        </script>
    @endpush


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Data dari Laravel
                const categories = @json($alasan_bepergian->pluck('name'));
                const data = @json($alasan_bepergian->pluck('y'));
                const colors = ['#00A1FE', '#5FD834', '#F7B500', '#FF6B6B', '#9B59B6']; // warna tiap bar
                const total = data.reduce((a, b) => a + b, 0);

                const seriesData = data.map((y, i) => ({
                    name: categories[i],
                    y: y,
                    persen: +(y / total * 100).toFixed(1),
                    color: colors[i % colors.length]
                }));

                Highcharts.chart('chartAlasanBepergian', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: null,
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#333'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: 'Jenis Alasan Bepergian'
                        },
                        labels: {
                            rotation: 0,
                            style: {
                                fontSize: '12px',
                                color: '#555'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Responden'
                        },
                        labels: {
                            style: {
                                fontSize: '12px',
                                color: '#555'
                            }
                        }
                    },
                    tooltip: {
                        formatter: function() {
                            return `<b>${this.point.name}</b><br/>
                        Jumlah: <b>${this.point.y}</b><br/>
                        Persentase: <b>${this.point.persen}%</b>`;
                        }
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    return `${this.point.y} (${this.point.persen}%)`;
                                },
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            },
                            borderRadius: 3
                        }
                    },
                    series: [{
                        name: 'Jumlah Responden',
                        data: seriesData,
                        showInLegend: false
                    }],
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });

            });
        </script>
    @endpush



    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Ambil data dari query Laravel
                const travelPlan = @json($travel_plan_grouped);

                // Siapkan kategori dan data series
                const categories = travelPlan.map(item => item.plan_group);
                const data = travelPlan.map(item => item.total);

                // Hitung total responden
                const total = data.reduce((a, b) => a + b, 0);

                // Buat series untuk Highcharts (angka + persen)
                const series = categories.map((cat, i) => ({
                    name: cat,
                    data: [{
                        y: data[i],
                        persen: +(data[i] / total * 100).toFixed(1)
                    }],
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return `${this.point.y} (${this.point.persen}%)`;
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold',
                            color: '#fff'
                        }
                    }
                }));

                Highcharts.chart('intensiPerjalanan1', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'Rencana Perjalanan ke Luar Kota',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#333'
                        }
                    },
                    xAxis: {
                        categories: ['Rasio'], // tetap ada label di bawah batang
                        title: {
                            text: null
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Responden'
                        }
                    },
                    legend: {
                        reversed: false,
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    },
                    tooltip: {
                        formatter: function() {
                            return `<b>${this.series.name}</b><br/>
                        Jumlah: <b>${this.point.y}</b><br/>
                        Persentase: <b>${this.point.persen}%</b>`;
                        }
                    },
                    plotOptions: {
                        series: {
                            stacking: 'percent', // angka asli, bukan persen
                            borderRadius: 3
                        }
                    },
                    colors: [
                        '#2E6CA4', '#BF0000'
                    ],
                    series: series,
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });

            });
        </script>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Ambil data dari query Laravel
                const travelPlan = @json($travel_plan_perjalanan);

                // Siapkan kategori dan data series
                const categories = travelPlan.map(item => item.plan_group);
                const data = travelPlan.map(item => item.total);

                // Hitung total responden
                const total = data.reduce((a, b) => a + b, 0);

                // Buat series untuk Highcharts (angka + persen)
                const series = categories.map((cat, i) => ({
                    name: cat,
                    data: [{
                        y: data[i],
                        persen: +(data[i] / total * 100).toFixed(1)
                    }],
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return `${this.point.y} (${this.point.persen}%)`;
                        },
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold',
                            color: '#fff'
                        }
                    }
                }));

                Highcharts.chart('intensiPerjalanan2', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'Rencana Perjalanan ke Luar Kota',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#333'
                        }
                    },
                    xAxis: {
                        categories: ['Rasio'], // tetap ada label di bawah batang
                        title: {
                            text: null
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Responden'
                        }
                    },
                    legend: {
                        reversed: false,
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    },
                    tooltip: {
                        formatter: function() {
                            return `<b>${this.series.name}</b><br/>
                        Jumlah: <b>${this.point.y}</b><br/>
                        Persentase: <b>${this.point.persen}%</b>`;
                        }
                    },
                    plotOptions: {
                        series: {
                            stacking: 'percent', // angka asli, bukan persen
                            borderRadius: 3
                        }
                    },
                    colors: [
                        '#BF0000', '#BFBFBF', '#708DA9', '#2E6CA4'
                    ],
                    series: series,
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });

            });
        </script>
    @endpush


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const provinsiData = @json($provinsi_tujuan_favorit_10);

                const categories = provinsiData.map(item => item.provinsi);
                const data = provinsiData.map(item => item.total);

                // 🔹 Hitung total keseluruhan untuk persen
                const total = data.reduce((sum, val) => sum + val, 0);

                // 🔹 Format data jadi objek dengan y dan persentase
                const formattedData = data.map(value => ({
                    y: value,
                    percentage: (value / total * 100)
                }));

                Highcharts.chart('provinsiTujuanFavorit10', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: null
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        },
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Responden'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    tooltip: {
                        useHTML: true,
                        formatter: function() {
                            return `
                    <b>${this.key}</b><br>
                    Jumlah: <b>${this.y}</b><br>
                    Persentase: <b>${Highcharts.numberFormat(this.point.percentage, 1)}%</b>
                `;
                        }
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    const y = this.y;
                                    const pct = Highcharts.numberFormat(this.point.percentage, 1);
                                    return `${y} (${pct}%)`;
                                },
                                style: {
                                    fontSize: '11px',
                                    fontWeight: 'bold',
                                    color: '#000'
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Jumlah Responden',
                        data: formattedData,
                        color: '#00A1FE'
                    }],
                    legend: {
                        enabled: false
                    },
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });

            });
        </script>
    @endpush


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categories = @json(array_column($travel_plan, 'travel_plan')); // ["Ya", "Tidak", "Mungkin"]
                const data = @json(array_column($travel_plan, 'jumlah')); // [40, 30, 20]
                const colors = ['#00A1FE', '#5FD834', '#F7B500']; // warna untuk setiap kategori
                const total = data.reduce((a, b) => a + b, 0);

                // Siapkan series stacked bar (angka asli)
                const series = categories.map((cat, i) => ({
                    name: cat,
                    color: colors[i % colors.length], // pakai warna manual
                    data: [{
                        y: data[i], // angka asli
                        persen: +(data[i] / total * 100).toFixed(1)
                    }],
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return `${this.point.y} (${this.point.persen}%)`; // angka + persen
                        },
                        style: {
                            fontSize: '11px',
                            fontWeight: 'bold',
                            color: '#fff'
                        }

                    }
                }));

                Highcharts.chart('stackedBarTravelPlan', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'Rencana Perjalanan ke Luar Kota',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#333'
                        }
                    },
                    xAxis: {
                        categories: ['Rasio'], // tetap ada label
                        title: {
                            text: 'Rencana Perjalanan'
                        },


                    },
                    yAxis: {
                        min: 0,
                        visible: true, // 🔹 sembunyikan Y-axis total min: 0, max: 100
                        title: {
                            text: 'Jumlah Responden'
                        }, // angka asli

                    },
                    legend: {
                        reversed: false,
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    },
                    tooltip: {
                        formatter: function() {
                            return `<b>${this.series.name}</b><br/>
                        Jumlah: <b>${this.point.y}</b><br/>
                        Persentase: <b>${this.point.persen}%</b>`;
                        }
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal', // angka asli, bukan persen
                            borderRadius: 3
                        }
                    },
                    series: series,
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });
            });
        </script>
    @endpush




    @push('scripts')
        <script>
            $(function() {
                loadChart(null); // Load awal

                $('#btnFilter').click(function() {
                    loadChart(1);
                });

                function getRandomSoftColor() {
                    const r = Math.floor(150 + Math.random() * 105);
                    const g = Math.floor(150 + Math.random() * 105);
                    const b = Math.floor(150 + Math.random() * 105);
                    return `rgb(${r},${g},${b})`;
                }

                function loadChart(st) {
                    $('#sankeyChart').html('<div class="text-muted text-center mt-5">⏳ Memuat data...</div>');

                    if (st == 1) {
                        $.get('{{ route('dashboard.flows.sankey.data') }}', {
                            year: $('#filterYear').val(),
                            month: $('#filterMonth').val(),
                            from: $('#filterFrom').val(),
                            to: $('#filterTo').val()
                        }, function(res) {

                            fetchChart(res);

                        });
                    } else {
                        const res = @json($sankey_chart ?? []);

                        fetchChart(res);

                    }
                }

                function fetchChart(res) {

                    $('#sankeyChart').html('');

                    if (!res.nodes.length) {
                        $('#sankeyChart').html('<div class="text-muted text-center mt-5">❌ Tidak ada data</div>');
                        return;
                    }

                    const labels = res.nodes.map(n => n.name);
                    const source = res.links.map(l => l.source);
                    const target = res.links.map(l => l.target);
                    const value = res.links.map(l => l.value);

                    const nodeColors = labels.map(() => getRandomSoftColor());
                    const linkColors = source.map(idx => nodeColors[idx].replace('rgb', 'rgba').replace(')', ',0.3)'));

                    // Klasifikasi node O / D / tengah
                    const leftNodes = [],
                        rightNodes = [],
                        middleNodes = [];
                    labels.forEach((label, idx) => {
                        if (label.startsWith('(O)')) leftNodes.push(idx);
                        else if (label.startsWith('(D)')) rightNodes.push(idx);
                        else middleNodes.push(idx);
                    });

                    function spreadY(count) {
                        if (count === 0) return [];
                        if (count === 1) return [0.5];
                        const margin = 0.05;
                        return Array.from({
                            length: count
                        }, (_, i) => margin + (i / (count - 1)) * (1 - 2 * margin));
                    }

                    const xPos = new Array(labels.length).fill(0.5);
                    const yPos = new Array(labels.length).fill(0.5);
                    const align = new Array(labels.length).fill('center');

                    // kiri (O) → label di kiri
                    spreadY(leftNodes.length).forEach((y, i) => {
                        const idx = leftNodes[i];
                        xPos[idx] = 0.08;
                        yPos[idx] = y;
                        align[idx] = 'right';
                    });

                    // kanan (D) → label di kanan
                    spreadY(rightNodes.length).forEach((y, i) => {
                        const idx = rightNodes[i];
                        xPos[idx] = 0.92;
                        yPos[idx] = y;
                        align[idx] = 'left';
                    });

                    // tengah
                    spreadY(middleNodes.length).forEach((y, i) => {
                        const idx = middleNodes[i];
                        xPos[idx] = 0.5;
                        yPos[idx] = y;
                        align[idx] = 'center';
                    });

                    const data = [{
                        type: 'sankey',
                        orientation: 'h',
                        arrangement: 'snap',
                        node: {
                            pad: 15,
                            thickness: 20,
                            line: {
                                color: 'rgba(0,0,0,0.1)',
                                width: 0.5
                            },
                            label: labels,
                            color: nodeColors,
                            x: xPos,
                            y: yPos,
                            align: align,
                            hovertemplate: '<b>%{label}</b><extra></extra>'
                        },
                        link: {
                            source,
                            target,
                            value,
                            color: linkColors,
                            hovertemplate: 'Dari <b>%{source.label}</b> ke <b>%{target.label}</b><br>Jumlah: <b>%{value}</b><extra></extra>'
                        }
                    }];

                    const layout = {

                        font: {
                            size: 11,
                            color: '#222'
                        },
                        margin: {
                            l: 0,
                            r: 0,
                            t: 50,
                            b: 20
                        },
                        paper_bgcolor: 'white',
                        plot_bgcolor: 'white',
                        autosize: true
                    };

                    // Config dengan export button
                    const config = {
                        responsive: true,
                        displayModeBar: true,
                        modeBarButtonsToAdd: [{
                            name: 'Download PNG',
                            icon: Plotly.Icons.camera,
                            click: function(gd) {
                                Plotly.downloadImage(gd, {
                                    format: 'png',
                                    filename: 'sankey_chart'
                                });
                            }
                        }]
                    };

                    Plotly.newPlot('sankeyChart', data, layout, config);
                }
                // Resize responsif
                $(window).on('resize', function() {
                    Plotly.Plots.resize('sankeyChart');
                });
            });
        </script>
    @endpush




    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const ageLabels = @json($labelsUsia); // Rentang usia dari controller
                const maleData = @json($maleData); // Data laki-laki
                const femaleData = @json($femaleData); // Data perempuan

                // Tentukan tinggi chart otomatis: misal 50px per kategori
                const chartHeight = Math.max(ageLabels.length * 50, 400);

                Highcharts.chart('rentangUsia', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent',
                        height: chartHeight
                    },
                    title: {
                        text: 'Jumlah & Persentase berdasarkan Rentang Usia — Laki-laki vs Perempuan'
                    },
                    xAxis: {
                        categories: ageLabels,
                        title: {
                            text: 'Rentang Usia'
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah'
                        },
                        stackLabels: {
                            enabled: true,
                            style: {
                                fontWeight: 'bold',
                                color: 'gray'
                            },
                            formatter: function() {
                                return this.total; // total jumlah per kategori
                            }
                        }
                    },
                    legend: {
                        reversed: true,
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    const total = this.point.stackTotal;
                                    const percent = ((this.y / total) * 100).toFixed(1);
                                    return `${this.y} (${percent}%)`;
                                }
                            }
                        }
                    },
                    tooltip: {
                        shared: true,
                        formatter: function() {
                            let s = `<b>${this.x}</b><br/>`;
                            this.points.forEach(point => {
                                const percent = ((point.y / point.total) * 100).toFixed(1);
                                s += `${point.series.name}: ${point.y} (${percent}%)<br/>`;
                            });
                            return s;
                        }
                    },
                    exporting: {
                        enabled: true,
                        buttons: {
                            contextButton: {
                                menuItems: ['downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG',
                                    'separator', 'downloadCSV', 'downloadXLS'
                                ]
                            }
                        }
                    },
                    series: [{
                            name: 'Laki-laki',
                            data: maleData,
                            color: '#00A1FE'
                        },
                        {
                            name: 'Perempuan',
                            data: femaleData,
                            color: '#5FD834'
                        }
                    ]
                });

            });
        </script>

    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const rawData = @json($chartModa);

                // Urutkan untuk cari 2 nilai tertinggi
                const sorted = [...rawData].sort((a, b) => b.y - a.y);
                const topTwoNames = sorted.slice(0, 2).map(item => item.name);

                Highcharts.chart('modeShareChart', {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent',
                        events: {
                            render: function() {
                                const chart = this;
                                const text = 'MODE SHARE<br>KEBERANGKATAN';

                                if (chart.customCenterText) chart.customCenterText.destroy();

                                chart.customCenterText = chart.renderer.text(
                                        text,
                                        chart.plotWidth / 2 + chart.plotLeft,
                                        chart.plotHeight / 2 + chart.plotTop
                                    )
                                    .attr({
                                        align: 'center',
                                        zIndex: 5
                                    })
                                    .css({
                                        color: '#666',
                                        fontSize: '14px',
                                        fontWeight: 'bold',
                                        textAlign: 'center'
                                    })
                                    .add();

                                const bbox = chart.customCenterText.getBBox();
                                chart.customCenterText.attr({
                                    y: chart.plotHeight / 2 + chart.plotTop - bbox.height / 4
                                });
                            }
                        }
                    },
                    title: {
                        text: null
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b><br>Total: <b>{point.y}</b>'
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    plotOptions: {
                        pie: {
                            innerSize: '60%',
                            allowPointSelect: true,
                            cursor: 'pointer',
                            borderWidth: 1,
                            borderColor: '#fff',
                            dataLabels: {
                                enabled: true,
                                useHTML: true,
                                formatter: function() {
                                    const isTopTwo = topTwoNames.includes(this.point.name);
                                    const persen = this.point.percentage.toFixed(0);
                                    const val = this.point.y;
                                    const name = this.point.name;

                                    // Jika termasuk dua tertinggi, beri border gold
                                    if (isTopTwo) {
                                        return `
                                <div style="
                                    border:2px solid gold;
                                    border-radius:6px;
                                    padding:3px 6px;
                                    background-color:rgba(255,255,255,0.85);
                                    box-shadow:0 0 6px gold;
                                    display:inline-block;
                                ">
                                    <b>${name}</b><br>${val} (${persen}%)
                                </div>
                            `;
                                    }

                                    // Label biasa
                                    return `<b>${name}</b><br>${val} (${persen}%)`;
                                },
                                style: {
                                    fontSize: '12px',
                                    textAlign: 'center'
                                }
                            },
                            showInLegend: true
                        }
                    },
                    series: [{
                        name: 'Persentase',
                        colorByPoint: true,
                        data: rawData
                    }],
                    credits: {
                        enabled: false
                    }
                });
            });
        </script>
    @endpush


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Highcharts.chart('hariJamChart', {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent',
                        animation: {
                            duration: 1000,
                            easing: 'easeOutBounce'
                        }
                    },
                    title: {
                        text: 'KEBERANGKATAN',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#999'
                        }
                    },
                    xAxis: {
                        categories: @json($tanggalnya),
                        labels: {
                            rotation: -90, // 🔹 label vertikal
                            style: {
                                fontSize: '10px'
                            }
                        },
                        crosshair: true,
                        lineColor: '#ccc',
                        tickWidth: 0
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: null
                        },
                        gridLineColor: 'rgba(200,200,200,0.2)',
                        labels: {
                            formatter: function() {
                                return this.value;
                            } // 🔹 angka asli
                        }
                    },
                    tooltip: {
                        shared: true,
                        backgroundColor: 'rgba(255,255,255,0.9)',
                        borderColor: '#ccc',
                        style: {
                            fontSize: '11px'
                        },
                        pointFormat: '{series.name}: <b>{point.y}</b><br/>' // 🔹 angka asli
                    },
                    plotOptions: {
                        column: {
                            stacking: 'normal',
                            borderRadius: 3,
                            pointPadding: 0.02,
                            groupPadding: 0.08,
                            shadow: false,
                            borderWidth: 0,
                            states: {
                                hover: {
                                    brightness: 0.1
                                }
                            },
                            dataLabels: {
                                enabled: false
                            }
                        },
                        series: {
                            animation: {
                                duration: 800
                            }
                        }
                    },
                    series: @json($seriesHariJam),
                    credits: {
                        enabled: false
                    },
                    legend: {
                        align: 'right',
                        verticalAlign: 'middle',
                        layout: 'vertical',
                        itemStyle: {
                            fontSize: '10px'
                        }
                    }
                });
            });
        </script>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Highcharts.chart('hariJamChart2', {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent',
                        animation: {
                            duration: 1000,
                            easing: 'easeOutBounce'
                        }
                    },
                    title: {
                        text: 'KEPULANGAN',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#999'
                        }
                    },
                    xAxis: {
                        categories: @json($tanggalnya2),
                        labels: {
                            rotation: -90, // 🔹 0 = tegak lurus, -90 = vertikal dari bawah
                            style: {
                                fontSize: '10px'
                            }
                        },
                        crosshair: true,
                        lineColor: '#ccc',
                        tickWidth: 0
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: null
                        },
                        gridLineColor: 'rgba(200,200,200,0.2)',
                        labels: {
                            formatter: function() {
                                return this.value; // tampilkan angka asli, bukan persen
                            }
                        }
                    },
                    tooltip: {
                        shared: true,
                        backgroundColor: 'rgba(255,255,255,0.9)',
                        borderColor: '#ccc',
                        style: {
                            fontSize: '11px'
                        },
                        pointFormat: '{series.name}: <b>{point.y}</b><br/>' // angka asli
                    },
                    plotOptions: {
                        column: {
                            stacking: 'normal',
                            borderRadius: 3,
                            pointPadding: 0.02,
                            groupPadding: 0.08,
                            shadow: false,
                            borderWidth: 0,
                            states: {
                                hover: {
                                    brightness: 0.1
                                }
                            },
                            dataLabels: {
                                enabled: false
                            }
                        },
                        series: {
                            animation: {
                                duration: 800
                            }
                        }
                    },
                    series: @json($seriesHariJam2),
                    credits: {
                        enabled: false
                    },
                    legend: {
                        align: 'right',
                        verticalAlign: 'middle',
                        layout: 'vertical',
                        itemStyle: {
                            fontSize: '10px'
                        }
                    }
                });
            });
        </script>
    @endpush

    <!-- kepemilikan tiket -->

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const seriesData = @json($seriesTiketGroup);

                // 🔹 Cari 3 nilai tertinggi berdasarkan persentase (y)
                const top3 = [...seriesData]
                    .sort((a, b) => b.y - a.y)
                    .slice(0, 1)
                    .map(d => d.name);

                Highcharts.chart('tiketGroupChart', {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'KEPEMILIKAN TIKET<BR>ANGKUTAN UMUM',
                        align: 'center',
                        verticalAlign: 'middle',
                        style: {
                            color: '#666',
                            fontWeight: 'bold',
                            fontSize: '16px'
                        },
                        y: 10
                    },
                    tooltip: {
                        useHTML: true,
                        formatter: function() {
                            // Pastikan `count` ada di data backend
                            return `<b>${this.point.name}</b><br>
                        Jumlah: <b>${this.point.count ?? 0}</b><br>
                        Persentase: <b>${this.point.y.toFixed(2)}%</b>`;
                        },
                        style: {
                            fontSize: '11px'
                        }
                    },
                    plotOptions: {
                        pie: {
                            innerSize: '70%',
                            borderWidth: 0,
                            allowPointSelect: true,
                            dataLabels: {
                                enabled: true,
                                useHTML: true,
                                distance: 20,
                                allowOverlap: true, // semua label tampil
                                formatter: function() {
                                    const isTop3 = top3.includes(this.point.name);
                                    const borderStyle = isTop3 ?
                                        'border:2px solid gold;border-radius:5px;padding:4px 6px;background:rgba(255,255,255,0.95);' :
                                        'border:1px solid #ccc;border-radius:3px;padding:2px 4px;background:rgba(255,255,255,0.85);';
                                    const weight = isTop3 ? 'bold' : 'normal';
                                    const color = isTop3 ? '#000' : '#444';
                                    return `
                            <div style="${borderStyle}font-weight:${weight};color:${color};font-size:10px;text-align:center;">
                                ${this.point.name}<br>${this.point.count ?? 0} (${this.point.y.toFixed(2)}%)
                            </div>
                        `;
                                },
                                style: {
                                    textOutline: 'none'
                                }
                            },
                            showInLegend: true
                        }
                    },
                    legend: {
                        align: 'center',
                        verticalAlign: 'bottom',
                        layout: 'horizontal',
                        itemStyle: {
                            fontSize: '11px',
                            color: '#333',
                            fontWeight: 'normal'
                        }
                    },
                    colors: [
                        '#d9e3d5', '#00d084', '#f78da7', '#a4bdfc', '#3b5998', '#f9cb9c'
                    ],
                    series: [{
                        name: 'Sumber Akses',
                        colorByPoint: true,
                        data: seriesData
                    }],
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });
            });
        </script>
    @endpush
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const seriesData = @json($seriesTiketGroupW);

                // 🔹 Cari 3 nilai tertinggi berdasarkan persentase (y)
                const top3 = [...seriesData]
                    .sort((a, b) => b.y - a.y)
                    .slice(0, 3)
                    .map(d => d.name);

                Highcharts.chart('tiketWaktuPembelian', {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'WAKTU PEMBELIAN<BR>TIKET',
                        align: 'center',
                        verticalAlign: 'middle',
                        style: {
                            color: '#666',
                            fontWeight: 'bold',
                            fontSize: '16px'
                        },
                        y: 10
                    },
                    tooltip: {
                        useHTML: true,
                        formatter: function() {
                            // Pastikan `count` ada di data backend
                            return `<b>${this.point.name}</b><br>
                        Jumlah: <b>${this.point.count ?? 0}</b><br>
                        Persentase: <b>${this.point.y.toFixed(2)}%</b>`;
                        },
                        style: {
                            fontSize: '11px'
                        }
                    },
                    plotOptions: {
                        pie: {
                            innerSize: '70%',
                            borderWidth: 0,
                            allowPointSelect: true,
                            dataLabels: {
                                enabled: true,
                                useHTML: true,
                                distance: 20,
                                allowOverlap: true, // semua label tampil
                                formatter: function() {
                                    const isTop3 = top3.includes(this.point.name);
                                    const borderStyle = isTop3 ?
                                        'border:2px solid gold;border-radius:5px;padding:4px 6px;background:rgba(255,255,255,0.95);' :
                                        'border:1px solid #ccc;border-radius:3px;padding:2px 4px;background:rgba(255,255,255,0.85);';
                                    const weight = isTop3 ? 'bold' : 'normal';
                                    const color = isTop3 ? '#000' : '#444';
                                    return `
                            <div style="${borderStyle}font-weight:${weight};color:${color};font-size:10px;text-align:center;">
                                ${this.point.name}<br>${this.point.count ?? 0} (${this.point.y.toFixed(2)}%)
                            </div>
                        `;
                                },
                                style: {
                                    textOutline: 'none'
                                }
                            },
                            showInLegend: true
                        }
                    },
                    legend: {
                        align: 'center',
                        verticalAlign: 'bottom',
                        layout: 'horizontal',
                        itemStyle: {
                            fontSize: '11px',
                            color: '#333',
                            fontWeight: 'normal'
                        }
                    },
                    colors: [
                        '#d9e3d5', '#00d084', '#f78da7', '#a4bdfc', '#3b5998', '#f9cb9c'
                    ],
                    series: [{
                        name: 'Sumber Akses',
                        colorByPoint: true,
                        data: seriesData
                    }],
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });
            });
        </script>
    @endpush
    <!-- sumber akses link -->

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const seriesData = @json($seriesSumberLink);

                // 🔹 Cari 3 nilai tertinggi berdasarkan persentase (y)
                const top3 = [...seriesData]
                    .sort((a, b) => b.y - a.y)
                    .slice(0, 3)
                    .map(d => d.name);

                Highcharts.chart('sumberLinkChart', {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'SUMBER<br>AKSES LINK',
                        align: 'center',
                        verticalAlign: 'middle',
                        style: {
                            color: '#666',
                            fontWeight: 'bold',
                            fontSize: '16px'
                        },
                        y: 10
                    },
                    tooltip: {
                        useHTML: true,
                        formatter: function() {
                            // Pastikan `count` ada di data backend
                            return `<b>${this.point.name}</b><br>
                        Jumlah: <b>${this.point.count ?? 0}</b><br>
                        Persentase: <b>${this.point.y.toFixed(1)}%</b>`;
                        },
                        style: {
                            fontSize: '11px'
                        }
                    },
                    plotOptions: {
                        pie: {
                            innerSize: '70%',
                            borderWidth: 0,
                            allowPointSelect: true,
                            dataLabels: {
                                enabled: true,
                                useHTML: true,
                                distance: 20,
                                allowOverlap: true, // semua label tampil
                                formatter: function() {
                                    const isTop3 = top3.includes(this.point.name);
                                    const borderStyle = isTop3 ?
                                        'border:2px solid gold;border-radius:5px;padding:4px 6px;background:rgba(255,255,255,0.95);' :
                                        'border:1px solid #ccc;border-radius:3px;padding:2px 4px;background:rgba(255,255,255,0.85);';
                                    const weight = isTop3 ? 'bold' : 'normal';
                                    const color = isTop3 ? '#000' : '#444';
                                    return `
                            <div style="${borderStyle}font-weight:${weight};color:${color};font-size:10px;text-align:center;">
                                ${this.point.name}<br>${this.point.count ?? 0} (${this.point.y.toFixed(1)}%)
                            </div>
                        `;
                                },
                                style: {
                                    textOutline: 'none'
                                }
                            },
                            showInLegend: true
                        }
                    },
                    legend: {
                        align: 'center',
                        verticalAlign: 'bottom',
                        layout: 'horizontal',
                        itemStyle: {
                            fontSize: '11px',
                            color: '#333',
                            fontWeight: 'normal'
                        }
                    },
                    colors: [
                        '#d9e3d5', '#00d084', '#f78da7', '#a4bdfc', '#3b5998', '#f9cb9c'
                    ],
                    series: [{
                        name: 'Sumber Akses',
                        colorByPoint: true,
                        data: seriesData
                    }],
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: true
                    }
                });
            });
        </script>
    @endpush

    @push('scripts')

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Highcharts.chart('biayaChart', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: null, //'Distribusi Biaya Dihabiskan vs Jumlah Orang',
                        align: 'left'
                    },
                    xAxis: {
                        categories: @json($biayaDihabiskan['categories'] ?? null),
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: null
                        }
                    },
                    legend: {
                        reversed: true
                    },
                    plotOptions: {
                        bar: {

                            borderRadius: 4,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                        },
                        series: {
                            stacking: 'normal',
                            dataLabels: {
                                enabled: true,
                                formatter() {
                                    return this.y ? Highcharts.numberFormat(this.y, 0, ',', '.') : ''
                                }
                            }
                        }
                    },
                    series: @json($biayaDihabiskan['series'] ?? null),
                    credits: {
                        enabled: false
                    }
                });
            });
        </script>
    @endpush


    @push('scripts')

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartData = @json($chart_pilihan_hari_pergi['series']);
                const labels = @json($chart_pilihan_hari_pergi['labels']);



                if (!chartData.length) {
                    document.getElementById('chartHariPergi').innerHTML =
                        '<div style="text-align:center;color:#999;padding:60px;">Tidak ada data tersedia</div>';
                    return;
                }

                Highcharts.chart('chartHariPergi', {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'Pilihan Hari Perjalanan (Pergi)',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#444'
                        }
                    },
                    xAxis: {
                        categories: labels,
                        labels: {
                            rotation: -90,
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Persentase (%)'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineColor: '#eee'
                    },
                    tooltip: {
                        headerFormat: '<b>{point.label}</b><br>',
                        pointFormat: 'Total: <b>{point.total}</b> responden<br>' +
                            'Persentase: <b>{point.y:.0f}%</b>'
                    },
                    plotOptions: {

                        column: {
                            borderRadius: 3,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                format: '{point.y:.0f}%',
                                style: {
                                    fontSize: '11px'
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Persentase',
                        data: chartData,
                        colorByPoint: true
                    }],
                    credits: {
                        enabled: false
                    }
                });
            });
        </script>
    @endpush

    @push('scripts')

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartData = @json($chart_pilihan_jam_pergi['series']);
                const labels = @json($chart_pilihan_jam_pergi['labels']);



                if (!chartData.length) {
                    document.getElementById('chartJamPergi').innerHTML =
                        '<div style="text-align:center;color:#999;padding:60px;">Tidak ada data tersedia</div>';
                    return;
                }

                Highcharts.chart('chartJamPergi', {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'Pilihan Jam Perjalanan (Pergi)',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#444'
                        }
                    },
                    xAxis: {
                        categories: labels,
                        labels: {
                            rotation: -90,
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Persentase (%)'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineColor: '#eee'
                    },
                    tooltip: {
                        headerFormat: '<b>{point.label}</b><br>',
                        pointFormat: 'Total: <b>{point.total}</b> responden<br>' +
                            'Persentase: <b>{point.y:.0f}%</b>'
                    },
                    plotOptions: {
                        column: {
                            borderRadius: 3,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                format: '{point.y:.0f}%',
                                style: {
                                    fontSize: '11px'
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Persentase',
                        data: chartData,
                        colorByPoint: true
                    }],
                    credits: {
                        enabled: false
                    }
                });
            });
        </script>
    @endpush


    @push('scripts')

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartData = @json($chart_pilihan_hari_balik['series']);
                const labels = @json($chart_pilihan_hari_balik['labels']);



                if (!chartData.length) {
                    document.getElementById('chartHariBalik').innerHTML =
                        '<div style="text-align:center;color:#999;padding:60px;">Tidak ada data tersedia</div>';
                    return;
                }

                Highcharts.chart('chartHariBalik', {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'Pilihan Hari Perjalanan (Balik)',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#444'
                        }
                    },
                    xAxis: {
                        categories: labels,
                        labels: {
                            rotation: -90,
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Persentase (%)'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineColor: '#eee'
                    },
                    tooltip: {
                        headerFormat: '<b>{point.label}</b><br>',
                        pointFormat: 'Total: <b>{point.total}</b> responden<br>' +
                            'Persentase: <b>{point.y:.0f}%</b>'
                    },
                    plotOptions: {
                        column: {
                            borderRadius: 3,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                format: '{point.y:.0f}%',
                                style: {
                                    fontSize: '11px'
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Persentase',
                        data: chartData,
                        colorByPoint: true
                    }],
                    credits: {
                        enabled: false
                    }
                });
            });
        </script>
    @endpush

    @push('scripts')

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartData = @json($chart_pilihan_jam_balik['series']);
                const labels = @json($chart_pilihan_jam_balik['labels']);



                if (!chartData.length) {
                    document.getElementById('chartJamBalik').innerHTML =
                        '<div style="text-align:center;color:#999;padding:60px;">Tidak ada data tersedia</div>';
                    return;
                }

                Highcharts.chart('chartJamBalik', {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: 'Pilihan Jam Perjalanan (Balik)',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#444'
                        }
                    },
                    xAxis: {
                        categories: labels,
                        labels: {
                            rotation: -90,
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Persentase (%)'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineColor: '#eee'
                    },
                    tooltip: {
                        headerFormat: '<b>{point.label}</b><br>',
                        pointFormat: 'Total: <b>{point.total}</b> responden<br>' +
                            'Persentase: <b>{point.y:.0f}%</b>'
                    },
                    plotOptions: {
                        column: {
                            borderRadius: 3,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                format: '{point.y:.0f}%',
                                style: {
                                    fontSize: '11px'
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Persentase',
                        data: chartData,
                        colorByPoint: true
                    }],
                    credits: {
                        enabled: false
                    }
                });
            });
        </script>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // === FUNGSI REUSABLE PIE CHART DENGAN BORDER GOLD UNTUK 3 TERTINGGI ===
                function renderPieChart(containerId, titleText, chartData) {
                    // Pastikan ada data
                    if (!chartData || chartData.length === 0) return;

                    // Urutkan dan ambil 3 tertinggi
                    const topThreeNames = [...chartData]
                        .sort((a, b) => b.y - a.y)
                        .slice(0, 3)
                        .map(item => item.name);

                    Highcharts.chart(containerId, {
                        chart: {
                            type: 'pie',
                            backgroundColor: 'transparent'
                        },
                        title: {
                            text: titleText,
                            style: {
                                fontSize: '14px',
                                fontWeight: 'bold'
                            }
                        },
                        tooltip: {
                            pointFormat: '<b>{point.percentage:.2f}%</b> ({point.name})'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                borderWidth: 1,
                                borderColor: '#fff',
                                dataLabels: {
                                    enabled: true,
                                    useHTML: true,
                                    formatter: function() {
                                        const persen = this.point.percentage.toFixed(1);
                                        const name = this.point.name;
                                        const isTopThree = topThreeNames.includes(name);

                                        if (isTopThree) {
                                            return `
                                    <div style="
                                        border: 2px solid gold;
                                        border-radius: 6px;
                                        padding: 3px 6px;
                                        background-color: rgba(255,255,255,0.85);
                                        box-shadow: 0 0 6px gold;
                                        display: inline-block;
                                    ">
                                        <b>${name}</b><br>${persen}%
                                    </div>
                                `;
                                        }
                                        return `<b>${name}</b><br>${persen}%`;
                                    },
                                    style: {
                                        fontSize: '10px',
                                        textAlign: 'center'
                                    }
                                }
                            }
                        },
                        credits: {
                            enabled: false
                        },
                        series: [{
                            name: 'Persentase',
                            colorByPoint: true,
                            data: chartData
                        }]
                    });
                }

                // === PIE RUTE MOBIL ===
                renderPieChart(
                    'chartRuteMobil',
                    'Distribusi Pilihan Rute – Mobil',
                    @json($pilihan_rute_nasional['chart_mobil'] ?? [])
                );

                // === PIE RUTE MOTOR ===
                renderPieChart(
                    'chartRuteMotor',
                    'Distribusi Pilihan Rute – Motor',
                    @json($pilihan_rute_nasional['chart_motor'] ?? [])
                );

            });
        </script>
    @endpush


    <!-- plan travel -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categories = @json($travel_plan_provinsi);
                const totalResponden = @json($travel_plan_total);
                const persenBerencana = @json($travel_plan_berencana);
                const persenTidak = @json($travel_plan_tidak);

                // Hitung tinggi tiap segmen berdasarkan jumlah asli
                const dataLiburan = totalResponden.map((total, i) => Math.round(total * persenBerencana[i] / 100));
                const dataTidak = totalResponden.map((total, i) => Math.round(total * persenTidak[i] / 100));

                Highcharts.chart('stackedBarChartTravel', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },

                    title: {
                        text: null,
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#333'
                        }
                    },

                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        },
                        labels: {
                            style: {
                                fontSize: '11px',
                                color: '#555'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Responden',
                            align: 'high'
                        },
                        labels: {
                            style: {
                                fontSize: '11px',
                                color: '#555'
                            }
                        },
                        gridLineDashStyle: 'Dash',
                        stackLabels: {
                            enabled: true,
                            formatter: function() {
                                return this.total; // tampilkan total responden
                            },
                            style: {
                                fontWeight: 'bold',
                                color: '#333'
                            }
                        }
                    },
                    tooltip: {
                        shared: true,
                        formatter: function() {
                            const i = this.points[0].point.index;
                            const total = totalResponden[i];
                            return `<b>${categories[i]}</b><br/>
                            Total responden: <b>${total}</b><br/>
                            Berencana: <b>${dataLiburan[i]}</b><br/>
                            Tidak: <b>${dataTidak[i]}</b>`;
                        }
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal',
                            pointPadding: 0.1,
                            groupPadding: 0.05,
                            dataLabels: {
                                enabled: true,
                                style: {
                                    fontSize: '10px',
                                    fontWeight: 'bold',
                                    color: '#fff'
                                }
                            }
                        },
                        bar: {
                            //pointWidth: 12,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                        }
                    },
                    series: [{
                            name: 'Berencana Liburan',
                            data: dataLiburan,
                            color: '#3b82f6'
                        },
                        {
                            name: 'Tidak Berencana',
                            data: dataTidak,
                            color: '#ef4444'
                        }
                    ],
                    legend: {
                        reversed: true
                    },
                    credits: {
                        enabled: false
                    }
                });
            });
        </script>
    @endpush

    <!--simpul terpadat -->
    <!--BANDARA-->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categories = @json($simpul['bandaraLabelsAsal'] ?? []);
                const values = @json($simpul['bandaraValuesAsal'] ?? []);

                // Hitung total semua nilai untuk persen
                const total = values.reduce((a, b) => a + b, 0);

                Highcharts.chart('simpulBandaraAsal', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: '10 Bandara Asal Terbanyak',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#666'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Perjalanan',
                            align: 'high'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineWidth: 0.5
                    },
                    tooltip: {
                        formatter: function() {
                            const persen = ((this.y / total) * 100).toFixed(1);
                            return `<b>${this.x}</b><br>${this.y} perjalanan (${persen}%)`;
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                align: 'right',
                                inside: false,
                                formatter: function() {
                                    const persen = ((this.y / total) * 100).toFixed(1);
                                    return `${this.y} (${persen}%)`;
                                },
                                style: {
                                    fontSize: '10px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            }
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: 'Jumlah',
                        colorByPoint: true,
                        data: values
                    }]
                });
            });
        </script>
    @endpush

    <!--simpul terpadat -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categories = @json($simpul['bandaraLabelsTujuan'] ?? []);
                const values = @json($simpul['bandaraValuesTujuan'] ?? []);

                // Hitung total semua nilai untuk persen
                const total = values.reduce((a, b) => a + b, 0);

                Highcharts.chart('simpulBandaraTujuan', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: '10 Bandara Tujuan Terbanyak',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#666'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Perjalanan',
                            align: 'high'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineWidth: 0.5
                    },
                    tooltip: {
                        formatter: function() {
                            const persen = ((this.y / total) * 100).toFixed(1);
                            return `<b>${this.x}</b><br>${this.y} perjalanan (${persen}%)`;
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                align: 'right',
                                inside: false,
                                formatter: function() {
                                    const persen = ((this.y / total) * 100).toFixed(1);
                                    return `${this.y} (${persen}%)`;
                                },
                                style: {
                                    fontSize: '10px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            }
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: 'Jumlah',
                        colorByPoint: true,
                        data: values
                    }]
                });
            });
        </script>
    @endpush

    <!--PELABUHAN-->

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categories = @json($simpul['pelabuhanLabelsAsal'] ?? []);
                const values = @json($simpul['pelabuhanValuesAsal'] ?? []);

                // Hitung total semua nilai untuk persen
                const total = values.reduce((a, b) => a + b, 0);

                Highcharts.chart('simpulPelabuhanAsal', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: '10 Pelabuhan Asal Terbanyak',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#666'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Perjalanan',
                            align: 'high'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineWidth: 0.5
                    },
                    tooltip: {
                        formatter: function() {
                            const persen = ((this.y / total) * 100).toFixed(1);
                            return `<b>${this.x}</b><br>${this.y} perjalanan (${persen}%)`;
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                align: 'right',
                                inside: false,
                                formatter: function() {
                                    const persen = ((this.y / total) * 100).toFixed(1);
                                    return `${this.y} (${persen}%)`;
                                },
                                style: {
                                    fontSize: '10px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            }
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: 'Jumlah',
                        colorByPoint: true,
                        data: values
                    }]
                });
            });
        </script>
    @endpush

    <!--simpul terpadat -->
    <!-- pelabuhan -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categories = @json($simpul['pelabuhanLabelsTujuan'] ?? []);
                const values = @json($simpul['pelabuhanValuesTujuan'] ?? []);

                // Hitung total semua nilai untuk persen
                const total = values.reduce((a, b) => a + b, 0);

                Highcharts.chart('simpulPelabuhanTujuan', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: '10 Pelabuhan Tujuan Terbanyak',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#666'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Perjalanan',
                            align: 'high'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineWidth: 0.5
                    },
                    tooltip: {
                        formatter: function() {
                            const persen = ((this.y / total) * 100).toFixed(1);
                            return `<b>${this.x}</b><br>${this.y} perjalanan (${persen}%)`;
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                align: 'right',
                                inside: false,
                                formatter: function() {
                                    const persen = ((this.y / total) * 100).toFixed(1);
                                    return `${this.y} (${persen}%)`;
                                },
                                style: {
                                    fontSize: '10px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            }
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: 'Jumlah',
                        colorByPoint: true,
                        data: values
                    }]
                });
            });
        </script>
    @endpush

    <!--STASIUN-->

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categories = @json($simpul['stasiunLabelsAsal'] ?? []);
                const values = @json($simpul['stasiunValuesAsal'] ?? []);

                // Hitung total semua nilai untuk persen
                const total = values.reduce((a, b) => a + b, 0);

                Highcharts.chart('simpulStasiunAsal', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: '10 Stasiun Asal Terbanyak',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#666'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Perjalanan',
                            align: 'high'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineWidth: 0.5
                    },
                    tooltip: {
                        formatter: function() {
                            const persen = ((this.y / total) * 100).toFixed(1);
                            return `<b>${this.x}</b><br>${this.y} perjalanan (${persen}%)`;
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                align: 'right',
                                inside: false,
                                formatter: function() {
                                    const persen = ((this.y / total) * 100).toFixed(1);
                                    return `${this.y} (${persen}%)`;
                                },
                                style: {
                                    fontSize: '10px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            }
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: 'Jumlah',
                        colorByPoint: true,
                        data: values
                    }]
                });
            });
        </script>
    @endpush

    <!--simpul terpadat -->
    <!-- pelabuhan -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categories = @json($simpul['stasiunLabelsTujuan'] ?? []);
                const values = @json($simpul['stasiunValuesTujuan'] ?? []);

                // Hitung total semua nilai untuk persen
                const total = values.reduce((a, b) => a + b, 0);

                Highcharts.chart('simpulStasiunTujuan', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: '10 Stasiun Tujuan Terbanyak',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#666'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Perjalanan',
                            align: 'high'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineWidth: 0.5
                    },
                    tooltip: {
                        formatter: function() {
                            const persen = ((this.y / total) * 100).toFixed(1);
                            return `<b>${this.x}</b><br>${this.y} perjalanan (${persen}%)`;
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                align: 'right',
                                inside: false,
                                formatter: function() {
                                    const persen = ((this.y / total) * 100).toFixed(1);
                                    return `${this.y} (${persen}%)`;
                                },
                                style: {
                                    fontSize: '10px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            }
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: 'Jumlah',
                        colorByPoint: true,
                        data: values
                    }]
                });
            });
        </script>
    @endpush


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const categories = @json($simpul['tolLabelsPadat'] ?? []);
                const values = @json($simpul['tolValuesPadat'] ?? []);

                // Hitung total semua nilai untuk persen
                const total = values.reduce((a, b) => a + b, 0);

                Highcharts.chart('simpulRuasTolAsal', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: '5 Ruas Tol Terpadat',
                        align: 'left',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            color: '#666'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: {
                            text: null
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Perjalanan',
                            align: 'high'
                        },
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        },
                        gridLineWidth: 0.5
                    },
                    tooltip: {
                        formatter: function() {
                            const persen = ((this.y / total) * 100).toFixed(1);
                            return `<b>${this.x}</b><br>${this.y} perjalanan (${persen}%)`;
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            pointWidth: 30, // 🔹 Lebar bar (default sekitar 10–12)
                            groupPadding: 0.1, // 🔹 Jarak antar grup bar
                            pointPadding: 0.05, // 🔹 Jarak antar bar individual
                            dataLabels: {
                                enabled: true,
                                align: 'right',
                                inside: false,
                                formatter: function() {
                                    const persen = ((this.y / total) * 100).toFixed(1);
                                    return `${this.y} (${persen}%)`;
                                },
                                style: {
                                    fontSize: '10px',
                                    fontWeight: 'bold',
                                    color: '#333'
                                }
                            }
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    credits: {
                        enabled: false
                    },
                    series: [{
                        name: 'Jumlah',
                        colorByPoint: true,
                        data: values
                    }]
                });
            });
        </script>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Highcharts.chart('chartTrenHistoris', {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: null
                    },
                    xAxis: {
                        categories: ['Survei Prakiraan 2025', 'Realisasi MPD 2025', 'Survei Prakiraan 2026'],
                        crosshair: true
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Jumlah Pergerakan (Juta)'
                        },
                        labels: {
                            formatter: function() {
                                return this.value / 1000000 + ' Jt';
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                            '<td style="padding:0"><b>{point.y:,.0f}</b></td></tr>',
                        footerFormat: '</table>',
                        shared: true,
                        useHTML: true
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.2,
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    return Highcharts.numberFormat(this.y, 0);
                                },
                                style: {
                                    fontWeight: 'bold',
                                    color: 'black'
                                }
                            }
                        }
                    },
                    series: [{
                        name: 'Jumlah Orang',
                        data: [{
                                y: 146489733,
                                color: '#2c7be5'
                            }, // Blue
                            {
                                y: 154623632,
                                color: '#6e84a3'
                            }, // Grey/Secondary
                            {
                                y: 143915053,
                                color: '#f6c23e'
                            } // Yellow/Warning to highlight new data
                        ],
                        showInLegend: false
                    }],
                    credits: {
                        enabled: false
                    },
                    exporting: {
                        enabled: false
                    }
                });
            });
        </script>
    @endpush
