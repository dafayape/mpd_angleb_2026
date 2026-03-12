@extends('layout.app')

@section('title', 'History File Upload')

@section('content')
    @component('layout.partials.page-header', ['number' => '31', 'title' => 'History File Upload'])
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
                                        <option value="XLSMART" {{ request('opsel') == 'XLSMART' ? 'selected' : '' }}>XLSMART</option>
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
                                    <th style="width: 4%;">No</th>
                                    <th>Tanggal Data</th>
                                    <th>Opsel</th>
                                    <th>Tipe</th>
                                    <th>File Name</th>
                                    <th>File Size</th>
                                    <th>Rows</th>
                                    <th>Skipped</th>
                                    <th>Status File</th>
                                    <th>Status ETL</th>
                                    <th>Data Lost</th>
                                    <th>Waktu</th>
                                    <th style="width: 8%;">Aksi</th>
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
                                        <td><span
                                                class="text-dark fw-medium">{{ $history->original_filename ?? $history->filename }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $size = $history->file_size ?? 0;
                                                if ($size >= 1073741824) {
                                                    $sizeStr = number_format($size / 1073741824, 2) . ' GB';
                                                } elseif ($size >= 1048576) {
                                                    $sizeStr = number_format($size / 1048576, 1) . ' MB';
                                                } elseif ($size >= 1024) {
                                                    $sizeStr = number_format($size / 1024, 0) . ' KB';
                                                } else {
                                                    $sizeStr = $size . ' B';
                                                }
                                            @endphp
                                            <span class="text-muted">{{ $sizeStr }}</span>
                                        </td>
                                        <td>{{ number_format($history->total_rows ?: $history->processed_rows) }}</td>
                                        <td>
                                            @if (($history->skipped_rows ?? 0) > 0)
                                                <span class="badge bg-warning text-dark">{{ number_format($history->skipped_rows) }}</span>
                                            @else
                                                <span class="text-success">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $sf = $history->status_file ?? $history->status;
                                            @endphp
                                            @if (in_array($sf, ['completed', 'completed_with_errors']))
                                                <span class="badge badge-pill badge-soft-success font-size-11">✅ Completed</span>
                                            @elseif ($sf === 'importing')
                                                <span class="badge badge-pill badge-soft-info font-size-11">🔄 Importing</span>
                                            @elseif ($sf === 'validating')
                                                <span class="badge badge-pill badge-soft-warning font-size-11">🔍 Validating</span>
                                            @elseif ($sf === 'queued')
                                                <span class="badge badge-pill badge-soft-info font-size-11">⏳ Queued</span>
                                            @elseif ($sf === 'failed' || $sf === 'validation_failed')
                                                <span class="badge badge-pill badge-soft-danger font-size-11">❌ Failed</span>
                                            @else
                                                <span class="badge badge-pill badge-soft-secondary font-size-11">{{ ucfirst($sf) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $se = $history->status_etl ?? 'pending';
                                                $ep = $history->etl_progress ?? 0;
                                            @endphp
                                            @if ($se === 'completed')
                                                <span class="badge badge-pill badge-soft-success font-size-11">✅ 100%</span>
                                            @elseif ($se === 'processing')
                                                <div style="min-width: 60px;">
                                                    <div class="progress" style="height: 16px;">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                                            style="width: {{ $ep }}%; font-size: 10px;">
                                                            {{ $ep }}%
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif ($se === 'queued')
                                                <span class="badge badge-pill badge-soft-info font-size-11">⏳ Queued</span>
                                            @elseif ($se === 'failed')
                                                <span class="badge badge-pill badge-soft-danger font-size-11">❌ Failed</span>
                                            @else
                                                <span class="badge badge-pill badge-soft-secondary font-size-11">⏸ Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (($history->data_lost ?? 0) > 0)
                                                <span class="badge bg-danger text-white" data-bs-toggle="tooltip"
                                                    title="Volume data yang tidak bisa di-mapping ke simpul transportasi">
                                                    {{ number_format($history->data_lost) }}
                                                </span>
                                            @else
                                                <span class="text-success">0</span>
                                            @endif
                                        </td>
                                        <td style="font-size: 11px;">{{ $history->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
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
                                        <td colspan="13" class="text-center py-4">
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

        // ═══════════════════════════════════════════════════════
        // Auto-refresh: Poll ETL status setiap 5 detik
        // Jika ada job aktif (queued/processing), reload halaman
        // ═══════════════════════════════════════════════════════
        (function() {
            var pollInterval = null;
            var lastState = '';

            function checkEtlStatus() {
                $.ajax({
                    url: "{{ route('datasource.etl-status') }}",
                    type: "GET",
                    timeout: 5000,
                    success: function(data) {
                        var currentState = JSON.stringify(data.jobs);
                        if (data.has_active) {
                            // Ada job aktif — jika state berubah, reload untuk update UI
                            if (lastState !== '' && lastState !== currentState) {
                                location.reload();
                            }
                            lastState = currentState;
                        } else {
                            // Semua job selesai — reload sekali lagi lalu stop polling
                            if (lastState !== '' && lastState !== currentState) {
                                location.reload();
                            }
                            clearInterval(pollInterval);
                            pollInterval = null;
                        }
                    },
                    error: function() {
                        // Silently ignore polling errors
                    }
                });
            }

            // Mulai polling jika ada indikator job aktif di halaman
            // Deteksi: progress bar animasi, badge info (queued/importing), badge warning (validating)
            var hasActiveJob = document.querySelector('.progress-bar-animated') ||
                               document.querySelector('.badge-soft-info') ||
                               document.querySelector('.badge-soft-warning');
            if (hasActiveJob) {
                lastState = ''; // initial
                checkEtlStatus(); // immediate check
                pollInterval = setInterval(checkEtlStatus, 5000);
            }
        })();
    </script>
@endpush
