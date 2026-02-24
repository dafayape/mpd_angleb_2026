@extends('layout.app')

@section('title', 'Rule Document')
@section('subtitle', '| Master Referensi')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Rule Document</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Master</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Referensi</a></li>
                        <li class="breadcrumb-item active">Rule Document</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

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
                                                <a href="{{ route('master.rule-document.download', $doc->id) }}"
                                                    class="btn btn-soft-primary btn-sm" title="Download">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                                @if (Auth::user()->role === 'admin')
                                                    <form action="{{ route('master.rule-document.destroy', $doc->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-soft-danger btn-sm"
                                                            title="Hapus">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
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
                    <form action="{{ route('master.rule-document.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="document" class="form-label">Pilih Dokumen (Max 100MB)</label>
                                <input class="form-control" type="file" id="document" name="document" required>
                                <div class="form-text">Pastikan file tidak melebihi 100MB.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Unggah Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection
