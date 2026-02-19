@extends('layouts.app')

@section('title', $title)

@push('css')
<style>
    .table-custom th {
        background-color: #f8f9fa;
        font-weight: 600;
        vertical-align: middle;
        text-align: center;
        font-size: 11px;
    }
    .table-custom td {
        font-size: 11px;
        vertical-align: middle;
        text-align: right;
    }
    .table-custom td:first-child, .table-custom td:nth-child(2) {
        text-align: left;
    }
    .hover-row:hover td {
        background-color: #e9f5ff !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    @foreach($breadcrumb as $crumb)
                        <li class="breadcrumb-item">{{ $crumb }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        
        @foreach(['DARAT', 'LAUT', 'UDARA', 'KERETA'] as $cat)
        <h5 class="mt-4 mb-3">OD SIMPUL {{ $cat }}</h5>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered table-custom">
                <thead>
                    <tr>
                        <th style="min-width: 80px;">TIPE DATA</th>
                        <th style="min-width: 60px;">OPSEL</th>
                        @foreach($data['dates'] as $date)
                        <th>{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['report'][$cat] as $row)
                    <tr class="hover-row">
                        <td>
                            @if($row['tipe_data'] === 'REAL')
                                <span class="badge bg-primary">REAL</span>
                            @else
                                <span class="badge bg-warning text-dark">FORECAST</span>
                            @endif
                        </td>
                        <td>
                            @if($row['opsel'] === 'XL') <span class="text-primary fw-bold">XL</span>
                            @elseif($row['opsel'] === 'IOH') <span class="text-warning fw-bold" style="color:#d4a017 !important">IOH</span>
                            @elseif($row['opsel'] === 'TSEL') <span class="text-danger fw-bold">TSEL</span>
                            @endif
                        </td>
                        @foreach($data['dates'] as $date)
                        <td>{{ number_format($row['dates'][$date]) }}</td>
                        @endforeach
                        <td class="fw-bold bg-light">{{ number_format($row['total']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <hr>
        @endforeach

    </div>
</div>
@endsection
