@extends('layout.app')

@section('title', 'Log Developer')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Log Developer</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Log Developer</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <form method="GET" action="{{ route('devlog') }}">
                <div class="input-group">
                    <select name="file" class="form-select" onchange="this.form.submit()">
                        @foreach ($logFiles as $file)
                            <option value="{{ $file }}" {{ $selectedFile === $file ? 'selected' : '' }}>
                                {{ $file }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="text" id="searchLog" class="form-control" placeholder="Cari log...">
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-danger btn-sm filter-btn active"
                    data-level="all">Semua</button>
                <button type="button" class="btn btn-outline-danger btn-sm filter-btn" data-level="ERROR">Error</button>
                <button type="button" class="btn btn-outline-warning btn-sm filter-btn"
                    data-level="WARNING">Warning</button>
                <button type="button" class="btn btn-outline-info btn-sm filter-btn" data-level="INFO">Info</button>
                <button type="button" class="btn btn-outline-secondary btn-sm filter-btn" data-level="DEBUG">Debug</button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    @if (count($lines) === 0)
                        <div class="text-center py-5">
                            <i class="bx bx-check-circle text-success" style="font-size: 48px;"></i>
                            <h5 class="mt-3">Tidak ada log</h5>
                            <p class="text-muted">File log kosong atau belum ada entri.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0" id="logTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 170px;">Waktu</th>
                                        <th style="width: 90px;">Level</th>
                                        <th style="width: 90px;">Channel</th>
                                        <th>Pesan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lines as $entry)
                                        <tr class="log-row" data-level="{{ $entry['level'] }}">
                                            <td class="text-nowrap">
                                                <small class="text-muted font-monospace">{{ $entry['timestamp'] }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match ($entry['level']) {
                                                        'ERROR' => 'bg-danger',
                                                        'WARNING' => 'bg-warning text-dark',
                                                        'INFO' => 'bg-info',
                                                        'DEBUG' => 'bg-secondary',
                                                        'CRITICAL' => 'bg-danger',
                                                        'EMERGENCY' => 'bg-danger',
                                                        default => 'bg-light text-dark',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $entry['level'] }}</span>
                                            </td>
                                            <td><small>{{ $entry['channel'] }}</small></td>
                                            <td>
                                                <div class="log-message"
                                                    style="max-height: 60px; overflow: hidden; cursor: pointer;"
                                                    onclick="this.style.maxHeight = this.style.maxHeight === 'none' ? '60px' : 'none'">
                                                    <code class="text-dark"
                                                        style="white-space: pre-wrap; font-size: 12px;">{{ \Illuminate\Support\Str::limit($entry['message'], 500) }}</code>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(function(b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                var level = this.dataset.level;
                document.querySelectorAll('.log-row').forEach(function(row) {
                    row.style.display = (level === 'all' || row.dataset.level === level) ? '' :
                        'none';
                });
            });
        });

        document.getElementById('searchLog').addEventListener('input', function() {
            var query = this.value.toLowerCase();
            document.querySelectorAll('.log-row').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    </script>
@endpush
