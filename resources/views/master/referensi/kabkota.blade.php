@extends('layout.app')

@section('title', 'Referensi Kabupaten / Kota')

@section('content')
    @component('layout.partials.page-header', ['number' => '25', 'title' => 'Referensi Kabupaten / Kota'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active">Kabupaten / Kota</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div>
                            <h4 class="card-title mb-1">Data Referensi Kabupaten / Kota</h4>
                            <p class="card-title-desc mb-0">Total: {{ $data->total() }} kabupaten/kota</p>
                        </div>
                    </div>

                    <form action="{{ route('master.referensi.kabkota') }}" method="GET" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <input type="text" class="form-control form-control-sm" name="search"
                                    placeholder="Cari kode / nama..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" name="province_code"
                                    onchange="this.form.submit()">
                                    <option value="">— Semua Provinsi —</option>
                                    @foreach ($provinces as $prov)
                                        <option value="{{ $prov->code }}"
                                            {{ request('province_code') == $prov->code ? 'selected' : '' }}>
                                            {{ $prov->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-sm btn-primary" type="submit"><i class="bx bx-search"></i>
                                    Cari</button>
                                @if (request('search') || request('province_code'))
                                    <a href="{{ route('master.referensi.kabkota') }}"
                                        class="btn btn-sm btn-outline-secondary"><i class="bx bx-x"></i></a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px" class="text-center">No</th>
                                    <th style="width:80px" class="text-center">Kode</th>
                                    <th>Nama Kabupaten / Kota</th>
                                    <th style="width:220px">Provinsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td class="text-center">
                                            {{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td class="text-center"><span class="badge bg-info">{{ $item->code }}</span></td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->province_name ?? $item->province_code }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle font-size-20 d-block mb-1"></i>
                                            Data kosong. Jalankan: <code>php artisan db:seed --class=CitySeeder</code>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($data->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari
                                {{ $data->total() }}</small>
                            {{ $data->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
