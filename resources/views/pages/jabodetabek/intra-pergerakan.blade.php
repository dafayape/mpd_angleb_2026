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
@endsection
