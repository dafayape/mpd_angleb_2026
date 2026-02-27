@extends('layout.app')

@section('title', 'Data Dasar Pergerakan Nasional')

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

        .content-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            margin-bottom: 24px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .content-card .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        .content-card .card-body {
            padding: 1.5rem;
        }

        .tech-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .tech-list li {
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }

        .tech-list li:last-child {
            border-bottom: none;
        }

        .tech-list li i {
            margin-right: 10px;
            font-size: 1.25rem;
        }

        .arch-step-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1.25rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .bg-soft-primary {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .bg-soft-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .bg-soft-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .bg-soft-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .bg-soft-info {
            background-color: rgba(6, 182, 212, 0.1);
            color: #06b6d4;
        }

        .bg-soft-dark {
            background-color: rgba(30, 41, 59, 0.1);
            color: #1e293b;
        }
    </style>
@endpush

@section('content')
    @component('layout.partials.page-header', ['number' => '02', 'title' => 'Data Dasar Pergerakan Nasional'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#">Nasional</a></li>
            <li class="breadcrumb-item active">Data Dasar</li>
        </ol>
    @endcomponent

    <div class="container-fluid py-2">
        <!-- Main Architecture Banner -->
        <div class="row mb-4" data-aos="fade-down" data-aos-duration="600">
            <div class="col-12">
                <div class="card bg-navy text-white rounded-3 border-0 shadow-lg overflow-hidden position-relative">
                    <div class="position-absolute end-0 top-0 h-100"
                        style="width: 30%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05));"></div>
                    <div class="card-body p-4 d-flex align-items-center position-relative z-1">
                        <div class="bg-white rounded p-3 me-4 shadow-sm">
                            <i class="bx bx-network-chart fs-1 text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-2 fw-bold text-white">Arsitektur & Alur Pemrosesan Platform MPD Angleb 2026</h4>
                            <p class="mb-0 text-white-50" style="font-size: 1.05rem;">
                                Platform ini dirancang untuk memproses jutaan baris data pergerakan masyarakat (MPD) dari
                                Operator Seluler (TSEL, IOH, XL). Secara garis besar, ekosistem <strong>Data Dasar
                                    Nasional</strong> mengimplementasikan mekanisme Data Ingestion berbasis sinkron &
                                asinkron agar beban <strong>PostgreSQL 18</strong> dan <strong>Apache 2.4</strong>
                                terdistribusi optimal melalui manajemen <i>Message Broker & Streaming Layer</i>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 1: Steps 1-3 -->
        <div class="row g-4 mb-4">
            <!-- STEP 1 -->
            <div class="col-md-4 d-flex" data-aos="fade-up" data-aos-delay="0">
                <div class="card content-card w-100 h-100 flex-column">
                    <div class="card-header d-flex align-items-center">
                        <span class="section-badge">1</span>
                        <h5 class="fw-bold text-navy mb-0">Ingestion Web Layer (Gateway)</h5>
                    </div>
                    <div class="card-body bg-light flex-grow-1 d-flex flex-column">
                        <div class="arch-step-icon bg-soft-primary">
                            <i class="bx bx-cloud-upload"></i>
                        </div>
                        <p class="text-muted mb-4" style="line-height: 1.6; text-align: justify;">
                            Sebagai entry point utama, user (Admin/Operator) melakukan upload CSV berukuran besar via Web
                            Dashboard. Server secara khusus mengelola batasan limit file upload dan <i>timeout</i> yang
                            besar
                            menggunakan Apache Event MPM.
                        </p>
                        <div class="bg-white p-3 rounded border mt-auto shadow-sm">
                            <h6 class="fw-bold fs-6 mb-3 text-navy border-bottom pb-2"><i
                                    class="bx bx-server me-2 text-primary"></i>Infrastruktur Endpoint:</h6>
                            <ul class="tech-list">
                                <li><i class="bx bx-server text-primary"></i> <strong>Apache HTTP Server 2.4</strong> (Web
                                    Server)
                                </li>
                                <li><i class="bx bx-cloud text-primary"></i> IDCloudHost Enterprise VPS Infrastructure</li>
                                <li><i class="bx bx-copy-alt text-primary"></i> Form Multipart Bulk CSV Upload</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 2 -->
            <div class="col-md-4 d-flex" data-aos="fade-up" data-aos-delay="100">
                <div class="card content-card w-100 h-100 flex-column">
                    <div class="card-header d-flex align-items-center">
                        <span class="section-badge">2</span>
                        <h5 class="fw-bold text-navy mb-0">API Server & Validator</h5>
                    </div>
                    <div class="card-body bg-light flex-grow-1 d-flex flex-column">
                        <div class="arch-step-icon bg-soft-success">
                            <i class="bx bx-shield-quarter"></i>
                        </div>
                        <p class="text-muted mb-4" style="line-height: 1.6; text-align: justify;">
                            Laravel 12 API bertugas menerima stream CSV per-chunk (Offset Streaming) secara live AJAX dari
                            Apache. Ia memvalidasi integritas row, header, kesesuaian OPSEL, dan date format tanpa memakan
                            VRAM secara berlebihan.
                        </p>
                        <div class="bg-white p-3 rounded border mt-auto shadow-sm">
                            <h6 class="fw-bold fs-6 mb-3 text-navy border-bottom pb-2"><i
                                    class="bx bx-check-shield me-2 text-success"></i>Validasi & Parsing:</h6>
                            <ul class="tech-list">
                                <li><i class="bx bx-code-block text-success"></i> <strong>Laravel 12</strong> (Core
                                    Application
                                    Server)
                                </li>
                                <li><i class="bx bx-code-block text-success"></i> <strong>PHP 8.4</strong> (Fast JIT
                                    Processing)</li>
                                <li><i class="bx bx-bolt-circle text-success"></i> Memory-efficient Generator Chunking
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3 -->
            <div class="col-md-4 d-flex" data-aos="fade-up" data-aos-delay="200">
                <div class="card content-card w-100 h-100 flex-column">
                    <div class="card-header d-flex align-items-center">
                        <span class="section-badge">3</span>
                        <h5 class="fw-bold text-navy mb-0">Message Broker / Queue Layer</h5>
                    </div>
                    <div class="card-body bg-light flex-grow-1 d-flex flex-column">
                        <div class="arch-step-icon bg-soft-warning">
                            <i class="bx bx-transfer-alt"></i>
                        </div>
                        <p class="text-muted mb-4" style="line-height: 1.6; text-align: justify;">
                            Data valid dari API / CSV diproyeksikan ke buffer memory queue. Skema ini mencegah blocking
                            response web saat <i>heavy spatial extraction</i> (ETL) berdurasi puluhan menit.
                        </p>
                        <div class="bg-white p-3 rounded border mt-auto shadow-sm">
                            <h6 class="fw-bold fs-6 mb-3 text-navy border-bottom pb-2"><i
                                    class="bx bx-layer me-2 text-warning"></i>Message Streaming:</h6>
                            <ul class="tech-list">
                                <li><i class="bx bx-data text-warning"></i> <strong>Redis Server 8.0.5</strong> (Broker
                                    utama)
                                </li>
                                <li><i class="bx bx-list-ul text-warning"></i> Laravel Scheduler & Redis Streams Queue
                                </li>
                                <li><i class="bx bx-terminal text-warning"></i> Supervisor (Daemon Background Monitor)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 2: Steps 4-6 -->
        <div class="row g-4 mb-5">
            <!-- STEP 4 -->
            <div class="col-md-4 d-flex" data-aos="fade-up" data-aos-delay="300">
                <div class="card content-card w-100 h-100 flex-column">
                    <div class="card-header d-flex align-items-center">
                        <span class="section-badge">4</span>
                        <h5 class="fw-bold text-navy mb-0">Processing Engine (ETL Spasial)</h5>
                    </div>
                    <div class="card-body bg-light flex-grow-1 d-flex flex-column">
                        <div class="arch-step-icon bg-soft-danger">
                            <i class="bx bx-cog"></i>
                        </div>
                        <p class="text-muted mb-4" style="line-height: 1.6; text-align: justify;">
                            Worker daemon PHP melakukan Extract-Transform-Load (ETL) asinkron: dari sinkronisasi tabel
                            referensi desa/kecamatan (BPS) hingga konversi atribut Geometris titik (ST_MakePoint).
                        </p>
                        <div class="bg-white p-3 rounded border mt-auto shadow-sm">
                            <h6 class="fw-bold fs-6 mb-3 text-navy border-bottom pb-2"><i
                                    class="bx bx-slider-alt me-2 text-danger"></i>Job Engine Tools:</h6>
                            <ul class="tech-list">
                                <li><i class="bx bx-cog text-danger"></i> Daemon Queue Workers (PHP CLI)</li>
                                <li><i class="bx bx-git-branch text-danger"></i> Algoritma Pemetaan Unique Subscriber
                                    OD</li>
                                <li><i class="bx bx-refresh text-danger"></i> Batch Insert `upsert()` Optimization</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 5 -->
            <div class="col-md-4 d-flex" data-aos="fade-up" data-aos-delay="400">
                <div class="card content-card w-100 h-100 flex-column">
                    <div class="card-header d-flex align-items-center">
                        <span class="section-badge">5</span>
                        <h5 class="fw-bold text-navy mb-0">Data Warehouse (Storage Layer)</h5>
                    </div>
                    <div class="card-body bg-light flex-grow-1 d-flex flex-column">
                        <div class="arch-step-icon bg-soft-info">
                            <i class="bx bx-list-ul"></i>
                        </div>
                        <p class="text-muted mb-4" style="line-height: 1.6; text-align: justify;">
                            Hasil <i>clean data</i> yang telah di-transformasi spasial & agregat koefisien disimpan di Data
                            Warehouse relasional super-cepat untuk menyajikan laporan (kueri OLAP).
                        </p>
                        <div class="bg-white p-3 rounded border mt-auto shadow-sm">
                            <h6 class="fw-bold fs-6 mb-3 text-navy border-bottom pb-2"><i
                                    class="bx bx-data me-2 text-info"></i>Storage & Indexing:</h6>
                            <ul class="tech-list">
                                <li><i class="bx bx-data text-info"></i> <strong>PostgreSQL 18</strong> (Database
                                    Core)</li>
                                <li><i class="bx bx-map-alt text-info"></i> <strong>PostGIS 3.6</strong> (Ekstensi
                                    Geospasial Vector)</li>
                                <li><i class="bx bxs-component text-info"></i> Partitioning & B-Tree Spatial/GIST Indexes
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 6 -->
            <div class="col-md-4 d-flex" data-aos="fade-up" data-aos-delay="500">
                <div class="card content-card w-100 h-100 flex-column">
                    <div class="card-header d-flex align-items-center">
                        <span class="section-badge">6</span>
                        <h5 class="fw-bold text-navy mb-0">Presentation (Aplikasi Web)</h5>
                    </div>
                    <div class="card-body bg-light flex-grow-1 d-flex flex-column">
                        <div class="arch-step-icon bg-soft-dark">
                            <i class="bx bx-pie-chart-alt-2"></i>
                        </div>
                        <p class="text-muted mb-4" style="line-height: 1.6; text-align: justify;">
                            Output final berupa Dashboard Interaktif, Peta Spasial Lalu Lintas (Heatmaps & Nodes), serta
                            Executive Summary yang diakses oleh Pimpinan tanpa hambatan loading dengan Cache redis.
                        </p>
                        <div class="bg-white p-3 rounded border mt-auto shadow-sm">
                            <h6 class="fw-bold fs-6 mb-3 text-navy border-bottom pb-2"><i
                                    class="bx bx-devices me-2 text-dark"></i>Visualisasi Dashboard:</h6>
                            <ul class="tech-list">
                                <li><i class="bx bxl-bootstrap text-dark"></i> Bootstrap 5 & jQuery (Interactive DOM)</li>
                                <li><i class="bx bx-pie-chart-alt-2 text-dark"></i> <strong>Highcharts.js &
                                        Leaflet.js</strong> (Grafik
                                    & Geoportal)</li>
                                <li><i class="bx bx-bolt-circle text-dark"></i> Cache Redis Analytics Query</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    once: true,
                    offset: 50,
                    duration: 600
                });
            }
        });
    </script>
@endpush
