@extends('layout.app')

@section('title', 'Upload Data Opsel')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Upload Data Opsel</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('datasource.history') }}">Datasource</a></li>
                        <li class="breadcrumb-item active">Upload</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

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
                        statusTitle.innerText = 'Memproses Data CSV...';
                        statusText.innerText = 'Mempersiapkan data...';
                        progressBar.classList.remove('bg-primary');
                        progressBar.classList.add('bg-info');
                        progressBar.style.width = '0%';
                        progressBar.innerHTML = '0%';
                        processChunk(response.history_id, 0);
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

            function processChunk(historyId, offset) {
                $.ajax({
                    url: "{{ route('datasource.process-chunk') }}",
                    timeout: 0, // No timeout — ETL runs synchronously and may take minutes
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

                            // Show ETL Result
                            var etlDiv = document.getElementById('etlStatus');
                            var etlText = document.getElementById('etlStatusText');
                            etlDiv.classList.remove('d-none');
                            if (response.etl_error) {
                                etlDiv.style.background = '#fff3cd';
                                etlText.innerHTML = '⚠️ ETL Error: ' + response.etl_error;
                            } else {
                                etlDiv.style.background = '#d1e7dd';
                                etlText.innerHTML = '✅ ETL berhasil! <strong>' + new Intl.NumberFormat(
                                        'id-ID').format(response.etl_rows || 0) +
                                    '</strong> baris tersimpan ke spatial_movements.';
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

        function bytesToSize(bytes) {
            var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            if (bytes == 0) return '0 Byte';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        }
    </script>
@endpush
