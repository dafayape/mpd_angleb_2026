@extends('layout.app')

@section('title', 'Daily Report')

@section('content')
<div class="container-fluid">

<div class="row no-print mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-0 font-size-18">Daily Report</h4>
        <div class="d-flex gap-2">
            <form class="d-flex align-items-center gap-2" method="GET">
                <input type="date" name="date" class="form-control" value="{{ $date }}">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </form>
            <button onclick="window.print()" class="btn btn-secondary"><i class="bx bx-printer me-1"></i> Print / PDF</button>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-lg print-card">
            <div class="card-body p-5">
                
                <!-- KOP SURAT -->
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-3 border-dark">
                    <img src="{{ asset('assets/images/logo-sm.svg') }}" alt="Logo" height="60" class="me-3">
                    <div class="text-center flex-grow-1">
                        <h4 class="fw-bold text-uppercase mb-1">Kementerian Perhubungan Republik Indonesia</h4>
                        <h5 class="fw-bold mb-1">Badan Kebijakan Transportasi</h5>
                        <small class="d-block">Gedung Karya Lt. 5-7, Jl. Medan Merdeka Barat No. 8, Jakarta Pusat</small>
                    </div>
                    <div style="width: 60px;"></div> <!-- Spacer for balance -->
                </div>

                <!-- DATE & ADDRESS -->
                <div class="row mb-4">
                    <div class="col-6">
                        Nomor: <strong>00{{ date('d', strtotime($date)) }}/MPD/ANGLEB/2026</strong><br>
                        Lampiran: -<br>
                        Perihal: <strong>Laporan Harian Pergerakan MPD</strong>
                    </div>
                    <div class="col-6 text-end">
                        Jakarta, {{ $formattedDate }}
                    </div>
                </div>

                <div class="mb-4">
                    <p>Kepada Yth.<br><strong>Kepala Badan Kebijakan Transportasi</strong><br>di Tempat</p>
                </div>

                <!-- CONTENT -->
                <div class="mb-4 content-text">
                    <p class="text-justify">
                        Dengan hormat,<br>
                        Bersama ini kami sampaikan laporan harian monitoring pergerakan masyarakat berbasis 
                        <em>Mobile Positioning Data</em> (MPD) untuk periode Angkutan Lebaran Tahun 2026 
                        pada tanggal <strong>{{ $formattedDate }}</strong> sebagai berikut:
                    </p>

                    <!-- 1. TOTALS -->
                    <h6 class="fw-bold mt-4">1. Ringkasan Pergerakan Nasional</h6>
                    <table class="table table-bordered table-sm w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Kategori</th>
                                <th class="text-end">Jumlah Pergerakan</th>
                                <th class="text-end">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Target (Forecast)</td>
                                <td class="text-end">{{ number_format($total_forecast) }}</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>Realisasi (Aktual)</td>
                                <td class="text-end fw-bold">{{ number_format($total_real) }}</td>
                                <td class="text-end">
                                    @if($total_forecast > 0)
                                        {{ number_format(($total_real / $total_forecast) * 100, 1) }}% Tercapai
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- 2. OPSEL -->
                    <h6 class="fw-bold mt-4">2. Distribusi per Operator Seluler (Aktual)</h6>
                    <table class="table table-bordered table-sm w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Operator</th>
                                <th class="text-end">Volume</th>
                                <th class="text-end">Proporsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($opsel_data as $row)
                            <tr>
                                <td>{{ $row->opsel }}</td>
                                <td class="text-end">{{ number_format($row->total) }}</td>
                                <td class="text-end">
                                    @if($total_real > 0)
                                        {{ number_format(($row->total / $total_real) * 100, 1) }}%
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- 3. MODE -->
                    <h6 class="fw-bold mt-4">3. Top 3 Moda Transportasi</h6>
                    <ul>
                        @forelse($mode_data as $row)
                            <li>
                                <strong>{{ $row->kode_moda }}</strong>: {{ number_format($row->total) }} pergerakan
                            </li>
                        @empty
                            <li><em>Data moda belum tersedia.</em></li>
                        @endforelse
                    </ul>

                    <p class="mt-4 text-justify">
                        Demikian laporan ini kami sampaikan untuk menjadi periksa dan arahan lebih lanjut.
                        Atas perhatian Bapak, kami ucapkan terima kasih.
                    </p>
                </div>

                <!-- SIGNATURE -->
                <div class="row mt-5">
                    <div class="col-6"></div>
                    <div class="col-6 text-center">
                        <p class="mb-5">Hormat Kami,<br>Tim Teknis MPD</p>
                        <br><br>
                        <p class="fw-bold text-decoration-underline mb-0">Koordinator Lapangan</p>
                        <small>NIP. 19850101 201001 1 001</small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* CSS FOR PRINTING (A4 Format) */
    @media print {
        @page { margin: 0; size: auto; }
        body { background: white; -webkit-print-color-adjust: exact !important; }
        .no-print, header, footer, .vertical-menu, .navbar { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .page-content { padding: 0 !important; }
        .container-fluid { padding: 0 !important; }
        .card { box-shadow: none !important; border: none !important; }
        .print-card { width: 100% !important; max-width: 100% !important; }
        .card-body { padding: 2cm !important; }
    }
    .content-text p { font-size: 1.1rem; }
    .table th, .table td { vertical-align: middle; }
</style>


</div>
@endsection
