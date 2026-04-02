@extends('layout.app')

@section('title', 'Log Developer')

@push('css')
    <style>
        /* ─── Variables & Defaults ─── */
        :root {
            --dl-bg-page: #f8fafc;
            --dl-color-text: #334155;
            --dl-color-heading: #0f172a;
            --dl-color-muted: #94a3b8;
            --dl-border: #e2e8f0;
            --dl-border-light: #f1f5f9;
            --dl-bg-card: #ffffff;
            
            --dl-primary: #3b82f6;
            --dl-primary-bg: #eff6ff;
            --dl-success: #059669;
            --dl-success-bg: #ecfdf5;
            --dl-warning: #d97706;
            --dl-warning-bg: #fffbeb;
            --dl-danger: #dc2626;
            --dl-danger-bg: #fef2f2;
            --dl-debug: #475569;
            --dl-debug-bg: #f8fafc;
        }

        /* ─── Stat Cards ─── */
        .dl-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
        }

        @media (max-width: 991px) {
            .dl-stats { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 575px) {
            .dl-stats { grid-template-columns: repeat(2, 1fr); }
        }

        .dl-stat {
            background: var(--dl-bg-card);
            border-radius: 14px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--dl-border-light);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dl-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
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

        .dl-stat-icon.t-total { background: linear-gradient(135deg, var(--dl-success-bg), #d1fae5); color: var(--dl-success); }
        .dl-stat-icon.t-error { background: linear-gradient(135deg, var(--dl-danger-bg), #fecaca); color: var(--dl-danger); }
        .dl-stat-icon.t-warn  { background: linear-gradient(135deg, var(--dl-warning-bg), #fde68a); color: var(--dl-warning); }
        .dl-stat-icon.t-info  { background: linear-gradient(135deg, var(--dl-primary-bg), #bfdbfe); color: var(--dl-primary); }
        .dl-stat-icon.t-debug { background: linear-gradient(135deg, var(--dl-debug-bg), var(--dl-border)); color: var(--dl-debug); }

        .dl-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--dl-color-heading);
            line-height: 1;
        }

        .dl-stat-lbl {
            font-size: 0.72rem;
            color: var(--dl-color-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 4px;
        }

        /* ─── Toolbar ─── */
        .dl-toolbar {
            background: var(--dl-bg-card);
            border-radius: 14px;
            padding: 14px 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--dl-border-light);
        }

        .dl-file-select {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
            font-weight: 600;
            background: var(--dl-bg-page);
            border: 1px solid var(--dl-border);
            border-radius: 8px;
            padding: 8px 12px;
            color: var(--dl-color-heading);
            cursor: pointer;
            transition: border-color 0.2s ease;
        }
        
        .dl-file-select:focus {
            border-color: var(--dl-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
            outline: none;
        }

        .dl-search {
            border: 1px solid var(--dl-border);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .dl-search:focus {
            border-color: var(--dl-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        .dl-level-pills {
            display: inline-flex;
            gap: 4px;
            background: var(--dl-border-light);
            padding: 4px;
            border-radius: 10px;
            flex-wrap: wrap; /* Support for smaller screens */
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

        .dl-pill:hover { background: rgba(255, 255, 255, 0.6); }
        .dl-pill.active {
            background: var(--dl-bg-card);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            color: var(--dl-color-heading);
        }

        .dl-pill.active[data-level="ERROR"] { color: var(--dl-danger); }
        .dl-pill.active[data-level="WARNING"] { color: var(--dl-warning); }
        .dl-pill.active[data-level="INFO"] { color: var(--dl-primary); }
        .dl-pill.active[data-level="DEBUG"] { color: var(--dl-debug); }

        .dl-pill i { font-size: 14px; }

        /* ─── Log Panel ─── */
        .dl-panel {
            background: var(--dl-bg-card);
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--dl-border-light);
            overflow: hidden;
        }

        .dl-panel-head {
            padding: 14px 20px;
            border-bottom: 2px solid var(--dl-border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .dl-panel-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--dl-color-heading);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dl-panel-title .file-tag {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.8rem;
            background: var(--dl-border-light);
            color: #64748b;
            padding: 3px 12px;
            border-radius: 6px;
            font-weight: 600;
        }

        .dl-panel-title .count-tag {
            font-size: 0.75rem;
            background: var(--dl-primary-bg);
            color: var(--dl-primary);
            padding: 3px 12px;
            border-radius: 20px;
            font-weight: 700;
        }

        .dl-action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid var(--dl-border);
            background: var(--dl-bg-card);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #64748b;
            font-size: 18px;
        }

        .dl-action-btn:hover {
            background: var(--dl-bg-page);
            color: var(--dl-color-heading);
            border-color: #cbd5e1;
        }

        /* ─── Log Entries ─── */
        .dl-scroll {
            max-height: 65vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .dl-scroll::-webkit-scrollbar { width: 6px; }
        .dl-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        .dl-entry {
            display: flex;
            border-bottom: 1px solid #f8fafc;
            transition: background 0.15s ease;
            cursor: pointer;
            position: relative;
        }

        .dl-entry:hover { background: #fafbff; }
        .dl-entry.expanded { background: #f5f7ff; }

        /* Timeline rail */
        .dl-rail {
            width: 50px;
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
            top: 36px;
            bottom: 0;
            left: 50%;
            width: 2px;
            background: var(--dl-border);
            transform: translateX(-50%);
        }

        .dl-entry:last-child .dl-rail::after { display: none; }

        .dl-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            z-index: 1;
            flex-shrink: 0;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px currentColor;
            background: currentColor;
        }

        .dl-dot.c-error   { color: #ef4444; }
        .dl-dot.c-warning { color: #f59e0b; }
        .dl-dot.c-info    { color: #3b82f6; }
        .dl-dot.c-debug   { color: #94a3b8; }

        .dl-body {
            flex: 1;
            padding: 14px 20px 14px 4px;
            min-width: 0;
        }

        .dl-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .dl-time {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.8rem;
            color: var(--dl-color-muted);
            font-weight: 600;
        }

        .dl-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dl-badge i { font-size: 13px; }

        .dl-badge.b-error   { background: var(--dl-danger-bg);  color: var(--dl-danger); }
        .dl-badge.b-warning { background: var(--dl-warning-bg); color: var(--dl-warning); }
        .dl-badge.b-info    { background: var(--dl-primary-bg); color: var(--dl-primary); }
        .dl-badge.b-debug   { background: var(--dl-border-light); color: var(--dl-debug); }

        .dl-ch {
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
            background: var(--dl-bg-page);
            padding: 3px 10px;
            border-radius: 6px;
            border: 1px solid var(--dl-border-light);
        }

        .dl-msg {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.85rem;
            color: var(--dl-color-text);
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 44px; /* Roughly 2 lines */
            overflow: hidden;
            transition: max-height 0.4s ease;
            position: relative;
        }

        .dl-entry.expanded .dl-msg {
            max-height: 3000px;
        }

        .dl-hint {
            font-size: 0.75rem;
            color: #cbd5e1;
            margin-top: 6px;
            font-style: italic;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .dl-entry.expanded .dl-hint { display: none; }

        /* ─── Empty State ─── */
        .dl-empty {
            text-align: center;
            padding: 6rem 2rem;
            background: #fafafb;
            border-radius: 12px;
            margin: 20px;
        }

        .dl-empty-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--dl-success-bg), #d1fae5);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.1);
        }

        .dl-empty-icon i {
            font-size: 40px;
            color: var(--dl-success);
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
        // Group Critical/Emergency into Error count for simplicity
        $errorCount = collect($lines)
            ->whereIn('level', ['ERROR', 'CRITICAL', 'EMERGENCY'])
            ->count();
        $warningCount = collect($lines)->where('level', 'WARNING')->count();
        $infoCount = collect($lines)->where('level', 'INFO')->count();
        $debugCount = collect($lines)
            ->whereNotIn('level', ['ERROR', 'CRITICAL', 'EMERGENCY', 'WARNING', 'INFO'])
            ->count();
        $totalCount = count($lines);
    @endphp

    {{-- Stat Cards --}}
    <div class="dl-stats mb-3 mt-2" data-aos="fade-up" data-aos-duration="400">
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
    <div class="dl-toolbar mb-3" data-aos="fade-up" data-aos-duration="500">
        <div class="row g-3 align-items-center">
            <div class="col-lg-3 col-md-6">
                <form method="GET" action="{{ route('devlog') }}">
                    <select name="file" class="form-select dl-file-select" onchange="this.form.submit()" aria-label="Pilih File Log">
                        @foreach ($logFiles as $file)
                            <option value="{{ $file }}" {{ $selectedFile === $file ? 'selected' : '' }}>
                                <i class="bx bx-file"></i> {{ $file }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 shadow-none" style="border-radius: 8px 0 0 8px; border-color: #e2e8f0; color: #94a3b8;">
                        <i class="bx bx-search"></i>
                    </span>
                    <input type="text" id="searchLog" class="form-control dl-search border-start-0 shadow-none"
                        placeholder="Cari pesan, channel, waktu..." style="border-radius: 0 8px 8px 0; border-color: #e2e8f0;">
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
    <div class="dl-panel" data-aos="fade-up" data-aos-duration="600">
        <div class="dl-panel-head">
            <div class="dl-panel-title">
                <i class="bx bx-terminal text-primary" style="font-size: 20px;"></i>
                Log Viewer
                <span class="file-tag"><i class="bx bx-file-blank"></i> {{ $selectedFile ?? '-' }}</span>
                <span class="count-tag"><i class="bx bx-list-check"></i> <span id="visibleCount">{{ number_format($totalCount) }}</span> entries</span>
            </div>
            <div class="d-flex gap-2">
                <button class="dl-action-btn" onclick="collapseAll()" title="Collapse semua entri">
                    <i class="bx bx-minus"></i>
                </button>
                <button class="dl-action-btn" onclick="scrollToTop()" title="Scroll ke batas atas">
                    <i class="bx bx-up-arrow-alt"></i>
                </button>
                <a href="{{ route('devlog', ['file' => $selectedFile]) }}" class="dl-action-btn" title="Refresh Log">
                    <i class="bx bx-sync"></i>
                </a>
            </div>
        </div>

        <div class="dl-scroll" id="logScroll">
            @if ($totalCount === 0)
                <div class="dl-empty">
                    <div class="dl-empty-icon">
                        <i class="bx bx-check-double"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Semua Sistem Berjalan Normal</h4>
                    <p class="text-muted mb-0 fs-6">File log ini belum memiliki catatan error atau event terbaru.</p>
                </div>
            @else
                @foreach ($lines as $idx => $entry)
                    @php
                        // Group level for filtering logic (e.g. CRITICAL counts as ERROR)
                        $levelGroup = match ($entry['level']) {
                            'ERROR', 'CRITICAL', 'EMERGENCY' => 'ERROR',
                            'WARNING' => 'WARNING',
                            'INFO' => 'INFO',
                            default => 'DEBUG',
                        };

                        $dotCls = match ($levelGroup) {
                            'ERROR'   => 'c-error',
                            'WARNING' => 'c-warning',
                            'INFO'    => 'c-info',
                            default   => 'c-debug',
                        };
                        
                        $badgeCls = match ($levelGroup) {
                            'ERROR'   => 'b-error',
                            'WARNING' => 'b-warning',
                            'INFO'    => 'b-info',
                            default   => 'b-debug',
                        };

                        $iconCls = match ($levelGroup) {
                            'ERROR'   => 'bx bx-x-circle',
                            'WARNING' => 'bx bx-error-alt',
                            'INFO'    => 'bx bx-info-circle',
                            default   => 'bx bx-bug-alt',
                        };

                        // Decide if the message string needs a "read more" hint
                        $isLong = strlen($entry['message']) > 160 || substr_count($entry['message'], "\n") >= 2;
                    @endphp
                    
                    <div class="dl-entry" data-level="{{ $levelGroup }}" onclick="this.classList.toggle('expanded')">
                        <div class="dl-rail">
                            <div class="dl-dot {{ $dotCls }}"></div>
                        </div>
                        <div class="dl-body">
                            <div class="dl-meta">
                                <span class="dl-time"><i class="bx bx-time-five"></i> {{ $entry['timestamp'] }}</span>
                                <span class="dl-badge {{ $badgeCls }}">
                                    <i class="{{ $iconCls }}"></i> {{ $entry['level'] }}
                                </span>
                                <span class="dl-ch"><i class="bx bx-server"></i> {{ $entry['channel'] }}</span>
                            </div>
                            <div class="dl-msg">{{ $entry['message'] }}</div>
                            @if ($isLong)
                                <div class="dl-hint"><i class="bx bx-chevron-down"></i> Klik untuk melihat detail lengkap</div>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Level pills
            const pills = document.querySelectorAll('.dl-pill');
            pills.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent accidental form submission if wrapped
                    pills.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    applyFilters();
                });
            });

            // Fast local search input listener
            const searchInput = document.getElementById('searchLog');
            if(searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }
        });

        function applyFilters() {
            const activePill = document.querySelector('.dl-pill.active');
            const level = activePill ? activePill.dataset.level : 'all';
            const query = document.getElementById('searchLog').value.toLowerCase().trim();
            
            let visible = 0;
            const entries = document.querySelectorAll('.dl-entry');

            entries.forEach(function(el) {
                const matchLevel = (level === 'all' || el.dataset.level === level);
                const elText = el.textContent || el.innerText;
                const matchSearch = (!query || elText.toLowerCase().includes(query));
                
                if (matchLevel && matchSearch) {
                    el.style.display = 'flex'; // Restore original flex display
                    visible++;
                } else {
                    el.style.display = 'none';
                }
            });

            const visibleCountEl = document.getElementById('visibleCount');
            if (visibleCountEl) {
                // Formatting the number cleanly
                visibleCountEl.textContent = new Intl.NumberFormat('id-ID').format(visible);
            }
        }

        // Collapse open entries
        window.collapseAll = function() {
            document.querySelectorAll('.dl-entry.expanded').forEach(function(el) {
                el.classList.remove('expanded');
            });
        };

        // Smooth scroll to the top of log panel
        window.scrollToTop = function() {
            const scrollContainer = document.getElementById('logScroll');
            if (scrollContainer) {
                scrollContainer.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        };
    </script>
@endpush
