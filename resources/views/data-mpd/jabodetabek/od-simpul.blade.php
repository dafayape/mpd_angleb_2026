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
                    <h5 class="card-title">Rekap Harian Simpul Pergerakan Origin (Jabodetabek)</h5>
                    <div>
                        <span class="badge bg-primary font-size-12">Periode: 13 Mar 2026 - 29 Mar 2026</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm w-100 mb-0" style="border-collapse: collapse; border-spacing: 0; font-size: 11px;">
                        <thead class="table-light text-center align-middle sticky-top" style="top: 0; z-index: 1;">
                            <tr>
                                <th rowspan="2" class="bg-light shadow-sm" style="min-width: 180px; width: 200px;">Kategori Simpul</th>
                                <th colspan="{{ $dates->count() }}" class="shadow-sm">Tanggal</th>
                                <th rowspan="2" class="bg-light shadow-sm" style="min-width: 80px;">Total</th>
                            </tr>
                            <tr>
                                @foreach($dates as $date)
                                    <th style="min-width: 65px; width: 65px;">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($matrix as $category => $data)
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
                                        <i class="bx bx-info-circle font-size-24 mb-2"></i>
                                        <p>Belum ada data tersedia untuk periode ini.</p>
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
                                            $dailyTotal = collect($matrix)->sum(fn($row) => $row[$date] ?? 0);
                                        @endphp
                                        {{ number_format($dailyTotal, 0, ',', '.') }}
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    @php
                                        $grandTotal = collect($matrix)->sum('total');
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
