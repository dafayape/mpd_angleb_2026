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
                border: 1px solid rgba(0, 0, 0, 0.1);
            }

            .table-custom-body td,
            .table-custom-body th {
                vertical-align: middle;
                border: 1px solid rgba(0, 0, 0, 0.1);
            }

            .bg-xl {
                background-color: #a4bdfc;
                /* Soft blue */
                color: #000;
            }

            .bg-tsel {
                background-color: #d50000;
                /* Soft red */
                color: #fff;
            }

            .bg-ioh {
                background-color: #ffd600;
                /* Soft yellow */
                color: #000;
            }

            .bg-total {
                background-color: #e67c73;
                /* Soft orange/coral */
                color: #fff;
                font-weight: bold;
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
                return number_format($val ?? 0, 0, ',', '.');
            }
        }
        if (!function_exists('fmtPctJab')) {
            function fmtPctJab($val)
            {
                return number_format($val ?? 0, 2, ',', '.') . '%';
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
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 text-center table-custom-body w-100 bg-white shadow-sm"
                            style="font-size: 0.85rem;">
                            <thead class="table-custom-header text-dark">
                                <tr>
                                    <th rowspan="4" style="width: 12%; background-color: #e9ecef;">Waktu</th>
                                    <th colspan="12" class="bg-light">DATA REAL</th>
                                </tr>
                                <tr>
                                    <!-- Opsel Headers -->
                                    <th colspan="4" class="bg-xl">XL</th>
                                    <th colspan="4" class="bg-tsel text-white">TSEL</th>
                                    <th colspan="4" class="bg-ioh">IOH</th>
                                </tr>
                                <tr>
                                    <!-- Inner Headers -->
                                    <th colspan="2" class="bg-white">Jumlah Pergerakan</th>
                                    <th colspan="2" class="bg-white">Jumlah Orang</th>
                                    <th colspan="2" class="bg-white">Jumlah Pergerakan</th>
                                    <th colspan="2" class="bg-white">Jumlah Orang</th>
                                    <th colspan="2" class="bg-white">Jumlah Pergerakan</th>
                                    <th colspan="2" class="bg-white">Jumlah Orang</th>
                                </tr>
                                <tr>
                                    <!-- Sub Inner Headers XL -->
                                    <th class="bg-white text-muted">Jumlah</th>
                                    <th class="bg-white text-muted">(%)</th>
                                    <th class="bg-white text-muted">Jumlah</th>
                                    <th class="bg-white text-muted">(%)</th>
                                    <!-- Sub Inner Headers TSEL -->
                                    <th class="bg-white text-muted">Jumlah</th>
                                    <th class="bg-white text-muted">(%)</th>
                                    <th class="bg-white text-muted">Jumlah</th>
                                    <th class="bg-white text-muted">(%)</th>
                                    <!-- Sub Inner Headers IOH -->
                                    <th class="bg-white text-muted">Jumlah</th>
                                    <th class="bg-white text-muted">(%)</th>
                                    <th class="bg-white text-muted">Jumlah</th>
                                    <th class="bg-white text-muted">(%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dates as $dateRaw)
                                    @php
                                        $parsedDate = \Carbon\Carbon::parse($dateRaw);
                                        $labelHariTanggal = $parsedDate->locale('id')->isoFormat('D MMMM YYYY');

                                        $rowXL = $data['daily'][$dateRaw]['XL'] ?? null;
                                        $rowTSEL = $data['daily'][$dateRaw]['TSEL'] ?? null;
                                        $rowIOH = $data['daily'][$dateRaw]['IOH'] ?? null;
                                    @endphp
                                    <tr>
                                        <!-- DATE -->
                                        <td class="text-start fw-medium text-dark bg-light">{{ $labelHariTanggal }}</td>

                                        <!-- XL -->
                                        <td>{{ fmtNumJab($rowXL['pergerakan'] ?? null) }}</td>
                                        <td class="text-muted bg-light">{{ fmtPctJab($rowXL['pct_pergerakan'] ?? null) }}
                                        </td>
                                        <td>{{ fmtNumJab($rowXL['orang'] ?? null) }}</td>
                                        <td class="text-muted bg-light">{{ fmtPctJab($rowXL['pct_orang'] ?? null) }}</td>

                                        <!-- TSEL -->
                                        <td>{{ fmtNumJab($rowTSEL['pergerakan'] ?? null) }}</td>
                                        <td class="text-muted bg-light">{{ fmtPctJab($rowTSEL['pct_pergerakan'] ?? null) }}
                                        </td>
                                        <td>{{ fmtNumJab($rowTSEL['orang'] ?? null) }}</td>
                                        <td class="text-muted bg-light">{{ fmtPctJab($rowTSEL['pct_orang'] ?? null) }}</td>

                                        <!-- IOH -->
                                        <td>{{ fmtNumJab($rowIOH['pergerakan'] ?? null) }}</td>
                                        <td class="text-muted bg-light">{{ fmtPctJab($rowIOH['pct_pergerakan'] ?? null) }}
                                        </td>
                                        <td>{{ fmtNumJab($rowIOH['orang'] ?? null) }}</td>
                                        <td class="text-muted bg-light">{{ fmtPctJab($rowIOH['pct_orang'] ?? null) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-total font-weight-bold" style="border-top: 2px solid #333;">
                                <tr>
                                    <th class="text-start text-white">Total</th>

                                    <!-- XL Total -->
                                    <th class="text-white">{{ fmtNumJab($data['totals']['XL']['pergerakan']) }}</th>
                                    <th class="text-white">100,00%</th>
                                    <th class="text-white">{{ fmtNumJab($data['totals']['XL']['orang']) }}</th>
                                    <th class="text-white">100,00%</th>

                                    <!-- TSEL Total -->
                                    <th class="text-white">{{ fmtNumJab($data['totals']['TSEL']['pergerakan']) }}</th>
                                    <th class="text-white">100,00%</th>
                                    <th class="text-white">{{ fmtNumJab($data['totals']['TSEL']['orang']) }}</th>
                                    <th class="text-white">100,00%</th>

                                    <!-- IOH Total -->
                                    <th class="text-white">{{ fmtNumJab($data['totals']['IOH']['pergerakan']) }}</th>
                                    <th class="text-white">100,00%</th>
                                    <th class="text-white">{{ fmtNumJab($data['totals']['IOH']['orang']) }}</th>
                                    <th class="text-white">100,00%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
