@extends('layout.app')

@section('content')
    @component('layout.partials.page-header', ['number' => '00', 'title' => 'Keynote Material'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="javascript: void(0);">MPD</a></li>
            <li class="breadcrumb-item active">Keynote Material</li>
        </ol>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="card-title mb-1 fw-bold text-primary">Daftar Kebutuhan Analisis dan Output</h5>
                            <p class="text-muted mb-0 small">Pengolahan dan Analisis Data Pergerakan Berdasarkan Mobile
                                Positioning Data (MPD) - Periode Angkutan Lebaran 2026</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-nowrap mb-0" id="table-requirements">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">No. Slide</th>
                                    <th>Konten / Substansi</th>
                                    <th>Kelompok Bahasan</th>
                                    <th class="text-center">Detail & Referensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requirements as $req)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $req['no'] }}</td>
                                        <td style="white-space: normal; min-width: 300px;">
                                            <div class="fw-semibold text-dark">{{ $req['content'] }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = 'bg-soft-primary text-primary';
                                                switch ($req['group']) {
                                                    case 'Executive Summary':
                                                        $badgeClass = 'bg-soft-info text-info';
                                                        break;
                                                    case 'Pergerakan Nasional':
                                                        $badgeClass = 'bg-soft-success text-success';
                                                        break;
                                                    case 'Pergerakan Jabodetabek':
                                                        $badgeClass = 'bg-soft-warning text-warning';
                                                        break;
                                                    case 'Kesimpulan dan Rekomendasi':
                                                        $badgeClass = 'bg-soft-danger text-danger';
                                                        break;
                                                    case 'Substansi Tambahan':
                                                        $badgeClass = 'bg-soft-secondary text-secondary';
                                                        break;
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $badgeClass }} px-3 py-2 font-size-11">{{ $req['group'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route($req['route']) }}"
                                                class="btn btn-primary btn-sm px-3 rounded-pill shadow-sm transition-all hover-scale">
                                                <i class="mdi mdi-eye-outline me-1"></i> Lihat Analisis
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <div class="alert alert-soft-info border-info d-flex align-items-start mb-0" role="alert">
                            <i class="mdi mdi-information-outline font-size-24 me-3 text-info"></i>
                            <div>
                                <h6 class="alert-heading fw-bold text-info mb-1">Catatan Penting:</h6>
                                <ul class="mb-0 small text-dark-50">
                                    <li><strong>Slide 36:</strong> Data simpul transportasi tidak ditampilkan karena output
                                        datanya dianggap anomali. Jika hasil olah data pada periode Angleb nanti masuk
                                        kategori normal, maka hasil analisis ini akan ditampilkan di dashboard utama.</li>
                                    <li><strong>Slide 37:</strong> Perhitungan netflow digunakan sebagai alternatif untuk
                                        menentukan kabupaten/kota origin/asal dan destination/tujuan favorit masyarakat.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-top: none;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
        }

        .bg-soft-primary {
            background-color: rgba(85, 110, 230, 0.1);
        }

        .bg-soft-info {
            background-color: rgba(80, 165, 241, 0.1);
        }

        .bg-soft-success {
            background-color: rgba(52, 195, 143, 0.1);
        }

        .bg-soft-warning {
            background-color: rgba(241, 180, 76, 0.1);
        }

        .bg-soft-danger {
            background-color: rgba(244, 106, 106, 0.1);
        }

        .bg-soft-secondary {
            background-color: rgba(116, 120, 141, 0.1);
        }

        .alert-soft-info {
            background-color: rgba(80, 165, 241, 0.05);
        }

        .hover-scale:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(85, 110, 230, 0.3) !important;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize simple sorting if needed, or just let it be a clean list
            // If DataTables is available and desired:
            // $('#table-requirements').DataTable({
            //     pageLength: 25,
            //     responsive: true,
            //     language: {
            //         search: "Cari:",
            //         lengthMenu: "Tampilkan _MENU_ data",
            //         info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data"
            //     }
            // });
        });
    </script>
@endpush
