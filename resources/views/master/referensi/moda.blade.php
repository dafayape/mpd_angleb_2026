@extends('layout.app')

@section('title', 'Referensi Moda Transportasi')

@section('content')
    @component('layout.partials.page-header', ['number' => '27', 'title' => 'Referensi Moda Transportasi'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active">Moda</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div>
                            <h4 class="card-title mb-1">Data Referensi Moda Transportasi</h4>
                            <p class="card-title-desc mb-0">Total: {{ $data->total() }} moda</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px" class="text-center">No</th>
                                    <th style="width:80px" class="text-center">Kode</th>
                                    <th>Nama Moda Transportasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td class="text-center">
                                            {{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td class="text-center"><span
                                                class="badge bg-success font-size-13">{{ $item->code }}</span></td>
                                        <td>{{ $item->name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle font-size-20 d-block mb-1"></i>
                                            Data kosong. Jalankan: <code>php artisan db:seed --class=ModaSeeder</code>
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
