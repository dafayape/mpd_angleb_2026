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
                    <h5 class="card-title mb-4 text-primary">REKAPTULASI PERGERAKAN ORANG PER OPSEL (NASIONAL)</h5>
                    <div>
                        <span class="badge bg-primary font-size-12">Periode: 13 Mar 2026 - 29 Mar 2026</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm w-100 mb-0" style="border-collapse: collapse; border-spacing: 0; font-size: 11px;">
                        <thead class="table-light text-center align-middle sticky-top" style="top: 0; z-index: 1;">
                            {{-- Header Row 1 --}}
                            <tr>
                                <th rowspan="3" class="bg-light shadow-sm align-middle" style="min-width: 100px;">Tanggal</th>
                                <th colspan="6" class="bg-primary text-white shadow-sm border-start">XL Smart</th>
                                <th colspan="6" class="bg-warning text-white shadow-sm border-start">IOH</th>
                                <th colspan="6" class="bg-danger text-white shadow-sm border-start">TSEL</th>
                            </tr>
                            {{-- Header Row 2 --}}
                            <tr>
                                {{-- XL --}}
                                <th colspan="3" class="bg-soft-primary text-primary border-start">Pergerakan</th>
                                <th colspan="3" class="bg-soft-primary text-primary border-start">Orang</th>
                                {{-- IOH --}}
                                <th colspan="3" class="bg-soft-warning text-warning border-start">Pergerakan</th>
                                <th colspan="3" class="bg-soft-warning text-warning border-start">Orang</th>
                                {{-- TSEL --}}
                                <th colspan="3" class="bg-soft-danger text-danger border-start">Pergerakan</th>
                                <th colspan="3" class="bg-soft-danger text-danger border-start">Orang</th>
                            </tr>
                            {{-- Header Row 3 --}}
                            <tr>
                                {{-- Loop for each provider x 2 categories --}}
                                @for($i = 0; $i < 3; $i++) 
                                    {{-- Pergerakan --}}
                                    <th class="border-start">Jumlah</th>
                                    <th>%</th>
                                    <th>Label</th>
                                    {{-- Orang --}}
                                    <th class="border-start">Jumlah</th>
                                    <th>%</th>
                                    <th>Label</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dates as $date)
                                <tr>
                                    <td class="text-center fw-bold">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                                    
                                    {{-- Logic to display data --}}
                                    @foreach(['XL', 'IOH', 'TSEL'] as $opsel)
                                        @php
                                            $row = $data[$date][$opsel] ?? ['movement' => 0, 'people' => 0];
                                            $totalDay = $data[$date]['Total']['movement'] ?? 1; // Avoid divide by zero
                                            if($totalDay == 0) $totalDay = 1;
                                            
                                            $pct = ($row['movement'] / $totalDay) * 100;
                                        @endphp
                                        
                                        {{-- Pergerakan --}}
                                        <td class="text-end border-start">{{ number_format($row['movement'], 0, ',', '.') }}</td>
                                        <td class="text-end text-muted">{{ number_format($pct, 1) }}%</td>
                                        <td class="text-center text-muted">-</td>

                                        {{-- Orang --}}
                                        <td class="text-end border-start">{{ number_format($row['people'], 0, ',', '.') }}</td>
                                        <td class="text-end text-muted">{{ number_format($pct, 1) }}%</td>
                                        <td class="text-center text-muted">-</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="19" class="text-center py-4 text-muted">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold text-end">
                            <tr>
                                <td class="text-center">Grand Total</td>
                                @foreach(['XL', 'IOH', 'TSEL'] as $opsel)
                                    @php
                                        $totalMov = collect($data)->sum(fn($d) => $d[$opsel]['movement']);
                                        $totalPpl = collect($data)->sum(fn($d) => $d[$opsel]['people']);
                                        
                                        $grandTotalMov = collect($data)->sum(fn($d) => $d['Total']['movement']);
                                        if($grandTotalMov == 0) $grandTotalMov = 1;
                                        
                                        $pct = ($totalMov / $grandTotalMov) * 100;
                                    @endphp
                                    <td class="text-end border-start">{{ number_format($totalMov, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($pct, 1) }}%</td>
                                    <td></td>
                                    <td class="text-end border-start">{{ number_format($totalPpl, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($pct, 1) }}%</td>
                                    <td></td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Section: Akumulasi --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4 text-primary">AKUMULASI PERGERAKAN ORANG (NASIONAL)</h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm w-100 mb-0" style="border-collapse: collapse; border-spacing: 0; font-size: 11px;">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th rowspan="2" class="bg-light shadow-sm" style="min-width: 150px;">Tanggal</th>
                                <th colspan="2" class="bg-success text-white shadow-sm">REAL</th>
                            </tr>
                            <tr>
                                <th class="bg-soft-success text-success" style="min-width: 150px;">Pergerakan</th>
                                <th class="bg-soft-success text-success" style="min-width: 150px;">Orang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dates as $index => $date)
                                <tr>
                                    <td class="text-center fw-bold">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</td>
                                    
                                    @php
                                        // $data[$date]['Total'] is already populated in getPergerakanData
                                        $mov = $data[$date]['Total']['movement'] ?? 0;
                                        $ppl = $data[$date]['Total']['people'] ?? 0;
                                    @endphp

                                    <td class="text-end border-start fw-bold">{{ number_format($mov, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($ppl, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light fw-bold text-end">
                            <tr>
                                <td class="text-center">Grand Total</td>
                                @php
                                    $grandTotalMov = collect($data)->sum(fn($d) => $d['Total']['movement'] ?? 0);
                                    $grandTotalPpl = collect($data)->sum(fn($d) => $d['Total']['people'] ?? 0);
                                @endphp
                                <td class="text-end border-start">{{ number_format($grandTotalMov, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($grandTotalPpl, 0, ',', '.') }}</td>
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
        font-size: 10px; /* Smaller font for complex table */
    }
    .sticky-top {
        position: sticky; 
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
    }
    .bg-soft-primary { background-color: rgba(85, 110, 230, 0.1) !important; }
    .bg-soft-warning { background-color: rgba(241, 180, 76, 0.1) !important; }
    .bg-soft-danger { background-color: rgba(244, 106, 106, 0.1) !important; }
    .bg-soft-success { background-color: rgba(52, 195, 143, 0.1) !important; }
    
    .border-start { border-left: 1px solid #dee2e6 !important; }
</style>
@endpush
