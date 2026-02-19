@extends('layout.app')

@section('title', 'Daily Report')

@section('content')
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Daily Report</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Executive Summary</a></li>
                        <li class="breadcrumb-item active">Daily Report</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-primary">Laporan Harian</h5>
                    <p class="card-text">Halaman ini akan menampilkan laporan harian detil.</p>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary">Tampilkan</button>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        Silakan pilih tanggal untuk melihat laporan harian.
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
