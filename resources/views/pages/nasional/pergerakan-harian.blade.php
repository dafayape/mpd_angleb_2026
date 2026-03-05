@extends('layout.app')

@section('title', 'Pergerakan Harian Nasional')

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

        .bg-tsel {
            background-color: #ef4444 !important;
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

        .table-custom-header th {
            vertical-align: middle;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .table-custom-body td,
        .table-custom-body th {
            vertical-align: middle;
            font-size: 0.85rem;
            border: 1px solid #e2e8f0;
            padding: 0.5rem;
        }

        .content-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            overflow: visible !important;
            background: white;
        }

        /* Highcharts Export Menu Fix */
        .highcharts-contextmenu {
            z-index: 9999 !important;
        }

        .highcharts-container {
            overflow: visible !important;
        }

        /* Export Dropdown Styling */
        .export-dropdown {
            position: relative;
            display: inline-block;
            margin-left: auto;
        }

        .export-dropdown .export-btn {
            background-color: #2a3042;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .export-dropdown .export-btn:hover {
            background-color: #1e2230;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(42, 48, 66, 0.3);
        }

        .export-dropdown .export-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 4px;
            min-width: 180px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .export-dropdown .export-menu.show {
            display: block;
        }

        .export-dropdown .export-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            font-size: 0.85rem;
            color: #334155;
            cursor: pointer;
            transition: background 0.15s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .export-dropdown .export-menu-item:hover {
            background-color: #f1f5f9;
            color: #2a3042;
        }

        .export-dropdown .export-menu-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .analysis-box {
            background: rgba(42, 48, 66, 0.03);
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
            padding: 1rem;
            margin-top: 1rem;
        }

        /* 03 Section Custom Styling */
        .table-03 th,
        .table-03 td {
            border: 1px solid #111;
            text-align: center;
            vertical-align: middle;
            padding: 6px;
            font-size: 0.8rem;
        }

        .table-03 th {
            color: white;
        }

        .chart-title-badge {
            background-color: #dbe4eb;
            border: 1px solid #999;
            border-radius: 4px;
            padding: 6px 20px;
            font-weight: bold;
            color: #333;
            display: inline-block;
            position: absolute;
            top: -16px;
            right: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-size: 1.15rem;
            z-index: 2;
        }

        .summary-box-03 {
            background-color: #f8f9fa;
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
    </style>
@endpush

@section('content')
    @component('layout.partials.page-header', ['number' => '03', 'title' => 'Pergerakan Harian Nasional'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#">Nasional</a></li>
            <li class="breadcrumb-item active">Pergerakan Harian</li>
        </ol>
    @endcomponent

    @php
        // Helper to safely format numbers, if 0 show 0 instead of empty.
        function fmtNum($val)
        {
            return $val == 0 ? '0' : number_format($val, 0, ',', '.');
        }
        function fmtPct($val)
        {
            return $val == 0 ? '0,00%' : number_format($val, 2, ',', '.') . '%';
        }

        // Setup Opsels
        $opselsConfig = [
            'XL' => ['name' => 'XL', 'bg_class' => 'bg-navy', 'text_class' => 'text-primary'],
            'IOH' => ['name' => 'IOH', 'bg_class' => 'bg-amber', 'text_class' => 'text-warning'],
            'TSEL' => ['name' => 'TSEL', 'bg_class' => 'bg-tsel', 'text_class' => 'text-danger'],
        ];

        // Setup chart data for Section 03
        $datesArrForChart = [];
        $movementPctChart = [];
        $peoplePctChart = [];
        $totMovAll = $data['akumulasi']['total_movement'] ?? 0;
        $totPplAll = $data['akumulasi']['total_people'] ?? 0;

        // Setup chart data for Section 04
        $series04_mov = ['XL' => [], 'IOH' => [], 'TSEL' => []];
        $series04_ppl = ['XL' => [], 'IOH' => [], 'TSEL' => []];

        foreach ($dates as $d) {
            $dt = \Carbon\Carbon::parse($d)->locale('id');
            // e.g "Kamis 18\nDesember 2025" -> we map to array for ApexCharts line breaks
            $datesArrForChart[] = [$dt->isoFormat('dddd D'), $dt->isoFormat('MMMM YYYY')];

            $mdDaily = $data['akumulasi']['daily'][$d]['movement'] ?? 0;
            $movementPctChart[] = $totMovAll > 0 ? round(($mdDaily / $totMovAll) * 100, 2) : 0;

            $ppDaily = $data['akumulasi']['daily'][$d]['people'] ?? 0;
            $peoplePctChart[] = $totPplAll > 0 ? round(($ppDaily / $totPplAll) * 100, 2) : 0;

            // Data per opsel for Section 04
            foreach (['XL', 'IOH', 'TSEL'] as $op) {
                $series04_mov[$op][] = $data['daily'][$d][$op]['movement'] ?? 0;
                $series04_ppl[$op][] = $data['daily'][$d][$op]['people'] ?? 0;
            }
        }
    @endphp

    <div class="row mb-4" data-aos="fade-up" data-aos-duration="600">
        <div class="col-12">
            <div class="card content-card w-100 flex-column" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">01</span>
                    <h5 class="fw-bold text-navy mb-0">Persandingan pergerakan harian total berdasarkan masing-masing opsel
                    </h5>
                    <div class="export-dropdown ms-auto">
                        <button class="export-btn" onclick="toggleExportMenu('export-menu-01')">
                            <i class="bx bx-download"></i> Export
                        </button>
                        <div class="export-menu" id="export-menu-01">
                            <button class="export-menu-item" onclick="exportSection01PNG()">
                                <i class="bx bx-image text-primary"></i> Download PNG
                            </button>
                            <button class="export-menu-item" onclick="exportSection01CSV()">
                                <i class="bx bx-spreadsheet text-success"></i> Download CSV (XLSX)
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light" id="section-01-content" style="padding: 1.5rem;">
                    <div class="row g-3">
                        @foreach ($opselsConfig as $opKey => $conf)
                            <div class="col-xl-4 col-lg-12 d-flex">
                                <div class="card w-100 shadow-sm border-0 d-flex flex-column h-100 overflow-hidden">
                                    <div class="table-responsive flex-grow-1">
                                        <table class="table table-bordered mb-0 text-center table-custom-body w-100">
                                            <thead class="{{ $conf['bg_class'] }} text-white table-custom-header">
                                                <tr>
                                                    <th rowspan="3" class="align-middle" style="width: 25%;">Hari,
                                                        Tanggal</th>
                                                    <th colspan="4" class="py-2 text-center">{{ $conf['name'] }}</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" class="py-2 border-bottom-0"><small
                                                            class="fw-normal">Jumlah Pergerakan</small></th>
                                                    <th colspan="2" class="py-2 border-bottom-0"><small
                                                            class="fw-normal">Jumlah Orang</small></th>
                                                </tr>
                                                <tr>
                                                    <th style="width: 18.75%;" class="py-2 border-top-0">Jumlah</th>
                                                    <th style="width: 18.75%;" class="py-2 border-top-0">%</th>
                                                    <th style="width: 18.75%;" class="py-2 border-top-0">Jumlah</th>
                                                    <th style="width: 18.75%;" class="py-2 border-top-0">%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($dates as $dateRaw)
                                                    @php
                                                        $parsedDate = \Carbon\Carbon::parse($dateRaw);
                                                        $labelHariTanggal = $parsedDate
                                                            ->locale('id')
                                                            ->isoFormat('dddd, D MMMM YYYY');

                                                        $row = $data['daily'][$dateRaw][$opKey] ?? null;
                                                        $mov = $row['movement'] ?? 0;
                                                        $movPct = $row['movement_pct'] ?? 0;
                                                        $ppl = $row['people'] ?? 0;
                                                        $pplPct = $row['people_pct'] ?? 0;
                                                    @endphp
                                                    <tr>
                                                        <td class="text-start fw-medium text-dark bg-light">
                                                            {{ $labelHariTanggal }}</td>
                                                        <td>{{ fmtNum($mov) }}</td>
                                                        <td class="text-muted bg-light">{{ fmtPct($movPct) }}</td>
                                                        <td>{{ fmtNum($ppl) }}</td>
                                                        <td class="text-muted bg-light">{{ fmtPct($pplPct) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="{{ $conf['bg_class'] }} text-white font-weight-bold">
                                                <tr>
                                                    <th class="text-start">Total</th>
                                                    @php
                                                        $totMov = $data['totals'][$opKey]['movement'] ?? 0;
                                                        $totPpl = $data['totals'][$opKey]['people'] ?? 0;
                                                    @endphp
                                                    <th>{{ fmtNum($totMov) }}</th>
                                                    <th>100%</th>
                                                    <th>{{ fmtNum($totPpl) }}</th>
                                                    <th>100%</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="px-3 pb-3 pt-3 mt-auto bg-white border-top">
                                        <div class="analysis-box mt-0 border-0"
                                            style="background: rgba(42, 48, 66, 0.03); border-left: 4px solid {{ $opKey === 'XL' ? '#2a3042' : ($opKey === 'IOH' ? '#f59e0b' : '#ef4444') }} !important; border-radius: 4px; padding: 1rem;">
                                            <h6 class="fw-bold fs-6 mb-2 {{ $conf['text_class'] }}">
                                                <i class="bx bx-bar-chart-alt-2 me-1"></i> Kesimpulan Analisis
                                                ({{ $conf['name'] }})
                                            </h6>
                                            <p class="mb-0 text-muted"
                                                style="font-size: 0.85rem; line-height: 1.5; text-align: justify;">
                                                @if ($totMov > 0)
                                                    Berdasarkan akumulasi tanggal 13 - 30 Maret 2026, total pergerakan yang
                                                    terekam oleh <strong>{{ $conf['name'] }}</strong> adalah
                                                    <strong>{{ fmtNum($totMov) }}</strong>, mencakup
                                                    <strong>{{ fmtNum($totPpl) }}</strong> target orang.
                                                @else
                                                    Pada rentang waktu ini, belum terdapat rekaman observasi pergerakan yang
                                                    valid secara menyeluruh untuk operator
                                                    <strong>{{ $conf['name'] }}</strong>.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 02 AKUMULASI -->
    <div class="row mt-4 mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="col-12">
            <div class="card content-card w-100 flex-column" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">02</span>
                    <h5 class="fw-bold text-navy mb-0">Akumulasi Pergerakan Harian
                    </h5>
                    <div class="export-dropdown ms-auto">
                        <button class="export-btn" onclick="toggleExportMenu('export-menu-02')">
                            <i class="bx bx-download"></i> Export
                        </button>
                        <div class="export-menu" id="export-menu-02">
                            <button class="export-menu-item" onclick="exportSection02PNG()">
                                <i class="bx bx-image text-primary"></i> Download PNG
                            </button>
                            <button class="export-menu-item" onclick="exportSection02CSV()">
                                <i class="bx bx-spreadsheet text-success"></i> Download CSV (XLSX)
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light" id="section-02-content" style="padding: 1.5rem;">
                    <div class="card w-100 shadow-sm border-0 d-flex flex-column mb-4">
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-bordered border-dark table-hover mb-0 text-center align-middle"
                                style="font-size: 0.85rem;">
                                <thead class="text-dark">
                                    <tr>
                                        <th rowspan="3" class="align-middle border-dark text-start px-4"
                                            style="width: 25%; background-color: #dbe4eb; font-weight: bold;">AKUMULASI
                                        </th>
                                        <th colspan="4" class="py-2 text-center border-dark"
                                            style="background-color: #dbe4eb; font-weight: bold;">Akumulasi</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="py-2 text-center border-dark"
                                            style="background-color: #dbe4eb; font-weight: bold;"><small class="fw-bold"
                                                style="font-size: 0.9rem;">Jumlah Pergerakan</small></th>
                                        <th colspan="2" class="py-2 text-center border-dark"
                                            style="background-color: #e7ebf0; font-weight: bold;"><small class="fw-bold"
                                                style="font-size: 0.9rem;">Jumlah Orang Harian</small></th>
                                    </tr>
                                    <tr>
                                        <th style="width: 18.75%; background-color: #ffffff;"
                                            class="py-2 border-dark text-dark fw-bold">Jumlah</th>
                                        <th style="width: 18.75%; background-color: #ffffff;"
                                            class="py-2 border-dark text-dark fw-bold">%</th>
                                        <th style="width: 18.75%; background-color: #ffffff;"
                                            class="py-2 border-dark text-dark fw-bold">Jumlah</th>
                                        <th style="width: 18.75%; background-color: #ffffff;"
                                            class="py-2 border-dark text-dark fw-bold">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dates as $dateRaw)
                                        @php
                                            $row = $data['akumulasi']['daily'][$dateRaw] ?? null;
                                            $mov = $row['movement'] ?? 0;
                                            $movPct = $row['movement_pct'] ?? 0;
                                            $ppl = $row['people'] ?? 0;
                                            $pplPct = $row['people_pct'] ?? 0;
                                            $carbonDate = \Carbon\Carbon::parse($dateRaw)->locale('id');
                                            $labelHariTanggal = $carbonDate->isoFormat('dddd, D MMMM YYYY');
                                        @endphp
                                        <tr>
                                            <td class="text-start text-dark border-dark"
                                                style="background-color: #fafafa;">
                                                {{ $labelHariTanggal }}</td>
                                            <td class="border-dark">{{ fmtNum($mov) }}</td>
                                            <td class="text-dark border-dark" style="background-color: #fafafa;">
                                                {{ fmtPct($movPct) }}</td>
                                            <td class="border-dark">{{ fmtNum($ppl) }}</td>
                                            <td class="text-dark border-dark" style="background-color: #fafafa;">
                                                {{ fmtPct($pplPct) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="font-weight-bold border-dark">
                                    <tr>
                                        <th class="text-center pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #ffffff; font-size: 1rem;">Total</th>
                                        <th class="pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #ffffff; font-size: 1rem;">
                                            {{ fmtNum($data['akumulasi']['total_movement']) }}</th>
                                        <th class="pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #ffffff; font-size: 1rem;">100%</th>
                                        <th class="pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #ffffff; font-size: 1rem;">
                                            {{ fmtNum($data['akumulasi']['total_people']) }}</th>
                                        <th class="pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #ffffff; font-size: 1rem;">100%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary Dashboard -->
                <div class="row mt-4 mb-5">
                    <div class="col-12">
                        <div class="card content-card" style="background-color: #eef2f5; border: none; box-shadow: none;">
                            <div class="card-body p-4">
                                <div class="row text-center mb-4 px-2">
                                    <!-- Box 1 -->
                                    <div class="col-md-4 mb-3 mb-md-0 px-2">
                                        <div class="card h-100 mb-0 border-0"
                                            style="background-color: #3b4b5e; color: white; border-radius: 8px;">
                                            <div class="card-body p-3 d-flex flex-column justify-content-center">
                                                <h6 class="mb-3 text-uppercase text-white letter-spacing-1"
                                                    style="font-size: 0.85rem;">Jumlah Pergerakan</h6>
                                                <div class="bg-white text-dark rounded py-3 px-2 mx-1 shadow-sm">
                                                    <h4 class="mb-1 fw-bold text-dark" style="font-size: 1.4rem;">
                                                        {{ number_format($data['akumulasi']['total_movement'] / 1000000, 2, ',', '.') }}
                                                        Juta</h4>
                                                    <small class="text-muted fw-medium font-size-12">Pergerakan</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Box 2 -->
                                    <div class="col-md-4 mb-3 mb-md-0 px-2">
                                        <div class="card h-100 mb-0 border-0"
                                            style="background-color: #eb7c22; color: white; border-radius: 8px;">
                                            <div class="card-body p-3 d-flex flex-column justify-content-center">
                                                <h6 class="mb-3 text-uppercase text-white letter-spacing-1"
                                                    style="font-size: 0.85rem;">Rata-Rata Koefisien</h6>
                                                <div class="bg-white text-dark rounded py-3 px-2 mx-1 shadow-sm d-flex flex-column justify-content-center h-100"
                                                    style="min-height: 80px;">
                                                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 1.8rem;">
                                                        {{ number_format($data['akumulasi']['koefisien'], 2, ',', '.') }}
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Box 3 -->
                                    <div class="col-md-4 px-2">
                                        <div class="card h-100 mb-0 border-0"
                                            style="background-color: #3b4b5e; color: white; border-radius: 8px;">
                                            <div class="card-body p-3 d-flex flex-column justify-content-center">
                                                <h6 class="mb-3 text-uppercase text-white letter-spacing-1"
                                                    style="font-size: 0.85rem; line-height: 1.3;">Jumlah Orang
                                                    Yang<br>Melakukan Perjalanan</h6>
                                                <div class="bg-white text-dark rounded py-2 px-2 mx-1 shadow-sm d-flex align-items-center justify-content-center h-100"
                                                    style="min-height: 80px;">
                                                    <div class="text-start me-3 border-end pe-3" style="line-height:1.2;">
                                                        <small class="text-muted d-block fw-medium"
                                                            style="font-size:0.75rem;">Jumlah Unik</small>
                                                        <small class="text-muted d-block fw-medium"
                                                            style="font-size:0.75rem;">Subscriber:</small>
                                                    </div>
                                                    <div class="text-center">
                                                        <h4 class="mb-1 fw-bold text-dark" style="font-size: 1.4rem;">
                                                            {{ number_format($data['akumulasi']['unique_subscriber'] / 1000000, 2, ',', '.') }}
                                                            juta</h4>
                                                        <small class="text-muted fw-medium font-size-12">masyarakat</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <ul class="mb-0 text-dark"
                                    style="font-size: 0.95rem; line-height: 1.6; padding-left: 2rem;">
                                    <li class="mb-2"><strong>Jumlah orang/individu unik subscriber</strong> yang
                                        melakukan
                                        pergerakan pada Periode Angkutan Lebaran 2026, dengan nilai realisasi tanggal 13 s/d
                                        30
                                        Maret 2026 adalah <strong>{{ fmtNum($data['akumulasi']['unique_subscriber']) }}
                                            orang.</strong></li>
                                    <li class="mb-2"><strong>Jumlah pergerakan/movement count</strong> pada periode
                                        tersebut
                                        adalah sebesar <strong>{{ fmtNum($data['akumulasi']['total_movement']) }}
                                            pergerakan</strong>, dengan <strong>puncak</strong> pergerakan di tanggal
                                        <strong>{{ isset($data['akumulasi']['peak_days'][0])? \Carbon\Carbon::parse($data['akumulasi']['peak_days'][0])->locale('id')->isoFormat('D MMMM YYYY'): '-' }}</strong>
                                        sebesar
                                        <strong>{{ isset($data['akumulasi']['peak_days'][0]) ? fmtNum($data['akumulasi']['daily'][$data['akumulasi']['peak_days'][0]]['movement']) : 0 }}
                                            pergerakan</strong> dan
                                        <strong>{{ isset($data['akumulasi']['peak_days'][1])? \Carbon\Carbon::parse($data['akumulasi']['peak_days'][1])->locale('id')->isoFormat('D MMMM YYYY'): '-' }}</strong>
                                        dengan
                                        <strong>{{ isset($data['akumulasi']['peak_days'][1]) ? fmtNum($data['akumulasi']['daily'][$data['akumulasi']['peak_days'][1]]['movement']) : 0 }}
                                            pergerakan</strong>.
                                    </li>
                                    <li>Dari data akumulasi tersebut maka data ini menunjukkan bahwa terdapat rata-rata
                                        lebih
                                        dari satu perjalanan per individu selama periode pengamatan, dengan <strong>rasio
                                            sekitar {{ number_format($data['akumulasi']['koefisien'], 2, ',', '.') }} kali
                                            perjalanan per orang</strong>, yang sekaligus menggambarkan <strong>tingginya
                                            aktivitas mobilitas masyarakat</strong>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>

    <!-- 03 PERGERAKAN HARIAN TOTAL -->
    <div class="row mt-4 mb-5" data-aos="fade-up" data-aos-delay="200">
        <div class="col-12">
            <div class="card content-card w-100 flex-column" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">03</span>
                    <h5 class="fw-bold text-navy mb-0">Pergerakan Harian Total (Pergerakan per hari dan orang per hari)
                    </h5>
                    <div class="export-dropdown ms-auto">
                        <button class="export-btn" onclick="toggleExportMenu('export-menu-03')">
                            <i class="bx bx-download"></i> Export
                        </button>
                        <div class="export-menu" id="export-menu-03" style="min-width: 260px;">
                            <div class="px-3 py-2 fw-bold text-muted"
                                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #eee;">
                                Pergerakan per Hari</div>
                            <button class="export-menu-item" onclick="exportSection03PNG('movement','chart')">
                                <i class="bx bx-line-chart text-primary"></i> PNG — Chart saja
                            </button>
                            <button class="export-menu-item" onclick="exportSection03PNG('movement','table')">
                                <i class="bx bx-table text-primary"></i> PNG — Tabel saja
                            </button>
                            <button class="export-menu-item" onclick="exportSection03CSV('movement')">
                                <i class="bx bx-spreadsheet text-success"></i> Download CSV (XLSX)
                            </button>
                            <div class="px-3 py-2 fw-bold text-muted"
                                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #eee; border-top: 1px solid #eee;">
                                Orang per Hari</div>
                            <button class="export-menu-item" onclick="exportSection03PNG('people','chart')">
                                <i class="bx bx-line-chart text-info"></i> PNG — Chart saja
                            </button>
                            <button class="export-menu-item" onclick="exportSection03PNG('people','table')">
                                <i class="bx bx-table text-info"></i> PNG — Tabel saja
                            </button>
                            <button class="export-menu-item" onclick="exportSection03CSV('people')">
                                <i class="bx bx-spreadsheet text-success"></i> Download CSV (XLSX)
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">

                    <!-- BLOCK 1: PERGERAKAN PER HARI -->
                    <div class="position-relative border rounded p-3 mb-5" id="section-03-movement-both"
                        style="border-color: #798797 !important; border-width: 2px !important; border-radius: 12px !important;">
                        <div class="chart-title-badge">PERGERAKAN PER HARI</div>

                        <div id="section-03-movement-chart">
                            <div id="chart-movement" style="min-height: 250px; margin-top: 20px;"></div>
                        </div>

                        <div id="section-03-movement-table">
                            <div class="row mt-3 g-0">
                                <div class="col-xl-9 col-lg-8 pe-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0 table-03 text-center align-middle"
                                            style="min-width: 1300px;">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" class="align-middle"
                                                        style="background-color: #2a3042; width: 80px;">Tanggal</th>
                                                    @foreach ($dates as $d)
                                                        <th style="background-color: #486284;">
                                                            <div style="font-size: 0.75rem;">{!! \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D-MMM-YY') !!}</div>
                                                        </th>
                                                    @endforeach
                                                    <th rowspan="2" class="align-middle"
                                                        style="background-color: #2a3042; width: 100px;">Total</th>
                                                </tr>
                                                <tr>
                                                    @foreach ($dates as $i => $d)
                                                        <th style="background-color: #5a7395; font-size: 0.7rem;">
                                                            H{{ $i < 7 ? $i - 7 : ($i == 7 ? '' : '+' . ($i - 7)) }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold text-dark" style="background-color: #f8f9fa;">
                                                        Jumlah
                                                    </td>
                                                    @foreach ($dates as $d)
                                                        <td class="fw-bold text-dark">
                                                            {{ fmtNum($data['akumulasi']['daily'][$d]['movement'] ?? 0) }}
                                                        </td>
                                                    @endforeach
                                                    <td class="fw-bold text-dark" style="font-size:0.9rem;">
                                                        {{ fmtNum($totMovAll) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4">
                                    <div class="summary-box-03 border border-dark border-2 rounded">
                                        <div style="font-size: 0.95rem; line-height: 1.5; color: #222;">
                                            Total pergerakan pada periode<br>
                                            <strong>13 Maret 2026 s/d 30 Maret 2026</strong> mencapai<br>
                                            <span class="highlight text-dark"
                                                style="background-color: #fef08a !important; padding: 6px 15px; font-size: 1.25rem; font-weight: 800; border-radius: 4px; display:inline-block; margin-top: 10px;">{{ fmtNum($totMovAll) }}
                                                Pergerakan</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BLOCK 2: ORANG PER HARI -->
                    <div class="position-relative border rounded p-3 mt-4" id="section-03-people-both"
                        style="border-color: #798797 !important; border-width: 2px !important; border-radius: 12px !important;">
                        <div class="chart-title-badge">ORANG PER HARI</div>

                        <div id="section-03-people-chart">
                            <div id="chart-people" style="min-height: 250px; margin-top: 20px;"></div>
                        </div>

                        <div id="section-03-people-table">
                            <div class="row mt-3 g-0">
                                <div class="col-xl-9 col-lg-8 pe-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0 table-03 text-center align-middle"
                                            style="min-width: 1300px;">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" class="align-middle"
                                                        style="background-color: #2a3042; width: 80px;">Tanggal</th>
                                                    @foreach ($dates as $d)
                                                        <th style="background-color: #1e6082;">
                                                            <div style="font-size: 0.75rem;">{!! \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D-MMM-YY') !!}</div>
                                                        </th>
                                                    @endforeach
                                                    <th rowspan="2" class="align-middle"
                                                        style="background-color: #2a3042; width: 100px;">Total</th>
                                                </tr>
                                                <tr>
                                                    @foreach ($dates as $i => $d)
                                                        <th style="background-color: #29769e; font-size: 0.7rem;">
                                                            H{{ $i < 7 ? $i - 7 : ($i == 7 ? '' : '+' . ($i - 7)) }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold text-dark" style="background-color: #f8f9fa;">
                                                        Jumlah
                                                    </td>
                                                    @foreach ($dates as $d)
                                                        <td class="fw-bold text-dark">
                                                            {{ fmtNum($data['akumulasi']['daily'][$d]['people'] ?? 0) }}
                                                        </td>
                                                    @endforeach
                                                    <td class="fw-bold text-dark" style="font-size:0.9rem;">
                                                        {{ fmtNum($totPplAll) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4">
                                    <div class="summary-box-03 border border-dark border-2 rounded">
                                        <div style="font-size: 0.95rem; line-height: 1.5; color: #222;">
                                            Total orang pada periode<br>
                                            <strong>13 Maret 2026 s/d 30 Maret 2026</strong> mencapai<br>
                                            <span class="highlight text-dark"
                                                style="background-color: #fef08a !important; padding: 6px 15px; font-size: 1.25rem; font-weight: 800; border-radius: 4px; display:inline-block; margin-top: 10px;">{{ fmtNum($totPplAll) }}
                                                Orang</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- 04 PERSANDINGAN PERGERAKAN OPSEL -->
    <div class="row mt-4 mb-5" data-aos="fade-up" data-aos-delay="300">
        <div class="col-12">
            <div class="card content-card w-100 flex-column" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">04</span>
                    <h5 class="fw-bold text-navy mb-0">Persandingan pergerakan harian total berdasarkan masing-masing opsel
                        (Pergerakan per hari dan orang per hari)
                    </h5>
                    <div class="export-dropdown ms-auto">
                        <button class="export-btn" onclick="toggleExportMenu('export-menu-04')">
                            <i class="bx bx-download"></i> Export
                        </button>
                        <div class="export-menu" id="export-menu-04" style="min-width: 260px;">
                            <div class="px-3 py-2 fw-bold text-muted"
                                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #eee;">
                                Pergerakan per Hari</div>
                            <button class="export-menu-item" onclick="exportSection04PNG('movement','chart')">
                                <i class="bx bx-line-chart text-primary"></i> PNG — Chart saja
                            </button>
                            <button class="export-menu-item" onclick="exportSection04PNG('movement','table')">
                                <i class="bx bx-table text-primary"></i> PNG — Tabel saja
                            </button>
                            <button class="export-menu-item" onclick="exportSection04CSV('movement')">
                                <i class="bx bx-spreadsheet text-success"></i> Download CSV (XLSX)
                            </button>
                            <div class="px-3 py-2 fw-bold text-muted"
                                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #eee; border-top: 1px solid #eee;">
                                Orang per Hari</div>
                            <button class="export-menu-item" onclick="exportSection04PNG('people','chart')">
                                <i class="bx bx-line-chart text-info"></i> PNG — Chart saja
                            </button>
                            <button class="export-menu-item" onclick="exportSection04PNG('people','table')">
                                <i class="bx bx-table text-info"></i> PNG — Tabel saja
                            </button>
                            <button class="export-menu-item" onclick="exportSection04CSV('people')">
                                <i class="bx bx-spreadsheet text-success"></i> Download CSV (XLSX)
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">

                    <!-- BLOCK 1: PERGERAKAN PER HARI -->
                    <div class="position-relative border rounded p-3 mb-5"
                        style="border-color: #798797 !important; border-width: 2px !important; border-radius: 12px !important;">
                        <div class="chart-title-badge">PERGERAKAN PER HARI</div>

                        <div id="section-04-movement-chart">
                            <div id="chart-movement-04" style="min-height: 250px; margin-top: 20px;"></div>
                        </div>

                        <div id="section-04-movement-table">
                            <div class="row mt-3 g-0">
                                <div class="col-xl-9 col-lg-8 pe-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0 table-03 text-center align-middle"
                                            style="min-width: 1400px;">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" class="align-middle"
                                                        style="background-color: #2a3042; width: 80px;">Tanggal</th>
                                                    @foreach ($dates as $i => $d)
                                                        <th style="background-color: #486284;">
                                                            <div style="font-size: 0.75rem;">
                                                                H{{ $i < 7 ? $i - 7 : ($i == 7 ? '' : '+' . ($i - 7)) }}
                                                            </div>
                                                        </th>
                                                    @endforeach
                                                    <th rowspan="2" class="align-middle"
                                                        style="background-color: #2a3042; width: 100px;">Total</th>
                                                </tr>
                                                <tr>
                                                    @foreach ($dates as $d)
                                                        <th style="background-color: #5a7395; font-size: 0.7rem;">
                                                            {!! \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D-MMM-YY') !!}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach (['XL', 'IOH', 'TSEL'] as $op)
                                                    <tr>
                                                        <td class="fw-bold text-dark" style="background-color: #f8f9fa;">
                                                            {{ $op }}</td>
                                                        @foreach ($dates as $d)
                                                            <td class="text-dark">
                                                                {{ fmtNum($data['daily'][$d][$op]['movement'] ?? 0) }}</td>
                                                        @endforeach
                                                        <td class="fw-bold text-dark" style="font-size:0.9rem;">
                                                            {{ fmtNum($data['totals'][$op]['movement'] ?? 0) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td class="fw-bold text-dark" style="background-color: #e0e0e0;">TOTAL
                                                    </td>
                                                    @foreach ($dates as $d)
                                                        <td class="fw-bold text-dark" style="background-color: #e0e0e0;">
                                                            {{ fmtNum($data['akumulasi']['daily'][$d]['movement'] ?? 0) }}
                                                        </td>
                                                    @endforeach
                                                    <td class="fw-bold text-dark"
                                                        style="background-color: #e0e0e0; font-size:0.9rem;">
                                                        {{ fmtNum($totMovAll) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4">
                                    <div class="d-flex flex-column justify-content-between h-100">
                                        @php
                                            // Specific order: TSEL, IOH, XL
                                            $orderBoxes = [
                                                'TSEL' => [
                                                    'Total Pergerakan<br>MPD Tsel',
                                                    '#ef4444',
                                                    $data['totals']['TSEL']['movement'] ?? 0,
                                                ],
                                                'IOH' => [
                                                    'Total Pergerakan<br>MPD IOH',
                                                    '#f59e0b',
                                                    $data['totals']['IOH']['movement'] ?? 0,
                                                ],
                                                'XL' => [
                                                    'Total Pergerakan<br>MPD XL',
                                                    '#2a3042',
                                                    $data['totals']['XL']['movement'] ?? 0,
                                                ],
                                            ];
                                        @endphp
                                        @foreach ($orderBoxes as $idx => $box)
                                            <div class="summary-box-03 border border-dark border-2 rounded mb-2 py-2">
                                                <div style="font-size: 0.85rem; line-height: 1.3; color: #333;">
                                                    {!! $box[0] !!}<br>
                                                    <span class="highlight d-inline-block mt-1"
                                                        style="color: {{ $box[1] }}; background-color: #fef08a !important; padding: 4px 12px; font-size: 1.15rem; font-weight: 800; border-radius: 4px;">{{ number_format($box[2] / 1000000, 2, ',', '.') }}
                                                        Juta</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BLOCK 2: ORANG PER HARI -->
                    <div class="position-relative border rounded p-3 mt-4"
                        style="border-color: #798797 !important; border-width: 2px !important; border-radius: 12px !important;">
                        <div class="chart-title-badge">ORANG PER HARI</div>

                        <div id="section-04-people-chart">
                            <div id="chart-people-04" style="min-height: 250px; margin-top: 20px;"></div>
                        </div>

                        <div id="section-04-people-table">

                            <div class="row mt-3 g-0">
                                <div class="col-xl-9 col-lg-8 pe-2">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0 table-03 text-center align-middle"
                                            style="min-width: 1400px;">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" class="align-middle"
                                                        style="background-color: #2a3042; width: 80px;">Tanggal</th>
                                                    @foreach ($dates as $i => $d)
                                                        <th style="background-color: #1e6082;">
                                                            <div style="font-size: 0.75rem;">
                                                                H{{ $i < 7 ? $i - 7 : ($i == 7 ? '' : '+' . ($i - 7)) }}
                                                            </div>
                                                        </th>
                                                    @endforeach
                                                    <th rowspan="2" class="align-middle"
                                                        style="background-color: #2a3042; width: 100px;">Total</th>
                                                </tr>
                                                <tr>
                                                    @foreach ($dates as $d)
                                                        <th style="background-color: #29769e; font-size: 0.7rem;">
                                                            {!! \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D-MMM-YY') !!}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach (['XL', 'IOH', 'TSEL'] as $op)
                                                    <tr>
                                                        <td class="fw-bold text-dark" style="background-color: #f8f9fa;">
                                                            {{ $op }}</td>
                                                        @foreach ($dates as $d)
                                                            <td class="text-dark">
                                                                {{ fmtNum($data['daily'][$d][$op]['people'] ?? 0) }}</td>
                                                        @endforeach
                                                        <td class="fw-bold text-dark" style="font-size:0.9rem;">
                                                            {{ fmtNum($data['totals'][$op]['people'] ?? 0) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td class="fw-bold text-dark" style="background-color: #e0e0e0;">TOTAL
                                                    </td>
                                                    @foreach ($dates as $d)
                                                        <td class="fw-bold text-dark" style="background-color: #e0e0e0;">
                                                            {{ fmtNum($data['akumulasi']['daily'][$d]['people'] ?? 0) }}
                                                        </td>
                                                    @endforeach
                                                    <td class="fw-bold text-dark"
                                                        style="background-color: #e0e0e0; font-size:0.9rem;">
                                                        {{ fmtNum($totPplAll) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-4">
                                    <div class="d-flex flex-column justify-content-between h-100">
                                        @php
                                            // Specific order: TSEL, IOH, XL
                                            $orderBoxes = [
                                                'TSEL' => [
                                                    'Total Orang<br>MPD Tsel',
                                                    '#ef4444',
                                                    $data['totals']['TSEL']['people'] ?? 0,
                                                ],
                                                'IOH' => [
                                                    'Total Orang<br>MPD IOH',
                                                    '#f59e0b',
                                                    $data['totals']['IOH']['people'] ?? 0,
                                                ],
                                                'XL' => [
                                                    'Total Orang<br>MPD XL',
                                                    '#2a3042',
                                                    $data['totals']['XL']['people'] ?? 0,
                                                ],
                                            ];
                                        @endphp
                                        @foreach ($orderBoxes as $idx => $box)
                                            <div class="summary-box-03 border border-dark border-2 rounded mb-2 py-2">
                                                <div style="font-size: 0.85rem; line-height: 1.3; color: #333;">
                                                    {!! $box[0] !!}<br>
                                                    <span class="highlight d-inline-block mt-1"
                                                        style="color: {{ $box[1] }}; background-color: #fef08a !important; padding: 4px 12px; font-size: 1.15rem; font-weight: 800; border-radius: 4px;">{{ number_format($box[2] / 1000000, 2, ',', '.') }}
                                                        Juta Orang</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
    <style>
        .section-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 0.9rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .chart-title-badge {
            position: absolute;
            top: -15px;
            left: 20px;
            background-color: #798797;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            z-index: 10;
        }

        .summary-box-03 {
            background-color: #eef2f5;
            padding: 20px;
            border-radius: 8px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .table-03 thead th {
            color: white;
            border-color: #555 !important;
            font-weight: normal;
            font-size: 0.8rem;
            padding: 0.5rem 0.2rem;
        }

        .table-03 tbody td {
            font-size: 0.8rem;
            padding: 0.5rem 0.2rem;
            border-color: #dee2e6 !important;
        }

        .table-03 tbody tr:nth-child(even) td {
            background-color: #f2f2f2;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <!-- html2canvas for PNG export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- SheetJS for XLSX export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    once: true,
                    offset: 50,
                    duration: 600
                });
            }

            // Data for Highcharts
            const datesLabels = {!! json_encode($datesArrForChart) !!};
            // Flatten 2D array datesLabels (e.g., ["Kamis 18", "Maret 2026"]) to single string separated by space
            const categories = datesLabels.map(d => d.join(' '));

            const movPcts = {!! json_encode($movementPctChart) !!};
            const pplPcts = {!! json_encode($peoplePctChart) !!};

            const seriesXLMov = {!! json_encode($series04_mov['XL']) !!};
            const seriesIOHMov = {!! json_encode($series04_mov['IOH']) !!};
            const seriesTSELMov = {!! json_encode($series04_mov['TSEL']) !!};

            const seriesXLPpl = {!! json_encode($series04_ppl['XL']) !!};
            const seriesIOHPpl = {!! json_encode($series04_ppl['IOH']) !!};
            const seriesTSELPpl = {!! json_encode($series04_ppl['TSEL']) !!};

            // Common Exporting options - PNG & CSV only, no hamburger menu
            const exportConfig = {
                enabled: true,
                buttons: {
                    contextButton: {
                        enabled: false
                    }
                }
            };

            const commonOptions = {
                chart: {
                    type: 'column',
                    height: 260
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories,
                    labels: {
                        style: {
                            fontSize: '10.5px'
                        }
                    },
                    crosshair: true
                },
                yAxis: {
                    title: {
                        text: null
                    },
                    labels: {
                        format: '{value}%'
                    },
                    min: 0
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y:.2f}%</b>'
                },
                plotOptions: {
                    column: {
                        dataLabels: {
                            enabled: true,
                            format: '{y:.2f}%',
                            style: {
                                color: '#555',
                                fontSize: '11px',
                                textOutline: 'none'
                            }
                        }
                    }
                },
                credits: {
                    enabled: false
                },
                exporting: exportConfig,
                legend: {
                    enabled: false
                }
            };

            if (document.querySelector("#chart-movement")) {
                Highcharts.chart('chart-movement', Highcharts.merge(commonOptions, {
                    colors: ['#1e6082'],
                    series: [{
                        name: 'Pergerakan',
                        data: movPcts
                    }]
                }));
            }

            if (document.querySelector("#chart-people")) {
                Highcharts.chart('chart-people', Highcharts.merge(commonOptions, {
                    colors: ['#1e6082'],
                    series: [{
                        name: 'Orang',
                        data: pplPcts
                    }]
                }));
            }

            // Section 04
            const commonOptions04 = {
                chart: {
                    type: 'column',
                    height: 260
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: categories,
                    labels: {
                        style: {
                            fontSize: '10.5px'
                        }
                    },
                    crosshair: true
                },
                yAxis: {
                    title: {
                        text: null
                    },
                    labels: {
                        formatter: function() {
                            return this.value.toLocaleString('id-ID');
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    pointFormatter: function() {
                        return '<br/><span style="color:' + this.color + '">\u25CF</span> ' + this.series
                            .name + ': <b>' + this.y.toLocaleString('id-ID') + '</b>';
                    }
                },
                plotOptions: {
                    column: {
                        dataLabels: {
                            enabled: false
                        }
                    }
                },
                colors: ['#2a3042', '#f59e0b', '#ef4444'], // XL, IOH, TSEL
                credits: {
                    enabled: false
                },
                legend: {
                    align: 'right',
                    verticalAlign: 'middle',
                    layout: 'vertical'
                },
                exporting: exportConfig
            };

            if (document.querySelector("#chart-movement-04")) {
                Highcharts.chart('chart-movement-04', Highcharts.merge(commonOptions04, {
                    series: [{
                            name: 'XLSmart',
                            data: seriesXLMov
                        },
                        {
                            name: 'IOH',
                            data: seriesIOHMov
                        },
                        {
                            name: 'Tsel',
                            data: seriesTSELMov
                        }
                    ]
                }));
            }

            if (document.querySelector("#chart-people-04")) {
                Highcharts.chart('chart-people-04', Highcharts.merge(commonOptions04, {
                    series: [{
                            name: 'XLSmart',
                            data: seriesXLPpl
                        },
                        {
                            name: 'IOH',
                            data: seriesIOHPpl
                        },
                        {
                            name: 'Tsel',
                            data: seriesTSELPpl
                        }
                    ]
                }));
            }

            // =========================================
            // Section 01 Custom Export Functions
            // =========================================

            // Toggle export dropdown menu
            window.toggleExportMenu = function(menuId) {
                const menu = document.getElementById(menuId);
                // Close other menus first
                document.querySelectorAll('.export-menu').forEach(m => {
                    if (m.id !== menuId) m.classList.remove('show');
                });
                menu.classList.toggle('show');
            };

            // Close menus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.export-dropdown')) {
                    document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));
                }
            });

            // Export Section 01 as PNG (all 3 opsel tables in 1 image, WITHOUT kesimpulan)
            window.exportSection01PNG = function() {
                document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));
                const section = document.querySelector('#section-01-content');
                if (!section) {
                    alert('Section 01 tidak ditemukan.');
                    return;
                }

                // Show loading
                const btn = document.querySelector('#export-menu-01').previousElementSibling;
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Processing...';
                btn.disabled = true;

                // 1. Hide all analysis/kesimpulan boxes
                const analysisBoxes = section.querySelectorAll('.analysis-box');
                const analysisParents = [];
                analysisBoxes.forEach(box => {
                    const parent = box.closest('.px-3.pb-3.pt-3');
                    if (parent) {
                        analysisParents.push({
                            el: parent,
                            display: parent.style.display
                        });
                        parent.style.display = 'none';
                    }
                });

                // 2. Force symmetric layout: all columns equal width in a row
                const columns = section.querySelectorAll('.row.g-3 > .col-xl-4');
                const savedStyles = [];
                columns.forEach(col => {
                    savedStyles.push({
                        el: col,
                        flex: col.style.flex,
                        maxWidth: col.style.maxWidth,
                        width: col.style.width
                    });
                    col.style.flex = '0 0 33.333%';
                    col.style.maxWidth = '33.333%';
                    col.style.width = '33.333%';
                });

                // 3. Ensure cards have equal height (no stretch by kesimpulan)
                const cards = section.querySelectorAll('.row.g-3 > .col-xl-4 > .card');
                const savedCardStyles = [];
                cards.forEach(card => {
                    savedCardStyles.push({
                        el: card,
                        height: card.style.height
                    });
                    card.style.height = 'auto';
                });

                // Small delay to let layout recalculate
                setTimeout(() => {
                    html2canvas(section, {
                        scale: 2,
                        backgroundColor: '#ffffff',
                        useCORS: true,
                        logging: false,
                        windowWidth: 1600
                    }).then(canvas => {
                        const link = document.createElement('a');
                        link.download = 'Persandingan_Pergerakan_Harian_Opsel.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    }).catch(err => {
                        console.error('PNG Export Error:', err);
                        alert('Gagal export PNG. Silakan coba lagi.');
                    }).finally(() => {
                        // Restore analysis boxes
                        analysisParents.forEach(item => {
                            item.el.style.display = item.display || '';
                        });
                        // Restore column styles
                        savedStyles.forEach(item => {
                            item.el.style.flex = item.flex || '';
                            item.el.style.maxWidth = item.maxWidth || '';
                            item.el.style.width = item.width || '';
                        });
                        // Restore card styles
                        savedCardStyles.forEach(item => {
                            item.el.style.height = item.height || '';
                        });
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    });
                }, 100);
            };

            // Export Section 01 as XLSX with 3 sheets (XL, IOH, TSEL)
            window.exportSection01CSV = function() {
                document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));

                // Data already available from PHP
                const dates = {!! json_encode($dates) !!};
                const dailyData = {!! json_encode($data['daily'] ?? []) !!};
                const totalsData = {!! json_encode($data['totals'] ?? []) !!};
                const opsels = ['XL', 'IOH', 'TSEL'];

                // Indonesian day/month names
                const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];

                function formatDateID(dateStr) {
                    const d = new Date(dateStr);
                    return dayNames[d.getDay()] + ', ' + d.getDate() + ' ' + monthNames[d.getMonth()] + ' ' + d
                        .getFullYear();
                }

                function fmtNum(val) {
                    if (!val || val === 0) return '0';
                    return val.toLocaleString('id-ID');
                }

                function fmtPct(val) {
                    if (!val || val === 0) return '0,00%';
                    return val.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '%';
                }

                const wb = XLSX.utils.book_new();

                opsels.forEach(opKey => {
                    const rows = [];

                    // Header rows
                    rows.push(['Hari, Tanggal', opKey, '', '', '']);
                    rows.push(['', 'Jumlah Pergerakan', '', 'Jumlah Orang', '']);
                    rows.push(['', 'Jumlah', '%', 'Jumlah', '%']);

                    // Data rows
                    dates.forEach(dateStr => {
                        const row = dailyData[dateStr] ? dailyData[dateStr][opKey] : null;
                        const mov = row ? (row.movement || 0) : 0;
                        const movPct = row ? (row.movement_pct || 0) : 0;
                        const ppl = row ? (row.people || 0) : 0;
                        const pplPct = row ? (row.people_pct || 0) : 0;

                        rows.push([
                            formatDateID(dateStr),
                            fmtNum(mov),
                            fmtPct(movPct),
                            fmtNum(ppl),
                            fmtPct(pplPct)
                        ]);
                    });

                    // Total row
                    const totMov = totalsData[opKey] ? (totalsData[opKey].movement || 0) : 0;
                    const totPpl = totalsData[opKey] ? (totalsData[opKey].people || 0) : 0;
                    rows.push(['Total', fmtNum(totMov), '100%', fmtNum(totPpl), '100%']);

                    const ws = XLSX.utils.aoa_to_sheet(rows);

                    // Set column widths
                    ws['!cols'] = [{
                            wch: 30
                        }, // Hari, Tanggal
                        {
                            wch: 18
                        }, // Jumlah Pergerakan
                        {
                            wch: 10
                        }, // %
                        {
                            wch: 18
                        }, // Jumlah Orang
                        {
                            wch: 10
                        } // %
                    ];

                    // Merge header cells
                    ws['!merges'] = [{
                            s: {
                                r: 0,
                                c: 0
                            },
                            e: {
                                r: 2,
                                c: 0
                            }
                        }, // Hari, Tanggal merged 3 rows
                        {
                            s: {
                                r: 0,
                                c: 1
                            },
                            e: {
                                r: 0,
                                c: 4
                            }
                        }, // Opsel name merged across
                        {
                            s: {
                                r: 1,
                                c: 1
                            },
                            e: {
                                r: 1,
                                c: 2
                            }
                        }, // Jumlah Pergerakan
                        {
                            s: {
                                r: 1,
                                c: 3
                            },
                            e: {
                                r: 1,
                                c: 4
                            }
                        }, // Jumlah Orang
                    ];

                    XLSX.utils.book_append_sheet(wb, ws, opKey);
                });

                XLSX.writeFile(wb, 'Persandingan_Pergerakan_Harian_Opsel.xlsx');
            };

            // =========================================
            // Section 02 Custom Export Functions
            // =========================================

            // Export Section 02 as PNG (table + summary dashboard)
            window.exportSection02PNG = function() {
                document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));
                const section = document.querySelector('#section-02-content');
                if (!section) {
                    alert('Section 02 tidak ditemukan.');
                    return;
                }

                const btn = document.querySelector('#export-menu-02').previousElementSibling;
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Processing...';
                btn.disabled = true;

                html2canvas(section, {
                    scale: 2,
                    backgroundColor: '#ffffff',
                    useCORS: true,
                    logging: false,
                    windowWidth: 1400
                }).then(canvas => {
                    const link = document.createElement('a');
                    link.download = 'Akumulasi_Pergerakan_Harian.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                }).catch(err => {
                    console.error('PNG Export Error:', err);
                    alert('Gagal export PNG. Silakan coba lagi.');
                }).finally(() => {
                    btn.innerHTML = origText;
                    btn.disabled = false;
                });
            };

            // Export Section 02 as XLSX
            window.exportSection02CSV = function() {
                document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));

                const dates = {!! json_encode($dates) !!};
                const akumulasiDaily = {!! json_encode($data['akumulasi']['daily'] ?? []) !!};
                const totalMovement = {!! json_encode($data['akumulasi']['total_movement'] ?? 0) !!};
                const totalPeople = {!! json_encode($data['akumulasi']['total_people'] ?? 0) !!};

                const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];

                function formatDateID(dateStr) {
                    const d = new Date(dateStr);
                    return dayNames[d.getDay()] + ', ' + d.getDate() + ' ' + monthNames[d.getMonth()] + ' ' + d
                        .getFullYear();
                }

                function fmtNum(val) {
                    if (!val || val === 0) return '0';
                    return val.toLocaleString('id-ID');
                }

                function fmtPct(val) {
                    if (!val || val === 0) return '0,00%';
                    return val.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '%';
                }

                const rows = [];
                // Header rows
                rows.push(['AKUMULASI', 'Akumulasi', '', '', '']);
                rows.push(['', 'Jumlah Pergerakan', '', 'Jumlah Orang Harian', '']);
                rows.push(['', 'Jumlah', '%', 'Jumlah', '%']);

                // Data rows
                dates.forEach(dateStr => {
                    const row = akumulasiDaily[dateStr] || null;
                    const mov = row ? (row.movement || 0) : 0;
                    const movPct = row ? (row.movement_pct || 0) : 0;
                    const ppl = row ? (row.people || 0) : 0;
                    const pplPct = row ? (row.people_pct || 0) : 0;

                    rows.push([
                        formatDateID(dateStr),
                        fmtNum(mov),
                        fmtPct(movPct),
                        fmtNum(ppl),
                        fmtPct(pplPct)
                    ]);
                });

                // Total row
                rows.push(['Total', fmtNum(totalMovement), '100%', fmtNum(totalPeople), '100%']);

                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(rows);

                ws['!cols'] = [{
                        wch: 30
                    },
                    {
                        wch: 18
                    },
                    {
                        wch: 10
                    },
                    {
                        wch: 18
                    },
                    {
                        wch: 10
                    }
                ];

                ws['!merges'] = [{
                        s: {
                            r: 0,
                            c: 0
                        },
                        e: {
                            r: 2,
                            c: 0
                        }
                    },
                    {
                        s: {
                            r: 0,
                            c: 1
                        },
                        e: {
                            r: 0,
                            c: 4
                        }
                    },
                    {
                        s: {
                            r: 1,
                            c: 1
                        },
                        e: {
                            r: 1,
                            c: 2
                        }
                    },
                    {
                        s: {
                            r: 1,
                            c: 3
                        },
                        e: {
                            r: 1,
                            c: 4
                        }
                    },
                ];

                XLSX.utils.book_append_sheet(wb, ws, 'Akumulasi');
                XLSX.writeFile(wb, 'Akumulasi_Pergerakan_Harian.xlsx');
            };

            // =========================================
            // Section 03 Custom Export Functions
            // =========================================

            // Export Section 03 as PNG (type: 'movement'|'people', scope: 'chart'|'table'|'both')
            window.exportSection03PNG = function(type, scope) {
                document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));

                const label = type === 'movement' ? 'Pergerakan' : 'Orang';
                const targetId = `section-03-${type}-${scope}`;
                const section = document.querySelector(`#${targetId}`);

                if (!section) {
                    alert(`Section tidak ditemukan: ${targetId}`);
                    return;
                }

                const btn = document.querySelector('#export-menu-03').previousElementSibling;
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Processing...';
                btn.disabled = true;

                // Temporarily expand table-responsive to prevent clipping
                const tableResponsives = section.querySelectorAll('.table-responsive');
                const savedOverflow = [];
                tableResponsives.forEach(el => {
                    savedOverflow.push({
                        el: el,
                        overflow: el.style.overflow,
                        minWidth: el.style.minWidth
                    });
                    el.style.overflow = 'visible';
                    el.style.minWidth = 'max-content';
                });

                // Also expand parent containers to full width
                const savedParentStyles = [];
                const parents = section.querySelectorAll('.col-xl-9, .col-lg-8');
                parents.forEach(el => {
                    savedParentStyles.push({
                        el: el,
                        flex: el.style.flex,
                        maxWidth: el.style.maxWidth,
                        width: el.style.width
                    });
                    el.style.flex = '0 0 100%';
                    el.style.maxWidth = '100%';
                    el.style.width = '100%';
                });

                // Hide summary box for cleaner table-only export
                const summaryBoxes = section.querySelectorAll('.col-xl-3, .col-lg-4');
                const savedSummary = [];
                if (scope === 'table') {
                    summaryBoxes.forEach(el => {
                        if (el.querySelector('.summary-box-03')) {
                            savedSummary.push({
                                el: el,
                                display: el.style.display
                            });
                            el.style.display = 'none';
                        }
                    });
                }

                setTimeout(() => {
                    // Measure actual content width after expanding
                    const tables = section.querySelectorAll('table');
                    let maxTableWidth = 0;
                    tables.forEach(t => {
                        maxTableWidth = Math.max(maxTableWidth, t.scrollWidth);
                    });
                    const captureWidth = Math.max(section.scrollWidth, maxTableWidth + 80);

                    html2canvas(section, {
                        scale: 2,
                        backgroundColor: '#ffffff',
                        useCORS: true,
                        logging: false,
                        windowWidth: captureWidth,
                        scrollX: 0,
                        scrollY: -window.scrollY
                    }).then(canvas => {
                        const scopeLabel = scope === 'chart' ? 'Chart' : (scope === 'table' ?
                            'Tabel' :
                            'Chart_Tabel');
                        const link = document.createElement('a');
                        link.download = `Pergerakan_Harian_Total_${label}_${scopeLabel}.png`;
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    }).catch(err => {
                        console.error('PNG Export Error:', err);
                        alert('Gagal export PNG. Silakan coba lagi.');
                    }).finally(() => {
                        // Restore table-responsive
                        savedOverflow.forEach(item => {
                            item.el.style.overflow = item.overflow || '';
                            item.el.style.minWidth = item.minWidth || '';
                        });
                        // Restore parent styles
                        savedParentStyles.forEach(item => {
                            item.el.style.flex = item.flex || '';
                            item.el.style.maxWidth = item.maxWidth || '';
                            item.el.style.width = item.width || '';
                        });
                        // Restore summary boxes
                        savedSummary.forEach(item => {
                            item.el.style.display = item.display || '';
                        });
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    });
                }, 150);
            };

            // Export Section 03 as XLSX (type: 'movement'|'people')
            // Sheet 1: Chart data (percentage per day), Sheet 2: Table data (absolute numbers)
            window.exportSection03CSV = function(type) {
                document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));

                const dates = {!! json_encode($dates) !!};
                const akumulasiDaily = {!! json_encode($data['akumulasi']['daily'] ?? []) !!};
                const movPcts = {!! json_encode($movementPctChart) !!};
                const pplPcts = {!! json_encode($peoplePctChart) !!};
                const totalMov = {!! json_encode($totMovAll) !!};
                const totalPpl = {!! json_encode($totPplAll) !!};

                const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];

                function formatDateID(dateStr) {
                    const d = new Date(dateStr);
                    return dayNames[d.getDay()] + ', ' + d.getDate() + ' ' + monthNames[d.getMonth()] + ' ' + d
                        .getFullYear();
                }

                function fmtNum(val) {
                    if (!val || val === 0) return '0';
                    return val.toLocaleString('id-ID');
                }

                const label = type === 'movement' ? 'Pergerakan' : 'Orang';
                const dataKey = type === 'movement' ? 'movement' : 'people';
                const pcts = type === 'movement' ? movPcts : pplPcts;
                const total = type === 'movement' ? totalMov : totalPpl;

                const wb = XLSX.utils.book_new();

                // Sheet 1: Chart Data (percentage)
                const chartRows = [];
                chartRows.push(['Tanggal', `${label} per Hari (%)`]);
                dates.forEach((dateStr, idx) => {
                    chartRows.push([formatDateID(dateStr), (pcts[idx] || 0) + '%']);
                });
                const wsChart = XLSX.utils.aoa_to_sheet(chartRows);
                wsChart['!cols'] = [{
                    wch: 30
                }, {
                    wch: 20
                }];
                XLSX.utils.book_append_sheet(wb, wsChart, 'Chart');

                // Sheet 2: Table Data (absolute numbers by H-day)
                const tableRows = [];
                // Header row 1: Tanggal + date labels
                const headerRow1 = ['Tanggal'];
                dates.forEach(d => {
                    const dt = new Date(d);
                    headerRow1.push(dt.getDate() + '-' + monthNames[dt.getMonth()].substring(0, 3) +
                        '-' + String(dt.getFullYear()).substring(2));
                });
                headerRow1.push('Total');
                tableRows.push(headerRow1);

                // Header row 2: H-day labels
                const headerRow2 = ['H-Day'];
                dates.forEach((d, i) => {
                    headerRow2.push('H' + (i < 7 ? (i - 7) : (i === 7 ? '' : '+' + (i - 7))));
                });
                headerRow2.push('');
                tableRows.push(headerRow2);

                // Data row
                const dataRow = ['Jumlah'];
                dates.forEach(d => {
                    const val = akumulasiDaily[d] ? (akumulasiDaily[d][dataKey] || 0) : 0;
                    dataRow.push(fmtNum(val));
                });
                dataRow.push(fmtNum(total));
                tableRows.push(dataRow);

                const wsTable = XLSX.utils.aoa_to_sheet(tableRows);
                const colWidths = [{
                    wch: 12
                }];
                dates.forEach(() => colWidths.push({
                    wch: 14
                }));
                colWidths.push({
                    wch: 16
                });
                wsTable['!cols'] = colWidths;
                XLSX.utils.book_append_sheet(wb, wsTable, 'Tabel');

                XLSX.writeFile(wb, `Pergerakan_Harian_Total_${label}.xlsx`);
            };

            // =========================================
            // Section 04 Custom Export Functions
            // =========================================

            // Export Section 04 as PNG (type: 'movement'|'people', scope: 'chart'|'table')
            window.exportSection04PNG = function(type, scope) {
                document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));

                const label = type === 'movement' ? 'Pergerakan' : 'Orang';
                const targetId = `section-04-${type}-${scope}`;
                const section = document.querySelector(`#${targetId}`);

                if (!section) {
                    alert(`Section tidak ditemukan: ${targetId}`);
                    return;
                }

                const btn = document.querySelector('#export-menu-04').previousElementSibling;
                const origText = btn.innerHTML;
                btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Processing...';
                btn.disabled = true;

                // Temporarily expand table-responsive to prevent clipping
                const tableResponsives = section.querySelectorAll('.table-responsive');
                const savedOverflow = [];
                tableResponsives.forEach(el => {
                    savedOverflow.push({
                        el: el,
                        overflow: el.style.overflow,
                        minWidth: el.style.minWidth
                    });
                    el.style.overflow = 'visible';
                    el.style.minWidth = 'max-content';
                });

                const savedParentStyles = [];
                const parents = section.querySelectorAll('.col-xl-9, .col-lg-8');
                parents.forEach(el => {
                    savedParentStyles.push({
                        el: el,
                        flex: el.style.flex,
                        maxWidth: el.style.maxWidth,
                        width: el.style.width
                    });
                    el.style.flex = '0 0 100%';
                    el.style.maxWidth = '100%';
                    el.style.width = '100%';
                });

                // Hide summary boxes for cleaner table export
                const summaryBoxes = section.querySelectorAll('.col-xl-3, .col-lg-4');
                const savedSummary = [];
                if (scope === 'table') {
                    summaryBoxes.forEach(el => {
                        if (el.querySelector('.summary-box-03') || el.querySelector(
                                '.d-flex.flex-column')) {
                            savedSummary.push({
                                el: el,
                                display: el.style.display
                            });
                            el.style.display = 'none';
                        }
                    });
                }

                setTimeout(() => {
                    const tables = section.querySelectorAll('table');
                    let maxTableWidth = 0;
                    tables.forEach(t => {
                        maxTableWidth = Math.max(maxTableWidth, t.scrollWidth);
                    });
                    const captureWidth = Math.max(section.scrollWidth, maxTableWidth + 80);

                    html2canvas(section, {
                        scale: 2,
                        backgroundColor: '#ffffff',
                        useCORS: true,
                        logging: false,
                        windowWidth: captureWidth,
                        scrollX: 0,
                        scrollY: -window.scrollY
                    }).then(canvas => {
                        const scopeLabel = scope === 'chart' ? 'Chart' : 'Tabel';
                        const link = document.createElement('a');
                        link.download = `Persandingan_Opsel_${label}_${scopeLabel}.png`;
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    }).catch(err => {
                        console.error('PNG Export Error:', err);
                        alert('Gagal export PNG. Silakan coba lagi.');
                    }).finally(() => {
                        savedOverflow.forEach(item => {
                            item.el.style.overflow = item.overflow || '';
                            item.el.style.minWidth = item.minWidth || '';
                        });
                        savedParentStyles.forEach(item => {
                            item.el.style.flex = item.flex || '';
                            item.el.style.maxWidth = item.maxWidth || '';
                            item.el.style.width = item.width || '';
                        });
                        savedSummary.forEach(item => {
                            item.el.style.display = item.display || '';
                        });
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    });
                }, 150);
            };

            // Export Section 04 as XLSX (type: 'movement'|'people')
            window.exportSection04CSV = function(type) {
                document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));

                const dates = {!! json_encode($dates) !!};
                const dailyData = {!! json_encode($data['daily'] ?? []) !!};
                const totalsData = {!! json_encode($data['totals'] ?? []) !!};
                const akumulasiDaily = {!! json_encode($data['akumulasi']['daily'] ?? []) !!};
                const totalMov = {!! json_encode($totMovAll) !!};
                const totalPpl = {!! json_encode($totPplAll) !!};

                const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];

                function fmtNum(val) {
                    if (!val || val === 0) return '0';
                    return val.toLocaleString('id-ID');
                }

                const label = type === 'movement' ? 'Pergerakan' : 'Orang';
                const dataKey = type === 'movement' ? 'movement' : 'people';
                const grandTotal = type === 'movement' ? totalMov : totalPpl;
                const opsels = ['XL', 'IOH', 'TSEL'];

                const wb = XLSX.utils.book_new();

                // Sheet 1: Chart Data (series values per opsel)
                const chartRows = [];
                chartRows.push(['Tanggal', 'XL', 'IOH', 'TSEL']);
                dates.forEach(dateStr => {
                    const row = [new Date(dateStr).getDate() + '-' + monthNames[new Date(dateStr)
                        .getMonth()].substring(0, 3)];
                    opsels.forEach(op => {
                        const val = dailyData[dateStr] && dailyData[dateStr][op] ? (dailyData[
                            dateStr][op][dataKey] || 0) : 0;
                        row.push(fmtNum(val));
                    });
                    chartRows.push(row);
                });
                const wsChart = XLSX.utils.aoa_to_sheet(chartRows);
                wsChart['!cols'] = [{
                    wch: 14
                }, {
                    wch: 16
                }, {
                    wch: 16
                }, {
                    wch: 16
                }];
                XLSX.utils.book_append_sheet(wb, wsChart, 'Chart');

                // Sheet 2: Table Data (per-opsel rows with H-day columns)
                const tableRows = [];
                // Header row 1: Tanggal + H-day labels + Total
                const headerRow1 = ['Tanggal'];
                dates.forEach((d, i) => {
                    headerRow1.push('H' + (i < 7 ? (i - 7) : (i === 7 ? '' : '+' + (i - 7))));
                });
                headerRow1.push('Total');
                tableRows.push(headerRow1);

                // Header row 2: date labels
                const headerRow2 = [''];
                dates.forEach(d => {
                    const dt = new Date(d);
                    headerRow2.push(dt.getDate() + '-' + monthNames[dt.getMonth()].substring(0, 3) +
                        '-' + String(dt.getFullYear()).substring(2));
                });
                headerRow2.push('');
                tableRows.push(headerRow2);

                // Data rows per opsel
                opsels.forEach(op => {
                    const dataRow = [op];
                    dates.forEach(d => {
                        const val = dailyData[d] && dailyData[d][op] ? (dailyData[d][op][
                            dataKey] || 0) : 0;
                        dataRow.push(fmtNum(val));
                    });
                    dataRow.push(fmtNum(totalsData[op] ? (totalsData[op][dataKey] || 0) : 0));
                    tableRows.push(dataRow);
                });

                // TOTAL row
                const totalRow = ['TOTAL'];
                dates.forEach(d => {
                    const val = akumulasiDaily[d] ? (akumulasiDaily[d][dataKey] || 0) : 0;
                    totalRow.push(fmtNum(val));
                });
                totalRow.push(fmtNum(grandTotal));
                tableRows.push(totalRow);

                const wsTable = XLSX.utils.aoa_to_sheet(tableRows);
                const colWidths = [{
                    wch: 12
                }];
                dates.forEach(() => colWidths.push({
                    wch: 14
                }));
                colWidths.push({
                    wch: 16
                });
                wsTable['!cols'] = colWidths;
                XLSX.utils.book_append_sheet(wb, wsTable, 'Tabel');

                XLSX.writeFile(wb, `Persandingan_Opsel_${label}.xlsx`);
            };

        });
    </script>
@endpush
