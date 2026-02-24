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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title">Akumulasi Harian Pergerakan & Orang (Jabodetabek)</h5>
                        <div>
                            <span class="badge bg-primary font-size-12">Periode: 13 Mar 2026 - 30 Mar 2026</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-sm w-100 mb-0"
                            style="border-collapse: collapse; border-spacing: 0; font-size: 11px;">
                            <thead class="table-light text-center align-middle sticky-top" style="top: 0; z-index: 1;">
                                <tr>
                                    <th rowspan="2" class="bg-light shadow-sm" style="min-width: 50px; width: 50px;">No
                                    </th>
                                    <th rowspan="2" class="bg-light shadow-sm" style="min-width: 150px;">Tanggal</th>
                                    <th colspan="2" class="bg-primary text-white shadow-sm">REAL</th>
                                </tr>
                                <tr>
                                    <th class="bg-soft-primary text-primary" style="min-width: 150px;">Pergerakan</th>
                                    <th class="bg-soft-primary text-primary" style="min-width: 150px;">Orang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dates as $index => $date)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="text-center fw-bold">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                        </td>

                                        @php
                                            $mov = $data[$date]['movement'] ?? 0;
                                            $ppl = $data[$date]['people'] ?? 0;
                                        @endphp

                                        <td class="text-end border-start fw-bold">{{ number_format($mov, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($ppl, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold text-end">
                                <tr>
                                    <td colspan="2" class="text-center">Grand Total</td>
                                    @php
                                        $totalMov = collect($data)->sum('movement');
                                        $totalPpl = collect($data)->sum('people');
                                    @endphp
                                    <td class="text-end border-start">{{ number_format($totalMov, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totalPpl, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .table th,
        .table td {
            vertical-align: middle;
            white-space: nowrap;
            font-size: 11px;
        }

        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
        }

        .bg-soft-primary {
            background-color: rgba(85, 110, 230, 0.1) !important;
        }

        .border-start {
            border-left: 1px solid #dee2e6 !important;
        }
    </style>
@endpush
