@extends('layout.app')

@section('title', 'Log Aktivitas')

@section('content')
    @component('layout.partials.page-header', ['number' => '34', 'title' => 'Log Aktivitas'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Log Aktivitas</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div>
                            <h4 class="card-title mb-1">Log Aktivitas Sistem</h4>
                            <p class="card-title-desc mb-0">Rekam jejak seluruh kegiatan pengguna dan sistem</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('log-aktivitas.export', request()->query()) }}"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-download"></i> Export CSV
                            </a>
                            <a href="{{ route('log-aktivitas') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-refresh"></i> Refresh
                            </a>
                        </div>
                    </div>

                    {{-- Filter Form --}}
                    <form id="filterForm" method="GET" action="{{ route('log-aktivitas') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Cari user / aktivitas..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua Status</option>
                                    <option value="Success" {{ request('status') == 'Success' ? 'selected' : '' }}>Success
                                    </option>
                                    <option value="Failed" {{ request('status') == 'Failed' ? 'selected' : '' }}>Failed
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                    value="{{ request('start_date') }}" placeholder="Dari">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                    value="{{ request('end_date') }}" placeholder="Sampai">
                            </div>
                            <div class="col-md-1">
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-primary" type="submit"><i
                                            class="bx bx-search"></i></button>
                                    @if (request()->hasAny(['search', 'status', 'user_id', 'start_date', 'end_date']))
                                        <a href="{{ route('log-aktivitas') }}" class="btn btn-sm btn-outline-secondary"><i
                                                class="bx bx-x"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Tabel Log --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px" class="text-center">No</th>
                                    <th style="width:160px">Waktu</th>
                                    <th style="width:150px">User</th>
                                    <th style="width:160px">Aktivitas</th>
                                    <th>Target Objek</th>
                                    <th style="width:100px" class="text-center">Status</th>
                                    <th style="width:60px" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="text-center">
                                            {{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                                        <td>
                                            <small class="text-muted"><i class="bx bx-time"></i>
                                                {{ $log->created_at->format('d M Y, H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avatar-xs me-2">
                                                    <span
                                                        class="avatar-title rounded-circle bg-primary bg-soft text-primary font-size-12">{{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}</span>
                                                </span>
                                                {{ \Illuminate\Support\Str::limit($log->user->name ?? 'System', 18) }}
                                            </div>
                                        </td>
                                        <td><span class="badge bg-primary bg-soft text-primary">{{ $log->action }}</span>
                                        </td>
                                        <td class="text-truncate" style="max-width:200px" title="{{ $log->subject }}">
                                            {{ $log->subject ?? '-' }}</td>
                                        <td class="text-center">
                                            @if ($log->status === 'Success')
                                                <span class="badge bg-success bg-soft text-success"><i
                                                        class="bx bx-check-circle"></i> Success</span>
                                            @else
                                                <span class="badge bg-danger bg-soft text-danger"><i
                                                        class="bx bx-x-circle"></i> {{ $log->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-detail"
                                                data-bs-toggle="modal" data-bs-target="#detailModal"
                                                data-time="{{ $log->created_at->format('d F Y H:i:s') }}"
                                                data-user="{{ $log->user->name ?? 'System' }}"
                                                data-action="{{ $log->action }}"
                                                data-subject="{{ $log->subject ?? '-' }}"
                                                data-status="{{ $log->status }}" data-ip="{{ $log->ip_address ?? '-' }}"
                                                data-agent="{{ $log->user_agent ?? '-' }}"
                                                data-description="{{ $log->description ?? '' }}">
                                                <i class="bx bx-show"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle font-size-20 d-block mb-1"></i>
                                            Belum ada data log aktivitas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($logs->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari
                                {{ $logs->total() }}</small>
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm mb-0">
                        <tr>
                            <th width="35%">Waktu</th>
                            <td id="modal-time"></td>
                        </tr>
                        <tr>
                            <th>User</th>
                            <td id="modal-user"></td>
                        </tr>
                        <tr>
                            <th>Aktivitas</th>
                            <td id="modal-action"></td>
                        </tr>
                        <tr>
                            <th>Target Objek</th>
                            <td id="modal-subject"></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td id="modal-status"></td>
                        </tr>
                        <tr>
                            <th>IP Address</th>
                            <td id="modal-ip"></td>
                        </tr>
                        <tr>
                            <th>User Agent</th>
                            <td id="modal-agent" style="word-break:break-all;font-size:.85rem"></td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td id="modal-description"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('detailModal');
            modal.addEventListener('show.bs.modal', function(e) {
                var btn = e.relatedTarget;
                ['time', 'user', 'action', 'subject', 'status', 'ip', 'agent', 'description'].forEach(
                    function(key) {
                        document.getElementById('modal-' + key).textContent = btn.getAttribute('data-' +
                            key) || '-';
                    });
            });
        });
    </script>
@endsection
