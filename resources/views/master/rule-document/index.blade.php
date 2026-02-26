@extends('layout.app')

@section('title', 'Rule Document')
@section('subtitle', '| Master Referensi')

@push('styles')
    <style>
        .progress {
            height: 20px;
            margin-bottom: 0;
            display: none;
        }
    </style>
@endpush

@section('content')
    @component('layout.partials.page-header', ['number' => '28', 'title' => 'Dokumentasi Teknis'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">Master</a></li>
            <li class="breadcrumb-item"><a href="javascript: void(0);">Referensi</a></li>
            <li class="breadcrumb-item active">Dokumentasi Teknis</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm">
                            <form action="{{ route('master.rule-document.index') }}" method="GET"
                                class="row gy-2 gx-3 align-items-center">
                                <div class="col-auto">
                                    <input type="text" name="search" class="form-control form-control-sm"
                                        placeholder="Cari nama file..." value="{{ request('search') }}">
                                </div>
                                <div class="col-auto">
                                    <input type="date" name="start_date" class="form-control form-control-sm"
                                        placeholder="Dari Tanggal" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-auto">
                                    <input type="date" name="end_date" class="form-control form-control-sm"
                                        placeholder="Sampai Tanggal" value="{{ request('end_date') }}">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="bx bx-search-alt"></i>
                                        Filter</button>
                                    <a href="{{ route('master.rule-document.index') }}" class="btn btn-sm btn-light"><i
                                            class="bx bx-reset"></i> Reset</a>
                                </div>
                            </form>
                        </div>
                        @if (Auth::user()->role === 'admin')
                            <div class="col-sm-auto">
                                <button type="button" class="btn btn-sm btn-success waves-effect waves-light"
                                    data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="bx bx-plus me-1"></i> Upload Dokumen
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;">No</th>
                                    <th>Tanggal Upload</th>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Diunggah Oleh</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $doc)
                                    <tr>
                                        <td>{{ ($documents->currentPage() - 1) * $documents->perPage() + $loop->iteration }}
                                        </td>
                                        <td>{{ $doc->created_at->format('d M Y H:i') }}</td>
                                        <td>{{ $doc->original_name }}</td>
                                        <td>{{ number_format($doc->file_size / 1048576, 2) }} MB</td>
                                        <td>{{ $doc->uploader->name ?? 'External User' }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('master.rule-document.preview', $doc->id) }}"
                                                    class="btn btn-soft-info btn-sm" title="Preview" target="_blank">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('master.rule-document.download', $doc->id) }}"
                                                    class="btn btn-soft-primary btn-sm" title="Download">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                                @if (Auth::user()->role === 'admin')
                                                    <button type="button" class="btn btn-soft-danger btn-sm btn-delete"
                                                        data-id="{{ $doc->id }}"
                                                        data-url="{{ route('master.rule-document.destroy', $doc->id) }}"
                                                        title="Hapus">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada dokumen ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $documents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (Auth::user()->role === 'admin')
        <!-- Upload Modal -->
        <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">Upload Rule Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="uploadForm" action="{{ route('master.rule-document.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="document" class="form-label">Pilih Dokumen (Max 100MB)</label>
                                <input class="form-control" type="file" id="document" name="document" required>
                                <div class="form-text">Pastikan file tidak melebihi 100MB.</div>
                            </div>
                            <div id="progressContainer" style="display: none;">
                                <div class="progress mb-2">
                                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100">0%</div>
                                </div>
                                <div id="progressStatus" class="text-muted small text-center">Mengunggah...</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                id="btnCancel">Batal</button>
                            <button type="submit" class="btn btn-primary" id="btnUpload">Unggah Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Delete Logic
            $('.btn-delete').on('click', function() {
                const url = $(this).data('url');
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Mohon Tunggu',
                            text: 'Sedang menghapus data...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Terhapus!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!',
                                    'Terjadi kesalahan saat menghapus data.',
                                    'error');
                            }
                        });
                    }
                });
            });

            // Upload Logic
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const action = $(this).attr('action');

                $('#progressContainer').show();
                $('.progress').show();
                $('#btnUpload').prop('disabled', true);
                $('#btnCancel').prop('disabled', true);

                $.ajax({
                    url: action,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                const percentComplete = Math.round((evt.loaded / evt
                                        .total) *
                                    100);
                                $('#progressBar').css('width', percentComplete + '%');
                                $('#progressBar').attr('aria-valuenow',
                                    percentComplete);
                                $('#progressBar').text(percentComplete + '%');

                                if (percentComplete === 100) {
                                    $('#progressStatus').text(
                                        'Memproses file di server...');
                                }
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                            resetUploadUI();
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan saat mengunggah file.';
                        if (xhr.status === 413) {
                            msg = 'File terlalu besar (di luar kapasitas post server).';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error!', msg, 'error');
                        resetUploadUI();
                    }
                });
            });

            function resetUploadUI() {
                $('#progressContainer').hide();
                $('.progress').hide();
                $('#progressBar').css('width', '0%');
                $('#progressBar').text('0%');
                $('#btnUpload').prop('disabled', false);
                $('#btnCancel').prop('disabled', false);
                $('#progressStatus').text('Mengunggah...');
            }
        });
    </script>
@endpush
