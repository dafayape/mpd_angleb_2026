@extends('layout.app')

@section('title', 'Log Developer')

@push('css')
    <style>
        /* ── CI/CD Deploy Log Theme ── */
        .devlog-stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            min-width: 140px;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: default;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-icon.error {
            background: #fef2f2;
            color: #ef4444;
        }

        .stat-icon.warning {
            background: #fffbeb;
            color: #f59e0b;
        }

        .stat-icon.info {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-icon.debug {
            background: #f8fafc;
            color: #64748b;
        }

        .stat-icon.total {
            background: #f0fdf4;
            color: #22c55e;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1;
            color: #0f172a;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Filter Bar ── */
        .filter-bar {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            padding: 16px 20px;
            background: #fff;
        }

        .log-file-select {
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .search-input {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .level-filter {
            display: inline-flex;
            gap: 4px;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 8px;
        }

        .level-btn {
            border: none;
            background: transparent;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #64748b;
        }

        .level-btn:hover {
            background: rgba(255, 255, 255, 0.7);
        }

        .level-btn.active {
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .level-btn.active[data-level="all"] {
            color: #0f172a;
        }

        .level-btn.active[data-level="ERROR"],
        .level-btn.active[data-level="CRITICAL"],
        .level-btn.active[data-level="EMERGENCY"] {
            color: #ef4444;
        }

        .level-btn.active[data-level="WARNING"] {
            color: #f59e0b;
        }

        .level-btn.active[data-level="INFO"] {
            color: #3b82f6;
        }

        .level-btn.active[data-level="DEBUG"] {
            color: #64748b;
        }

        /* ── Timeline Log ── */
        .log-container {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .log-header {
            background: #fff;
            padding: 14px 20px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .log-header-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .log-body {
            background: #fafbfc;
            max-height: 70vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .log-body::-webkit-scrollbar {
            width: 6px;
        }

        .log-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        /* ── Log Entry ── */
        .log-entry {
            display: flex;
            gap: 0;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s ease;
            cursor: pointer;
        }

        .log-entry:hover {
            background: #f8fafc;
        }

        .log-entry.expanded {
            background: #f0f4ff;
        }

        .log-gutter {
            width: 50px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 14px;
            position: relative;
        }

        .log-gutter::after {
            content: '';
            position: absolute;
            top: 36px;
            bottom: 0;
            left: 50%;
            width: 2px;
            background: #e2e8f0;
            transform: translateX(-50%);
        }

        .log-entry:last-child .log-gutter::after {
            display: none;
        }

        .log-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
            z-index: 1;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px currentColor;
        }

        .log-dot.error {
            color: #ef4444;
            background: #ef4444;
        }

        .log-dot.warning {
            color: #f59e0b;
            background: #f59e0b;
        }

        .log-dot.info {
            color: #3b82f6;
            background: #3b82f6;
        }

        .log-dot.debug {
            color: #94a3b8;
            background: #94a3b8;
        }

        .log-content {
            flex: 1;
            padding: 12px 16px 12px 0;
            min-width: 0;
        }

        .log-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }

        .log-time {
            font-family: 'Courier New', monospace;
            font-size: 0.78rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .log-level-badge {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .log-level-badge.error {
            background: #fef2f2;
            color: #dc2626;
        }

        .log-level-badge.warning {
            background: #fffbeb;
            color: #d97706;
        }

        .log-level-badge.info {
            background: #eff6ff;
            color: #2563eb;
        }

        .log-level-badge.debug {
            background: #f1f5f9;
            color: #64748b;
        }

        .log-channel {
            font-size: 0.75rem;
            color: #94a3b8;
            background: #f1f5f9;
            padding: 1px 8px;
            border-radius: 4px;
        }

        .log-message-text {
            font-family: 'Courier New', monospace;
            font-size: 0.82rem;
            color: #334155;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 40px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .log-entry.expanded .log-message-text {
            max-height: 600px;
        }

        .log-expand-hint {
            font-size: 0.72rem;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* ── Empty ── */
        .empty-log {
            text-align: center;
            padding: 4rem 1rem;
            color: #94a3b8;
        }

        .empty-log i {
            font-size: 56px;
            margin-bottom: 16px;
            color: #22c55e;
        }

        /* ── Responsive ── */
        @media (max-width: 767px) {
            .devlog-stats {
                flex-direction: column;
            }

            .stat-card {
                min-width: 100%;
            }

            .log-gutter {
                width: 36px;
            }
        }
    </style>
@endpush

@section('content')
    @component('layout.partials.page-header', ['number' => '35', 'title' => 'Log Developer'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Log Developer</li>
        </ol>
    @endcomponent

    @php
        $errorCount =
            collect($lines)->where('level', 'ERROR')->count() +
            collect($lines)->where('level', 'CRITICAL')->count() +
            collect($lines)->where('level', 'EMERGENCY')->count();
        $warningCount = collect($lines)->where('level', 'WARNING')->count();
        $infoCount = collect($lines)->where('level', 'INFO')->count();
        $debugCount = collect($lines)->where('level', 'DEBUG')->count();
        $totalCount = count($lines);
    @endphp

    {{-- Stats Row --}}
    <div class="devlog-stats mb-3 mt-2">
        <div class="stat-card">
            <div class="stat-icon total"><i class="bx bx-list-ul"></i></div>
            <div>
                <div class="stat-value">{{ $totalCount }}</div>
                <div class="stat-label">Total Entries</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon error"><i class="bx bx-x-circle"></i></div>
            <div>
                <div class="stat-value">{{ $errorCount }}</div>
                <div class="stat-label">Errors</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bx bx-error"></i></div>
            <div>
                <div class="stat-value">{{ $warningCount }}</div>
                <div class="stat-label">Warnings</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info"><i class="bx bx-info-circle"></i></div>
            <div>
                <div class="stat-value">{{ $infoCount }}</div>
                <div class="stat-label">Info</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon debug"><i class="bx bx-bug"></i></div>
            <div>
                <div class="stat-value">{{ $debugCount }}</div>
                <div class="stat-label">Debug</div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-lg-3 col-md-6">
                <form method="GET" action="{{ route('devlog') }}" id="logFileForm">
                    <select name="file" class="form-select log-file-select" onchange="this.form.submit()">
                        @foreach ($logFiles as $file)
                            <option value="{{ $file }}" {{ $selectedFile === $file ? 'selected' : '' }}>
                                📄 {{ $file }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"
                        style="border-radius:8px 0 0 8px;border-color:#e2e8f0">
                        <i class="bx bx-search text-muted"></i>
                    </span>
                    <input type="text" id="searchLog" class="form-control search-input border-start-0"
                        placeholder="Cari log..." style="border-radius:0 8px 8px 0;border-color:#e2e8f0">
                </div>
            </div>
            <div class="col-lg-5 col-md-12 text-lg-end">
                <div class="level-filter">
                    <button class="level-btn active" data-level="all">Semua</button>
                    <button class="level-btn" data-level="ERROR">🔴 Error</button>
                    <button class="level-btn" data-level="WARNING">🟡 Warning</button>
                    <button class="level-btn" data-level="INFO">🔵 Info</button>
                    <button class="level-btn" data-level="DEBUG">⚪ Debug</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Log Timeline --}}
    <div class="log-container">
        <div class="log-header">
            <div class="log-header-title">
                <i class="bx bx-terminal text-primary"></i>
                <span>{{ $selectedFile ?? 'Log Viewer' }}</span>
                <span class="log-channel" id="visibleCount">{{ $totalCount }} entries</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="collapseAll()" title="Collapse All">
                    <i class="bx bx-collapse-vertical"></i>
                </button>
                <a href="{{ route('devlog', ['file' => $selectedFile]) }}" class="btn btn-sm btn-outline-primary"
                    title="Refresh">
                    <i class="bx bx-refresh"></i>
                </a>
            </div>
        </div>

        <div class="log-body">
            @if ($totalCount === 0)
                <div class="empty-log">
                    <i class="bx bx-check-circle"></i>
                    <h5 class="fw-bold text-dark">Tidak ada log</h5>
                    <p>File log kosong atau belum ada entri. Semua berjalan lancar! 🚀</p>
                </div>
            @else
                @foreach ($lines as $entry)
                    @php
                        $levelLower = strtolower($entry['level']);
                        $dotClass = match ($entry['level']) {
                            'ERROR', 'CRITICAL', 'EMERGENCY' => 'error',
                            'WARNING' => 'warning',
                            'INFO' => 'info',
                            default => 'debug',
                        };
                        $iconMap = match ($entry['level']) {
                            'ERROR', 'CRITICAL', 'EMERGENCY' => 'bx bx-x-circle',
                            'WARNING' => 'bx bx-error',
                            'INFO' => 'bx bx-info-circle',
                            'DEBUG' => 'bx bx-bug',
                            default => 'bx bx-dots-horizontal-rounded',
                        };
                        $msgShort = \Illuminate\Support\Str::limit($entry['message'], 200);
                        $isLong = strlen($entry['message']) > 200;
                    @endphp
                    <div class="log-entry" data-level="{{ $entry['level'] }}" onclick="toggleExpand(this)">
                        <div class="log-gutter">
                            <div class="log-dot {{ $dotClass }}"></div>
                        </div>
                        <div class="log-content">
                            <div class="log-meta">
                                <span class="log-time">{{ $entry['timestamp'] }}</span>
                                <span class="log-level-badge {{ $dotClass }}">
                                    <i class="{{ $iconMap }}" style="font-size:10px"></i>
                                    {{ $entry['level'] }}
                                </span>
                                <span class="log-channel">{{ $entry['channel'] }}</span>
                            </div>
                            <div class="log-message-text">{{ $entry['message'] }}</div>
                            @if ($isLong)
                                <div class="log-expand-hint">Klik untuk expand/collapse</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Level filter
        document.querySelectorAll('.level-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.level-btn').forEach(function(b) {
                    b.classList.remove('active');
                });
                this.classList.add('active');
                applyFilters();
            });
        });

        // Search
        document.getElementById('searchLog').addEventListener('input', function() {
            applyFilters();
        });

        function applyFilters() {
            var level = document.querySelector('.level-btn.active').dataset.level;
            var query = document.getElementById('searchLog').value.toLowerCase();
            var visible = 0;

            document.querySelectorAll('.log-entry').forEach(function(entry) {
                var matchLevel = (level === 'all' || entry.dataset.level === level);
                var matchSearch = !query || entry.textContent.toLowerCase().includes(query);
                var show = matchLevel && matchSearch;
                entry.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            document.getElementById('visibleCount').textContent = visible + ' entries';
        }

        function toggleExpand(el) {
            el.classList.toggle('expanded');
        }

        function collapseAll() {
            document.querySelectorAll('.log-entry.expanded').forEach(function(el) {
                el.classList.remove('expanded');
            });
        }
    </script>
@endpush
