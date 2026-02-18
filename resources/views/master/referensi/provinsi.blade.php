@extends('layout.app')

@section('title', 'Referensi Provinsi')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Referensi Provinsi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Provinsi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div>
                            <h4 class="card-title mb-1">Data Referensi Provinsi</h4>
                            <p class="card-title-desc mb-0">Total: {{ $data->total() }} provinsi</p>
                        </div>
                        <form action="{{ route('master.referensi.provinsi') }}" method="GET">
                            <div class="input-group" style="width: 300px;">
                                <input type="text" class="form-control form-control-sm" name="search" placeholder="Cari kode / nama..." value="{{ request('search') }}">
                                <button class="btn btn-sm btn-primary" type="submit"><i class="bx bx-search"></i></button>
                                @if(request('search'))
                                    <a href="{{ route('master.referensi.provinsi') }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-x"></i></a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px" class="text-center">No</th>
                                    <th style="width:80px" class="text-center">Kode</th>
                                    <th>Nama Provinsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td class="text-center"><span class="badge bg-primary">{{ $item->code }}</span></td>
                                        <td>{{ $item->name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle font-size-20 d-block mb-1"></i>
                                            Data kosong. Jalankan: <code>php artisan db:seed --class=ProvinceSeeder</code>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($data->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }}</small>
                            {{ $data->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
