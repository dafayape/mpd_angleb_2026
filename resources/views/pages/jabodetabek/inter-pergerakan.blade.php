@extends('layout.app')

@section('title', 'Pergerakan Harian Inter Jabodetabek')

@section('content')
    @component('layout.partials.page-header', ['number' => '08', 'title' => 'Pergerakan Harian Inter Jabodetabek'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#">Jabodetabek</a></li>
            <li class="breadcrumb-item active">Pergerakan Harian Inter Jabodetabek</li>
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
                overflow-x: auto;
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

    <div class="row mb-5 pb-4" data-aos="fade-up" data-aos-duration="600">
        <div class="col-12">
            <div class="card content-card w-100 flex-column" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">01</span>
                    <h5 class="fw-bold text-navy mb-0">Persandingan pergerakan harian total berdasarkan masing-masing opsel
                        (Inter Jabodetabek)</h5>
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
                        subscribernya (Inter Jabodetabek)
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
                                                    style="font-size: 0.85rem;">Jumlah Pergerakan Inter Jabodetabek</h6>
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
                                                    Yang Melakukan<br>Perjalanan (Inter Jabodetabek)</h6>
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
@endsection
