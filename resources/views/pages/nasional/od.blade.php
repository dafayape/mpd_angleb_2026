@extends('layout.app')

@section('title', 'Origin-Destination (OD) Nasional')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Origin-Destination (OD) Nasional</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Nasional</a></li>
                        <li class="breadcrumb-item active">Origin-Destination (OD) Nasional</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-bar-chart-alt-2 text-primary" style="font-size: 48px;"></i>
                    <h5 class="mt-3">Origin-Destination (OD) Nasional</h5>
                    <p class="text-muted">Halaman ini sedang dalam pengembangan.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
