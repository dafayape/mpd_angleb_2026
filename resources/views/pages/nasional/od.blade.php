@extends('layout.app')

@section('title', 'Origin-Destination (OD) Nasional')

@section('content')
    @component('layout.partials.page-header', ['number' => '04', 'title' => 'Origin-Destination (OD) Nasional'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#">Nasional</a></li>
            <li class="breadcrumb-item active">Origin-Destination (OD) Nasional</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card placeholder-page-card shadow-sm">
                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                    <i class="bx bx-bar-chart-alt-2 text-primary mb-3" style="font-size: 64px;"></i>
                    <h5 class="fw-bold text-dark">Origin-Destination (OD) Nasional</h5>
                    <p class="text-muted">Halaman ini sedang dalam pengembangan.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
