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
            overflow: hidden;
            background: white;
        }

        .analysis-box {
            background: rgba(42, 48, 66, 0.03);
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
            padding: 1rem;
            margin-top: 1rem;
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
    @endphp

    <div class="row mb-4" data-aos="fade-up" data-aos-duration="600">
        <div class="col-12">
            <div class="card content-card w-100 flex-column" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">01</span>
                    <h5 class="fw-bold text-navy mb-0">Persandingan pergerakan harian total berdasarkan masing-masing opsel
                    </h5>
                </div>
                <div class="card-body bg-light" style="padding: 1.5rem;">
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
    </div>

    <!-- 02 AKUMULASI -->
    <div class="row mt-5" data-aos="fade-up" data-aos-delay="100">
        <div class="col-12">
            <h5 class="fw-bold mb-4 text-dark" style="font-family: 'Poppins', sans-serif;">
                <span class="d-inline-flex align-items-center justify-content-center text-white me-2 section-badge"
                    style="background-color: #2a3042; box-shadow: 0 4px 6px rgba(42, 48, 66, 0.3);">
                    02
                </span>
                Akumulasi Pergerakan Harian
            </h5>

            <div class="card content-card border-dark">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered border-dark table-hover mb-0 text-center align-middle"
                            style="font-size: 0.85rem;">
                            <thead class="text-dark">
                                <tr>
                                    <th rowspan="3" class="align-middle border-dark"
                                        style="width: 25%; background-color: #dbe4eb; font-weight: bold;">AKUMULASI</th>
                                    <th colspan="2" class="py-2 text-center border-dark"
                                        style="background-color: #dbe4eb; font-weight: bold;">Akumulasi</th>
                                    <th colspan="2" class="py-2 text-center border-dark"
                                        style="background-color: #e7ebf0;"></th>
                                </tr>
                                <tr>
                                    <th colspan="2" class="py-2 border-dark"
                                        style="background-color: #dbe4eb; font-weight: bold;"><small class="fw-bold"
                                            style="font-size: 0.9rem;">Jumlah Pergerakan</small></th>
                                    <th colspan="2" class="py-2 border-dark"
                                        style="background-color: #e7ebf0; font-weight: bold;"><small class="fw-bold"
                                            style="font-size: 0.9rem;">Jumlah Orang Harian</small></th>
                                </tr>
                                <tr>
                                    <th style="width: 18.75%; background-color: #ffffff;"
                                        class="py-2 border-dark text-dark fw-bold">Jumlah</th>
                                    <th style="width: 18.75%; background-color: #ffffff;"
                                        class="py-2 border-dark text-dark fw-bold">%</th>
                                    <th style="width: 18.75%; background-color: #ffffff;" class="py-2 border-dark"></th>
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
                                        <td class="text-start text-dark border-dark" style="background-color: #fafafa;">
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
                                                    {{ number_format($data['akumulasi']['koefisien'], 2, ',', '.') }}</h4>
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

                            <ul class="mb-0 text-dark" style="font-size: 0.95rem; line-height: 1.6; padding-left: 2rem;">
                                <li class="mb-2"><strong>Jumlah orang/individu unik subscriber</strong> yang melakukan
                                    pergerakan pada Periode Angkutan Lebaran 2026, dengan nilai realisasi tanggal 13 s/d 30
                                    Maret 2026 adalah <strong>{{ fmtNum($data['akumulasi']['unique_subscriber']) }}
                                        orang.</strong></li>
                                <li class="mb-2"><strong>Jumlah pergerakan/movement count</strong> pada periode tersebut
                                    adalah sebesar <strong>{{ fmtNum($data['akumulasi']['total_movement']) }}
                                        pergerakan</strong>, dengan <strong>puncak</strong> pergerakan di tanggal
                                    <strong>{{ isset($data['akumulasi']['peak_days'][0])? \Carbon\Carbon::parse($data['akumulasi']['peak_days'][0])->locale('id')->isoFormat('D MMMM YYYY'): '-' }}</strong>
                                    sebesar
                                    <strong>{{ isset($data['akumulasi']['peak_days'][0]) ? fmtNum($data['akumulasi']['daily'][$data['akumulasi']['peak_days'][0]]['movement']) : 0 }}
                                        pergerakan</strong> dan
                                    <strong>{{ isset($data['akumulasi']['peak_days'][1])? \Carbon\Carbon::parse($data['akumulasi']['peak_days'][1])->locale('id')->isoFormat('D MMMM YYYY'): '-' }}</strong>
                                    dengan
                                    <strong>{{ isset($data['akumulasi']['peak_days'][1]) ? fmtNum($data['akumulasi']['daily'][$data['akumulasi']['peak_days'][1]]['movement']) : 0 }}
                                        pergerakan</strong>.</li>
                                <li>Dari data akumulasi tersebut maka data ini menunjukkan bahwa terdapat rata-rata lebih
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
