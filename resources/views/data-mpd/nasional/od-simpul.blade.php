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

@push('css')
<style>
    .table th, .table td {
        vertical-align: middle;
        white-space: nowrap;
        font-size: 10px;
    }
    .hoverTable tbody tr:hover td {
        background-color: #e9f5ff !important;
    }
    .bg-soft-primary { background-color: rgba(85, 110, 230, 0.1) !important; }
</style>
@endpush

@php
    $tables = [
        ['title' => 'OD SIMPUL DARAT (TERMINAL)', 'data' => $simpul_darat, 'icon' => 'bx-bus'],
        ['title' => 'OD SIMPUL LAUT (PELABUHAN)', 'data' => $simpul_laut, 'icon' => 'bx-anchor'],
        ['title' => 'OD SIMPUL UDARA (BANDARA)', 'data' => $simpul_udara, 'icon' => 'bx-plane-alt'],
        ['title' => 'OD SIMPUL KERETA (STASIUN)', 'data' => $simpul_kereta, 'icon' => 'bx-train'],
    ];
@endphp

@foreach($tables as $tbl)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4 text-uppercase">
                    <i class='bx {{ $tbl["icon"] }} me-2 font-size-16 text-primary'></i> {{ $tbl['title'] }}
                </h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm w-100 hoverTable mb-0" style="border-collapse: collapse; border-spacing: 0;">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th class="bg-light shadow-sm" style="min-width: 80px;">TIPE DATA</th>
                                <th class="bg-light shadow-sm" style="min-width: 80px;">OPSEL</th>
                                @foreach($dates as $date)
                                    <th class="bg-soft-primary">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</th>
                                @endforeach
                                <th class="bg-dark text-white shadow-sm">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tbl['data'] as $opsel => $row)
                                <tr>
                                    <td class="text-center">{{ $row['tipe_data'] }}</td>
                                    <td class="text-center fw-bold">{{ $row['opsel'] }}</td>
                                    @foreach($dates as $date)
                                        <td class="text-end">{{ number_format($row[$date] ?? 0, 0, ',', '.') }}</td>
                                    @endforeach
                                    <td class="text-end fw-bold bg-light">{{ number_format($row['total'] ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($dates) + 3 }}" class="text-center py-4 text-muted">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
