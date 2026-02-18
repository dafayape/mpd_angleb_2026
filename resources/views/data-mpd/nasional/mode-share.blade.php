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
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    .bg-soft-primary { background-color: rgba(85, 110, 230, 0.1) !important; }
    .border-start-thick { border-left: 2px solid #aaa !important; }
</style>
@endpush

{{-- Helper Macro for Aggregated Tables --}}
@php
    function renderAggregatedTable($title, $data, $dates) {
        // Headers
        echo '<div class="row"><div class="col-12"><div class="card"><div class="card-body">';
        echo '<h5 class="card-title mb-3 text-uppercase"><i class="bx bx-table me-2"></i> ' . $title . '</h5>';
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-striped table-sm w-100 table-hover mb-0" style="font-size: 9px;">';
        
        // THEAD
        echo '<thead class="table-light text-center align-middle sticky-top">';
        echo '<tr>';
        echo '<th rowspan="2" class="bg-light" style="min-width: 80px;">TANGGAL</th>';
        
        // REAL Header
        echo '<th colspan="6" class="bg-success text-white border-start-thick">REAL</th>';
        // FORECAST Header
        echo '<th colspan="6" class="bg-warning text-dark border-start-thick">FORECAST</th>';
        // TOTAL Header
        echo '<th rowspan="2" class="bg-dark text-white border-start-thick">TOTAL</th>';
        echo '</tr>';

        echo '<tr>';
        // REAL Subheaders
        foreach(['XL', 'IOH', 'TSEL'] as $op) {
            echo '<th class="bg-success text-white bg-opacity-75 border-start">'.$op.'<br>Pergerakan</th>';
            echo '<th class="bg-success text-white bg-opacity-75">'.$op.'<br>Orang</th>';
        }
        // FORECAST Subheaders
        foreach(['XL', 'IOH', 'TSEL'] as $op) {
            echo '<th class="bg-warning text-dark bg-opacity-75 border-start">'.$op.'<br>Pergerakan</th>';
            echo '<th class="bg-warning text-dark bg-opacity-75">'.$op.'<br>Orang</th>';
        }
        echo '</tr>';
        echo '</thead>';

        // TBODY
        echo '<tbody>';
        foreach($dates as $date) {
            $formattedDate = \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM Y');
            // Data for this date
            $rowReal = $data[$date]['REAL'] ?? [];
            $rowFc = $data[$date]['FORECAST'] ?? [];
            
            $totalRow = 0;

            echo '<tr>';
            echo '<td class="fw-bold text-start">'.$formattedDate.'</td>';

            // REAL Columns
            foreach(['XL', 'IOH', 'TSEL'] as $op) {
                $val = $rowReal[$op]['mov'] ?? 0;
                $ppl = $rowReal[$op]['ppl'] ?? 0;
                $totalRow += $val; 
                // Note: user sample shows Total column. Usually sum of movements? Or movements + people? 
                // Sample "TOTAL" implies sum of movements across all opsels/types? Or just sum of movements?
                // User sample total: 3,837,878 (Kamis 18 Des). 
                // Sum reals: 100460 + 34209 + 1684968 = 1,819,637. 
                // Wait, sample columns: REAL (XL Mov, XL Ppl, IOH Mov, IOH Ppl, TSEL Mov, TSEL Ppl) FOREcast (...) 
                // If I sum REAL XL Mov + IOH Mov + TSEL Mov + FORECAST XL Mov + ...
                // Let's assume Total is sum of ALL Movement columns (Real + Forecast).
                
                echo '<td class="text-end border-start">'.number_format($val, 0, ',', '.').'</td>';
                echo '<td class="text-end text-muted">'.number_format($ppl, 0, ',', '.').'</td>';
            }
            
            // FORECAST Columns
            foreach(['XL', 'IOH', 'TSEL'] as $op) {
                $val = $rowFc[$op]['mov'] ?? 0;
                $ppl = $rowFc[$op]['ppl'] ?? 0;
                $totalRow += $val;

                echo '<td class="text-end border-start">'.number_format($val, 0, ',', '.').'</td>';
                echo '<td class="text-end text-muted">'.number_format($ppl, 0, ',', '.').'</td>';
            }

            echo '<td class="text-end fw-bold bg-light border-start-thick">'.number_format($totalRow, 0, ',', '.').'</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table></div></div></div></div></div>';
    }
@endphp

{{-- 1. Table Angkutan UMUM --}}
{{ renderAggregatedTable('ANGKUTAN UMUM', $data_umum, $dates) }}

{{-- 2. Table Angkutan PRIBADI --}}
{{ renderAggregatedTable('ANGKUTAN PRIBADI', $data_pribadi, $dates) }}

{{-- 3. Table Detailed Breakdown --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3 text-uppercase"><i class="bx bx-list-ul me-2"></i> PER OPSEL - PER TIPE - PER MODA</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm w-100 hoverTable mb-0" style="font-size: 10px;">
                        <thead class="table-light text-center align-middle sticky-top">
                            <tr>
                                <th class="bg-light" style="min-width: 50px;">OPSEL</th>
                                <th class="bg-light" style="min-width: 150px;">ANGKUTAN</th>
                                <th class="bg-light" style="min-width: 80px;">TIPE DATA</th>
                                <th class="bg-light" style="min-width: 80px;">KATEGORI</th>
                                @foreach($dates as $date)
                                     <th class="bg-soft-primary" style="min-width: 60px;">{{ \Carbon\Carbon::parse($date)->format('d M') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data_detail as $row)
                                <tr>
                                    <td class="text-center fw-bold">{{ $row['opsel'] }}</td>
                                    <td>{{ $row['moda'] }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $row['tipe'] === 'REAL' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $row['tipe'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $row['kategori'] }}</td>
                                    @foreach($dates as $date)
                                        <td class="text-end">{{ number_format($row['daily'][$date] ?? 0, 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($dates) + 4 }}" class="text-center">No Data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
