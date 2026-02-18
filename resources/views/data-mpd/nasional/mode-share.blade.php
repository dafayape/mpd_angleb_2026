@extends('layout.app')

@section('title', $title)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    @foreach($breadcrumb as $crumb)
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
                    <h5 class="card-title">Rekap Data Mode Share Nasional</h5>
                    <div>
                        <span class="badge bg-primary font-size-12">Periode: 13 Mar 2026 - 29 Mar 2026</span>
                    </div>
                </div>

                {{-- Section 1: Pergerakan --}}
                <h5 class="mt-4 mb-3 text-primary"><i class="bx bx-run"></i> PERGERAKAN</h5>
                <div class="table-responsive mb-5">
                    <table class="table table-bordered table-striped table-hover table-sm w-100 mb-0" style="border-collapse: collapse; border-spacing: 0; font-size: 11px;">
                        <thead class="table-info text-dark text-center align-middle sticky-top" style="top: 0; z-index: 1;">
                            <tr>
                                <th rowspan="2" class="shadow-sm" style="min-width: 180px; width: 200px;">Moda Transportasi</th>
                                <th colspan="{{ $dates->count() }}" class="shadow-sm">Tanggal</th>
                                <th rowspan="2" class="shadow-sm" style="min-width: 80px;">Total</th>
                            </tr>
                            <tr>
                                @foreach($dates as $date)
                                    <th style="min-width: 65px; width: 65px; border-bottom: 2px solid #ccc;">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movementMatrix as $category => $data)
                                <tr>
                                    <td class="fw-bold align-middle">{{ $category }}</td>
                                    @foreach($dates as $date)
                                        <td class="text-end align-middle">
                                            @if(($data[$date] ?? 0) > 0)
                                                {{ number_format($data[$date], 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-end fw-bold bg-light align-middle">
                                        {{ number_format($data['total'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $dates->count() + 2 }}" class="text-center py-4 text-muted">
                                        Pergerakan: Belum ada data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold text-end">
                            <tr>
                                <td class="text-start">Grand Total</td>
                                @foreach($dates as $date)
                                    <td class="text-end">
                                        @php
                                            $dailyTotal = collect($movementMatrix)->sum(fn($row) => $row[$date] ?? 0);
                                        @endphp
                                        {{ number_format($dailyTotal, 0, ',', '.') }}
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    @php
                                        $grandTotal = collect($movementMatrix)->sum('total');
                                    @endphp
                                    {{ number_format($grandTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Section 2: Orang --}}
                <hr class="my-5" style="border-top: 2px dashed #999;">

                <h5 class="mt-4 mb-3 text-success"><i class="bx bx-user"></i> ORANG</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm w-100 mb-0" style="border-collapse: collapse; border-spacing: 0; font-size: 11px;">
                        <thead class="table-success text-dark text-center align-middle sticky-top" style="top: 0; z-index: 1;">
                            <tr>
                                <th rowspan="2" class="shadow-sm" style="min-width: 180px; width: 200px;">Moda Transportasi</th>
                                <th colspan="{{ $dates->count() }}" class="shadow-sm">Tanggal</th>
                                <th rowspan="2" class="shadow-sm" style="min-width: 80px;">Total</th>
                            </tr>
                            <tr>
                                @foreach($dates as $date)
                                    <th style="min-width: 65px; width: 65px; border-bottom: 2px solid #ccc;">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peopleMatrix as $category => $data)
                                <tr>
                                    <td class="fw-bold align-middle">{{ $category }}</td>
                                    @foreach($dates as $date)
                                        <td class="text-end align-middle">
                                            @if(($data[$date] ?? 0) > 0)
                                                {{ number_format($data[$date], 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-end fw-bold bg-light align-middle">
                                        {{ number_format($data['total'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $dates->count() + 2 }}" class="text-center py-4 text-muted">
                                        Orang: Belum ada data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold text-end">
                            <tr>
                                <td class="text-start">Grand Total</td>
                                @foreach($dates as $date)
                                    <td class="text-end">
                                        @php
                                            $dailyTotal = collect($peopleMatrix)->sum(fn($row) => $row[$date] ?? 0);
                                        @endphp
                                        {{ number_format($dailyTotal, 0, ',', '.') }}
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    @php
                                        $grandTotal = collect($peopleMatrix)->sum('total');
                                    @endphp
                                    {{ number_format($grandTotal, 0, ',', '.') }}
                                </td>
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
    .table th, .table td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .sticky-top {
        position: sticky; 
        top: 0;
        z-index: 100;
    }
    .shadow-sm {
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
    }
    
    /* Ensure borders are visible on sticky headers */
    .table-bordered th, .table-bordered td {
        border: 1px solid #dee2e6;
    }
    
    /* Custom Scrollbar for horizontal scrolling */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #888; 
        border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555; 
    }
</style>
@endpush
