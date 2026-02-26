@extends('layout.app')

@section('title', 'History File Upload')

@section('content')
    @component('layout.partials.page-header', ['number' => '30', 'title' => 'History File Upload'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">History Upload</li>
        </ol>
    @endcomponent

    @if (isset($summary) && $summary['total_rows'] > 0)
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Total Data</p>
                                <h5 class="mb-0">{{ number_format($summary['total_rows']) }}</h5>
                            </div>
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-primary align-self-center">
                                <span class="avatar-title"><i class="bx bx-data font-size-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Total Upload</p>
                                <h5 class="mb-0">{{ number_format($summary['total_uploads']) }}</h5>
                            </div>
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-success align-self-center">
                                <span class="avatar-title"><i class="bx bx-upload font-size-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Data Terbaru</p>
                                <h5 class="mb-0">{{ $summary['latest_date'] ?? '-' }}</h5>
                            </div>
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-info align-self-center">
                                <span class="avatar-title"><i class="bx bx-calendar font-size-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-1">Per Opsel</p>
                                <h6 class="mb-0" style="font-size: 12px;">
                                    @foreach ($summary['by_opsel'] as $opsel => $count)
                                        <span class="badge bg-primary bg-soft text-primary">{{ $opsel }}:
                                            {{ number_format($count) }}</span>
                                    @endforeach
                                </h6>
                            </div>
                            <div class="mini-stat-icon avatar-sm rounded-circle bg-warning align-self-center">
                                <span class="avatar-title"><i class="bx bx-bar-chart font-size-24"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Riwayat Upload File MPD</h4>
                    <p class="card-title-desc">Daftar file yang telah diupload dan status pemrosesannya.</p>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form action="{{ route('datasource.history') }}" method="GET" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="opsel" class="form-label"
                                        style="font-size: 11px; text-transform: uppercase;">Opsel</label>
                                    <select class="form-select" id="opsel" name="opsel" style="font-size: 12px;">
                                        <option value="">Semua Opsel</option>
                                        <option value="TSEL" {{ request('opsel') == 'TSEL' ? 'selected' : '' }}>TSEL
                                        </option>
                                        <option value="IOH" {{ request('opsel') == 'IOH' ? 'selected' : '' }}>IOH
                                        </option>
                                        <option value="XL" {{ request('opsel') == 'XL' ? 'selected' : '' }}>XL</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="kategori" class="form-label"
                                        style="font-size: 11px; text-transform: uppercase;">Tipe</label>
                                    <select class="form-select" id="kategori" name="kategori" style="font-size: 12px;">
                                        <option value="">Semua Tipe</option>
                                        <option value="REAL" {{ request('kategori') == 'REAL' ? 'selected' : '' }}>REAL
                                        </option>
                                        <option value="FORECAST" {{ request('kategori') == 'FORECAST' ? 'selected' : '' }}>
                                            FORECAST</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-size: 11px; text-transform: uppercase;">Rentang
                                        Tanggal Data</label>
                                    <div class="input-group input-group-sm">
                                        <input type="date" class="form-control" name="start_date"
                                            value="{{ request('start_date') }}" style="font-size: 12px;">
                                        <span class="input-group-text">s/d</span>
                                        <input type="date" class="form-control" name="end_date"
                                            value="{{ request('end_date') }}" style="font-size: 12px;">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm w-100"><i
                                                class="bx bx-filter-alt me-1"></i> Filter</button>
                                        <a href="{{ route('datasource.history') }}"
                                            class="btn btn-secondary btn-sm w-100"><i class="bx bx-reset me-1"></i>
                                            Reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover dt-responsive nowrap w-100 align-middle">
                            <thead class="table-light">
                                <tr class="text-uppercase text-muted" style="font-size: 11px;">
                                    <th style="width: 5%;">No</th>
                                    <th>Tanggal Data</th>
                                    <th>Opsel</th>
                                    <th>Tipe</th>
                                    <th>Frekuensi</th>
                                    <th>File Name</th>
                                    <th>Waktu Upload</th>
                                    <th>Status</th>
                                    <th style="width: 10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 12px;">
                                @forelse($histories as $history)
                                    <tr>
                                        <td>{{ $loop->iteration + ($histories->currentPage() - 1) * $histories->perPage() }}
                                        </td>
                                        <td class="fw-bold">
                                            {{ $history->tanggal_data ? \Carbon\Carbon::parse($history->tanggal_data)->format('Y-m-d') : '-' }}
                                        </td>
                                        <td><span
                                                class="badge bg-primary bg-soft text-primary font-size-11">{{ $history->opsel ?? '-' }}</span>
                                        </td>
                                        <td>{{ $history->kategori ?? '-' }}</td>
                                        <td>{{ number_format($history->total_rows ?: $history->processed_rows) }} Rows</td>
                                        <td><span
                                                class="text-dark fw-medium">{{ $history->original_filename ?? $history->filename }}</span>
                                        </td>
                                        <td>{{ $history->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            @if ($history->status == 'completed')
                                                <span
                                                    class="badge badge-pill badge-soft-success font-size-11">Completed</span>
                                            @elseif ($history->status == 'processing')
                                                <span
                                                    class="badge badge-pill badge-soft-warning font-size-11">Processing</span>
                                            @elseif ($history->status == 'failed')
                                                <span class="badge badge-pill badge-soft-danger font-size-11">Failed</span>
                                            @else
                                                <span
                                                    class="badge badge-pill badge-soft-secondary font-size-11">{{ ucfirst($history->status) }}</span>
                                            @endif
                                            @if ($history->status == 'failed' && $history->error_message)
                                                <div class="text-danger mt-1" style="font-size: 10px;">
                                                    {{ Str::limit($history->error_message, 20) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('datasource.raw-data', ['tanggal' => $history->tanggal_data, 'opsel' => $history->opsel]) }}"
                                                    class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                                    title="View Raw Data">
                                                    <i class="bx bx-show-alt"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="confirmDelete({{ $history->id }}, {{ $history->total_rows ?: $history->processed_rows }})"
                                                    data-bs-toggle="tooltip" title="Hapus Data">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">Belum ada riwayat upload.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $histories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Menghapus Data...</h5>
                </div>
                <div class="modal-body">
                    <div class="progress mb-3">
                        <div id="deleteProgressBar"
                            class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar"
                            style="width: 0%">0%</div>
                    </div>
                    <p class="text-center mb-0" id="deleteStatusText">Menghapus data, mohon tunggu...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete(id, totalRows) {
            if (confirm('Apakah Anda yakin ingin menghapus data ini? Aksi ini tidak dapat dibatalkan.')) {
                startChunkedDelete(id, totalRows);
            }
        }

        function startChunkedDelete(id, totalRows) {
            const modal = new bootstrap.Modal(document.getElementById('deleteProgressModal'));
            const progressBar = document.getElementById('deleteProgressBar');
            const statusText = document.getElementById('deleteStatusText');

            modal.show();
            progressBar.style.width = '0%';
            progressBar.innerHTML = '0%';
            statusText.innerText = 'Memulai penghapusan...';

            let deletedSoFar = 0;

            // Generate URL dari Laravel route helper (support subdirectory/prefix)
            const deleteUrl = "{{ route('datasource.destroy-chunk', ['id' => '__ID__']) }}".replace('__ID__', id);

            function deleteChunk() {
                $.ajax({
                    url: deleteUrl,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status === 'progress') {
                            deletedSoFar += response.deleted;
                            let percentage = totalRows > 0 ? Math.min(Math.round((deletedSoFar / totalRows) *
                                100), 99) : 50;
                            progressBar.style.width = percentage + '%';
                            progressBar.innerHTML = percentage + '%';
                            statusText.innerText = 'Terhapus: ' + new Intl.NumberFormat('id-ID').format(
                                deletedSoFar) + ' baris...';
                            deleteChunk();
                        } else if (response.status === 'completed') {
                            progressBar.style.width = '100%';
                            progressBar.innerHTML = '100%';
                            progressBar.classList.remove('bg-danger');
                            progressBar.classList.add('bg-success');
                            statusText.innerText = 'Selesai! Halaman akan dimuat ulang.';
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            progressBar.style.width = '100%';
                            statusText.innerText = 'Data sudah terhapus.';
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.responseText;
                            alert('Terjadi kesalahan: ' + msg);
                            modal.hide();
                        }
                    }
                });
            }

            deleteChunk();
        }
    </script>
@endpush
