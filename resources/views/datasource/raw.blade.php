@extends('layout.app')

@section('title', 'View Raw Data')

@section('content')
    @component('layout.partials.page-header', ['number' => '31', 'title' => 'View Raw Data'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('datasource.history') }}">Datasource</a></li>
            <li class="breadcrumb-item active">Raw Data</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">View Raw Data MPD</h4>
                    <p class="card-title-desc">Data mentah hasil import dari CSV — PostgreSQL + BRIN Index.</p>

                    <form action="{{ route('datasource.raw-data') }}" method="GET" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Rentang Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" name="start_date"
                                        value="{{ request('start_date') }}">
                                    <span class="input-group-text">s/d</span>
                                    <input type="date" class="form-control" name="end_date"
                                        value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Opsel</label>
                                <select class="form-select" name="opsel">
                                    <option value="">Semua Opsel</option>
                                    <option value="TSEL" {{ request('opsel') == 'TSEL' ? 'selected' : '' }}>TSEL</option>
                                    <option value="IOH" {{ request('opsel') == 'IOH' ? 'selected' : '' }}>IOH</option>
                                    <option value="XL" {{ request('opsel') == 'XL' ? 'selected' : '' }}>XL</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" name="kategori">
                                    <option value="">Semua</option>
                                    <option value="ORANG" {{ request('kategori') == 'ORANG' ? 'selected' : '' }}>ORANG
                                    </option>
                                    <option value="PERGERAKAN" {{ request('kategori') == 'PERGERAKAN' ? 'selected' : '' }}>
                                        PERGERAKAN</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipe</label>
                                <select class="form-select" name="is_forecast">
                                    <option value="">Semua</option>
                                    <option value="0" {{ request('is_forecast') === '0' ? 'selected' : '' }}>REAL
                                    </option>
                                    <option value="1" {{ request('is_forecast') === '1' ? 'selected' : '' }}>FORECAST
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i>
                                    Filter</button>
                                <a href="{{ route('datasource.raw-data') }}" class="btn btn-secondary"><i
                                        class="bx bx-reset"></i> Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover dt-responsive nowrap w-100 table-sm"
                            style="font-size: 0.75rem;">
                            <thead class="table-light">
                                <tr class="text-uppercase text-nowrap">
                                    <th>Tanggal</th>
                                    <th>Opsel</th>
                                    <th>Tipe</th>
                                    <th>Kategori</th>
                                    <th>Kode Origin Prov</th>
                                    <th>Origin Prov</th>
                                    <th>Kode Origin Kab/Kota</th>
                                    <th>Origin Kab/Kota</th>
                                    <th>Kode Dest Prov</th>
                                    <th>Dest Prov</th>
                                    <th>Kode Dest Kab/Kota</th>
                                    <th>Dest Kab/Kota</th>
                                    <th>Kode Origin Simpul</th>
                                    <th>Origin Simpul</th>
                                    <th>Kode Dest Simpul</th>
                                    <th>Dest Simpul</th>
                                    <th>Kode Moda</th>
                                    <th>Moda</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                    <tr>
                                        <td>{{ $row->tanggal }}</td>
                                        <td>{{ $row->opsel }}</td>
                                        <td>
                                            @if ($row->is_forecast)
                                                <span class="badge bg-warning bg-soft text-warning">FORECAST</span>
                                            @else
                                                <span class="badge bg-success bg-soft text-success">REAL</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->kategori }}</td>
                                        <td>{{ $row->kode_origin_provinsi }}</td>
                                        <td>{{ $row->origin_provinsi }}</td>
                                        <td>{{ $row->kode_origin_kabupaten_kota }}</td>
                                        <td>{{ $row->origin_kabupaten_kota }}</td>
                                        <td>{{ $row->kode_dest_provinsi }}</td>
                                        <td>{{ $row->dest_provinsi }}</td>
                                        <td>{{ $row->kode_dest_kabupaten_kota }}</td>
                                        <td>{{ $row->dest_kabupaten_kota }}</td>
                                        <td>{{ $row->kode_origin_simpul ?: '-' }}</td>
                                        <td>{{ $row->origin_simpul ?: '-' }}</td>
                                        <td>{{ $row->kode_dest_simpul ?: '-' }}</td>
                                        <td>{{ $row->dest_simpul ?: '-' }}</td>
                                        <td>{{ $row->kode_moda ?: '-' }}</td>
                                        <td>{{ $row->moda }}</td>
                                        <td class="text-end fw-bold">{{ number_format($row->total) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="19" class="text-center">Data kosong.</td>
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
