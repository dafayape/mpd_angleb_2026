@extends('layout.app')

@section('title', 'Referensi Kabupaten/Kota')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Referensi Kabupaten / Kota</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Kabupaten / Kota</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data Referensi Kabupaten / Kota</h4>
                    <p class="card-title-desc">Daftar kabupaten/kota se-Indonesia ({{ $data->total() }} data)</p>

                    <form action="{{ route('master.referensi.kabkota') }}" method="GET" class="mb-3">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari kode atau nama..." value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i></button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="province_code" onchange="this.form.submit()">
                                    <option value="">Semua Provinsi</option>
                                    @foreach($provinces as $prov)
                                        <option value="{{ $prov->code }}" {{ request('province_code') == $prov->code ? 'selected' : '' }}>{{ $prov->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                @if(request('search') || request('province_code'))
                                    <a href="{{ route('master.referensi.kabkota') }}" class="btn btn-secondary"><i class="bx bx-reset"></i> Reset</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped dt-responsive nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 100px;">Kode</th>
                                    <th>Nama Kabupaten / Kota</th>
                                    <th>Provinsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td><span class="badge bg-info bg-soft text-info">{{ $item->code }}</span></td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->province_name ?? $item->province_code }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle font-size-20"></i><br>
                                            Data kosong. Jalankan seeder: <code>php artisan db:seed --class=CitySeeder</code>
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
