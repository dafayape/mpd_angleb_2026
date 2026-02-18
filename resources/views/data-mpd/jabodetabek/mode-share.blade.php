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
                    <h5 class="card-title">Rekap Data Mode Share Jabodetabek</h5>
                    <div>
                        <span class="badge bg-primary font-size-12">Periode: 13 Mar 2026 - 29 Mar 2026</span>
                    </div>
                </div>

                <h5 class="mt-4 mb-3 text-primary"><i class="bx bx-run"></i> PERGERAKAN</h5>
                @if(request()->has('debug'))
                    @dump($movementMatrix)
                @endif
                <div class="table-responsive mb-5">
                    <table class="table table-bordered table-striped table-hover table-sm w-100" style="border-collapse: collapse; border-spacing: 0; font-size: 11px;">
                        <thead class="table-info text-dark text-center align-middle sticky-top" style="top: 0; z-index: 1;">
                            <tr>
                                <th rowspan="2" class="" style="min-width: 150px;">Moda Transportasi</th>
                                <th colspan="{{ $dates->count() }}">Tanggal</th>
                                <th rowspan="2" class="">Total</th>
                            </tr>
                            <tr>
                                @foreach($dates as $date)
                                    <th style="min-width: 80px;">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movementMatrix as $category => $data)
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
                                        Pergerakan: Belum ada data.
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
                <hr class="my-5" style="border-top: 2px dashed #ccc;">

                <h5 class="mt-4 mb-3 text-success"><i class="bx bx-user"></i> ORANG</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm dt-responsive nowrap w-100" style="border-collapse: collapse; border-spacing: 0; font-size: 11px;">
                        <thead class="table-success text-dark text-center align-middle sticky-top" style="top: 0; z-index: 1;">
                            <tr>
                                <th rowspan="2" class="" style="min-width: 150px;">Moda Transportasi</th>
                                <th colspan="{{ $dates->count() }}">Tanggal</th>
                                <th rowspan="2" class="">Total</th>
                            </tr>
                            <tr>
                                @foreach($dates as $date)
                                    <th style="min-width: 80px;">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($peopleMatrix as $category => $data)
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
                                        Orang: Belum ada data.
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
    }
    .sticky-top {
        position: sticky; 
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
    }
</style>
@endpush
