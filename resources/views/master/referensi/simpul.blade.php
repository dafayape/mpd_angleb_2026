@extends('layout.app')

@section('title', 'Referensi Simpul')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Referensi Simpul</h4>
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
                    <h4 class="card-title">Data Referensi Simpul Transportasi</h4>
                    <p class="card-title-desc">Daftar simpul/node transportasi ({{ $data->total() }} data)</p>

                    <form action="{{ route('master.referensi.simpul') }}" method="GET" class="mb-3">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari kode atau nama simpul..." value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i></button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category" onchange="this.form.submit()">
                                    <option value="">Semua Kategori</option>
                                    <option value="BANDARA" {{ request('category') == 'BANDARA' ? 'selected' : '' }}>Bandara</option>
                                    <option value="PELABUHAN" {{ request('category') == 'PELABUHAN' ? 'selected' : '' }}>Pelabuhan</option>
                                    <option value="STASIUN" {{ request('category') == 'STASIUN' ? 'selected' : '' }}>Stasiun</option>
                                    <option value="TERMINAL" {{ request('category') == 'TERMINAL' ? 'selected' : '' }}>Terminal</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                @if(request('search') || request('category'))
                                    <a href="{{ route('master.referensi.simpul') }}" class="btn btn-secondary"><i class="bx bx-reset"></i> Reset</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped dt-responsive nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 130px;">Kode</th>
                                    <th>Nama Simpul</th>
                                    <th>Kategori</th>
                                    <th>Sub Kategori</th>
                                    <th>Radius</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td><span class="badge bg-warning bg-soft text-warning">{{ $item->code }}</span></td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->category }}</td>
                                        <td>{{ $item->sub_category ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle font-size-20"></i><br>
                                            Data simpul belum tersedia.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
