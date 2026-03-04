@extends('layout.app')

@section('title', 'Log Developer')

@push('css')
    <style>
        /* ─── Stat Cards ─── */
        .dl-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
        }

        @media (max-width: 991px) {
            .dl-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 575px) {
            .dl-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .dl-stat {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dl-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .dl-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .dl-stat-icon.t-total {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            color: #059669;
        }

        .dl-stat-icon.t-error {
            background: linear-gradient(135deg, #fef2f2, #fecaca);
            color: #dc2626;
        }

        .dl-stat-icon.t-warn {
            background: linear-gradient(135deg, #fffbeb, #fde68a);
            color: #d97706;
        }

        .dl-stat-icon.t-info {
            background: linear-gradient(135deg, #eff6ff, #bfdbfe);
            color: #2563eb;
        }

        .dl-stat-icon.t-debug {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            color: #475569;
        }

        .dl-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .dl-stat-lbl {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 2px;
        }

        /* ─── Toolbar ─── */
        .dl-toolbar {
            background: #fff;
            border-radius: 14px;
            padding: 14px 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
        }

        .dl-file-select {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .dl-search {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .dl-search:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .dl-level-pills {
            display: inline-flex;
            gap: 4px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
        }

        .dl-pill {
            border: none;
            background: transparent;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .dl-pill:hover {
            background: rgba(255, 255, 255, 0.6);
        }

        .dl-pill.active {
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            color: #0f172a;
        }

        .dl-pill.active[data-level="ERROR"] {
            color: #dc2626;
        }

        .dl-pill.active[data-level="WARNING"] {
            color: #d97706;
        }

        .dl-pill.active[data-level="INFO"] {
            color: #2563eb;
        }

        .dl-pill.active[data-level="DEBUG"] {
            color: #475569;
        }

        .dl-pill i {
            font-size: 14px;
        }

        /* ─── Log Panel ─── */
        .dl-panel {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        .dl-panel-head {
            padding: 14px 20px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dl-panel-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dl-panel-title .file-tag {
            font-family: 'Courier New', monospace;
            font-size: 0.78rem;
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 10px;
            border-radius: 6px;
        }

        .dl-panel-title .count-tag {
            font-size: 0.72rem;
            background: #eff6ff;
            color: #2563eb;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .dl-action-btn {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #64748b;
            font-size: 16px;
        }

        .dl-action-btn:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1;
        }

        /* ─── Log Entries ─── */
        .dl-scroll {
            max-height: 65vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .dl-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .dl-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .dl-entry {
            display: flex;
            border-bottom: 1px solid #f8fafc;
            transition: background 0.15s ease;
            cursor: pointer;
            position: relative;
        }

        .dl-entry:hover {
            background: #fafbff;
        }

        .dl-entry.expanded {
            background: #f5f7ff;
        }

        /* Timeline rail */
        .dl-rail {
            width: 48px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 18px;
            position: relative;
        }

        .dl-rail::after {
            content: '';
            position: absolute;
            top: 34px;
            bottom: 0;
            left: 50%;
            width: 2px;
            background: #e2e8f0;
            transform: translateX(-50%);
        }

        .dl-entry:last-child .dl-rail::after {
            display: none;
        }

        .dl-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            z-index: 1;
            flex-shrink: 0;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px currentColor;
        }

        .dl-dot.c-error {
            color: #ef4444;
            background: #ef4444;
        }

        .dl-dot.c-warning {
            color: #f59e0b;
            background: #f59e0b;
        }

        .dl-dot.c-info {
            color: #3b82f6;
            background: #3b82f6;
        }

        .dl-dot.c-debug {
            color: #94a3b8;
            background: #94a3b8;
        }

        .dl-body {
            flex: 1;
            padding: 14px 18px 14px 4px;
            min-width: 0;
        }

        .dl-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            flex-wrap: wrap;
        }

        .dl-time {
            font-family: 'Courier New', monospace;
            font-size: 0.76rem;
            color: #94a3b8;
        }

        .dl-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .dl-badge i {
            font-size: 11px;
        }

        .dl-badge.b-error {
            background: #fef2f2;
            color: #dc2626;
        }

        .dl-badge.b-warning {
            background: #fffbeb;
            color: #d97706;
        }

        .dl-badge.b-info {
            background: #eff6ff;
            color: #2563eb;
        }

        .dl-badge.b-debug {
            background: #f1f5f9;
            color: #64748b;
        }

        .dl-ch {
            font-size: 0.72rem;
            color: #94a3b8;
            background: #f8fafc;
            padding: 1px 8px;
            border-radius: 4px;
            border: 1px solid #f1f5f9;
        }

        .dl-msg {
            font-family: 'Courier New', monospace;
            font-size: 0.82rem;
            color: #334155;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 42px;
            overflow: hidden;
            transition: max-height 0.35s ease;
        }

        .dl-entry.expanded .dl-msg {
            max-height: 800px;
        }

        .dl-hint {
            font-size: 0.7rem;
            color: #cbd5e1;
            margin-top: 3px;
            font-style: italic;
        }

        .dl-entry.expanded .dl-hint {
            display: none;
        }

        /* ─── Empty State ─── */
        .dl-empty {
            text-align: center;
            padding: 5rem 2rem;
        }

        .dl-empty-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .dl-empty-icon i {
            font-size: 36px;
            color: #22c55e;
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
        $errorCount = collect($lines)
            ->whereIn('level', ['ERROR', 'CRITICAL', 'EMERGENCY'])
            ->count();
        $warningCount = collect($lines)->where('level', 'WARNING')->count();
        $infoCount = collect($lines)->where('level', 'INFO')->count();
        $debugCount = collect($lines)->where('level', 'DEBUG')->count();
        $totalCount = count($lines);
    @endphp

    {{-- Stat Cards --}}
    <div class="dl-stats mb-3 mt-2">
        <div class="dl-stat">
            <div class="dl-stat-icon t-total"><i class="bx bx-list-ul"></i></div>
            <div>
                <div class="dl-stat-num">{{ number_format($totalCount) }}</div>
                <div class="dl-stat-lbl">Total Entries</div>
            </div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-icon t-error"><i class="bx bx-x-circle"></i></div>
            <div>
                <div class="dl-stat-num">{{ number_format($errorCount) }}</div>
                <div class="dl-stat-lbl">Errors</div>
            </div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-icon t-warn"><i class="bx bx-error-alt"></i></div>
            <div>
                <div class="dl-stat-num">{{ number_format($warningCount) }}</div>
                <div class="dl-stat-lbl">Warnings</div>
            </div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-icon t-info"><i class="bx bx-info-circle"></i></div>
            <div>
                <div class="dl-stat-num">{{ number_format($infoCount) }}</div>
                <div class="dl-stat-lbl">Info</div>
            </div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-icon t-debug"><i class="bx bx-bug-alt"></i></div>
            <div>
                <div class="dl-stat-num">{{ number_format($debugCount) }}</div>
                <div class="dl-stat-lbl">Debug</div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="dl-toolbar mb-3">
        <div class="row g-3 align-items-center">
            <div class="col-lg-3 col-md-6">
                <form method="GET" action="{{ route('devlog') }}">
                    <select name="file" class="form-select dl-file-select" onchange="this.form.submit()">
                        @foreach ($logFiles as $file)
                            <option value="{{ $file }}" {{ $selectedFile === $file ? 'selected' : '' }}>
                                {{ $file }}
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
                    <input type="text" id="searchLog" class="form-control dl-search border-start-0"
                        placeholder="Cari pesan, channel, waktu..." style="border-radius:0 8px 8px 0;border-color:#e2e8f0">
                </div>
            </div>
            <div class="col-lg-5 col-md-12 text-lg-end">
                <div class="dl-level-pills">
                    <button class="dl-pill active" data-level="all"><i class="bx bx-layer"></i> Semua</button>
                    <button class="dl-pill" data-level="ERROR"><i class="bx bx-x-circle"></i> Error</button>
                    <button class="dl-pill" data-level="WARNING"><i class="bx bx-error-alt"></i> Warning</button>
                    <button class="dl-pill" data-level="INFO"><i class="bx bx-info-circle"></i> Info</button>
                    <button class="dl-pill" data-level="DEBUG"><i class="bx bx-bug-alt"></i> Debug</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Log Panel --}}
    <div class="dl-panel">
        <div class="dl-panel-head">
            <div class="dl-panel-title">
                <i class="bx bx-terminal text-primary" style="font-size:18px"></i>
                Log Viewer
                <span class="file-tag">{{ $selectedFile ?? '-' }}</span>
                <span class="count-tag" id="visibleCount">{{ $totalCount }} entries</span>
            </div>
            <div class="d-flex gap-2">
                <button class="dl-action-btn" onclick="collapseAll()" title="Collapse semua">
                    <i class="bx bx-collapse-vertical"></i>
                </button>
                <button class="dl-action-btn" onclick="scrollToTop()" title="Scroll ke atas">
                    <i class="bx bx-up-arrow-alt"></i>
                </button>
                <a href="{{ route('devlog', ['file' => $selectedFile]) }}" class="dl-action-btn" title="Refresh">
                    <i class="bx bx-refresh"></i>
                </a>
            </div>
        </div>

        <div class="dl-scroll" id="logScroll">
            @if ($totalCount === 0)
                <div class="dl-empty">
                    <div class="dl-empty-icon">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Tidak Ada Log</h5>
                    <p class="text-muted mb-0">File log kosong atau belum ada entri. Sistem berjalan normal.</p>
                </div>
            @else
                @foreach ($lines as $idx => $entry)
                    @php
                        $dotCls = match ($entry['level']) {
                            'ERROR', 'CRITICAL', 'EMERGENCY' => 'c-error',
                            'WARNING' => 'c-warning',
                            'INFO' => 'c-info',
                            default => 'c-debug',
                        };
                        $badgeCls = match ($entry['level']) {
                            'ERROR', 'CRITICAL', 'EMERGENCY' => 'b-error',
                            'WARNING' => 'b-warning',
                            'INFO' => 'b-info',
                            default => 'b-debug',
                        };
                        $iconCls = match ($entry['level']) {
                            'ERROR', 'CRITICAL', 'EMERGENCY' => 'bx bx-x-circle',
                            'WARNING' => 'bx bx-error-alt',
                            'INFO' => 'bx bx-info-circle',
                            'DEBUG' => 'bx bx-bug-alt',
                            default => 'bx bx-dots-horizontal-rounded',
                        };
                        $isLong = strlen($entry['message']) > 150;
                    @endphp
                    <div class="dl-entry" data-level="{{ $entry['level'] }}" onclick="this.classList.toggle('expanded')">
                        <div class="dl-rail">
                            <div class="dl-dot {{ $dotCls }}"></div>
                        </div>
                        <div class="dl-body">
                            <div class="dl-meta">
                                <span class="dl-time">{{ $entry['timestamp'] }}</span>
                                <span class="dl-badge {{ $badgeCls }}">
                                    <i class="{{ $iconCls }}"></i> {{ $entry['level'] }}
                                </span>
                                <span class="dl-ch">{{ $entry['channel'] }}</span>
                            </div>
                            <div class="dl-msg">{{ $entry['message'] }}</div>
                            @if ($isLong)
                                <div class="dl-hint">klik untuk expand</div>
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
        // Level pills
        document.querySelectorAll('.dl-pill').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.dl-pill').forEach(function(b) {
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
            var level = document.querySelector('.dl-pill.active').dataset.level;
            var query = document.getElementById('searchLog').value.toLowerCase();
            var visible = 0;

            document.querySelectorAll('.dl-entry').forEach(function(el) {
                var matchLevel = (level === 'all' || el.dataset.level === level);
                var matchSearch = !query || el.textContent.toLowerCase().indexOf(query) !== -1;
                el.style.display = (matchLevel && matchSearch) ? '' : 'none';
                if (matchLevel && matchSearch) visible++;
            });

            document.getElementById('visibleCount').textContent = visible + ' entries';
        }

        function collapseAll() {
            document.querySelectorAll('.dl-entry.expanded').forEach(function(el) {
                el.classList.remove('expanded');
            });
        }

        function scrollToTop() {
            document.getElementById('logScroll').scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
@endpush
