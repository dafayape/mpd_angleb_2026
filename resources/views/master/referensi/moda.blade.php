@extends('layout.app')

@section('title', 'Referensi Moda Transportasi')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Referensi Moda Transportasi</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item active">Moda</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Data Referensi Moda Transportasi</h4>
                    <p class="card-title-desc">Daftar moda transportasi yang digunakan dalam data MPD ({{ $data->total() }} data)</p>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped dt-responsive nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 100px;">Kode</th>
                                    <th>Nama Moda Transportasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td><span class="badge bg-success bg-soft text-success font-size-14">{{ $item->code }}</span></td>
                                        <td>{{ $item->name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle font-size-20"></i><br>
                                            Data kosong. Jalankan seeder: <code>php artisan db:seed --class=ModaSeeder</code>
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
