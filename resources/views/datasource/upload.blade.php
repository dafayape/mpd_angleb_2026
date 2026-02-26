@extends('layout.app')

@section('title', 'Upload Data Opsel')

@section('content')
    @component('layout.partials.page-header', ['number' => '30', 'title' => 'Upload Data Opsel'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('datasource.history') }}">Datasource</a></li>
            <li class="breadcrumb-item active">Upload</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Import Data MPD</h4>
                    <p class="card-title-desc">Upload file .csv dengan separator titik koma (;) sesuai format yang
                        ditentukan.</p>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form id="uploadForm" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="opsel" class="form-label font-weight-bold text-uppercase text-muted"
                                    style="font-size: 11px;">OPSEL</label>
                                <select class="form-select" id="opsel" name="opsel" required
                                    style="border-radius: 6px;">
                                    <option value="" selected disabled>Pilih Opsel</option>
                                    <option value="TSEL">TSEL</option>
                                    <option value="IOH">IOH</option>
                                    <option value="XL">XL</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kategori" class="form-label font-weight-bold text-uppercase text-muted"
                                    style="font-size: 11px;">TIPE</label>
                                <select class="form-select" id="kategori" name="kategori" required
                                    style="border-radius: 6px;">
                                    <option value="" selected disabled>Pilih Tipe</option>
                                    <option value="REAL">REAL</option>
                                    <option value="FORECAST">FORECAST</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_data" class="form-label font-weight-bold text-uppercase text-muted"
                                style="font-size: 11px;">Tanggal</label>
                            <input class="form-control" type="date" id="tanggal_data" name="tanggal_data" required
                                style="border-radius: 6px;">
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label font-weight-bold text-uppercase text-muted"
                                style="font-size: 11px;">Pilih File CSV</label>
                            <div class="input-group">
                                <label class="input-group-text btn-light" for="file"
                                    style="cursor: pointer; background-color: #f1f3f5; border: 1px solid #ced4da;">Browse...</label>
                                <input type="file" class="form-control" id="file" name="file" accept=".csv"
                                    required style="display: none;">
                                <input type="text" class="form-control" id="filename-display"
                                    placeholder="Belum ada file dipilih." readonly style="background-color: #fff;">
                            </div>
                            <div class="form-text text-muted" style="font-size: 11px;">Format: CSV (separator ;) (maks 1GB)
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="bx bx-upload me-1"></i> Upload & Proses
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Modal --}}
    <div class="modal fade" id="uploadProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Mengupload Data...</h5>
                </div>
                <div class="modal-body">
                    <div class="progress mb-2">
                        <div id="uploadProgressBar"
                            class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar"
                            style="width: 0%">0%</div>
                    </div>
                    <p class="text-center mb-0 fw-bold" id="uploadStatusTitle">Mengupload File...</p>
                    <p class="text-center text-muted small" id="uploadStatusText">Mohon tunggu sebentar.</p>
                    <div id="etlStatus" class="d-none mt-3 p-2 rounded" style="background: #f8f9fa; font-size: 12px;">
                        <i class="mdi mdi-cog-transfer me-1 text-primary"></i>
                        <span id="etlStatusText">ETL sedang berjalan...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Validation Error Modal --}}
    <div class="modal fade" id="validationErrorModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bx bx-error-circle me-1"></i> Validasi CSV Gagal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="validationSummary" class="alert alert-warning mb-3" style="font-size: 13px;"></div>

                    {{-- Header Errors --}}
                    <div id="headerErrorSection" class="d-none mb-3">
                        <h6 class="fw-bold text-danger"><i class="bx bx-columns me-1"></i> Masalah Header</h6>
                        <div id="headerErrorContent" style="font-size: 13px;"></div>
                    </div>

                    {{-- Row Errors --}}
                    <div id="rowErrorSection" class="d-none">
                        <h6 class="fw-bold text-danger"><i class="bx bx-table me-1"></i> Masalah Data Baris</h6>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">Baris</th>
                                        <th style="width: 150px;">Kolom</th>
                                        <th style="width: 120px;">Tipe Error</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody id="rowErrorTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-arrow-back me-1"></i> Kembali ke Form
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('file').addEventListener('change', function(e) {
            document.getElementById('filename-display').value = e.target.files[0] ? e.target.files[0].name :
                "Belum ada file dipilih.";
        });

        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            var fileInput = document.getElementById('file');
            if (fileInput.files.length === 0) {
                alert('Pilih file terlebih dahulu.');
                return;
            }

            var formData = new FormData(this);
            var modal = new bootstrap.Modal(document.getElementById('uploadProgressModal'));
            var progressBar = document.getElementById('uploadProgressBar');
            var statusTitle = document.getElementById('uploadStatusTitle');
            var statusText = document.getElementById('uploadStatusText');
            var btn = document.getElementById('btnSubmit');

            modal.show();
            btn.disabled = true;

            // ═══════════════════════════════════════════════════════
            // STEP 1: Upload file ke server
            // ═══════════════════════════════════════════════════════
            $.ajax({
                url: "{{ route('datasource.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var pct = Math.round((evt.loaded / evt.total) * 100);
                            progressBar.style.width = pct + '%';
                            progressBar.innerHTML = pct + '%';
                            statusText.innerText = 'Mengupload ' + bytesToSize(evt.loaded) +
                                ' / ' + bytesToSize(evt.total);
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // ═══════════════════════════════════════════════════════
                        // STEP 2: Validasi CSV terhadap standar database
                        // ═══════════════════════════════════════════════════════
                        statusTitle.innerText = 'Memvalidasi CSV...';
                        statusText.innerText = 'Mengecek header, tipe data, dan kode referensi...';
                        progressBar.classList.remove('bg-primary');
                        progressBar.classList.add('bg-warning');
                        progressBar.style.width = '100%';
                        progressBar.innerHTML = 'Validasi...';

                        validateCsv(response.history_id, modal, progressBar, statusTitle,
                            statusText, btn);
                    } else {
                        alert('Upload Gagal: ' + response.message);
                        btn.disabled = false;
                        modal.hide();
                    }
                },
                error: function(xhr) {
                    alert('Error Upload: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr
                        .statusText));
                    btn.disabled = false;
                    modal.hide();
                }
            });

            // ═══════════════════════════════════════════════════════
            // STEP 2 handler: Validate CSV
            // ═══════════════════════════════════════════════════════
            function validateCsv(historyId, modal, progressBar, statusTitle, statusText, btn) {
                $.ajax({
                    url: "{{ route('datasource.validate') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        history_id: historyId
                    },
                    success: function(result) {
                        if (result.is_valid) {
                            if (result.opsel_mismatch) {
                                // ⚠️ OPSEL Mismatch Warning
                                modal.hide();
                                showValidationErrors(result);
                                btn.disabled = false;
                            } else {
                                // ✅ CSV Valid → lanjut import
                                proceedToImport(historyId, modal, progressBar, statusTitle, statusText);
                            }
                        } else {
                            // ❌ CSV Invalid → tampilkan detail error
                            modal.hide();
                            showValidationErrors(result);
                            btn.disabled = false;
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON
                            .message) : xhr.statusText;
                        alert('Error Validasi: ' + msg);
                        btn.disabled = false;
                        modal.hide();
                    }
                });
            }

            // ═══════════════════════════════════════════════════════
            // STEP 2b: Proceed to Import
            // ═══════════════════════════════════════════════════════
            function proceedToImport(historyId, modal, progressBar, statusTitle, statusText) {
                modal.show();
                statusTitle.innerText = 'Memproses Data CSV...';
                statusText.innerText = 'CSV valid. Memulai import data...';
                progressBar.classList.remove('bg-warning');
                progressBar.classList.add('bg-info');
                progressBar.style.width = '0%';
                progressBar.innerHTML = '0%';
                processChunk(historyId, 0);
            }

            // Expose proceedToImport to global scope so modal button can call it
            window.forceProcessChunk = function(historyId) {
                var modal = new bootstrap.Modal(document.getElementById('uploadProgressModal'));
                var progressBar = document.getElementById('uploadProgressBar');
                var statusTitle = document.getElementById('uploadStatusTitle');
                var statusText = document.getElementById('uploadStatusText');

                document.getElementById('btnSubmit').disabled = true;

                // Hide validation modal first
                var errorModal = bootstrap.Modal.getInstance(document.getElementById('validationErrorModal'));
                if (errorModal) {
                    errorModal.hide();
                }

                proceedToImport(historyId, modal, progressBar, statusTitle, statusText);
            };

            // ═══════════════════════════════════════════════════════
            // STEP 3: Process chunks (sama seperti sebelumnya)
            // ═══════════════════════════════════════════════════════
            function processChunk(historyId, offset) {
                $.ajax({
                    url: "{{ route('datasource.process-chunk') }}",
                    timeout: 0,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        history_id: historyId,
                        offset: offset
                    },
                    success: function(response) {
                        if (response.errors && response.errors.length > 0) {
                            console.warn('Processing errors:', response.errors);
                        }

                        if (response.status === 'progress') {
                            progressBar.style.width = response.percent + '%';
                            progressBar.innerHTML = response.percent + '%';
                            statusText.innerText = 'Memproses... ' + new Intl.NumberFormat('id-ID')
                                .format(response.rows_processed) + ' baris per chunk.';
                            processChunk(historyId, response.offset);
                        } else if (response.status === 'completed') {
                            progressBar.style.width = '100%';
                            progressBar.innerHTML = '100%';
                            progressBar.classList.remove('bg-info');
                            progressBar.classList.add('bg-success');
                            statusTitle.innerText = '✅ Import + ETL Selesai!';
                            statusText.innerText = 'Total ' + new Intl.NumberFormat('id-ID').format(
                                response.rows_processed) + ' baris CSV diimport.';

                            var etlDiv = document.getElementById('etlStatus');
                            var etlText = document.getElementById('etlStatusText');
                            etlDiv.classList.remove('d-none');
                            if (response.etl_dispatched) {
                                etlDiv.style.background = '#e2e3e5';
                                etlText.innerHTML =
                                    '<i class="bx bx-loader bx-spin text-primary me-2"></i> Proses ETL (agregasi spasial PostGIS) sedang berjalan di background server. Data akan muncul di dashboard beberapa saat lagi.';
                            }

                            setTimeout(function() {
                                window.location.href = "{{ route('datasource.history') }}";
                            }, 3000);
                        } else if (response.status === 'error') {
                            progressBar.classList.remove('bg-info');
                            progressBar.classList.add('bg-danger');
                            statusTitle.innerText = 'Error!';
                            statusText.innerText = response.message ||
                                'Terjadi kesalahan saat pemrosesan.';
                            btn.disabled = false;
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText;
                        progressBar.classList.remove('bg-info');
                        progressBar.classList.add('bg-danger');
                        statusTitle.innerText = 'Error!';
                        statusText.innerText = msg;
                        btn.disabled = false;
                    }
                });
            }
        });

        // ═══════════════════════════════════════════════════════
        // Show validation errors in a dedicated modal
        // ═══════════════════════════════════════════════════════
        function showValidationErrors(result) {
            var errorModal = new bootstrap.Modal(document.getElementById('validationErrorModal'));
            var summary = document.getElementById('validationSummary');
            var headerSection = document.getElementById('headerErrorSection');
            var headerContent = document.getElementById('headerErrorContent');
            var rowSection = document.getElementById('rowErrorSection');
            var rowBody = document.getElementById('rowErrorTableBody');

            // Summary
            var s = result.summary || {};
            var isValidObj = result.is_valid;

            summary.className = isValidObj ? 'alert alert-warning mb-3' : 'alert alert-danger mb-3';

            var summaryHtml = '<strong>Hasil Validasi:</strong> ' +
                (isValidObj ? '<span class="text-warning fw-bold">PERINGATAN</span><br>' :
                    '<span class="text-danger fw-bold">TIDAK VALID</span><br>') +
                'Total baris data: ' + new Intl.NumberFormat('id-ID').format(s.total_data_rows || 0) + '<br>' +
                'Baris bermasalah: ' + (isValidObj ? '<span class="text-success fw-bold">0</span>' :
                    '<span class="text-danger fw-bold">' + (s.rows_with_errors || 0) + '</span>');

            if (s.errors_truncated) {
                summaryHtml += '<br><span class="text-muted fst-italic">*Hanya menampilkan 50 error pertama*</span>';
            }
            summary.innerHTML = summaryHtml;

            // Opsel Mismatch Warning
            var mismatchSection = document.getElementById('opselMismatchSection');
            if (!mismatchSection) {
                // Create it if it doesn't exist
                mismatchSection = document.createElement('div');
                mismatchSection.id = 'opselMismatchSection';
                mismatchSection.className = 'd-none mb-3 alert alert-warning';
                summary.parentNode.insertBefore(mismatchSection, summary.nextSibling);
            }

            mismatchSection.classList.add('d-none');
            var footerHtml =
                '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bx bx-arrow-back me-1"></i> Kembali ke Form</button>';

            if (result.opsel_mismatch) {
                mismatchSection.classList.remove('d-none');
                mismatchSection.innerHTML =
                    '<h6 class="fw-bold text-dark"><i class="bx bx-error text-warning me-1"></i> Peringatan OPSEL Tidak Sesuai</h6>' +
                    '<div style="font-size: 13px;">Anda memilih OPSEL <span class="badge bg-primary">' + result
                    .opsel_mismatch.selected + '</span> di form, ' +
                    'namun di dalam file CSV ditemukan OPSEL <span class="badge bg-danger">' + result.opsel_mismatch
                    .mismatched.join(', ') + '</span>.</div>' +
                    '<div class="mt-2 fw-bold text-dark" style="font-size: 12px;">Apakah Anda yakin ingin tetap melanjutkan import data ini?</div>';

                if (isValidObj) {
                    footerHtml += '<button type="button" class="btn btn-warning" onclick="window.forceProcessChunk(' +
                        document.querySelector('input[name="history_id"]').value +
                        ')"><i class="bx bx-check-circle me-1"></i> Ya, Lanjutkan Import</button>';
                }
            }

            document.querySelector('#validationErrorModal .modal-footer').innerHTML = footerHtml;

            // Header errors
            headerSection.classList.add('d-none');
            if (result.header && !result.header.valid) {
                headerSection.classList.remove('d-none');
                var html = '';
                if (result.header.missing && result.header.missing.length > 0) {
                    html += '<div class="mb-1"><strong>Kolom hilang:</strong> <code>' +
                        result.header.missing.join('</code>, <code>') + '</code></div>';
                }
                if (result.header.extra && result.header.extra.length > 0) {
                    html += '<div class="mb-1"><strong>Kolom tidak dikenal:</strong> <code>' +
                        result.header.extra.join('</code>, <code>') + '</code></div>';
                }
                if (!result.header.order_correct) {
                    html +=
                        '<div class="mb-1"><strong>Urutan kolom salah.</strong> Pastikan kolom sesuai format standar.</div>';
                }
                html += '<div class="text-muted mt-1">Diharapkan ' + result.header.expected_count +
                    ' kolom, ditemukan ' + result.header.actual_count + ' kolom.</div>';
                headerContent.innerHTML = html;
            }

            // Row errors
            rowSection.classList.add('d-none');
            rowBody.innerHTML = '';
            if (result.row_errors && result.row_errors.length > 0) {
                rowSection.classList.remove('d-none');
                result.row_errors.forEach(function(row) {
                    row.issues.forEach(function(issue) {
                        var tr = document.createElement('tr');
                        var typeLabel = {
                            'COLUMN_COUNT': 'Jumlah Kolom',
                            'INVALID_DATE': 'Format Tanggal',
                            'EMPTY_REQUIRED': 'Field Kosong',
                            'INVALID_VALUE': 'Nilai Invalid',
                            'INVALID_NUMERIC': 'Bukan Angka',
                            'REF_NOT_FOUND': 'Kode Tidak Terdaftar'
                        };
                        tr.innerHTML = '<td class="text-center">' + row.row + '</td>' +
                            '<td><code>' + (issue.field || '-') + '</code></td>' +
                            '<td><span class="badge bg-' +
                            (issue.type === 'REF_NOT_FOUND' ? 'danger' : 'warning') +
                            ' text-dark" style="font-size: 11px;">' +
                            (typeLabel[issue.type] || issue.type) + '</span></td>' +
                            '<td>' + issue.detail + '</td>';
                        rowBody.appendChild(tr);
                    });
                });
            }

            errorModal.show();
        }

        function bytesToSize(bytes) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes == 0) return '0 Byte';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        }
    </script>
@endpush
