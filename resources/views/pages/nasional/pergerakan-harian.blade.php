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

    <div class="row mb-3" data-aos="fade-down" data-aos-duration="600">
        <div class="col-12">
            <div class="alert bg-white border shadow-sm d-flex align-items-center mb-0" role="alert">
                <i class="bx bx-info-circle fs-4 text-primary me-3"></i>
                <div class="text-muted">
                    Persandingan data total pergerakan harian <strong>(13 - 30 Maret 2026)</strong> berdasarkan
                    masing-masing Operator Seluler: XLSmart, IOH, dan TSEL. Jika data observasi nol, maka sistem akan
                    langsung menampilkannya secara transparan (0).
                </div>
            </div>
        </div>
    </div>

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
            'XL' => ['name' => 'XLSmart', 'bg_class' => 'bg-navy', 'text_class' => 'text-primary'],
            'IOH' => ['name' => 'IOH', 'bg_class' => 'bg-amber', 'text_class' => 'text-warning'],
            'TSEL' => ['name' => 'TSEL', 'bg_class' => 'bg-tsel', 'text_class' => 'text-danger'],
        ];
    @endphp

    <div class="row g-3" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
        @foreach ($opselsConfig as $opKey => $conf)
            <div class="col-xl-4 col-lg-12 mb-4">
                <div class="content-card h-100 d-flex flex-column">
                    <div class="table-responsive flex-grow-1 p-2">
                        <table class="table table-bordered mb-0 text-center table-custom-body w-100">
                            <thead class="{{ $conf['bg_class'] }} table-custom-header">
                                <tr>
                                    @if ($loop->first)
                                        <th rowspan="2" style="width: 25%;">Hari, Tanggal</th>
                                    @else
                                        <!-- Hide date header on other tables visually in sync if requested, but for responsive layout, rendering it is better. Let's render it for standalone integrity -> but the user screenshot shows middle and right tables don't specify date column headers? Oh wait, the screenshot only shows headers for Movement and People! The user cropped the image to show them seamlessly -> if stacked, they MUST have dates. -->
                                        <th rowspan="2" class="d-xl-none" style="width: 25%;">Hari, Tanggal</th>
                                    @endif
                                    <th colspan="2">{{ $conf['name'] }}<br><small class="fw-normal">Jumlah
                                            Pergerakan</small></th>
                                    <th colspan="2"><br><small class="fw-normal">Jumlah Orang</small></th>
                                </tr>
                                <tr>
                                    <th style="width: 18%;">Jumlah</th>
                                    <th style="width: 15%;">%</th>
                                    <th style="width: 18%;">Jumlah</th>
                                    <th style="width: 15%;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dates as $dateRaw)
                                    @php
                                        $parsedDate = \Carbon\Carbon::parse($dateRaw);
                                        $labelHariTanggal = $parsedDate->locale('id')->isoFormat('dddd, D MMMM YYYY');

                                        $row = $data['daily'][$dateRaw][$opKey] ?? null;
                                        $mov = $row['movement'] ?? 0;
                                        $movPct = $row['movement_pct'] ?? 0;
                                        $ppl = $row['people'] ?? 0;
                                        $pplPct = $row['people_pct'] ?? 0;
                                    @endphp
                                    <tr>
                                        @if ($loop->parent->first)
                                            <td class="text-start fw-medium text-dark bg-light">{{ $labelHariTanggal }}</td>
                                        @else
                                            <td class="text-start fw-medium text-dark bg-light d-xl-none">
                                                {{ $labelHariTanggal }}</td>
                                        @endif

                                        <td>{{ fmtNum($mov) }}</td>
                                        <td class="text-muted">{{ fmtPct($movPct) }}</td>
                                        <td>{{ fmtNum($ppl) }}</td>
                                        <td class="text-muted">{{ fmtPct($pplPct) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="{{ $conf['bg_class'] }} text-white font-weight-bold">
                                <tr>
                                    @if ($loop->first)
                                        <th class="text-start">Total</th>
                                    @else
                                        <th class="text-start d-xl-none">Total</th>
                                    @endif
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

                    <!-- Analysis Box -->
                    <div class="px-3 pb-3 mt-auto">
                        <div class="analysis-box">
                            <h6 class="fw-bold fs-6 mb-2 {{ $conf['text_class'] }}">
                                <i class="bx bx-bar-chart-alt-2 me-1"></i> Kesimpulan Analisis ({{ $conf['name'] }})
                            </h6>
                            <p class="mb-0 text-muted" style="font-size: 0.85rem; line-height: 1.5; text-align: justify;">
                                @if ($totMov > 0)
                                    Berdasarkan akumulasi tanggal 13 - 30 Maret 2026, total pergerakan yang terekam oleh
                                    <strong>{{ $conf['name'] }}</strong> adalah <strong>{{ fmtNum($totMov) }}</strong>,
                                    mencakup basis <strong>{{ fmtNum($totPpl) }}</strong> target orang. Pola pergerakan
                                    cenderung dinamis tiap harinya.
                                @else
                                    Pada rentang waktu ini, belum terdapat rekaman observasi pergerakan yang valid secara
                                    menyeluruh untuk operator <strong>{{ $conf['name'] }}</strong>.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
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
