@extends('layout.app')

@section('title', 'Pengaturan')

@section('content')
    @component('layout.partials.page-header', ['number' => '36', 'title' => 'Pengaturan'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pengaturan</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card placeholder-page-card shadow-sm">
                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                    <i class="bx bx-cog text-primary mb-3" style="font-size: 64px;"></i>
                    <h5 class="fw-bold text-dark">Pengaturan Sistem</h5>
                    <p class="text-muted">Halaman ini sedang dalam pengembangan.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
