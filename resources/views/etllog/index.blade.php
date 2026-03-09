@extends('layout.app')

@section('title', 'Log ETL Pipeline')

@push('css')
    <style>
        .terminal-box {
            background-color: #1e1e1e;
            color: #d4d4d4;
            font-family: 'Courier New', Courier, monospace;
            padding: 15px;
            border-radius: 5px;
            height: 400px;
            overflow-y: auto;
            font-size: 13px;
        }
        .log-entry { margin-bottom: 5px; }
        .log-time { color: #858585; margin-right: 10px; }
        .log-level.info { color: #4fc1ff; }
        .log-level.warning { color: #cece7e; }
        .log-level.error { color: #f44747; }
        .log-message { color: #d4d4d4; }
    </style>
@endpush

@section('content')
    @component('layout.partials.page-header', ['number' => '36', 'title' => 'Log ETL Pipeline'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Log ETL</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Status Pipeline ETL</h4>
                    <p class="card-title-desc">Pantau proses ekstraksi, transformasi, dan pemuatan data ruang ke PostGIS.</p>
                    
                    <button class="btn btn-sm btn-light mb-3" onclick="location.reload()"><i class="bx bx-refresh"></i> Refresh Data</button>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover dt-responsive nowrap w-100 align-middle">
                            <thead class="table-light">
                                <tr class="text-uppercase text-muted" style="font-size: 11px;">
                                    <th style="width: 5%;">ID</th>
                                    <th>File Upload</th>
                                    <th>Opsel</th>
                                    <th>Tgl Data</th>
                                    <th>Status Upload</th>
                                    <th>Status ETL</th>
                                    <th style="width: 25%;">Progress ETL</th>
                                    <th>Integritas</th>
                                    <th style="width: 15%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 12px;">
                                @forelse($jobs as $job)
                                    @php
                                        $meta = $job->metadata ?? [];
                                        $etlStatus = $meta['etl_status'] ?? 'pending';
                                        $etlProgress = (int) ($meta['etl_progress'] ?? 0);
                                        
                                        $badgeClass = 'bg-secondary';
                                        if ($etlStatus == 'processing') $badgeClass = 'bg-warning text-dark';
                                        if ($etlStatus == 'completed') $badgeClass = 'bg-success';
                                        if ($etlStatus == 'failed') $badgeClass = 'bg-danger';
                                    @endphp
                                    <tr>
                                        <td>#{{ $job->id }}</td>
                                        <td class="fw-bold">{{ $job->original_filename ?? $job->filename }}</td>
                                        <td><span class="badge bg-primary bg-soft text-primary font-size-11">{{ $job->opsel }}</span></td>
                                        <td>{{ $job->tanggal_data }}</td>
                                        <td>
                                            @if ($job->status == 'completed')
                                                <span class="badge badge-soft-success">Completed</span>
                                            @elseif ($job->status == 'failed')
                                                <span class="badge badge-soft-danger">Failed</span>
                                            @else
                                                <span class="badge badge-soft-secondary">{{ $job->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $badgeClass }} font-size-11" id="status-badge-{{ $job->id }}">{{ strtoupper($etlStatus) }}</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 15px;">
                                                <div id="pb-{{ $job->id }}" class="progress-bar {{ $etlStatus == 'processing' ? 'progress-bar-striped progress-bar-animated bg-warning' : ($etlStatus == 'failed' ? 'bg-danger' : 'bg-success') }}" 
                                                    role="progressbar" style="width: {{ $etlProgress }}%;" aria-valuenow="{{ $etlProgress }}" aria-valuemin="0" aria-valuemax="100">
                                                    {{ $etlProgress }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td id="integrity-{{ $job->id }}">
                                            @if(isset($meta['etl_stats']))
                                                @php $stats = $meta['etl_stats']; @endphp
                                                <div class="d-flex flex-column" style="font-size: 10px;">
                                                    <span class="badge {{ $stats['success_rate'] >= 95 ? 'bg-success' : ($stats['success_rate'] >= 80 ? 'bg-info' : 'bg-danger') }} mb-1">
                                                        {{ $stats['success_rate'] }}% Success
                                                    </span>
                                                    <span class="text-muted">Lost: {{ number_format($stats['unmapped_volume'] ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-info" onclick="viewLogs({{ $job->id }})" title="Lihat Terminal Log">
                                                    <i class="bx bx-terminal"></i> Logs
                                                </button>
                                                @if($etlStatus === 'failed')
                                                    <button class="btn btn-sm btn-danger" onclick="retryEtl({{ $job->id }})" title="Retry ETL Process">
                                                        <i class="bx bx-repost"></i> Retry
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">Belum ada Job Import/ETL.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terminal Modal -->
    <div class="modal fade" id="terminalModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-dark text-white border-bottom-0">
                    <h5 class="modal-title font-monospace"><i class="bx bx-terminal text-success me-2"></i> ETL Output - Job #<span id="termJobId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="stopPolling()"></button>
                </div>
                <div class="modal-body p-0 bg-dark">
                    <div class="p-3 border-bottom border-secondary">
                        <div class="d-flex align-items-center justify-content-between text-light mb-2">
                            <span>Status: <span id="termStatus" class="fw-bold text-warning">PROCESSING</span></span>
                            <span>Progress: <span id="termProgressText">0%</span></span>
                        </div>
                        <div class="progress" style="height: 6px; background-color: #333;">
                            <div id="termProgressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="terminal-box" id="terminalOutput">
                        <!-- Logs will be appended here -->
                    </div>
                </div>
                <div class="modal-footer bg-dark border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" onclick="stopPolling()">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    let pollInterval = null;
    let currentJobId = null;

    function viewLogs(jobId) {
        currentJobId = jobId;
        document.getElementById('termJobId').innerText = jobId;
        document.getElementById('terminalOutput').innerHTML = '<div class="text-center mt-5"><i class="bx bx-loader bx-spin font-size-24 text-success"></i><br>Connecting to ETL logs...</div>';
        
        const modal = new bootstrap.Modal(document.getElementById('terminalModal'));
        modal.show();

        // Start polling immediately
        fetchStatus(jobId);
        pollInterval = setInterval(() => fetchStatus(jobId), 3000);
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    function fetchStatus(jobId) {
        $.ajax({
            url: `{{ url('/etllog') }}/${jobId}/status`,
            type: 'GET',
            success: function(res) {
                updateTerminal(res);
                updateTableRow(jobId, res);

                // Stop polling if done
                if (res.etl_status === 'completed' || res.etl_status === 'failed') {
                    stopPolling();
                    let pb = document.getElementById('termProgressBar');
                    pb.classList.remove('progress-bar-animated', 'progress-bar-striped');
                    if(res.etl_status === 'failed') {
                        pb.classList.remove('bg-success');
                        pb.classList.add('bg-danger');
                    }
                }
            },
            error: function(xhr) {
                const errMsg = xhr.responseJSON?.error || 'Lost connection to server';
                document.getElementById('terminalOutput').innerHTML += `<div class="log-entry"><span class="log-level error">[ERROR]</span> <span class="log-message text-danger">${errMsg} (HTTP ${xhr.status}).</span></div>`;
                stopPolling();
            }
        });
    }

    function updateTerminal(res) {
        // Status & Progress Bar
        const statusEl = document.getElementById('termStatus');
        statusEl.innerText = res.etl_status.toUpperCase();
        statusEl.className = 'fw-bold ' + 
            (res.etl_status === 'completed' ? 'text-success' : 
            (res.etl_status === 'failed' ? 'text-danger' : 'text-warning'));

        document.getElementById('termProgressText').innerText = res.etl_progress + '%';
        document.getElementById('termProgressBar').style.width = res.etl_progress + '%';

        // Stats Summary inside terminal
        if (res.etl_stats) {
            const stats = res.etl_stats;
            const lostFormatted = new Intl.NumberFormat('id-ID').format(stats.unmapped_volume || 0);
            const rawFormatted = new Intl.NumberFormat('id-ID').format(stats.raw_volume || 0);
            
            let statsHtml = `
                <div class="alert alert-soft-secondary d-flex justify-content-between mb-2 py-1 px-2 border-0" style="font-size: 11px;">
                    <span><i class="bx bx-check-shield text-success"></i> Success: <b>${stats.success_rate}%</b></span>
                    <span><i class="bx bx-package text-info"></i> Raw: <b>${rawFormatted}</b></span>
                    <span><i class="bx bx-error text-danger"></i> Unmapped: <b>${lostFormatted}</b></span>
                </div>
            `;
            
            // Insertion point before logs
            let existingAlert = document.getElementById('stats-alert-' + currentJobId);
            if (!existingAlert) {
                let div = document.createElement('div');
                div.id = 'stats-alert-' + currentJobId;
                div.innerHTML = statsHtml;
                document.getElementById('terminalOutput').parentNode.insertBefore(div, document.getElementById('terminalOutput'));
            } else {
                existingAlert.innerHTML = statsHtml;
            }
        }

        // Logs
        const termOut = document.getElementById('terminalOutput');
        if (res.etl_logs && res.etl_logs.length > 0) {
            // Check if we need to clear loader
            if (termOut.innerHTML.includes('bx-loader')) {
                termOut.innerHTML = '';
            }

            let html = '';
            res.etl_logs.forEach(log => {
                let lvlClass = log.level.toLowerCase() === 'error' ? 'error' : 
                              (log.level.toLowerCase() === 'warning' ? 'warning' : 'info');
                
                html += `<div class="log-entry">
                            <span class="log-time">[${log.time}]</span>
                            <span class="log-level ${lvlClass}">[${log.level}]</span>
                            <span class="log-message">${escapeHtml(log.message)}</span>
                         </div>`;
            });
            termOut.innerHTML = html;
            // Auto scroll to bottom
            termOut.scrollTop = termOut.scrollHeight;
        } else if (termOut.innerHTML.includes('bx-loader')) {
            termOut.innerHTML = '<div class="text-muted fst-italic">Waiting for ETL process to start...</div>';
        }
    }

    function updateTableRow(jobId, res) {
        let badge = document.getElementById('status-badge-' + jobId);
        let pb = document.getElementById('pb-' + jobId);
        
        if(badge && pb) {
            badge.innerText = res.etl_status.toUpperCase();
            badge.className = 'badge font-size-11 ' + 
                (res.etl_status === 'completed' ? 'bg-success' : 
                (res.etl_status === 'failed' ? 'bg-danger' : 'bg-warning text-dark'));

            pb.style.width = res.etl_progress + '%';
            pb.innerText = res.etl_progress + '%';
            pb.className = 'progress-bar ' + 
                (res.etl_status === 'processing' ? 'progress-bar-striped progress-bar-animated bg-warning' : 
                (res.etl_status === 'failed' ? 'bg-danger' : 'bg-success'));
        }

        let integrity = document.getElementById('integrity-' + jobId);
        if (integrity && res.etl_stats) {
            let stats = res.etl_stats;
            let badgeClass = stats.success_rate >= 95 ? 'bg-success' : (stats.success_rate >= 80 ? 'bg-info' : 'bg-danger');
            let lostFormatted = new Intl.NumberFormat('id-ID').format(stats.unmapped_volume || 0);
            
            integrity.innerHTML = `
                <div class="d-flex flex-column" style="font-size: 10px;">
                    <span class="badge ${badgeClass} mb-1">${stats.success_rate}% Success</span>
                    <span class="text-muted">Lost: ${lostFormatted}</span>
                </div>
            `;
        }
    }

    function retryEtl(jobId) {
        if (!confirm('Apakah Anda yakin ingin mencoba memproses ulang data ETL ini? Data spatial_movements sebelumnya mungkin akan ditimpa.')) {
            return;
        }

        $.ajax({
            url: `{{ url('/etllog') }}/${jobId}/retry`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if(res.status === 'success') {
                    alert('Job ETL berhasil di-queue ulang. Silahkan buka Log Terminal untuk melihat progress.');
                    location.reload();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function(xhr) {
                alert('Gagal trigger retry: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
    }

    function escapeHtml(unsafe) {
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Stop polling if modal is closed via background click or ESC key
    document.getElementById('terminalModal').addEventListener('hidden.bs.modal', function () {
        stopPolling();
    });
</script>
@endpush
