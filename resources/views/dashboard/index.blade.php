@extends('layout.app')

@section('title', 'Dashboard Utama')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Dashboard Utama</h4>
                <div class="page-title-right">
                    {{-- Button removed as requested --}}
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay Kalender -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0 text-primary">Overlay Kalender Angkutan Lebaran 2026</h6>
                </div>
                <div class="card-body overflow-auto">
                    {{-- Simple Custom Calendar Visualization --}}
                    <div style="min-width: 800px;">
                        <h6 class="text-center fw-bold mb-3">MARET 2026</h6>
                        <div class="d-flex border text-center font-size-12">
                            <!-- Header Days -->
                            @php
                                $days = [
                                    ['tgl' => 11, 'hari' => 'Rabu', 'h' => 'H-10', 'bg' => ''],
                                    ['tgl' => 12, 'hari' => 'Kamis', 'h' => 'H-9', 'bg' => ''],
                                    ['tgl' => 13, 'hari' => 'Jumat', 'h' => 'H-8', 'bg' => ''],
                                    ['tgl' => 14, 'hari' => 'Sabtu', 'h' => 'H-7', 'bg' => 'bg-danger text-white'],
                                    ['tgl' => 15, 'hari' => 'Minggu', 'h' => 'H-6', 'bg' => 'bg-danger text-white'],
                                    ['tgl' => 16, 'hari' => 'Senin', 'h' => 'H-5', 'bg' => 'bg-warning bg-soft'],
                                    ['tgl' => 17, 'hari' => 'Selasa', 'h' => 'H-4', 'bg' => 'bg-warning bg-soft'],
                                    ['tgl' => 18, 'hari' => 'Rabu', 'h' => 'H-3', 'bg' => 'bg-warning'],
                                    [
                                        'tgl' => 19,
                                        'hari' => 'Kamis',
                                        'h' => 'H-2',
                                        'bg' => 'bg-success text-white',
                                        'note' => 'Nyepi',
                                    ],
                                    ['tgl' => 20, 'hari' => 'Jumat', 'h' => 'H-1', 'bg' => 'bg-warning'],
                                    [
                                        'tgl' => 21,
                                        'hari' => 'Sabtu',
                                        'h' => 'H',
                                        'bg' => 'bg-success text-white',
                                        'note' => 'Lebaran',
                                    ],
                                    [
                                        'tgl' => 22,
                                        'hari' => 'Minggu',
                                        'h' => 'H+1',
                                        'bg' => 'bg-success text-white',
                                        'note' => 'Lebaran',
                                    ],
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
                                <div class="flex-fill border-end" style="width: 5%;">
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
                        <div class="position-relative mt-2" style="height: 60px;">
                            <!-- Nyepi -->
                            <div class="position-absolute text-center bg-success text-white rounded p-1 shadow-sm"
                                style="left: 40%; width: 5%; font-size: 10px; top: 0;">Hari Raya Nyepi</div>
                            <!-- Lebaran -->
                            <div class="position-absolute text-center bg-success text-white rounded p-1 shadow-sm"
                                style="left: 50%; width: 10%; font-size: 10px; top: 0;">Hari Raya Lebaran</div>

                            <!-- Cuti Bersama -->
                            <div class="position-absolute text-center bg-warning bg-soft rounded p-1"
                                style="left: 25%; width: 10%; font-size: 10px; top: 30px;">Potensi WFA</div>
                            <div class="position-absolute text-center bg-warning rounded p-1"
                                style="left: 35%; width: 5%; font-size: 10px; top: 30px;">Cuti Nyepi</div>
                            <div class="position-absolute text-center bg-warning rounded p-1"
                                style="left: 45%; width: 25%; font-size: 10px; top: 30px;">Cuti Bersama Lebaran</div>
                            <div class="position-absolute text-center bg-warning bg-soft rounded p-1"
                                style="left: 70%; width: 15%; font-size: 10px; top: 30px;">Potensi WFA</div>
                        </div>
                        <!-- Posko Bar -->
                        <div class="mt-2 text-center text-white fw-bold py-1 rounded"
                            style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%); width: 100%;">
                            PELAKSANAAN POSKO ANGLEB 2026
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Highlight Alert --}}
    <div class="alert alert-info border-0 bg-info-subtle text-info" role="alert">
        <div class="d-flex align-items-start">
            <div class="flex-grow-1">
                <h5 class="alert-heading font-size-14 text-info"><i class="mdi mdi-information-outline me-1"></i>
                    Highlight
                    Survei Angkutan Lebaran 2026</h5>
                <p class="mb-0 font-size-13">Dalam rangka persiapan dan pelaksanaan layanan Angkutan
                    Lebaran (Angleb) tahun
                    2026, Badan Kebijakan Transportasi melaksanakan survei online pada periode
                    <strong>15 - 30 Januari
                        2026</strong> untuk mengidentifikasi preferensi dan mengukur persepsi masyarakat
                    yang akan melakukan
                    perjalanan. Survei tersebut bekerja sama dengan <strong>LAPI ITB, BPS, dan
                        Kementerian Komdigi.</strong>
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
                    <p class="text-muted mb-0 small" style="text-align: justify;">Periode Lebaran
                        menjadi salah satu momen
                        dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan
                        signifikan pergerakan
                        antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada
                        tanggal 21 dan 22 Maret
                        2026 sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal 19 Maret 2026.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">Metodelogi MPD</h5>
                    <hr class="mt-0 mb-3 opacity-25">
                    <p class="text-muted mb-0 small" style="text-align: justify;">Periode Lebaran
                        menjadi salah satu momen
                        dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan
                        signifikan pergerakan
                        antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada
                        tanggal 21 dan 22 Maret
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
                        <h2 class="mb-0 fw-bold text-primary display-6">
                            {{ number_format($total_real, 0, ',', '.') }} <span
                                class="fs-6 text-muted fw-normal">Pergerakan</span></h2>
                        <small class="text-muted">Target Forecast:
                            {{ number_format($total_forecast, 0, ',', '.') }}</small>
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
                    <h5 class="card-title mb-0 text-primary fw-bold">Perbandingan Pergerakan Dengan
                        Tahun Lalu</h5>
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
                        <small class="text-muted d-block ms-1" style="font-size: 11px;">Target /
                            Forecast</small>
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
                        <small class="text-muted d-block ms-1" style="font-size: 11px;">Aktual
                            (Real)</small>
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
                    <h5 class="card-title mb-0 text-primary fw-bold">Tren Jumlah Orang dan Pergerakan
                        per OPSEL</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                        <div id="chart-opsel" class="w-100" style="height: 380px;"></div>
                    </div>

                    <div
                        class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start rounded-3">
                        <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1 text-info font-size-14">Analisis
                                Trend Orang</h6>
                            <p class="mb-0 small" style="line-height: 1.5;">{!! $analysis['opsel'] !!}
                            </p>
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
                    <h5 class="card-title mb-0 text-primary fw-bold">Tren Pergerakan Harian per Moda
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Chart --}}
                    <div id="chart-moda" class="w-100" style="height: 400px;"></div>
                    <p class="text-center fw-bold mt-2 text-muted small fst-italic">Grafik menunjukkan
                        tren pergerakan
                        harian untuk setiap moda transportasi (13 Mar - 29 Mar 2026)</p>


                    <div
                        class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start rounded-3">
                        <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1 text-info font-size-14">Analisis Moda
                                Transportasi</h6>
                            <p class="mb-0 small" style="line-height: 1.5;">{!! $analysis['moda'] !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* CSS for Monthly Grid Calendar */
        .calendar-grid-table th,
        .calendar-grid-table td {
            border: 1px solid #ced4da;
            vertical-align: top;
        }

        .calendar-grid-table th {
            text-align: center;
            padding: 8px;
            font-size: 13px;
            background-color: #f8f9fa;
        }

        .calendar-grid-table td {
            padding: 10px;
            width: 14.28%;
            font-size: 13px;
        }

        .calendar-date-header {
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .calendar-task-list {
            padding-left: 20px;
            margin-bottom: 0;
            color: #495057;
        }

        .calendar-task-list li {
            margin-bottom: 4px;
        }

        .calendar-task-list li:last-child {
            margin-bottom: 0;
        }

        /* Custom Background Colors */
        .bg-empty-day {
            background-color: #e9ecef;
            opacity: 0.5;
        }

        .bg-beta-ready {
            background-color: #ffebd6;
            /* Light orange/yellow */
        }

        .bg-posko-period {
            background-color: #e0f2f1;
            /* Light teal/cyan */
        }

        .bg-deliverable {
            background-color: #ffebd6;
            /* Match the Beta Ready color for emphasis */
        }

        /* Ensure table is responsive */
        .table-responsive-xl {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .min-w-1000 {
            min-width: 1000px;
        }

        /* Horizontal Calendar Timeline */
        .timeline-calendar {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 20px;
        }

        .timeline-wrapper {
            min-width: 900px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .timeline-grid {
            display: grid;
            grid-template-columns: repeat(18, 1fr);
            /* 18 days: 13 to 30 Mar */
            text-align: center;
        }

        .timeline-day-header {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #495057;
            padding: 5px 0;
            background-color: #f1f5f7;
            border: 1px solid #fff;
        }

        .timeline-date-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid #fff;
            padding: 10px 0;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            height: 60px;
        }

        /* Date Colors based on mockup */
        .date-bg-gray {
            background-color: #fff;
            border: 1px solid #e9ecef;
            color: #495057;
        }

        .date-bg-red {
            background-color: #f46a6a;
        }

        .date-bg-yellow {
            background-color: #f1b44c;
        }

        .date-bg-green {
            background-color: #34c38f;
        }

        .timeline-h-label {
            font-size: 10px;
            color: #74788d;
            background-color: #e9ecef;
            padding: 4px 0;
            border: 1px solid #fff;
        }

        /* Event Bars Area */
        .event-bars-container {
            display: grid;
            grid-template-columns: repeat(18, 1fr);
            position: relative;
            gap: 2px;
            margin-top: 10px;
        }

        .event-bar {
            border-radius: 4px;
            padding: 4px 0;
            font-size: 9px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
        }

        /* Event Colors */
        .event-wfa {
            background-color: #fef0d7;
            color: #c0852e;
            border-top: 2px solid #f1b44c;
        }

        .event-nyepi-cuti {
            background-color: #f1b44c;
            color: #fff;
        }

        .event-nyepi-hari {
            background-color: #34c38f;
            color: #fff;
        }

        .event-lebaran-cuti {
            background-color: #f1b44c;
            color: #fff;
            border-top: 2px solid #34c38f;
        }

        .event-lebaran-hari {
            background-color: #34c38f;
            color: #fff;
            border-top: 2px solid #34c38f;
        }

        .event-sekolah {
            background-color: #e2e3e5;
            color: #383d41;
            border-left: 2px solid #6c757d;
            border-right: 2px solid #6c757d;
            border-radius: 0;
        }

        .event-posko {
            background-color: #2ab57d;
            /* BKT Blue/Teal */
            color: #ffffff;
            background-color: #3b82f6;
            /* Blueish */
            border-radius: 4px;
        }

        .timeline-grid-lines {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            display: grid;
            grid-template-columns: repeat(18, 1fr);
            pointer-events: none;
        }

        .timeline-grid-lines>div {
            border-right: 1px dashed #eff2f7;
        }

        .timeline-grid-lines>div:last-child {
            border-right: none;
        }

        /* Grid Spans for Events */
        .col-span-1 {
            grid-column: span 1;
        }

        .col-span-2 {
            grid-column: span 2;
        }

        .col-span-3 {
            grid-column: span 3;
        }

        .col-span-4 {
            grid-column: span 4;
        }

        .col-span-5 {
            grid-column: span 5;
        }

        .col-span-18 {
            grid-column: span 18;
        }

        .start-col-4 {
            grid-column-start: 4;
        }

        .start-col-6 {
            grid-column-start: 6;
        }

        .start-col-7 {
            grid-column-start: 7;
        }

        .start-col-8 {
            grid-column-start: 8;
        }

        .start-col-13 {
            grid-column-start: 13;
        }
    </style>
@endpush

@section('content')

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
