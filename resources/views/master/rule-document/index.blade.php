@extends('layout.app')

@section('title', 'Dokumentasi Teknis')
@section('subtitle', '| Master Referensi')

@section('content')
    @component('layout.partials.page-header', ['number' => '29', 'title' => 'Dokumentasi Teknis'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">Master</a></li>
            <li class="breadcrumb-item"><a href="javascript: void(0);">Referensi</a></li>
            <li class="breadcrumb-item active">Dokumentasi Teknis</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: none;">
                <div class="card-body" style="padding: 1.5rem;">
                    {{-- Filter & Actions Toolbar --}}
                    <div class="row mb-4 align-items-center">
                        <div class="col-sm">
                            <form action="{{ route('master.rule-document.index') }}" method="GET" class="row gy-2 gx-3 align-items-center">
                                <div class="col-auto">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bx bx-search"></i></span>
                                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama file..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="bx bx-calendar"></i></span>
                                        <input type="date" name="start_date" class="form-control" title="Dari Tanggal" value="{{ request('start_date') }}">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="bx bx-calendar"></i></span>
                                        <input type="date" name="end_date" class="form-control" title="Sampai Tanggal" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Filter
                                    </button>
                                    <a href="{{ route('master.rule-document.index') }}" class="btn btn-sm btn-light border" title="Reset Pencarian">
                                        <i class="bx bx-reset"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                        @if (Auth::user()->role === 'admin')
                            <div class="col-sm-auto mt-3 mt-sm-0">
                                <button type="button" class="btn btn-sm btn-success waves-effect waves-light shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="bx bx-upload me-1"></i> Upload Dokumen
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Data Table --}}
                    <div class="table-responsive rounded border mb-0">
                        <table class="table align-middle table-nowrap table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;" class="text-center">No</th>
                                    <th>Tanggal Upload</th>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Diunggah Oleh</th>
                                    <th class="text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $doc)
                                    <tr>
                                        <td class="text-center fw-medium text-muted">
                                            {{ ($documents->currentPage() - 1) * $documents->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bx bx-time-five me-1"></i>{{ $doc->created_at->format('d M Y H:i') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle bg-primary bg-soft text-primary font-size-14">
                                                        <i class="bx bx-file"></i>
                                                    </span>
                                                </div>
                                                <span class="fw-semibold text-truncate" style="max-width: 300px;" title="{{ $doc->original_name }}">
                                                    {{ $doc->original_name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>{{ number_format($doc->file_size / 1048576, 2) }} <small class="text-muted">MB</small></td>
                                        <td>
                                            <i class="bx bx-user-circle me-1 text-muted"></i>
                                            {{ $doc->uploader->name ?? 'Eksternal' }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('master.rule-document.preview', $doc->id) }}" class="btn border btn-light btn-sm" title="Preview" target="_blank">
                                                    <i class="bx bx-show text-info"></i>
                                                </a>
                                                <a href="{{ route('master.rule-document.download', $doc->id) }}" class="btn border btn-light btn-sm" title="Download">
                                                    <i class="bx bx-download text-primary"></i>
                                                </a>
                                                @if (Auth::user()->role === 'admin')
                                                    <button type="button" class="btn border btn-light btn-sm btn-delete" data-id="{{ $doc->id }}" data-url="{{ route('master.rule-document.destroy', $doc->id) }}" title="Hapus Dokumen">
                                                        <i class="bx bx-trash text-danger"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="mb-3">
                                                <div class="avatar-md mx-auto">
                                                    <span class="avatar-title rounded-circle bg-light text-muted" style="font-size: 2rem;">
                                                        <i class="bx bx-folder-open"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <h6 class="fw-bold mb-1">Belum ada dokumen</h6>
                                            <p class="text-muted mb-0">Silahkan unggah dokumen teknis baru untuk ditampilkan.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Menampilkan {{ $documents->firstItem() ?? 0 }} hingga {{ $documents->lastItem() ?? 0 }} dari {{ $documents->total() }} entri
                        </div>
                        <div>
                            {{ $documents->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (Auth::user()->role === 'admin')
        {{-- Formulir Upload Modal --}}
        <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-bottom bg-light bg-soft">
                        <h5 class="modal-title fw-bold" id="uploadModalLabel">
                            <i class="bx bx-cloud-upload text-primary me-1"></i> Unggah Dokumen Form
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="uploadForm" action="{{ route('master.rule-document.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="mb-2">
                                <label for="document" class="form-label font-weight-bold text-uppercase text-muted" style="font-size: 11px;">Pilih File Dokumen</label>
                                <div class="input-group">
                                    <label class="input-group-text btn-light" id="btn-browse-trigger" style="cursor: pointer; background-color: #f1f3f5; border: 1px solid #ced4da;">
                                        <i class="bx bx-search-alt me-1"></i> Browse...
                                    </label>
                                    <input type="file" class="form-control d-none" id="document" name="document" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar">
                                    <input type="text" class="form-control" id="filename-display" placeholder="Belum ada file dipilih." readonly style="background-color: #fff; cursor: pointer;">
                                </div>
                                <div class="form-text text-muted mt-2" style="font-size: 11px;">
                                    <i class="bx bx-info-circle text-info"></i> Kapasitas maksimal file 100MB.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light bg-soft border-top">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="bx bx-check me-1"></i> Mulai Unggah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Progress Modal Static Overlay --}}
        <div class="modal fade" id="uploadProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3 mt-2">
                            <i class="bx bx-cloud-upload bx-fade-up text-primary" style="font-size: 3.5rem;"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" id="uploadStatusTitle">Sedang Mengunggah...</h5>
                        <p class="text-muted small mb-4" id="uploadStatusText">Mohon tunggu sementara file diproses ke server.</p>
                        
                        <div class="progress mb-2" style="height: 12px; border-radius: 10px;">
                            <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; border-radius: 10px;"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="small fw-medium text-muted" id="uploadPercentage">0%</span>
                            <span class="small fw-medium text-muted">100%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // File input mapping UI logic
            const docInput = document.getElementById('document');
            const fileDisplay = document.getElementById('filename-display');
            const btnBrowse = document.getElementById('btn-browse-trigger');

            if (docInput) {
                // Forward clicks from the simulated input to the real hidden input
                if (fileDisplay) {
                    fileDisplay.addEventListener('click', function() {
                        docInput.click();
                    });
                }
                if (btnBrowse) {
                    btnBrowse.addEventListener('click', function() {
                        docInput.click();
                    });
                }

                docInput.addEventListener('change', function(e) {
                    fileDisplay.value = e.target.files[0] ? e.target.files[0].name : "Belum ada file dipilih.";
                });
            }

            // Delete Logic - Using Vanilla JS with fetch to perfectly handle Delete HTTP method and headers
            document.querySelectorAll('.btn-delete').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Gunakan currentTarget untuk memastikan kita mengambil data dari button, bukan icon child-nya
                    const targetBtn = e.currentTarget;
                    const url = targetBtn.getAttribute('data-url');

                    Swal.fire({
                        title: 'Hapus Dokumen?',
                        text: "File yang dihapus tidak dapat dipulihkan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bx bx-trash me-1"></i> Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Memproses',
                                text: 'Sedang menghapus dokumen...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(err => { throw err; }).catch(() => {
                                        throw new Error('Server merespon dengan status ' + response.status);
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: data.message || 'Dokumen teknis berhasil dihapus.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Gagal!', data.message || 'Gagal menghapus dokumen', 'error');
                                }
                            })
                            .catch(error => {
                                let errorMsg = error.message || 'Terjadi kesalahan sistem saat penghapusan.';
                                Swal.fire('Error!', errorMsg, 'error');
                            });
                        }
                    });
                });
            });

            // Upload Logic
            const uploadForm = document.getElementById('uploadForm');
            if (uploadForm) {
                uploadForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const fileInput = document.getElementById('document');
                    if (fileInput.files.length === 0) {
                        Swal.fire('Perhatian', 'Pilih dokumen terlebih dahulu', 'warning');
                        return;
                    }

                    // Hide the form modal, show progress modal (no backdrop closing)
                    var formModal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
                    if (formModal) formModal.hide();

                    var progressModal = new bootstrap.Modal(document.getElementById('uploadProgressModal'));
                    progressModal.show();

                    var progressBar = document.getElementById('uploadProgressBar');
                    var percentageText = document.getElementById('uploadPercentage');
                    var statusTitle = document.getElementById('uploadStatusTitle');
                    var statusText = document.getElementById('uploadStatusText');
                    
                    const formData = new FormData(this);
                    const actionUrl = this.getAttribute('action');

                    // Reset styling before upload
                    progressBar.style.width = '0%';
                    progressBar.classList.remove('bg-success', 'bg-danger');
                    progressBar.classList.add('bg-primary');
                    percentageText.textContent = '0%';
                    statusTitle.innerText = 'Mengunggah Data...';
                    statusText.innerText = 'Mohon tunggu sementara file Anda diproses.';

                    $.ajax({
                        url: actionUrl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        xhr: function() {
                            const xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function(evt) {
                                if (evt.lengthComputable) {
                                    const pct = Math.round((evt.loaded / evt.total) * 100);
                                    progressBar.style.width = pct + '%';
                                    percentageText.textContent = pct + '%';

                                    if (pct === 100) {
                                        progressBar.classList.add('progress-bar-striped', 'progress-bar-animated');
                                        statusTitle.innerText = 'Memproses File...';
                                        statusText.innerText = 'Server sedang menyimpan dokumen Anda.';
                                    }
                                }
                            }, false);
                            return xhr;
                        },
                        success: function(response) {
                            if (response.success) {
                                progressBar.classList.remove('bg-primary', 'progress-bar-striped', 'progress-bar-animated');
                                progressBar.classList.add('bg-success');
                                statusTitle.innerText = '✅ Berhasil!';
                                statusText.innerText = 'Dokumen berhasil tersimpan di sistem.';
                                
                                setTimeout(() => {
                                    location.reload();
                                }, 1200);
                            } else {
                                handleUploadError(response.message || 'Gagal menyimpan dokumen.', progressModal);
                            }
                        },
                        error: function(xhr) {
                            let msg = 'Terjadi kesalahan tidak terduga pada jaringan.';
                            if (xhr.status === 413) {
                                msg = 'File terlalu besar. Melebihi kapasitas maksimal server.';
                            } else if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) {
                                msg = xhr.responseJSON.message || xhr.responseJSON.error;
                            }
                            handleUploadError(msg, progressModal);
                        }
                    });
                });
            }

            function handleUploadError(errorMessage, progressModalReference) {
                // Return safely to upload modal if failure
                progressModalReference.hide();
                Swal.fire('Upload Gagal', errorMessage, 'error');
                
                // Allow re-opening with clean slate
                document.getElementById('document').value = '';
                document.getElementById('filename-display').value = '';
            }
        });
    </script>
@endpush
