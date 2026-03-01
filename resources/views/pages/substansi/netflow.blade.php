@extends('layout.app')

@section('title', $title)

@section('content')
    @component('layout.partials.page-header', ['number' => '10', 'title' => $title])
        <ol class="breadcrumb m-0 mb-0">
            @foreach ($breadcrumb as $crumb)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $crumb }}</li>
                @else
                    <li class="breadcrumb-item"><a href="#">{{ $crumb }}</a></li>
                @endif
            @endforeach
        </ol>
    @endcomponent

    @push('css')
        <style>
            .table th,
            .table td {
                vertical-align: middle;
                font-size: 11.5px;
            }

            .table-custom th {
                background-color: #c8d9e8 !important;
                font-weight: 600;
                font-size: 12px;
                border-color: #999;
            }

            .table-custom td {
                font-size: 12.5px;
                border-color: #ccc;
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

            .border-custom-thick {
                border-width: 2px !important;
                border-color: #aab5c3 !important;
            }
        </style>
    @endpush

    <!-- Section 01 -->
    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">01</span>
                    <h5 class="fw-bold text-navy mb-0">20 Besar Kabupaten/Kota Asal Nasional berdasarkan kekuatan Netflow
                        MPD</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">
                    <div class="table-responsive border rounded border-custom-thick">
                        <table class="table table-bordered table-striped table-custom mb-0 text-center">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Rank</th>
                                    <th class="text-start">Kabupaten/Kota</th>
                                    <th class="text-end">Pergerakan Keluar (Outflow)</th>
                                    <th class="text-end">Pergerakan Masuk (Inflow)</th>
                                    <th class="text-end">Netflow</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($top_origin_netflow as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-start">{{ $row['name'] }}</td>
                                        <td class="text-end">{{ number_format($row['outflow'], 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($row['inflow'], 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            @if ($row['netflow'] < 0)
                                                - {{ number_format(abs($row['netflow']), 0, ',', '.') }}
                                            @else
                                                {{ number_format($row['netflow'], 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td>{{ $row['keterangan'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 02 -->
    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">02</span>
                    <h5 class="fw-bold text-navy mb-0">20 Besar Kabupaten/Kota Tujuan Nasional berdasarkan kekuatan Netflow
                        MPD</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">
                    <div class="table-responsive border rounded border-custom-thick">
                        <table class="table table-bordered table-striped table-custom mb-0 text-center">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Rank</th>
                                    <th class="text-start">Kabupaten/Kota</th>
                                    <th class="text-end">Pergerakan Keluar (Outflow)</th>
                                    <th class="text-end">Pergerakan Masuk (Inflow)</th>
                                    <th class="text-end">Netflow</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($top_dest_netflow as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-start">{{ $row['name'] }}</td>
                                        <td class="text-end">{{ number_format($row['outflow'], 0, ',', '.') }}</td>
                                        <td class="text-end">{{ number_format($row['inflow'], 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            @if ($row['netflow'] < 0)
                                                - {{ number_format(abs($row['netflow']), 0, ',', '.') }}
                                            @else
                                                {{ number_format($row['netflow'], 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td>{{ $row['keterangan'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 03 -->
    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">03</span>
                    <h5 class="fw-bold text-navy mb-0">Perbandingan 20 Besar Asal dan Tujuan Berdasarkan Kekuatan NFR</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">
                    <div class="row">
                        <!-- Left Table -->
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h6 class="fw-bold text-center mb-3">20 Besar Kabupaten/Kota Asal Nasional berdasarkan kekuatan
                                NFR</h6>
                            <div class="table-responsive border rounded border-custom-thick">
                                <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">Rank</th>
                                            <th class="text-start">Kabupaten/Kota</th>
                                            <th class="text-end" style="font-size: 10px;">Outflow</th>
                                            <th class="text-end" style="font-size: 10px;">Inflow</th>
                                            <th class="text-end">Netflow</th>
                                            <th>NFR</th>
                                            <th>Ket</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($top_origin_nfr as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-start">{{ $row['name'] }}</td>
                                                <td class="text-end">{{ number_format($row['outflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">{{ number_format($row['inflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">
                                                    @if ($row['netflow'] < 0)
                                                        - {{ number_format(abs($row['netflow']), 0, ',', '.') }}
                                                    @else
                                                        {{ number_format($row['netflow'], 0, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td>{{ number_format($row['nfr'], 2, ',', '.') }}</td>
                                                <td>{{ $row['keterangan'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted">Belum ada data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Right Table -->
                        <div class="col-lg-6">
                            <h6 class="fw-bold text-center mb-3">20 Besar Kabupaten/Kota Tujuan Nasional berdasarkan
                                kekuatan NFR</h6>
                            <div class="table-responsive border rounded border-custom-thick">
                                <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">Rank</th>
                                            <th class="text-start">Kabupaten/Kota</th>
                                            <th class="text-end" style="font-size: 10px;">Outflow</th>
                                            <th class="text-end" style="font-size: 10px;">Inflow</th>
                                            <th class="text-end">Netflow</th>
                                            <th>NFR</th>
                                            <th>Ket</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($top_dest_nfr as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-start">{{ $row['name'] }}</td>
                                                <td class="text-end">{{ number_format($row['outflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">{{ number_format($row['inflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">
                                                    @if ($row['netflow'] < 0)
                                                        - {{ number_format(abs($row['netflow']), 0, ',', '.') }}
                                                    @else
                                                        {{ number_format($row['netflow'], 0, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td>{{ number_format($row['nfr'], 2, ',', '.') }}</td>
                                                <td>{{ $row['keterangan'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted">Belum ada data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
