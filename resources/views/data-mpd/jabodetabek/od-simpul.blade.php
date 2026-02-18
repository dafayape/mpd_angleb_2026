@extends('layouts.app')

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
                    <table class="table table-bordered table-striped table-hover table-sm dt-responsive nowrap w-100" style="border-collapse: collapse; border-spacing: 0; font-size: 11px;">
                        <thead class="table-light text-center align-middle sticky-top" style="top: 0; z-index: 1;">
                            <tr>
                                <th rowspan="2" class="bg-light" style="min-width: 150px;">Kategori Simpul</th>
                                <th colspan="{{ $dates->count() }}">Tanggal</th>
                                <th rowspan="2" class="bg-light">Total</th>
                            </tr>
                            <tr>
                                @foreach($dates as $date)
                                    <th style="min-width: 80px;">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($matrix as $category => $data)
                                <tr>
                                    <td class="fw-bold">{{ $category }}</td>
                                    @foreach($dates as $date)
                                        <td class="text-end">
                                            {{ number_format($data[$date] ?? 0, 0, ',', '.') }}
                                        </td>
                                    @endforeach
                                    <td class="text-end fw-bold bg-light">
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
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td>Grand Total</td>
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
    }
    .sticky-top {
        position: sticky; 
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
    }
</style>
@endpush
