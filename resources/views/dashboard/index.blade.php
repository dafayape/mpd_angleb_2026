@extends('layout.app')

@section('title', 'Dashboard Utama')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Dashboard Utama</h4>
                <div class="page-title-right">
                    <button type="button"
                        class="btn btn-primary btn-sm waves-effect waves-light shadow-sm d-flex align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#timelineModal">
                        <i class="bx bx-calendar-event fs-5"></i>
                        <span>Lihat Jadwal / Timeline</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Highlight Alert --}}
    <div class="alert alert-info border-0 bg-info-subtle text-info" role="alert">
        <div class="d-flex align-items-start">
            <div class="flex-grow-1">
                <h5 class="alert-heading font-size-14 text-info"><i class="mdi mdi-information-outline me-1"></i> Highlight
                    Survei Angkutan Lebaran 2026</h5>
                <p class="mb-0 font-size-13">Dalam rangka persiapan dan pelaksanaan layanan Angkutan Lebaran (Angleb) tahun
                    2026, Badan Kebijakan Transportasi melaksanakan survei online pada periode <strong>15 - 30 Januari
                        2026</strong> untuk mengidentifikasi preferensi dan mengukur persepsi masyarakat yang akan melakukan
                    perjalanan. Survei tersebut bekerja sama dengan <strong>LAPI ITB, BPS, dan Kementerian Komdigi.</strong>
                </p>
            </div>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">Latar Belakang MPD</h5>
                    <hr class="mt-0 mb-3 opacity-25">
                    <p class="text-muted mb-0 small" style="text-align: justify;">Periode Lebaran menjadi salah satu momen
                        dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan signifikan pergerakan
                        antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada tanggal 21 dan 22 Maret
                        2026 sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal 19 Maret 2026.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">Metodelogi MPD</h5>
                    <hr class="mt-0 mb-3 opacity-25">
                    <p class="text-muted mb-0 small" style="text-align: justify;">Periode Lebaran menjadi salah satu momen
                        dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan signifikan pergerakan
                        antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada tanggal 21 dan 22 Maret
                        2026 sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal 19 Maret 2026.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        {{-- Left Column: Stats --}}
        <div class="col-xl-4 d-flex flex-column gap-4">
            {{-- Total Orang dan Pergerakan --}}
            <div class="card shadow-sm mb-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title mb-0 text-primary fw-bold">Total Pergerakan (Aktual)</h5>
                </div>
                <div class="card-body pb-4">
                    <p class="mb-2 text-muted fw-medium small">Total Akumulasi (13-29 Mar)</p>
                    <div class="mb-3">
                        <h2 class="mb-0 fw-bold text-primary display-6">{{ number_format($total_real, 0, ',', '.') }} <span
                                class="fs-6 text-muted fw-normal">Pergerakan</span></h2>
                        <small class="text-muted">Target Forecast: {{ number_format($total_forecast, 0, ',', '.') }}</small>
                    </div>
                    <div class="alert alert-success bg-success-subtle text-success border-0 mb-0 py-2 px-3">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-check-circle-outline me-2 fs-5"></i>
                            <span class="small fst-italic">{!! strip_tags($analysis['general']) !!}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Perbandingan Pergerakan --}}
            <div class="card shadow-sm mb-0 flex-grow-1">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title mb-0 text-primary fw-bold">Perbandingan Pergerakan Dengan Tahun Lalu</h5>
                </div>
                <div class="card-body">
                    <p class="mb-4 fw-bold small text-uppercase text-muted">Perbandingan Pergerakan</p>

                    @php
                        $maxValue = max($total_real, $total_forecast);
                        $widthReal = $maxValue > 0 ? ($total_real / $maxValue) * 100 : 0;
                        $widthForecast = $maxValue > 0 ? ($total_forecast / $maxValue) * 100 : 0;
                    @endphp

                    {{-- Horizontal Bar: Forecast (Blue) --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-1">
                            <div class="flex-grow-1 bg-light rounded-pill" style="height: 25px; overflow: hidden;">
                                <div class="bg-primary h-100 d-flex align-items-center justify-content-end pe-2 text-white fw-bold small"
                                    style="width: {{ $widthForecast }}%;">
                                </div>
                            </div>
                            <div class="ms-3 fw-bold text-primary text-end" style="width: 100px;">
                                {{ number_format($total_forecast, 0, ',', '.') }}</div>
                        </div>
                        <small class="text-muted d-block ms-1" style="font-size: 11px;">Target / Forecast</small>
                    </div>

                    {{-- Horizontal Bar: Real (Yellow) --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-1">
                            <div class="flex-grow-1 bg-light rounded-pill" style="height: 25px; overflow: hidden;">
                                <div class="bg-warning h-100 d-flex align-items-center justify-content-end pe-2 text-white fw-bold small"
                                    style="width: {{ $widthReal }}%;">
                                </div>
                            </div>
                            <div class="ms-3 fw-bold text-warning text-end" style="width: 100px;">
                                {{ number_format($total_real, 0, ',', '.') }}</div>
                        </div>
                        <small class="text-muted d-block ms-1" style="font-size: 11px;">Aktual (Real)</small>
                    </div>

                    <div class="mt-auto">
                        <span
                            class="badge {{ $persen_capaian >= 100 ? 'bg-success-subtle text-success' : 'bg-success-subtle text-success' }} fs-5 fw-bold px-3 py-2">
                            +{{ number_format($persen_capaian - 100, 1) }}%
                        </span>
                        <span class="text-muted ms-2 small">dari target forecast</span>
                    </div>

                </div>
            </div>
        </div>

        {{-- Right Column: Chart per OPSEL --}}
        <div class="col-xl-8">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title mb-0 text-primary fw-bold">Tren Jumlah Orang dan Pergerakan per OPSEL</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                        <div id="chart-opsel" class="w-100" style="height: 380px;"></div>
                    </div>

                    <div
                        class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start rounded-3">
                        <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1 text-info font-size-14">Analisis Trend Orang</h6>
                            <p class="mb-0 small" style="line-height: 1.5;">{!! $analysis['opsel'] !!}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Chart: Per Moda --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title mb-0 text-primary fw-bold">Tren Pergerakan Harian per Moda</h5>
                </div>
                <div class="card-body">
                    {{-- Chart --}}
                    <div id="chart-moda" class="w-100" style="height: 400px;"></div>
                    <p class="text-center fw-bold mt-2 text-muted small fst-italic">Grafik menunjukkan tren pergerakan
                        harian untuk setiap moda transportasi (13 Mar - 29 Mar 2026)</p>


                    <div
                        class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start rounded-3">
                        <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1 text-info font-size-14">Analisis Moda Transportasi</h6>
                            <p class="mb-0 small" style="line-height: 1.5;">{!! $analysis['moda'] !!}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* CSS for Vertical Timeline in Modal */
        .timeline-overlay {
            position: relative;
            padding: 20px 0;
        }

        .timeline-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 45px;
            width: 3px;
            background: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-icon {
            position: absolute;
            left: 28px;
            top: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            background: #fff;
            border: 3px solid #556ee6;
            color: #556ee6;
            font-size: 16px;
            z-index: 1;
        }

        .timeline-icon.bg-success {
            border-color: #34c38f;
            color: #34c38f;
        }

        .timeline-icon.bg-warning {
            border-color: #f1b44c;
            color: #f1b44c;
        }

        .timeline-content {
            margin-left: 90px;
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .timeline-content h5 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 15px;
            font-weight: 600;
        }

        .timeline-content .date {
            display: inline-block;
            font-size: 12px;
            color: #74788d;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .timeline-content ul {
            padding-left: 20px;
            margin-bottom: 0;
            font-size: 13px;
            color: #495057;
        }

        .timeline-content ul li {
            margin-bottom: 4px;
        }
    </style>
@endpush

@section('content')
    <!-- Timeline Modal -->
    <div class="modal fade" id="timelineModal" tabindex="-1" aria-labelledby="timelineModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="timelineModalLabel">
                        <i class="bx bx-calendar-event me-2"></i> Jadwal & Rincian Pengolahan Data
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="timeline-overlay">

                        <!-- Tahap 1 -->
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="bx bx-cog"></i>
                            </div>
                            <div class="timeline-content">
                                <h5 class="text-primary">Tahap Persiapan & Finalisasi Sistem</h5>
                                <span class="date"><i class="bx bx-time-five me-1"></i> 23 Februari - 12 Maret
                                    2026</span>
                                <ul>
                                    <li>Penyusunan daftar kebutuhan analisis, output, dan sinkronisasi laporan.</li>
                                    <li>Review <em>keynote material</em> MPD.</li>
                                    <li>Pengembangan, uji coba (beta testing), dan finalisasi sistem oleh tim IT.</li>
                                    <li><strong>Target 6 Maret 2026:</strong> Kesiapan sistem 100%.</li>
                                    <li>Validasi data dummy dari Opsel untuk memastikan format dan struktur tabel.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Tahap 2 -->
                        <div class="timeline-item">
                            <div class="timeline-icon bg-warning">
                                <i class="bx bx-data"></i>
                            </div>
                            <div class="timeline-content border-warning border-start border-4">
                                <h5 class="text-warning">Tahap Pengolahan Data (Periode Posko)</h5>
                                <span class="date"><i class="bx bx-time-five me-1"></i> 13 Maret - 30 Maret 2026</span>
                                <ul>
                                    <li><strong>Penarikan Data Harian Opsel:</strong> Rekam pergerakan harian mulai dari H-8
                                        Lebaran hingga H+9 Lebaran secara rutin.</li>
                                    <li><strong>Quality Control (QC) Data Harian:</strong> Memeriksa indikasi anomali,
                                        ketidaklengkapan data (null), dan standar struktur data masuk.</li>
                                    <li><strong>Monitoring Kestabilan Sistem:</strong> Memastikan kelancaran sistem dalam
                                        menangani injeksi data besar.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Tahap 3 -->
                        <div class="timeline-item">
                            <div class="timeline-icon bg-success">
                                <i class="bx bx-book-content"></i>
                            </div>
                            <div class="timeline-content border-success border-start border-4">
                                <h5 class="text-success">Tahap Penyusunan Laporan & Finalisasi</h5>
                                <span class="date"><i class="bx bx-time-five me-1"></i> 31 Maret - 12 April 2026</span>
                                <ul>
                                    <li><strong>31 Mar - 1 Apr:</strong> Finalisasi laporan hasil keseluruhan olah data MPD
                                        Angkutan Lebaran 2026.</li>
                                    <li><strong>2 Apr - 12 Apr:</strong> Penyusunan Dokumen <em>Policy Paper</em> dan
                                        <em>Policy Brief</em>.
                                    </li>
                                    <li>Penyerahan output rekomendasi penyempurnaan sistem MPD.</li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Timeline Modal -->
@endsection

@push('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartOpsel = @json($chart_opsel);
            const chartModa = @json($chart_moda);

            // Chart 1: Opsel (Column: Real vs Forecast)
            Highcharts.chart('chart-opsel', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: ''
                },
                xAxis: {
                    categories: chartOpsel.categories,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Jumlah Pergerakan'
                    },
                    labels: {
                        formatter: function() {
                            return this.value / 1000 + 'rb';
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
                        pointPadding: 0.1,
                        borderWidth: 0,
                        borderRadius: 5
                    }
                },
                series: chartOpsel.series,
                credits: {
                    enabled: false
                }
            });

            // Chart 2: Moda (Line: Daily Trend)
            Highcharts.chart('chart-moda', {
                chart: {
                    type: 'spline' // Smoother line
                },
                title: {
                    text: ''
                },
                yAxis: {
                    title: {
                        text: 'Jumlah Pergerakan'
                    },
                    labels: {
                        formatter: function() {
                            return this.value / 1000 + 'rb';
                        }
                    }
                },
                xAxis: {
                    categories: chartModa.categories,
                    accessibility: {
                        rangeDescription: 'Range: 13 Mar to 29 Mar'
                    }
                },
                legend: {
                    layout: 'horizontal',
                    align: 'center',
                    verticalAlign: 'bottom'
                },
                plotOptions: {
                    series: {
                        label: {
                            connectorAllowed: false
                        },
                        marker: {
                            enabled: true,
                            radius: 4
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    valueValues: true,
                    valueDecimals: 0,
                    valueSuffix: ' pergerakan'
                },
                series: chartModa.series,
                credits: {
                    enabled: false
                }
            });
        });
    </script>
@endpush
