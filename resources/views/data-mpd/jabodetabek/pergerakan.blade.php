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
    .sticky-top {
        position: sticky; 
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
    }
    .bg-soft-primary { background-color: rgba(85, 110, 230, 0.1) !important; }
    .border-start-thick { border-left: 2px solid #aaa !important; }
</style>
@endpush

{{-- Helper Macro for Main Tables --}}
@php
    function renderMainTable($title, $data, $dates, $type) {
        $bgClass = $type === 'REAL' ? 'bg-success' : 'bg-warning text-dark';
        $textClass = $type === 'REAL' ? 'text-white' : 'text-dark';
        
        echo '<div class="row"><div class="col-12"><div class="card"><div class="card-body">';
        echo '<h5 class="card-title mb-3 text-uppercase"><i class="bx bx-table me-2"></i> ' . $title . '</h5>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-striped table-sm w-100 table-hover mb-0" style="font-size: 9px;">';
        
        // THEAD
        echo '<thead class="table-light text-center align-middle sticky-top">';
        echo '<tr>';
        echo '<th rowspan="2" class="bg-light" style="min-width: 80px;">TANGGAL</th>';
        
        // Opsel Headers
        foreach(['XL', 'IOH', 'TSEL'] as $op) {
            echo '<th colspan="3" class="'.$bgClass.' '.$textClass.' bg-opacity-75 border-start-thick">'.$op.'</th>';
        }
        // Total Header
        echo '<th colspan="5" class="bg-dark text-white border-start-thick">TOTAL</th>';
        echo '</tr>';

        echo '<tr>';
        // Opsel Subheaders
        for($i=0; $i<3; $i++) {
            echo '<th class="'.$bgClass.' '.$textClass.' bg-opacity-50 border-start">Jumlah</th>';
            echo '<th class="'.$bgClass.' '.$textClass.' bg-opacity-50">%</th>';
            echo '<th class="'.$bgClass.' '.$textClass.' bg-opacity-50">Label</th>';
        }
        // Total Subheaders
        echo '<th class="bg-secondary text-white border-start">Pergerakan</th>';
        echo '<th class="bg-secondary text-white">Akumulasi</th>';
        echo '<th class="bg-secondary text-white">Orang</th>';
        echo '<th class="bg-secondary text-white">Akumulasi</th>';
        echo '<th class="bg-dark text-warning">Rasio</th>'; // Mov/Ppl
        
        echo '</tr>';
        echo '</thead>';

        // TBODY
        echo '<tbody>';
        foreach($dates as $date) {
            $formattedDate = \Carbon\Carbon::parse($date)->locale('id')->isoFormat('YYYY-MM-DD');
            $row = $data[$date] ?? [];
            
            echo '<tr>';
            echo '<td class="fw-bold text-start">'.$formattedDate.'</td>';

            // Opsels: XL, IOH, TSEL
            foreach(['XL', 'IOH', 'TSEL'] as $op) {
                // Determine Data Key
                $dataKey = $op;
                
                $vol = $row['opsels'][$dataKey]['vol'] ?? 0;
                $pct = $row['opsels'][$dataKey]['pct'] ?? 0;
                $lbl = $row['opsels'][$dataKey]['label'] ?? '-';

                echo '<td class="text-end border-start">'.number_format($vol, 0, ',', '.').'</td>';
                echo '<td class="text-end text-muted">'.number_format($pct, 2, ',', '.').'%</td>';
                echo '<td class="text-end fst-italic">'.$lbl.'</td>';
            }

            // Totals
            $mov = $row['total_mov'] ?? 0;
            $movAcc = $row['accum_mov'] ?? 0;
            $ppl = $row['total_ppl'] ?? 0;
            $pplAcc = $row['accum_ppl'] ?? 0;
            
            $ratio = $ppl > 0 ? $mov / $ppl : 0;

            echo '<td class="text-end fw-bold bg-light border-start-thick">'.number_format($mov, 0, ',', '.').'</td>';
            echo '<td class="text-end text-muted small">'.number_format($movAcc, 0, ',', '.').'</td>';
            echo '<td class="text-end fw-bold bg-light border-start">'.number_format($ppl, 0, ',', '.').'</td>';
            echo '<td class="text-end text-muted small">'.number_format($pplAcc, 0, ',', '.').'</td>';
            echo '<td class="text-center fw-bold text-warning border-start bg-dark">'.number_format($ratio, 2, ',', '.').'</td>';

            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table></div></div></div></div></div>';
    }
@endphp

{{-- 1. Table REAL --}}
{{ renderMainTable('REAL PERGERAKAN & ORANG JABODETABEK PER OPSEL', $real, $dates, 'REAL') }}

{{-- 2. Table FORECAST --}}
{{ renderMainTable('FORECAST PERGERAKAN & ORANG JABODETABEK PER OPSEL', $forecast, $dates, 'FORECAST') }}

{{-- 3. Table Akumulasi --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3 text-uppercase"><i class="bx bx-chart me-2"></i> Akumulasi Pergerakan Harian (Real All Operator)</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm w-100 hoverTable mb-0" style="font-size: 10px;">
                        <thead class="table-light text-center align-middle sticky-top">
                            <tr>
                                <th rowspan="2" class="bg-light" style="min-width: 80px;">TANGGAL</th>
                                <th colspan="4" class="bg-primary text-white border-start-thick">Jumlah Pergerakan</th>
                                <th colspan="4" class="bg-info text-white border-start-thick">Jumlah Orang</th>
                            </tr>
                            <tr>
                                <th class="bg-soft-primary border-start">Jumlah</th>
                                <th class="bg-soft-primary">Persen</th>
                                <th class="bg-soft-primary">Label</th>
                                <th class="bg-soft-primary fw-bold">Akumulasi</th>
                                
                                <th class="bg-soft-info border-start">Jumlah</th>
                                <th class="bg-soft-info">Persen</th>
                                <th class="bg-soft-info">Label</th>
                                <th class="bg-soft-info fw-bold">Akumulasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dates as $date)
                                @php
                                    $formattedDate = \Carbon\Carbon::parse($date)->locale('id')->isoFormat('YYYY-MM-DD');
                                    $row = $accum[$date] ?? [];
                                @endphp
                                <tr>
                                    <td class="fw-bold text-start">{{ $formattedDate }}</td>
                                    
                                    {{-- Pergerakan --}}
                                    <td class="text-end border-start">{{ number_format($row['mov']['vol'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($row['mov']['pct'] ?? 0, 2, ',', '.') }} %</td>
                                    <td class="text-end fst-italic">{{ $row['mov']['label'] ?? '-' }}</td>
                                    <td class="text-end fw-bold bg-light">{{ number_format($row['mov']['accum'] ?? 0, 0, ',', '.') }}</td>

                                    {{-- Orang --}}
                                    <td class="text-end border-start">{{ number_format($row['ppl']['vol'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($row['ppl']['pct'] ?? 0, 2, ',', '.') }} %</td>
                                    <td class="text-end fst-italic">{{ $row['ppl']['label'] ?? '-' }}</td>
                                    <td class="text-end fw-bold bg-light">{{ number_format($row['ppl']['accum'] ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
