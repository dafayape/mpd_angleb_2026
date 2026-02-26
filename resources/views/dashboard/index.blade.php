@extends('layout.app')

@section('title', 'Executive Summary')

@push('css')
    <!-- AOS Animation Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <style>
        .bg-navy {
            background-color: #2a3042 !important;
            color: white !important;
        }

        .bg-amber {
            background-color: #f59e0b !important;
            color: white !important;
        }

        .text-navy {
            color: #2a3042 !important;
        }

        .kpi-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .kpi-title {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            opacity: 0.9;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .kpi-value-box {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
        }

        .kpi-value-box-amber {
            background-color: rgba(255, 255, 255, 0.25);
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(4px);
        }

        .sticky-filter {
            position: sticky;
            top: 70px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 28px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .narrative-box {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 20px;
            margin-top: 16px;
            border-left: 5px solid #3b82f6;
            font-size: 1rem;
            line-height: 1.7;
            color: #334155;
        }

        .narrative-box.kesimpulan {
            border-left: 5px solid #10b981;
            background-color: #f0fdf4;
        }

        .section-badge {
            background-color: #2a3042;
            color: white;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 1.3rem;
            font-weight: 900;
            margin-right: 16px;
            line-height: 1;
            box-shadow: 0 4px 10px rgba(42, 48, 66, 0.15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .custom-toggle-group .btn {
            border-color: #5a67d8;
            /* Indigo matches reference image */
            color: #5a67d8;
            border-radius: 4px;
        }

        .custom-toggle-group .btn-check:checked+.btn {
            background-color: #5a67d8;
            color: white;
            border-color: #5a67d8;
        }

        .custom-toggle-group .btn:hover {
            background-color: rgba(90, 103, 216, 0.1);
            color: #5a67d8;
        }

        .custom-toggle-group .btn-check:checked+.btn:hover {
            background-color: #4c51bf;
            color: white;
        }

        .content-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            mb-4;
            overflow: hidden;
        }

        .content-card .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        .content-card .card-body {
            padding: 1.5rem;
        }

        .skeleton-block {
            height: 200px;
            background-color: #e2e8f0;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            border-radius: 12px;
        }

        .skeleton-text {
            height: 20px;
            background-color: #e2e8f0;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .5;
            }
        }
    </style>
@endpush

@section('content')

    @component('layout.partials.page-header', ['number' => '02', 'title' => 'Dashboard Nasional'])
    @endcomponent

    <div class="container-fluid py-2">
        <!-- Sticky Filter Bar -->
        <div class="sticky-filter d-flex align-items-center justify-content-between flex-wrap gap-3" data-aos="fade-down"
            data-aos-duration="600">
            <div class="d-flex align-items-end gap-4 flex-wrap">
                <div class="d-flex flex-column">
                    <label class="small fw-bold text-muted mb-2 text-uppercase" style="letter-spacing: 0.5px;">Tipe
                        Data</label>
                    <div class="btn-group custom-toggle-group shadow-sm" role="group" style="height: 38px;">
                        <input type="radio" class="btn-check filter-toggle" name="data_type" id="dt_real" value="real"
                            autocomplete="off" checked>
                        <label class="btn btn-sm px-4 d-flex align-items-center fw-bold" for="dt_real"
                            style="font-size: 0.9rem;">Realisasi</label>
                        <input type="radio" class="btn-check filter-toggle" name="data_type" id="dt_fore"
                            value="forecast" autocomplete="off">
                        <label class="btn btn-sm px-4 d-flex align-items-center fw-bold" for="dt_fore"
                            style="font-size: 0.9rem;">Prakiraan</label>
                    </div>
                </div>
                <div class="d-flex flex-column">
                    <label class="small fw-bold text-muted mb-2 text-uppercase" style="letter-spacing: 0.5px;">Operator
                        Seluler</label>
                    <select class="form-select form-select-sm shadow-sm fw-medium" id="opselSelect"
                        style="width: auto; min-width: 200px; cursor: pointer; height: 38px;">
                        <option value="">Semua Operator (Agregat)</option>
                        <option value="TSEL">Telkomsel</option>
                        <option value="IOH">Indosat Ooredoo</option>
                        <option value="XL">XL Axiata</option>
                    </select>
                </div>
                <div class="d-flex flex-column">
                    <label class="small fw-bold text-muted mb-2 text-uppercase" style="letter-spacing: 0.5px;">Satuan
                        Data</label>
                    <select class="form-select form-select-sm shadow-sm fw-medium" id="satuanSelect"
                        style="width: auto; min-width: 150px; cursor: pointer; height: 38px;">
                        <option value="auto">Auto (Angka & %)</option>
                        <option value="angka">Angka Penuh</option>
                        <option value="persen">Persentase (%)</option>
                    </select>
                </div>
            </div>
            <div class="d-flex align-items-center bg-light px-4 py-3 rounded-pill border shadow-sm">
                <i class="bx bx-calendar-event fs-4 me-3 text-primary"></i>
                <div class="fw-bold text-navy" style="font-size: 1rem;">Periode: 13 Maret 2026 – 30 Maret 2026</div>
            </div>
        </div>

        <div id="contentSkeletons">
            @for ($i = 0; $i < 4; $i++)
                <div class="card content-card mb-4" data-aos="fade-up" data-aos-delay="{{ $i * 100 }}">
                    <div class="card-body">
                        <div class="skeleton-text w-25 mb-4" style="height: 30px;"></div>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="skeleton-block"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="skeleton-block"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="skeleton-block"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <div id="contentData" style="display: none;">

            <!-- ROW 1: Blocks 01 & 02 -->
            <div class="row g-4 mb-4">
                <!-- BLOCK 01 -->
                <div class="col-xl-5" data-aos="fade-up">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex align-items-center">
                            <span class="section-badge">01</span>
                            <h5 class="fw-bold text-navy mb-0">Latar Belakang Kegiatan MPD</h5>
                        </div>
                        <div class="card-body d-flex align-items-center bg-light">
                            <p class="text-muted mb-0" style="line-height: 1.8; text-align: justify; font-size: 1.05rem;">
                                Pemantauan pergerakan masyarakat pada periode Lebaran (Angleb) 2026 dilakukan melalui
                                pemanfaatan <strong>Mobile Positioning Data (MPD)</strong> yang diperoleh dari tiga operator
                                seluler utama: Telkomsel, Indosat Ooredoo Hutchison, dan XL Axiata.<br /><br />
                                Analisis ini dirancang secara khusus untuk mendukung pengambilan kebijakan dan mitigasi
                                strategis oleh Kementerian Perhubungan dalam rangka kesiapan infrastruktur, manajemen lalu
                                lintas, dan logistik nasional secara aktual.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- BLOCK 02 -->
                <div class="col-xl-7" data-aos="fade-up" data-aos-delay="100">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex align-items-center">
                            <span class="section-badge">02</span>
                            <h5 class="fw-bold text-navy mb-0">Pendefinisian Pergerakan & Unique Subscriber</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4 h-100">
                                <div class="col-md-6">
                                    <div class="p-4 bg-light rounded-3 h-100 border transition hover-shadow">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary text-white rounded p-2 me-3"><i
                                                    class="bx bx-run fs-4"></i></div>
                                            <h6 class="fw-bold text-navy mb-0">Jumlah Pergerakan <br /><small
                                                    class="text-muted fw-normal">(Movement Count)</small></h6>
                                        </div>
                                        <p class="text-muted mb-0" style="line-height: 1.6; text-align: justify;">Total
                                            frekuensi perjalanan yang terjadi per hari selama periode pengamatan. Satu orang
                                            dapat dihitung <b>lebih dari satu kali</b> apabila melakukan lebih dari satu
                                            aktivitas mobilitas yang memenuhi syarat algoritma MPD.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-4 bg-light rounded-3 h-100 border transition hover-shadow">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-amber text-white rounded p-2 me-3"><i
                                                    class="bx bx-user-pin fs-4"></i></div>
                                            <h6 class="fw-bold text-navy mb-0">Unique Subscriber <br /><small
                                                    class="text-muted fw-normal">(Individu Unik)</small></h6>
                                        </div>
                                        <p class="text-muted mb-0" style="line-height: 1.6; text-align: justify;">Jumlah
                                            individu unik aktual yang melakukan perjalanan. Seseorang hanya dihitung <b>satu
                                                kali (1)</b> meskipun melakukan perjalanan berkali-kali melintasi berbagai
                                            wilayah sepanjang periode Angleb 2026.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 2: Block 03 -->
            <div class="card content-card mb-4" data-aos="fade-up">
                <div class="card-header d-flex align-items-center">
                    <span class="section-badge">03</span>
                    <h5 class="fw-bold text-navy mb-0 text-uppercase">Hasil Pengolahan Data Unique Subscriber (Nasional)
                    </h5>
                </div>
                <div class="card-body bg-light">
                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <div class="card kpi-card bg-navy h-100">
                                <div class="card-body p-4 d-flex flex-column justify-content-center">
                                    <div class="kpi-title text-light">Total Unique Subscriber</div>
                                    <div class="kpi-value-box fs-2 font-monospace text-white" id="val_nas_orang">-</div>
                                    <div class="kpi-subtitle text-light">Individu Penduduk Indonesia</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card kpi-card bg-amber h-100">
                                <div class="card-body p-4 d-flex flex-column justify-content-center">
                                    <div class="kpi-title text-light">Rata-Rata Koefisien</div>
                                    <div class="kpi-value-box-amber fs-2 font-monospace text-white"
                                        id="val_nas_koefisien">-</div>
                                    <div class="kpi-subtitle text-light">Perjalanan Aktif per Individu</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card kpi-card bg-navy h-100">
                                <div class="card-body p-4 d-flex flex-column justify-content-center">
                                    <div class="kpi-title text-light">Total Pergerakan (Akumulatif)</div>
                                    <div class="kpi-value-box fs-2 font-monospace text-white" id="val_nas_pergerakan">-
                                    </div>
                                    <div class="kpi-subtitle text-light">Perjalanan / Trip Nasional</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="narrative-box shadow-sm border-0 mt-4" id="nar_nas_pergerakan">Memuat narasi otomatis...
                    </div>
                </div>
            </div>

            <!-- ROW 3: Blocks 04 & 05 -->
            <div class="row g-4 mb-4">
                <!-- BLOCK 04 -->
                <div class="col-xl-6" data-aos="fade-up">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex align-items-center">
                            <span class="section-badge">04</span>
                            <h5 class="fw-bold text-navy mb-0">Puncak Pergerakan Berdasarkan MPD</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4" id="peak_cards_container">
                                <!-- JS Injected Peak Cards -->
                            </div>
                            <div class="bg-light p-3 rounded-3" style="border: 1px dashed #cbd5e1;">
                                <div id="chart_nas_trend" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BLOCK 05 -->
                <div class="col-xl-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex align-items-center">
                            <span class="section-badge">05</span>
                            <h5 class="fw-bold text-navy mb-0">Jumlah Individu Unique Subscriber (Harian)</h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="bg-light p-3 rounded-3 mb-4 flex-grow-1" style="border: 1px dashed #cbd5e1;">
                                <div id="chart_nas_orang_trend" style="height: 100%; min-height: 250px;"></div>
                            </div>
                            <p class="text-muted small fst-italic text-center mb-0"><i
                                    class="bx bx-info-circle me-1"></i>Grafik di atas merepresentasikan probabilitas
                                masyarakat yang terekam berpindah posisi signifikansinya secara harian.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 4: Blocks 06 & 07 -->
            <div class="row g-4 mb-4">
                <!-- BLOCK 06 -->
                <div class="col-xl-7" data-aos="fade-up">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex align-items-center">
                            <span class="section-badge">06</span>
                            <h5 class="fw-bold text-navy mb-0">Kontribusi Setiap Operator terhadap MPD</h5>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center">
                            <div class="row g-4 mb-3">
                                <div class="col-md-6">
                                    <div class="bg-light rounded p-3 h-100 shadow-sm border">
                                        <div id="chart_opsel_orang" style="height: 250px;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light rounded p-3 h-100 shadow-sm border">
                                        <div id="chart_opsel_pergerakan" style="height: 250px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="narrative-box shadow-sm border-0 mt-2" id="nar_nas_opsel">Memuat narasi distribusi
                                operator...</div>
                        </div>
                    </div>
                </div>

                <!-- BLOCK 07 -->
                <div class="col-xl-5" data-aos="fade-up" data-aos-delay="100">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex align-items-center">
                            <span class="section-badge">07</span>
                            <h5 class="fw-bold text-navy mb-0">Kesimpulan Sementara Pengolahan MPD</h5>
                        </div>
                        <div class="card-body d-flex align-items-center">
                            <div class="w-100 text-center">
                                <i class="bx bxs-quote-alt-left fs-1 text-navy mb-3" style="opacity: 0.2;"></i>
                                <div class="narrative-box kesimpulan shadow text-start mx-auto mb-4" id="nar_kstmp"
                                    style="max-width: 90%;">
                                    Menghitung konklusi eksekutif...
                                </div>
                                <i class="bx bxs-quote-alt-right fs-1 text-navy" style="opacity: 0.2;"></i>
                                <p class="text-muted mt-4 fw-medium"><i class="bx bx-check-shield me-2"></i>Summary
                                    Auto-Generated by System (AI-Driven
                                    Data Intelligence)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 5: Blocks 08 & 09 -->
            <div class="row g-4 mb-4">
                <!-- BLOCK 08 -->
                <div class="col-xl-7" data-aos="fade-up">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex align-items-center">
                            <span class="section-badge">08</span>
                            <h5 class="fw-bold text-navy mb-0">Realisasi MPD Aktual vs Survei Prakiraan (Trend)</h5>
                        </div>
                        <div class="card-body">
                            <div class="bg-light p-3 rounded-3 border">
                                <div id="chart_forecast_comparison" style="height: 350px;"></div>
                            </div>
                            <p class="text-muted small fst-italic text-center mt-3 mb-0"><i
                                    class="bx bx-bar-chart-alt-2 me-1"></i>Analisis membandingkan proporsi distribusi
                                sebaran harian (Tingkat signifikansi deviasi).</p>
                        </div>
                    </div>
                </div>

                <!-- BLOCK 09 -->
                <div class="col-xl-5" data-aos="fade-up" data-aos-delay="100">
                    <div class="card content-card h-100">
                        <div class="card-header d-flex align-items-center">
                            <span class="section-badge">09</span>
                            <h5 class="fw-bold text-navy mb-0">Perbandingan MPD Tahun Ini vs YoY</h5>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="bg-light p-3 rounded-3 border mb-4">
                                <div id="chart_yoy" style="height: 280px;"></div>
                            </div>
                            <div class="narrative-box shadow-sm border-0 m-0" id="nar_yoy">Memuat narasi fluktuasi
                                YoY...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 6: Block 10 -->
            <div class="card content-card mb-4" data-aos="fade-up">
                <div class="card-header d-flex align-items-center">
                    <span class="section-badge">10</span>
                    <h5 class="fw-bold text-navy mb-0">Data Opsel Unique Subscriber
                        (Intra & Inter Jabodetabek)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-5">
                        <div class="col-xl-6 text-center border-end">
                            <h6 class="fw-bold px-4 py-2 bg-navy text-white rounded-pill d-inline-block shadow-sm mb-4">
                                INTRA JABODETABEK</h6>
                            <div class="bg-light rounded p-3 border">
                                <div id="chart_opsel_intra" style="height: 280px;"></div>
                            </div>
                        </div>
                        <div class="col-xl-6 text-center">
                            <h6 class="fw-bold px-4 py-2 bg-navy text-white rounded-pill d-inline-block shadow-sm mb-4">
                                INTER JABODETABEK</h6>
                            <div class="bg-light rounded p-3 border">
                                <div id="chart_opsel_inter" style="height: 280px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROW 7: Block 11 -->
            <div class="card content-card mb-5" data-aos="fade-up">
                <div class="card-header d-flex align-items-center">
                    <span class="section-badge">11</span>
                    <h5 class="fw-bold text-navy mb-0">Jumlah Individu Unique Subscriber Berpergian (Jabodetabek)</h5>
                </div>
                <div class="card-body">

                    <div class="row g-5 mb-5">
                        <div class="col-xl-6 position-relative z-1 d-flex flex-column">
                            <div class="row g-3 mb-4 flex-grow-1">
                                <div class="col-4">
                                    <div class="card kpi-card bg-navy h-100">
                                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                            <div class="kpi-title text-light" style="font-size: 0.70rem;">Intra Jabo
                                                (Orang)</div>
                                            <div class="kpi-value-box text-white p-2 fs-5 font-monospace"
                                                id="val_intra_orang">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card kpi-card bg-amber h-100">
                                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                            <div class="kpi-title text-light" style="font-size: 0.70rem;">Rata-rata Koef.
                                            </div>
                                            <div class="kpi-value-box-amber text-white p-2 fs-4 font-monospace"
                                                id="val_intra_koefisien">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card kpi-card bg-navy h-100">
                                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                            <div class="kpi-title text-light" style="font-size: 0.70rem;">Pergerakan
                                                (Trip)</div>
                                            <div class="kpi-value-box text-white p-2 fs-5 font-monospace"
                                                id="val_intra_pergerakan">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="narrative-box shadow-sm border-0 m-0" id="nar_intra">Memuat narasi...</div>
                        </div>

                        <div class="col-xl-6 d-flex flex-column">
                            <div class="row g-3 mb-4 flex-grow-1">
                                <div class="col-4">
                                    <div class="card kpi-card bg-navy h-100">
                                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                            <div class="kpi-title text-light" style="font-size: 0.70rem;">Inter Jabo
                                                (Orang)</div>
                                            <div class="kpi-value-box text-white p-2 fs-5 font-monospace"
                                                id="val_inter_orang">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card kpi-card bg-amber h-100">
                                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                            <div class="kpi-title text-light" style="font-size: 0.70rem;">Rata-rata Koef.
                                            </div>
                                            <div class="kpi-value-box-amber text-white p-2 fs-4 font-monospace"
                                                id="val_inter_koefisien">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card kpi-card bg-navy h-100">
                                        <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                            <div class="kpi-title text-light" style="font-size: 0.70rem;">Pergerakan
                                                (Trip)</div>
                                            <div class="kpi-value-box text-white p-2 fs-5 font-monospace"
                                                id="val_inter_pergerakan">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="narrative-box shadow-sm border-0 m-0" id="nar_inter">Memuat narasi...</div>
                        </div>
                    </div>

                    <hr class="text-muted border-dashed my-4">

                    <!-- Trend Jabo -->
                    <h6 class="fw-bold text-navy mb-4 text-center text-uppercase"><i
                            class="bx bx-trending-up me-2 text-primary"></i>Tren Unique Subscriber Intra & Inter
                        Jabodetabek</h6>
                    <div class="row">
                        <div class="col-xl-6 border-end">
                            <div id="chart_intra_trend" style="height: 300px;"></div>
                        </div>
                        <div class="col-xl-6">
                            <div id="chart_inter_trend" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 50
        });

        const formatJuta = (num) => {
            if (num >= 1000000) return (num / 1000000).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' Juta';
            return num.toLocaleString('id-ID');
        };

        const formatNumber = (num) => num.toLocaleString('id-ID');

        const formatDateShort = (dtStr) => {
            const dt = new Date(dtStr);
            const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            return days[dt.getDay()] + ', ' + dt.getDate() + ' ' + months[dt.getMonth()];
        };

        $(document).ready(function() {
            $('.filter-toggle, #opselSelect, #satuanSelect').on('change', fetchExecutiveSummary);
            fetchExecutiveSummary(); // initial load
        });

        function fetchExecutiveSummary() {
            $('#contentData').hide();
            $('#contentSkeletons').fadeIn(200);

            const dataType = $('input[name="data_type"]:checked').val();
            const opsel = $('#opselSelect').val();
            const satuan = $('#satuanSelect').val();

            $.ajax({
                url: '{{ route('executive.summary.data') }}',
                data: {
                    data_type: dataType,
                    opsel: opsel
                },
                success: function(res) {
                    $('#contentSkeletons').hide();
                    $('#contentData').fadeIn(300);
                    AOS.refresh();
                    renderAllBlocks(res, satuan);
                },
                error: function(err) {
                    console.error("Failed to load Executive Summary data.");
                    $('#contentSkeletons').hide();
                }
            });
        }

        function createPieChart(containerId, titleText, dataRawObj, opselFocusColors, satuan = 'auto') {
            const dataPts = Object.keys(dataRawObj || {}).map(k => ({
                name: k,
                y: dataRawObj[k].pct,
                vol: dataRawObj[k].total
            }));

            let labelFormat = '<b>{point.name}</b><br>{point.y}%';
            if (satuan === 'angka') {
                labelFormat = '<b>{point.name}</b><br>{point.vol:,.0f}';
            } else if (satuan === 'persen') {
                labelFormat = '<b>{point.name}</b><br>{point.y}%';
            }

            Highcharts.chart(containerId, {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: titleText,
                    verticalAlign: 'middle',
                    y: 0,
                    style: {
                        fontSize: '13px',
                        fontWeight: 'bold',
                        color: '#2a3042'
                    }
                },
                plotOptions: {
                    pie: {
                        innerSize: '70%',
                        dataLabels: {
                            enabled: true,
                            format: labelFormat,
                            distance: 15,
                            style: {
                                fontSize: '11px',
                                fontWeight: 'bold'
                            }
                        },
                        borderWidth: 2,
                        colors: opselFocusColors // Array of custom colors if needed
                    }
                },
                series: [{
                    name: 'Persentase',
                    data: dataPts
                }],
                credits: {
                    enabled: false
                },
                tooltip: {
                    valueSuffix: '%'
                }
            });
        }

        function renderAllBlocks(data, satuan) {
            // Block 03: Nasional KPIs
            $('#val_nas_pergerakan').text(formatJuta(data.nasional.pergerakan));
            $('#val_nas_orang').text(formatJuta(data.nasional.orang));
            $('#val_nas_koefisien').text(data.nasional.koefisien.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            }));
            $('#nar_nas_pergerakan').html(data.nasional.narrative);

            // Block 07: Kesimpulan
            $('#nar_kstmp').html('<strong>Kesimpulan Hasil Pengolahan:</strong><br/><br/>' + data.kesimpulan);

            // Block 04: Peak Cards
            let peakHtml = '';
            if (data.peak && data.peak.list && data.peak.list.length > 0) {
                const gridClass = data.peak.list.length === 3 ? 'col-md-4' : (data.peak.list.length === 2 ? 'col-md-6' :
                    'col-md-12');
                data.peak.list.forEach((p, idx) => {
                    const bg = idx === 0 ? 'bg-primary text-white border-primary shadow' :
                        'bg-white border-light shadow-sm';
                    const text = idx === 0 ? 'text-white' : 'text-navy';
                    const icon = idx === 0 ? '<i class="bx bxs-star me-1 text-warning fs-5"></i> ' : '';

                    peakHtml += `<div class="${gridClass}">
                        <div class="card h-100 border transition hover-shadow ${bg}" style="border-radius: 12px;">
                            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                <div class="fw-bold mb-2 ${text} text-uppercase" style="font-size: 0.9rem; letter-spacing: 0.5px;">${icon}${formatDateShort(p.tanggal)}</div>
                                <div class="fs-3 fw-bolder ${text} font-monospace my-2">${formatNumber(p.total)}</div>
                                <div class="small fw-medium ${text} opacity-75 bg-black bg-opacity-10 py-1 px-3 rounded-pill d-inline-block mx-auto mt-2">${p.pct}% dari akumulasi</div>
                            </div>
                        </div>
                    </div>`;
                });
            } else {
                peakHtml =
                    '<div class="col-12"><div class="alert alert-light text-center border">Data harian tidak mencukupi untuk menentukan puncak pergerakan.</div></div>';
            }
            $('#peak_cards_container').html(peakHtml);

            // Block 04: Peak Trend Chart
            const trendPergerakan = data.trend_pergerakan || {};
            const tpCats = Object.keys(trendPergerakan).map(formatDateShort);
            const tpData = Object.values(trendPergerakan);
            Highcharts.chart('chart_nas_trend', {
                chart: {
                    type: 'areaspline',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: tpCats,
                    crosshair: true,
                    tickmarkPlacement: 'on',
                    labels: {
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Total Pergerakan'
                    },
                    labels: {
                        format: '{value:,.0f}'
                    },
                    gridLineDashStyle: 'Dash'
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.15,
                        color: '#f59e0b',
                        lineWidth: 4,
                        marker: {
                            enabled: true,
                            radius: 5,
                            symbol: 'circle'
                        }
                    }
                },
                series: [{
                    name: 'Pergerakan',
                    data: tpData
                }],
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    shared: true,
                    valueSuffix: ' Pergerakan'
                }
            });

            // Block 05: Orang Trend 
            const trendOrang = data.trend_orang || {};
            Highcharts.chart('chart_nas_orang_trend', {
                chart: {
                    type: 'spline',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: Object.keys(trendOrang).map(formatDateShort),
                    crosshair: true,
                    labels: {
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Unique Subscriber'
                    },
                    gridLineDashStyle: 'Dash'
                },
                plotOptions: {
                    spline: {
                        color: '#2a3042',
                        lineWidth: 4,
                        marker: {
                            enabled: true,
                            radius: 5,
                            symbol: 'circle'
                        }
                    }
                },
                series: [{
                    name: 'Subscriber',
                    data: Object.values(trendOrang)
                }],
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    valueSuffix: ' Individu'
                }
            });

            // Block 06: Opsel Donut (Nasional)
            const opselRaw = data.opsel;
            $('#nar_nas_opsel').html(opselRaw.narrative);
            const masterColors = ['#e11d48', '#f59e0b', '#2563eb']; // Red(TSEL), Yellow(IOH), Blue(XL) - approximations
            createPieChart('chart_opsel_orang', 'Orang (Sub)', opselRaw.orang, masterColors, satuan);
            createPieChart('chart_opsel_pergerakan', 'Pergerakan', opselRaw.pergerakan, masterColors, satuan);

            // Block 10: Opsel Donut (Jabo)
            createPieChart('chart_opsel_intra', 'Intra Jabo (Sub)', data.opsel_intra.orang || {}, masterColors, satuan);
            createPieChart('chart_opsel_inter', 'Inter Jabo (Sub)', data.opsel_inter.orang || {}, masterColors, satuan);

            // Block 08: Forecast vs Real
            const fc = data.forecast || {};
            const fcCats = Object.keys(fc).map(formatDateShort);
            const fcReal = Object.values(fc).map(i => i.real_pct);
            const fcFore = Object.values(fc).map(i => i.fore_pct);
            Highcharts.chart('chart_forecast_comparison', {
                chart: {
                    type: 'line',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: fcCats,
                    crosshair: true,
                    labels: {
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Distribusi / Proporsi Harian (%)'
                    },
                    labels: {
                        format: '{value}%'
                    },
                    gridLineDashStyle: 'Dash'
                },
                plotOptions: {
                    line: {
                        lineWidth: 4,
                        marker: {
                            enabled: true,
                            radius: 5,
                            symbol: 'circle'
                        }
                    }
                },
                series: [{
                        name: 'Hasil Survei (Prakiraan)',
                        data: fcFore,
                        color: '#94a3b8',
                        dashStyle: 'ShortDash'
                    },
                    {
                        name: 'Realisasi MPD Aktual',
                        data: fcReal,
                        color: '#3b82f6'
                    }
                ],
                legend: {
                    verticalAlign: 'top',
                    itemStyle: {
                        fontWeight: 'bold'
                    }
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    shared: true,
                    valueSuffix: '%'
                }
            });

            // Block 09: YoY
            const yoy = data.yoy || {};
            $('#nar_yoy').html(yoy.narrative);
            Highcharts.chart('chart_yoy', {
                chart: {
                    type: 'column',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: ['MPD Angleb 2025<br/><small>(Baseline)</small>',
                        'MPD Angleb 2026<br/><small>(Aktual)</small>'
                    ],
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: null
                    },
                    labels: {
                        enabled: false
                    },
                    gridLineWidth: 0
                },
                plotOptions: {
                    column: {
                        dataLabels: {
                            enabled: true,
                            formatter: function() {
                                return formatJuta(this.y);
                            },
                            style: {
                                fontSize: '14px',
                                fontWeight: 'bold',
                                textOutline: 'none'
                            }
                        },
                        colorByPoint: true,
                        colors: ['#cbd5e1', '#2a3042'],
                        borderRadius: 6,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Total Unique Subscriber',
                    data: [yoy.previous, yoy.current]
                }],
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    pointFormatter: function() {
                        return '<b>' + formatNumber(this.y) + '</b> Individu';
                    }
                }
            });

            // Block 11: Jabo Variables
            $('#val_intra_pergerakan').text(formatJuta(data.intra.pergerakan));
            $('#val_intra_orang').text(formatJuta(data.intra.orang));
            $('#val_intra_koefisien').text(data.intra.koefisien.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            }));
            $('#nar_intra').html(data.intra.narrative);

            $('#val_inter_pergerakan').text(formatJuta(data.inter.pergerakan));
            $('#val_inter_orang').text(formatJuta(data.inter.orang));
            $('#val_inter_koefisien').text(data.inter.koefisien.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            }));
            $('#nar_inter').html(data.inter.narrative);

            // Jabo Trends
            const ti = data.trend_intra || {};
            Highcharts.chart('chart_intra_trend', {
                chart: {
                    type: 'line',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: 'Intra Jabodetabek',
                    style: {
                        fontSize: '13px',
                        color: '#2a3042'
                    }
                },
                xAxis: {
                    categories: Object.keys(ti).map(formatDateShort),
                    crosshair: true,
                    tickmarkPlacement: 'on',
                    labels: {
                        style: {
                            fontSize: '9px'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Individu'
                    },
                    gridLineDashStyle: 'Dash',
                    labels: {
                        format: '{value:,.0f}'
                    }
                },
                series: [{
                    name: 'Subscribers',
                    data: Object.values(ti),
                    color: '#3b82f6',
                    lineWidth: 4
                }],
                plotOptions: {
                    line: {
                        marker: {
                            radius: 4,
                            symbol: 'circle'
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                }
            });
            const te = data.trend_inter || {};
            Highcharts.chart('chart_inter_trend', {
                chart: {
                    type: 'line',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: 'Inter Jabodetabek',
                    style: {
                        fontSize: '13px',
                        color: '#2a3042'
                    }
                },
                xAxis: {
                    categories: Object.keys(te).map(formatDateShort),
                    crosshair: true,
                    tickmarkPlacement: 'on',
                    labels: {
                        style: {
                            fontSize: '9px'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: 'Individu'
                    },
                    gridLineDashStyle: 'Dash',
                    labels: {
                        format: '{value:,.0f}'
                    }
                },
                series: [{
                    name: 'Subscribers',
                    data: Object.values(te),
                    color: '#10b981',
                    lineWidth: 4
                }],
                plotOptions: {
                    line: {
                        marker: {
                            radius: 4,
                            symbol: 'circle'
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                }
            });
        }
    </script>
@endpush
