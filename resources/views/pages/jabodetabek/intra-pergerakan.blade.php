@extends('layout.app')

@section('title', 'Pergerakan Harian Intra Jabodetabek')

@section('content')
    @component('layout.partials.page-header', ['number' => '06', 'title' => 'Pergerakan Harian Intra Jabodetabek'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#">Jabodetabek</a></li>
            <li class="breadcrumb-item active">Pergerakan Harian Intra Jabodetabek</li>
        </ol>
    @endcomponent

    @push('css')
        <style>
            .table-custom-header th {
                vertical-align: middle;
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .table-custom-body td,
            .table-custom-body th {
                vertical-align: middle;
                border: 1px solid rgba(0, 0, 0, 0.05);
            }

            .bg-xl {
                background-color: #2a3042;
                color: #fff;
            }

            .bg-tsel {
                background-color: #ef4444;
                color: #fff;
            }

            .bg-ioh {
                background-color: #f59e0b;
                color: #fff;
            }

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

            .text-navy {
                color: #2a3042 !important;
            }

            .table-responsive {
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
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

    @php
        // Helper function for numbers
        if (!function_exists('fmtNumJab')) {
            function fmtNumJab($val)
            {
                if (!$val) {
                    return '0';
                }
                return number_format($val ?? 0, 0, ',', '.');
            }
        }
        if (!function_exists('fmtPctJab')) {
            function fmtPctJab($val)
            {
                if (!$val) {
                    return '0,00%';
                }
                return number_format($val ?? 0, 2, ',', '.') . '%';
            }
        }

        $opselsConfig = [
            'XL' => ['name' => 'XL', 'bg_class' => 'bg-xl'],
            'IOH' => ['name' => 'IOH', 'bg_class' => 'bg-ioh'],
            'TSEL' => ['name' => 'TSEL', 'bg_class' => 'bg-tsel'],
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
                $series04_mov[$op][] = $data['daily'][$d][$op]['pergerakan'] ?? 0;
                $series04_ppl[$op][] = $data['daily'][$d][$op]['orang'] ?? 0;
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
                        (Intra Jabodetabek)</h5>
                </div>
                <div class="card-body bg-light" style="padding: 1.5rem;">
                    <div class="row g-3">
                        @foreach ($opselsConfig as $opKey => $conf)
                            <div class="col-xl-4 col-lg-12 d-flex">
                                <div class="card w-100 shadow-sm border-0 d-flex flex-column h-100 overflow-hidden">
                                    <div class="table-responsive flex-grow-1">
                                        <table
                                            class="table table-bordered mb-0 text-center table-custom-body w-100 bg-white"
                                            style="font-size: 0.85rem;">
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

                                                        $rowOp = $data['daily'][$dateRaw][$opKey] ?? null;
                                                        $mov = $rowOp['pergerakan'] ?? null;
                                                        $movPct = $rowOp['pct_pergerakan'] ?? null;
                                                        $ppl = $rowOp['orang'] ?? null;
                                                        $pplPct = $rowOp['pct_orang'] ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td class="text-start fw-medium text-dark bg-light">
                                                            {{ $labelHariTanggal }}</td>
                                                        <td>{{ fmtNumJab($mov) }}</td>
                                                        <td class="text-muted bg-light">{{ fmtPctJab($movPct) }}</td>
                                                        <td>{{ fmtNumJab($ppl) }}</td>
                                                        <td class="text-muted bg-light">{{ fmtPctJab($pplPct) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="{{ $conf['bg_class'] }} text-white font-weight-bold"
                                                style="border-top: 2px solid #333;">
                                                <tr>
                                                    <th class="text-start">Total</th>
                                                    @php
                                                        $totMov = $data['totals'][$opKey]['pergerakan'] ?? 0;
                                                        $totPpl = $data['totals'][$opKey]['orang'] ?? 0;
                                                    @endphp
                                                    <th>{{ fmtNumJab($totMov) }}</th>
                                                    <th>100,00%</th>
                                                    <th>{{ fmtNumJab($totPpl) }}</th>
                                                    <th>100,00%</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="px-3 pb-3 pt-3 mt-auto bg-white border-top">
                                        <div class="analysis-box mt-0 border-0"
                                            style="background: rgba(42, 48, 66, 0.03); border-left: 4px solid {{ $opKey === 'XL' ? '#2a3042' : ($opKey === 'IOH' ? '#f59e0b' : '#ef4444') }} !important; border-radius: 4px; padding: 1rem;">
                                            <h6 class="fw-bold mb-2"
                                                style="color: {{ $opKey === 'XL' ? '#2a3042' : ($opKey === 'IOH' ? '#f59e0b' : '#ef4444') }}; font-size: 0.85rem;">
                                                <i class="bx bx-pie-chart-alt-2 me-1"></i> Kesimpulan Analisis
                                                ({{ $conf['name'] }})
                                            </h6>
                                            <p class="mb-0 text-muted" style="font-size: 0.8rem; line-height: 1.5;">
                                                @if ($totMov == 0)
                                                    Pada rentang waktu ini, belum terdapat rekaman observasi pergerakan
                                                    yang valid secara menyeluruh untuk operator {{ $conf['name'] }}.
                                                @else
                                                    Berdasarkan akumulasi tanggal 13 - 30 Maret 2026, total pergerakan
                                                    yang terekam oleh <strong>{{ $conf['name'] }}</strong> adalah
                                                    <strong>{{ fmtNumJab($totMov) }}</strong>, mencakup
                                                    <strong>{{ fmtNumJab($totPpl) }}</strong> target orang.
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
                    <h5 class="fw-bold text-navy mb-0">Akumulasi pergerakan harian dari seluruh opsel dan jumlah unique
                        subscribernya (Intra Jabodetabek)
                    </h5>
                </div>
                <div class="card-body bg-light" style="padding: 1.5rem;">
                    <div class="card w-100 shadow-sm border-0 d-flex flex-column mb-4">
                        <div class="table-responsive flex-grow-1">
                            <table class="table table-bordered border-dark table-hover mb-0 text-center align-middle"
                                style="font-size: 0.85rem;">
                                <thead class="text-dark">
                                    <tr>
                                        <th rowspan="3" class="align-middle border-dark text-start px-4"
                                            style="width: 25%; background-color: #dbe4eb; font-weight: bold;">AKUMULASI</th>
                                        <th colspan="4" class="py-2 text-center border-dark"
                                            style="background-color: #dbe4eb; font-weight: bold;">DATA REAL</th>
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
                                            $labelHariTanggal = $carbonDate->isoFormat('D MMMM YYYY');
                                        @endphp
                                        <tr>
                                            <td class="text-start text-dark border-dark"
                                                style="background-color: #fafafa;">
                                                {{ $labelHariTanggal }}</td>
                                            <td class="border-dark">{{ fmtNumJab($mov) }}</td>
                                            <td class="text-dark border-dark" style="background-color: #fafafa;">
                                                {{ fmtPctJab($movPct) }}</td>
                                            <td class="border-dark">{{ fmtNumJab($ppl) }}</td>
                                            <td class="text-dark border-dark" style="background-color: #fafafa;">
                                                {{ fmtPctJab($pplPct) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="font-weight-bold border-dark">
                                    <tr>
                                        <th class="text-center pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #dbe4eb; font-size: 1rem;">Total</th>
                                        <th class="pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #dbe4eb; font-size: 1rem;">
                                            {{ fmtNumJab($data['akumulasi']['total_movement']) }}</th>
                                        <th class="pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #dbe4eb; font-size: 1rem;">100%</th>
                                        <th class="pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #e7ebf0; font-size: 1rem;">
                                            {{ fmtNumJab($data['akumulasi']['total_people']) }}</th>
                                        <th class="pb-3 pt-3 border-dark fw-bold text-dark"
                                            style="background-color: #e7ebf0; font-size: 1rem;">100%</th>
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
                                                    style="font-size: 0.85rem;">Jumlah Pergerakan Intra Jabodetabek</h6>
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
                                                        {{ number_format($data['akumulasi']['koefisien'], 3, ',', '.') }}
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
                                                    Yang Melakukan<br>Perjalanan (Intra Jabodetabek)</h6>
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
                                    <li class="mb-2"><strong>Jumlah <em>unique subscriber</em></strong> yang
                                        melakukan pergerakan pada Periode Angkutan Lebaran 2026 di wilayah
                                        <strong>Jabodetabek</strong> pada tanggal 13 - 30 Maret 2026 (realisasi) sebanyak
                                        <strong>{{ fmtNumJab($data['akumulasi']['unique_subscriber']) }}
                                            orang.</strong>
                                    </li>
                                    <li class="mb-2"><strong>Jumlah pergerakan/<em>movement count</em></strong> di
                                        wilayah Jabodetabek sebesar
                                        <strong>{{ fmtNumJab($data['akumulasi']['total_movement']) }}
                                            pergerakan.</strong>
                                    </li>
                                    <li>Dari data akumulasi tersebut maka data ini menunjukkan bahwa terdapat rata-rata
                                        lebih dari satu perjalanan per individu selama periode pengamatan, dengan
                                        <strong>rasio sekitar
                                            {{ number_format($data['akumulasi']['koefisien'], 3, ',', '.') }} kali
                                            perjalanan per orang</strong>.
                                    </li>
                                </ul>

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
                        Intra Jabodetabek
                    </h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">

                    <!-- BLOCK 1: PERGERAKAN PER HARI -->
                    <div class="position-relative border rounded p-3 mb-5"
                        style="border-color: #798797 !important; border-width: 2px !important; border-radius: 12px !important;">
                        <div class="chart-title-badge">PERGERAKAN PER HARI</div>

                        <div id="chart-movement" style="min-height: 250px; margin-top: 20px;"></div>

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
                                                <td class="fw-bold text-dark" style="background-color: #f8f9fa;">Jumlah
                                                </td>
                                                @foreach ($dates as $d)
                                                    <td class="fw-bold text-dark">
                                                        {{ fmtNumJab($data['akumulasi']['daily'][$d]['movement'] ?? 0) }}
                                                    </td>
                                                @endforeach
                                                <td class="fw-bold text-dark" style="font-size:0.9rem;">
                                                    {{ fmtNumJab($totMovAll) }}</td>
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
                                            style="background-color: #fef08a !important; padding: 6px 15px; font-size: 1.25rem; font-weight: 800; border-radius: 4px; display:inline-block; margin-top: 10px;">{{ fmtNumJab($totMovAll) }}
                                            Pergerakan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BLOCK 2: ORANG PER HARI -->
                    <div class="position-relative border rounded p-3 mt-4"
                        style="border-color: #798797 !important; border-width: 2px !important; border-radius: 12px !important;">
                        <div class="chart-title-badge">ORANG PER HARI</div>

                        <div id="chart-people" style="min-height: 250px; margin-top: 20px;"></div>

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
                                                <td class="fw-bold text-dark" style="background-color: #f8f9fa;">Jumlah
                                                </td>
                                                @foreach ($dates as $d)
                                                    <td class="fw-bold text-dark">
                                                        {{ fmtNumJab($data['akumulasi']['daily'][$d]['people'] ?? 0) }}
                                                    </td>
                                                @endforeach
                                                <td class="fw-bold text-dark" style="font-size:0.9rem;">
                                                    {{ fmtNumJab($totPplAll) }}</td>
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
                                            style="background-color: #fef08a !important; padding: 6px 15px; font-size: 1.25rem; font-weight: 800; border-radius: 4px; display:inline-block; margin-top: 10px;">{{ fmtNumJab($totPplAll) }}
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

    <!-- 04 PERSANDINGAN PERGERAKAN OPSEL -->
    <div class="row mt-4 mb-5" data-aos="fade-up" data-aos-delay="300">
        <div class="col-12">
            <div class="card content-card w-100 flex-column" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">04</span>
                    <h5 class="fw-bold text-navy mb-0">Persandingan pergerakan harian total berdasarkan masing-masing opsel
                        (Pergerakan per hari dan orang per hari) Intra Jabodetabek
                    </h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">

                    <!-- BLOCK 1: PERGERAKAN PER HARI -->
                    <div class="position-relative border rounded p-3 mb-5"
                        style="border-color: #798797 !important; border-width: 2px !important; border-radius: 12px !important;">
                        <div class="chart-title-badge">PERGERAKAN PER HARI</div>

                        <div id="chart-movement-04" style="min-height: 250px; margin-top: 20px;"></div>

                        <div class="row mt-3 g-0">
                            <div class="col-xl-9 col-lg-8 pe-2">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 table-03 text-center align-middle"
                                        style="min-width: 1400px;">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="align-middle text-white"
                                                    style="background-color: #2a3042; width: 80px;">Tanggal</th>
                                                @foreach ($dates as $i => $d)
                                                    <th style="background-color: #486284; color: white;">
                                                        <div style="font-size: 0.75rem;">
                                                            H{{ $i < 7 ? $i - 7 : ($i == 7 ? '' : '+' . ($i - 7)) }}
                                                        </div>
                                                    </th>
                                                @endforeach
                                                <th rowspan="2" class="align-middle text-white"
                                                    style="background-color: #2a3042; width: 100px;">Total</th>
                                            </tr>
                                            <tr>
                                                @foreach ($dates as $d)
                                                    <th
                                                        style="background-color: #5a7395; color: white; font-size: 0.7rem;">
                                                        {!! \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D-<br>MMM-YY') !!}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach (['XL', 'IOH', 'TSEL'] as $op)
                                                <tr>
                                                    <td class="fw-bold text-dark" style="background-color: #ffffff;">
                                                        {{ $op }}</td>
                                                    @foreach ($dates as $d)
                                                        <td class="text-dark" style="background-color: #ffffff;">
                                                            {{ fmtNumJab($data['daily'][$d][$op]['pergerakan'] ?? 0) }}
                                                        </td>
                                                    @endforeach
                                                    <td class="fw-bold text-dark"
                                                        style="font-size:0.9rem; background-color: #ffffff;">
                                                        {{ fmtNumJab($data['totals'][$op]['pergerakan'] ?? 0) }}</td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td class="fw-bold text-dark" style="background-color: #e0e0e0;">TOTAL
                                                </td>
                                                @foreach ($dates as $d)
                                                    <td class="fw-bold text-dark" style="background-color: #e0e0e0;">
                                                        {{ fmtNumJab($data['akumulasi']['daily'][$d]['movement'] ?? 0) }}
                                                    </td>
                                                @endforeach
                                                <td class="fw-bold text-dark"
                                                    style="background-color: #e0e0e0; font-size:0.9rem;">
                                                    {{ fmtNumJab($totMovAll) }}</td>
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
                                                $data['totals']['TSEL']['pergerakan'] ?? 0,
                                            ],
                                            'IOH' => [
                                                'Total Pergerakan<br>MPD IOH',
                                                '#f59e0b',
                                                $data['totals']['IOH']['pergerakan'] ?? 0,
                                            ],
                                            'XL' => [
                                                'Total Pergerakan<br>MPD XL',
                                                '#2a3042',
                                                $data['totals']['XL']['pergerakan'] ?? 0,
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

                    <!-- BLOCK 2: ORANG PER HARI -->
                    <div class="position-relative border rounded p-3 mt-4"
                        style="border-color: #798797 !important; border-width: 2px !important; border-radius: 12px !important;">
                        <div class="chart-title-badge">ORANG PER HARI</div>

                        <div id="chart-people-04" style="min-height: 250px; margin-top: 20px;"></div>

                        <div class="row mt-3 g-0">
                            <div class="col-xl-9 col-lg-8 pe-2">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 table-03 text-center align-middle"
                                        style="min-width: 1400px;">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="align-middle text-white"
                                                    style="background-color: #2a3042; width: 80px;">Tanggal</th>
                                                @foreach ($dates as $i => $d)
                                                    <th style="background-color: #1e6082; color: white;">
                                                        <div style="font-size: 0.75rem;">
                                                            H{{ $i < 7 ? $i - 7 : ($i == 7 ? '' : '+' . ($i - 7)) }}
                                                        </div>
                                                    </th>
                                                @endforeach
                                                <th rowspan="2" class="align-middle text-white"
                                                    style="background-color: #2a3042; width: 100px;">Total</th>
                                            </tr>
                                            <tr>
                                                @foreach ($dates as $d)
                                                    <th
                                                        style="background-color: #29769e; color: white; font-size: 0.7rem;">
                                                        {!! \Carbon\Carbon::parse($d)->locale('id')->isoFormat('D-<br>MMM-YY') !!}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach (['XL', 'IOH', 'TSEL'] as $op)
                                                <tr>
                                                    <td class="fw-bold text-dark" style="background-color: #ffffff;">
                                                        {{ $op }}</td>
                                                    @foreach ($dates as $d)
                                                        <td class="text-dark" style="background-color: #ffffff;">
                                                            {{ fmtNumJab($data['daily'][$d][$op]['orang'] ?? 0) }}</td>
                                                    @endforeach
                                                    <td class="fw-bold text-dark"
                                                        style="font-size:0.9rem; background-color: #ffffff;">
                                                        {{ fmtNumJab($data['totals'][$op]['orang'] ?? 0) }}</td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td class="fw-bold text-dark" style="background-color: #e0e0e0;">TOTAL
                                                </td>
                                                @foreach ($dates as $d)
                                                    <td class="fw-bold text-dark" style="background-color: #e0e0e0;">
                                                        {{ fmtNumJab($data['akumulasi']['daily'][$d]['people'] ?? 0) }}
                                                    </td>
                                                @endforeach
                                                <td class="fw-bold text-dark"
                                                    style="background-color: #e0e0e0; font-size:0.9rem;">
                                                    {{ fmtNumJab($totPplAll) }}</td>
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
                                                $data['totals']['TSEL']['orang'] ?? 0,
                                            ],
                                            'IOH' => [
                                                'Total Orang<br>MPD IOH',
                                                '#f59e0b',
                                                $data['totals']['IOH']['orang'] ?? 0,
                                            ],
                                            'XL' => [
                                                'Total Orang<br>MPD XL',
                                                '#2a3042',
                                                $data['totals']['XL']['orang'] ?? 0,
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

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    once: true,
                    offset: 50,
                    duration: 600
                });
            }

            // ApexCharts Rendering for Section 03
            const datesLabels = {!! json_encode($datesArrForChart) !!};
            const movPcts = {!! json_encode($movementPctChart) !!};
            const pplPcts = {!! json_encode($peoplePctChart) !!};

            const commonOptions = {
                chart: {
                    type: 'bar',
                    height: 260,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true
                    }
                },
                plotOptions: {
                    bar: {
                        dataLabels: {
                            position: 'top'
                        },
                        columnWidth: '55%'
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val + "%";
                    },
                    offsetY: -22,
                    style: {
                        fontSize: '12px',
                        colors: ["#555"]
                    }
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: datesLabels,
                    labels: {
                        style: {
                            fontSize: '10.5px',
                            colors: '#666'
                        }
                    }
                },
                yaxis: {
                    max: function(max) {
                        return max + 2;
                    }, // give some top padding
                    labels: {
                        formatter: function(val) {
                            return val.toFixed(2) + "%";
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                grid: {
                    borderColor: '#e0e0e0',
                    strokeDashArray: 4
                },
                colors: ['#1e6082']
            };

            if (document.querySelector("#chart-movement")) {
                new ApexCharts(document.querySelector("#chart-movement"), {
                    ...commonOptions,
                    series: [{
                        name: 'Pergerakan',
                        data: movPcts
                    }]
                }).render();
            }

            if (document.querySelector("#chart-people")) {
                new ApexCharts(document.querySelector("#chart-people"), {
                    ...commonOptions,
                    series: [{
                        name: 'Orang',
                        data: pplPcts
                    }]
                }).render();
            }

            // ApexCharts Rendering for Section 04
            const seriesXLMov = {!! json_encode($series04_mov['XL']) !!};
            const seriesIOHMov = {!! json_encode($series04_mov['IOH']) !!};
            const seriesTSELMov = {!! json_encode($series04_mov['TSEL']) !!};

            const seriesXLPpl = {!! json_encode($series04_ppl['XL']) !!};
            const seriesIOHPpl = {!! json_encode($series04_ppl['IOH']) !!};
            const seriesTSELPpl = {!! json_encode($series04_ppl['TSEL']) !!};

            const commonOptions04 = {
                chart: {
                    type: 'bar',
                    height: 260,
                    toolbar: {
                        show: false
                    },
                    stacked: false
                },
                plotOptions: {
                    bar: {
                        columnWidth: '60%',
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: datesLabels,
                    labels: {
                        style: {
                            fontSize: '10px',
                            colors: '#666'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return val.toLocaleString('id-ID');
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                grid: {
                    borderColor: '#e0e0e0',
                    strokeDashArray: 4
                },
                colors: ['#2a3042', '#f59e0b', '#ef4444'], // XL, IOH, TSEL
                legend: {
                    position: 'right',
                    offsetY: 40
                }
            };

            if (document.querySelector("#chart-movement-04")) {
                new ApexCharts(document.querySelector("#chart-movement-04"), {
                    ...commonOptions04,
                    series: [{
                            name: 'XL',
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
                }).render();
            }

            if (document.querySelector("#chart-people-04")) {
                new ApexCharts(document.querySelector("#chart-people-04"), {
                    ...commonOptions04,
                    series: [{
                            name: 'XL',
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
                }).render();
            }
        });
    </script>
@endpush
