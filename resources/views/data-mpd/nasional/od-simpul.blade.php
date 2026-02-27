@extends('layout.app')

@section('title', $title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        @foreach ($breadcrumb as $crumb)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                {{ $crumb }}
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @push('css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            .table th,
            .table td {
                vertical-align: middle;
                font-size: 11px;
            }

            .hoverTable tbody tr:hover td {
                background-color: #e9f5ff !important;
            }

            .bg-soft-primary {
                background-color: rgba(85, 110, 230, 0.1) !important;
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

            .content-card {
                border: none;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                margin-bottom: 2rem;
            }

            #sankey-container,
            #sankey-container-tujuan,
            #sankey-container-kabkota {
                height: 650px;
            }

            #desire-line-map {
                z-index: 1;
            }

            .table-custom th {
                background-color: #c8d9e8 !important;
                font-weight: 600;
                font-size: 12px;
                border-color: #999;
            }

            .table-custom td {
                font-size: 12px;
                border-color: #ccc;
            }
        </style>
    @endpush

    <!-- 01 O-D PROVINSI ASAL & DESIRE LINE -->
    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">01</span>
                    <h5 class="fw-bold text-navy mb-0">O-D Provinsi Asal (10 besar provinsi asal favorit Nasional) & Desire
                        line</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">

                    <div class="row align-items-stretch">
                        <!-- Left: Sankey -->
                        <div class="col-xl-7 col-lg-12 mb-4 mb-xl-0 d-flex flex-column">
                            <div class="border rounded p-3 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <div id="sankey-container" class="w-100"></div>
                            </div>
                        </div>

                        <!-- Right: Table -->
                        <div class="col-xl-5 col-lg-12 d-flex flex-column">
                            <div class="border rounded p-3 mb-4 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <h5 class="text-center fw-bold text-primary mb-3 mt-2" style="font-size: 1.25rem;">10 Besar
                                    Provinsi Asal Favorit Nasional</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;">Rank</th>
                                                <th>Kode</th>
                                                <th class="text-start">Provinsi Asal</th>
                                                <th class="text-end">Total Pergerakan</th>
                                                <th class="text-end">Persen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($top_origin as $index => $row)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $row['code'] }}</td>
                                                    <td class="text-start">{{ $row['name'] }}</td>
                                                    <td class="text-end fw-bold">
                                                        {{ number_format($row['total'], 0, ',', '.') }}</td>
                                                    <td class="text-end">{{ number_format($row['pct'], 2, ',', '.') }} %
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3 text-muted">Belum ada data
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Summary Box -->
                            <div class="rounded p-4 d-flex align-items-center justify-content-center text-center"
                                style="background-color: #e9f0f7; border: 1px solid #d2e0ed; min-height:100px;">
                                @if (count($top_origin) > 0)
                                    <span class="text-navy fw-medium" style="font-size: 1.05rem; line-height:1.5;">
                                        Pada periode <strong>13 Maret 2026 s/d 30 Maret 2026</strong>, <strong
                                            style="font-size:1.15rem; color:#1e3a8a;">Provinsi
                                            {{ ucwords(strtolower($top_origin[0]['name'])) }}</strong> merupakan Provinsi
                                        Asal dengan jumlah pergerakan terbanyak, yaitu sekitar <strong
                                            style="font-size:1.15rem;">{{ number_format($top_origin[0]['total'] / 1000000, 2, ',', '.') }}
                                            juta pergerakan</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    </div>

    <!-- 02 O-D PROVINSI TUJUAN & DESIRE LINE -->
    <div class="row mt-4" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">02</span>
                    <h5 class="fw-bold text-navy mb-0">O-D Provinsi Tujuan (10 besar provinsi tujuan favorit Nasional) &
                        Desire line</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">

                    <div class="row align-items-stretch">
                        <!-- Left: Sankey -->
                        <div class="col-xl-7 col-lg-12 mb-4 mb-xl-0 d-flex flex-column">
                            <div class="border rounded p-3 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <div id="sankey-container-tujuan" class="w-100"></div>
                            </div>
                        </div>

                        <!-- Right: Table -->
                        <div class="col-xl-5 col-lg-12 d-flex flex-column">
                            <div class="border rounded p-3 mb-4 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <h5 class="text-center fw-bold text-primary mb-3 mt-2" style="font-size: 1.25rem;">10 Besar
                                    Provinsi Tujuan Favorit Nasional</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;">Rank</th>
                                                <th>Kode</th>
                                                <th class="text-start">Provinsi Tujuan</th>
                                                <th class="text-end">Total Pergerakan</th>
                                                <th class="text-end">Persen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($top_dest as $index => $row)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $row['code'] }}</td>
                                                    <td class="text-start">{{ $row['name'] }}</td>
                                                    <td class="text-end fw-bold">
                                                        {{ number_format($row['total'], 0, ',', '.') }}</td>
                                                    <td class="text-end">{{ number_format($row['pct'], 2, ',', '.') }} %
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3 text-muted">Belum ada data
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Summary Box -->
                            <div class="rounded p-4 d-flex align-items-center justify-content-center text-center"
                                style="background-color: #e9f0f7; border: 1px solid #d2e0ed; min-height:100px;">
                                @if (count($top_dest) > 0)
                                    <span class="text-navy fw-medium" style="font-size: 1.05rem; line-height:1.5;">
                                        Pada periode <strong>13 Maret 2026 s/d 30 Maret 2026</strong>, <strong
                                            style="font-size:1.15rem; color:#1e3a8a;">Provinsi
                                            {{ ucwords(strtolower($top_dest[0]['name'])) }}</strong> merupakan Provinsi
                                        Tujuan dengan jumlah pergerakan terbanyak, yaitu sekitar <strong
                                            style="font-size:1.15rem;">{{ number_format($top_dest[0]['total'] / 1000000, 2, ',', '.') }}
                                            juta pergerakan</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- 03 TOP 10 KAB/KOTA -->
    <div class="row mt-4 mb-5" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">03</span>
                    <h5 class="fw-bold text-navy mb-0">Top 10 Kab/Kota (10 besar kab/kota asal dan tujuan favorit Nasional)
                    </h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">

                    <div class="row align-items-stretch">
                        <!-- Left: Sankey -->
                        <div class="col-xl-6 col-lg-12 mb-4 mb-xl-0 d-flex flex-column">
                            <div class="border rounded p-3 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <h5 class="text-center fw-bold text-primary mb-3 mt-2" style="font-size: 1.25rem;">Top 20
                                    Kab/Kota (Asal - Tujuan)</h5>
                                <div id="sankey-container-kabkota" class="w-100"></div>
                            </div>
                        </div>

                        <!-- Right: Table -->
                        <div class="col-xl-6 col-lg-12 d-flex flex-column">
                            <div class="border rounded p-3 mb-4 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <h5 class="text-center fw-bold text-primary mb-3 mt-2" style="font-size: 1.25rem;">10
                                    Besar Kabupaten/Kota Asal - Tujuan Favorit Nasional</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                        <thead>
                                            <tr>
                                                <th colspan="3" class="text-center"
                                                    style="background-color: #b0c4de !important;">Asal</th>
                                                <th colspan="3" class="text-center"
                                                    style="background-color: #b0c4de !important;">Tujuan</th>
                                            </tr>
                                            <tr>
                                                <th style="width: 40px;">Rank</th>
                                                <th class="text-start">Kabupaten/Kota</th>
                                                <th class="text-end">Total</th>
                                                <th style="width: 40px;">Rank</th>
                                                <th class="text-start">Kabupaten/Kota</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $maxRows = max(count($top_origin_kab), count($top_dest_kab));
                                                $maxRows = $maxRows > 10 ? 10 : $maxRows;
                                            @endphp
                                            @if ($maxRows > 0)
                                                @for ($i = 0; $i < $maxRows; $i++)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td class="text-start">
                                                            {{ isset($top_origin_kab[$i]) ? $top_origin_kab[$i]['name'] : '-' }}
                                                        </td>
                                                        <td class="text-end fw-bold">
                                                            {{ isset($top_origin_kab[$i]) ? number_format($top_origin_kab[$i]['total'], 0, ',', '.') : '-' }}
                                                        </td>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td class="text-start">
                                                            {{ isset($top_dest_kab[$i]) ? $top_dest_kab[$i]['name'] : '-' }}
                                                        </td>
                                                        <td class="text-end fw-bold">
                                                            {{ isset($top_dest_kab[$i]) ? number_format($top_dest_kab[$i]['total'], 0, ',', '.') : '-' }}
                                                        </td>
                                                    </tr>
                                                @endfor
                                            @else
                                                <tr>
                                                    <td colspan="6" class="text-center py-3 text-muted">Belum ada data
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Summary Box -->
                            <div class="rounded p-4 d-flex align-items-center justify-content-center text-center"
                                style="background-color: #e9f0f7; border: 1px solid #d2e0ed; min-height:100px;">
                                @if (count($top_origin_kab) > 0 && count($top_dest_kab) > 0)
                                    <span class="text-navy fw-medium" style="font-size: 1.05rem; line-height:1.5;">
                                        Pada periode <strong>13 Maret 2026 s/d 30 Maret 2026</strong>, <strong
                                            style="font-size:1.15rem; color:#1e3a8a;">{{ ucwords(strtolower($top_origin_kab[0]['name'])) }}</strong>
                                        merupakan Kab/Kota
                                        Asal terbanyak dengan <strong
                                            style="font-size:1.15rem;">{{ number_format($top_origin_kab[0]['total'] / 1000000, 2, ',', '.') }}
                                            juta pergerakan</strong>, sedangkan <strong
                                            style="font-size:1.15rem; color:#1e3a8a;">{{ ucwords(strtolower($top_dest_kab[0]['name'])) }}</strong>
                                        merupakan Tujuan terbanyak.
                                    </span>
                                @endif
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
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/sankey.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    once: true,
                    offset: 50,
                    duration: 600
                });
            }

            // === SANKEY CHART ===
            const sankeyData = @json($sankey);
            Highcharts.chart('sankey-container', {
                title: {
                    text: ''
                },
                series: [{
                    keys: ['from', 'to', 'weight'],
                    data: sankeyData,
                    type: 'sankey',
                    name: 'Pergerakan O-D',
                    dataLabels: {
                        style: {
                            color: '#1a1a1a',
                            textOutline: 'none',
                            fontSize: '10px'
                        }
                    }
                }],
                tooltip: {
                    headerFormat: '',
                    pointFormat: '<b>{point.from}</b> &rarr; <b>{point.to}</b><br/>Total: <b>{point.weight:,.0f}</b>'
                },
                credits: {
                    enabled: false
                }
            });

            // === SANKEY CHART TUJUAN ===
            Highcharts.chart('sankey-container-tujuan', {
                title: {
                    text: ''
                },
                series: [{
                    keys: ['from', 'to', 'weight'],
                    data: sankeyData,
                    type: 'sankey',
                    name: 'Pergerakan O-D',
                    dataLabels: {
                        style: {
                            color: '#1a1a1a',
                            textOutline: 'none',
                            fontSize: '10px'
                        }
                    }
                }],
                tooltip: {
                    headerFormat: '',
                    pointFormat: '<b>{point.from}</b> &rarr; <b>{point.to}</b><br/>Total: <b>{point.weight:,.0f}</b>'
                },
                credits: {
                    enabled: false
                }
            });

            // === SANKEY CHART KAB/KOTA ===
            const sankeyKabData = @json($sankey_kab);
            Highcharts.chart('sankey-container-kabkota', {
                title: {
                    text: ''
                },
                series: [{
                    keys: ['from', 'to', 'weight'],
                    data: sankeyKabData,
                    type: 'sankey',
                    name: 'Pergerakan O-D Kab/Kota',
                    dataLabels: {
                        style: {
                            color: '#1a1a1a',
                            textOutline: 'none',
                            fontSize: '9px'
                        }
                    }
                }],
                tooltip: {
                    headerFormat: '',
                    pointFormat: '<b>{point.from}</b> &rarr; <b>{point.to}</b><br/>Total: <b>{point.weight:,.0f}</b>'
                },
                credits: {
                    enabled: false
                }
            });

        });
    </script>
@endpush
