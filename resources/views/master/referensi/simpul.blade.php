@extends('layout.app')

@section('title', 'Referensi Simpul')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Referensi Simpul Transportasi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Simpul</li>
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
                            <h4 class="card-title mb-1">Data Referensi Simpul Transportasi</h4>
                            <p class="card-title-desc mb-0">Total: {{ $data->total() }} simpul</p>
                        </div>
                    </div>

                    <form action="{{ route('master.referensi.simpul') }}" method="GET" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-sm" name="search" placeholder="Cari kode / nama simpul..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" name="category" onchange="this.form.submit()">
                                    <option value="">— Semua Kategori —</option>
                                    <option value="BANDARA" {{ request('category') == 'BANDARA' ? 'selected' : '' }}>Bandara</option>
                                    <option value="PELABUHAN" {{ request('category') == 'PELABUHAN' ? 'selected' : '' }}>Pelabuhan</option>
                                    <option value="STASIUN" {{ request('category') == 'STASIUN' ? 'selected' : '' }}>Stasiun</option>
                                    <option value="TERMINAL" {{ request('category') == 'TERMINAL' ? 'selected' : '' }}>Terminal</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-sm btn-primary" type="submit"><i class="bx bx-search"></i> Cari</button>
                                @if(request('search') || request('category'))
                                    <a href="{{ route('master.referensi.simpul') }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-x"></i></a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px" class="text-center">No</th>
                                    <th style="width:120px" class="text-center">Kode</th>
                                    <th>Nama Simpul</th>
                                    <th style="width:150px">Kategori</th>
                                    <th style="width:150px">Sub Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td class="text-center"><span class="badge bg-warning text-dark">{{ $item->code }}</span></td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->category }}</td>
                                        <td>{{ $item->sub_category ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle font-size-20 d-block mb-1"></i>
                                            Data simpul belum tersedia.
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
