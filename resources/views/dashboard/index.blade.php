@extends('layout.app')

@section('title', 'Dashboard Utama')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Dashboard Utama</h4>
                <div class="page-title-right">
                    {{-- Button removed as requested --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Timeline Card Overlay --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 text-center">
                    <h5 class="fw-bold text-primary mb-0">Overlay Kalender Angkutan Lebaran 2026</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive-xl mb-0">
                        <table class="table table-bordered calendar-grid-table min-w-1000 mb-0">
                            <thead>
                                <tr>
                                    <th width="14.28%">Senin</th>
                                    <th width="14.28%">Selasa</th>
                                    <th width="14.28%">Rabu</th>
                                    <th width="14.28%">Kamis</th>
                                    <th width="14.28%">Jumat</th>
                                    <th width="14.28%">Sabtu</th>
                                    <th width="14.28%">Minggu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Week 1: 19 - 22 Feb -->
                                <tr>
                                    <td colspan="3" class="bg-empty-day border-0"></td>
                                    <td>
                                        <div class="calendar-date-header">19 Februari</div>
                                        <ol class="calendar-task-list">
                                            <li>Tahap persiapan dan koordinasi</li>
                                            <li>Meeting dengan tim IT</li>
                                            <li>Penyampaian timeline, target, dan deliverables</li>
                                        </ol>
                                    </td>
                                    <td>
                                        <div class="calendar-date-header">20 Februari</div>
                                    </td>
                                    <td>
                                        <div class="calendar-date-header">21 Februari</div>
                                    </td>
                                    <td>
                                        <div class="calendar-date-header">22 Februari</div>
                                    </td>
                                </tr>
                                <!-- Week 2: 23 Feb - 1 Mar -->
                                <tr class="bg-light">
                                    <th>23 Februari</th>
                                    <th>24 Februari</th>
                                    <th>25 Februari</th>
                                    <th>26 Februari</th>
                                    <th>27 Februari</th>
                                    <th>28 Februari</th>
                                    <th>1 Maret</th>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <ol class="calendar-task-list">
                                            <li>Penyusunan daftar kebutuhan analisis dan output</li>
                                            <li>Persiapan pengembangan sistem oleh tim IT</li>
                                        </ol>
                                    </td>
                                    <td colspan="3">
                                        <ol class="calendar-task-list">
                                            <li>Sinkronisasi substansi</li>
                                            <li>Review keynote material yang akan ditampilkan di dalam dashboard</li>
                                        </ol>
                                    </td>
                                    <td colspan="2" class="align-middle">
                                        Review progres pengembangan sistem dan validasi kesesuaian data yang ada di sistem
                                        dengan kebutuhan analisis
                                    </td>
                                </tr>
                                <!-- Week 3: 2 Mar - 8 Mar -->
                                <tr class="bg-light">
                                    <th>2 Maret</th>
                                    <th>3 Maret</th>
                                    <th>4 Maret</th>
                                    <th>5 Maret</th>
                                    <th class="bg-beta-ready">6 Maret</th>
                                    <th>7 Maret</th>
                                    <th>8 Maret</th>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <ol class="calendar-task-list">
                                            <li>Uji coba dan finalisasi sistem</li>
                                            <li>Pengolahan data dummy yang diterima dari Opsel</li>
                                            <li>Validasi kesesuaian data yang ada di sistem dengan kebutuhan analisis dan
                                                output yang diharapkan</li>
                                        </ol>
                                    </td>
                                    <td class="bg-beta-ready align-middle text-center">
                                        <strong>Target sistem sudah siap 100%</strong><br>
                                        <small>(Beta Version)</small>
                                    </td>
                                    <td colspan="2" class="bg-empty-day border-0"></td>
                                </tr>
                                <!-- Week 4: 9 Mar - 15 Mar -->
                                <tr class="bg-light">
                                    <th>9 Maret</th>
                                    <th>10 Maret</th>
                                    <th>11 Maret</th>
                                    <th>12 Maret</th>
                                    <th class="bg-posko-period">13 Maret</th>
                                    <th class="bg-posko-period">14 Maret</th>
                                    <th class="bg-posko-period">15 Maret</th>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <ol class="calendar-task-list">
                                            <li>Uji coba (<em>beta testing</em>) dan <em>final cross-check</em> data</li>
                                            <li><em>Cross-check</em> kesesuaian data dan output-nya (tabel, grafik, desire
                                                line, dll)</li>
                                        </ol>
                                    </td>
                                    <td class="bg-posko-period">
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD Hari-1<br>(H-8 Lebaran)</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                    <td class="bg-posko-period">
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD Hari-2<br>(H-7 Lebaran)</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                    <td class="bg-posko-period">
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD Hari-3<br>(H-6 Lebaran)</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                </tr>

                                <!-- Week 5: 16 Mar - 22 Mar -->
                                <tr class="bg-posko-period border-top">
                                    <th>16 Maret</th>
                                    <th>17 Maret</th>
                                    <th>18 Maret</th>
                                    <th>19 Maret</th>
                                    <th>20 Maret</th>
                                    <th>21 Maret</th>
                                    <th>22 Maret</th>
                                </tr>
                                <tr class="bg-posko-period">
                                    @php
                                        $w5_dates = [
                                            ['day' => 'Hari-4', 'h' => 'H-5 Lebaran'],
                                            ['day' => 'Hari-5', 'h' => 'H-4 Lebaran'],
                                            ['day' => 'Hari-6', 'h' => 'H-3 Lebaran'],
                                            ['day' => 'Hari-7', 'h' => 'H-2 Lebaran'],
                                            ['day' => 'Hari-8', 'h' => 'H-1 Lebaran'],
                                            ['day' => 'Hari-9', 'h' => 'Hari Pertama Lebaran'],
                                            ['day' => 'Hari-10', 'h' => 'H+1 Lebaran'],
                                        ];
                                    @endphp
                                    @foreach ($w5_dates as $d)
                                        <td>
                                            <ol class="calendar-task-list">
                                                <li>Penarikan data MPD {{ $d['day'] }}<br>({{ $d['h'] }})</li>
                                                <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                                <li>Monitoring kestabilan sistem</li>
                                            </ol>
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Week 6: 23 Mar - 29 Mar -->
                                <tr class="bg-posko-period border-top">
                                    <th>23 Maret</th>
                                    <th>24 Maret</th>
                                    <th>25 Maret</th>
                                    <th>26 Maret</th>
                                    <th>27 Maret</th>
                                    <th>28 Maret</th>
                                    <th>29 Maret</th>
                                </tr>
                                <tr class="bg-posko-period">
                                    @php
                                        $w6_dates = [
                                            ['day' => 'Hari-11', 'h' => 'H+2 Lebaran'],
                                            ['day' => 'Hari-12', 'h' => 'H+3 Lebaran'],
                                            ['day' => 'Hari-13', 'h' => 'H+4 Lebaran'],
                                            ['day' => 'Hari-14', 'h' => 'H+5 Lebaran'],
                                            ['day' => 'Hari-15', 'h' => 'H+6 Lebaran'],
                                            ['day' => 'Hari-16', 'h' => 'H+7 Lebaran'],
                                            ['day' => 'Hari-17', 'h' => 'H+8 Lebaran'],
                                        ];
                                    @endphp
                                    @foreach ($w6_dates as $d)
                                        <td>
                                            <ol class="calendar-task-list">
                                                <li>Penarikan data MPD {{ $d['day'] }}<br>({{ $d['h'] }})</li>
                                                <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                                <li>Monitoring kestabilan sistem</li>
                                            </ol>
                                        </td>
                                    @endforeach
                                </tr>

                                <!-- Week 7: 30 Mar - 5 Apr -->
                                <tr class="bg-light">
                                    <th class="bg-posko-period">30 Maret</th>
                                    <th class="bg-posko-period">31 Maret</th>
                                    <th>1 April</th>
                                    <th>2 April</th>
                                    <th>3 April</th>
                                    <th>4 April</th>
                                    <th>5 April</th>
                                </tr>
                                <tr>
                                    <td class="bg-posko-period">
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD Hari-18<br>(H+9 Lebaran)</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                    <td class="bg-posko-period align-middle">
                                        Finalisasi laporan hasil olah data MPD Angleb 2026
                                    </td>
                                    <td class="align-middle">
                                        Finalisasi laporan hasil olah data MPD Angleb 2026
                                    </td>
                                    <td class="align-middle">
                                        Penyusunan Dokumen Policy Paper dan Policy Brief
                                    </td>
                                    <td class="align-middle">
                                        Penyusunan Dokumen Policy Paper dan Policy Brief
                                    </td>
                                    <td colspan="2" class="bg-empty-day border-0"></td>
                                </tr>

                                <!-- Week 8: 6 Apr - 12 Apr -->
                                <tr class="bg-light">
                                    <th>6 April</th>
                                    <th>7 April</th>
                                    <th>8 April</th>
                                    <th>9 April</th>
                                    <th class="bg-deliverable">10 April</th>
                                    <th>11 April</th>
                                    <th>12 April</th>
                                </tr>
                                <tr>
                                    <td class="align-middle">Penyusunan Dokumen Policy Paper dan Policy Brief</td>
                                    <td class="align-middle">Penyusunan Dokumen Policy Paper dan Policy Brief</td>
                                    <td class="align-middle">Penyusunan Dokumen Policy Paper dan Policy Brief</td>
                                    <td class="align-middle">Penyusunan Dokumen Policy Paper dan Policy Brief</td>
                                    <td class="bg-deliverable align-middle text-center">
                                        <strong>Penyampaian Seluruh Deliverables ke BKT</strong>
                                    </td>
                                    <td colspan="2" class="bg-empty-day border-0"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Highlight Alert --}}
    <div class="alert alert-info border-0 bg-info-subtle text-info" role="alert">
        <div class="d-flex align-items-start">
            <div class="flex-grow-1">
                <h5 class="alert-heading font-size-14 text-info"><i class="mdi mdi-information-outline me-1"></i>
                    Highlight
                    Survei Angkutan Lebaran 2026</h5>
                <p class="mb-0 font-size-13">Dalam rangka persiapan dan pelaksanaan layanan Angkutan
                    Lebaran (Angleb) tahun
                    2026, Badan Kebijakan Transportasi melaksanakan survei online pada periode
                    <strong>15 - 30 Januari
                        2026</strong> untuk mengidentifikasi preferensi dan mengukur persepsi masyarakat
                    yang akan melakukan
                    perjalanan. Survei tersebut bekerja sama dengan <strong>LAPI ITB, BPS, dan
                        Kementerian Komdigi.</strong>
                </p>
            </div>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">Latar Belakang MPD</h5>
                    <hr class="mt-0 mb-3 opacity-25">
                    <p class="text-muted mb-0 small" style="text-align: justify;">Periode Lebaran
                        menjadi salah satu momen
                        dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan
                        signifikan pergerakan
                        antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada
                        tanggal 21 dan 22 Maret
                        2026 sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal 19 Maret 2026.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">Metodelogi MPD</h5>
                    <hr class="mt-0 mb-3 opacity-25">
                    <p class="text-muted mb-0 small" style="text-align: justify;">Periode Lebaran
                        menjadi salah satu momen
                        dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan
                        signifikan pergerakan
                        antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada
                        tanggal 21 dan 22 Maret
                        2026 sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal 19 Maret 2026.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        {{-- Left Column: Stats --}}
        <div class="col-xl-4 d-flex flex-column gap-4">
            {{-- Total Orang dan Pergerakan --}}
            <div class="card shadow-sm mb-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title mb-0 text-primary fw-bold">Total Pergerakan (Aktual)</h5>
                </div>
                <div class="card-body pb-4">
                    <p class="mb-2 text-muted fw-medium small">Total Akumulasi (13-29 Mar)</p>
                    <div class="mb-3">
                        <h2 class="mb-0 fw-bold text-primary display-6">
                            {{ number_format($total_real, 0, ',', '.') }} <span
                                class="fs-6 text-muted fw-normal">Pergerakan</span></h2>
                        <small class="text-muted">Target Forecast:
                            {{ number_format($total_forecast, 0, ',', '.') }}</small>
                    </div>
                    <div class="alert alert-success bg-success-subtle text-success border-0 mb-0 py-2 px-3">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-check-circle-outline me-2 fs-5"></i>
                            <span class="small fst-italic">{!! strip_tags($analysis['general']) !!}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Perbandingan Pergerakan --}}
            <div class="card shadow-sm mb-0 flex-grow-1">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title mb-0 text-primary fw-bold">Perbandingan Pergerakan Dengan
                        Tahun Lalu</h5>
                </div>
                <div class="card-body">
                    <p class="mb-4 fw-bold small text-uppercase text-muted">Perbandingan Pergerakan</p>

                    @php
                        $maxValue = max($total_real, $total_forecast);
                        $widthReal = $maxValue > 0 ? ($total_real / $maxValue) * 100 : 0;
                        $widthForecast = $maxValue > 0 ? ($total_forecast / $maxValue) * 100 : 0;
                    @endphp

                    {{-- Horizontal Bar: Forecast (Blue) --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-1">
                            <div class="flex-grow-1 bg-light rounded-pill" style="height: 25px; overflow: hidden;">
                                <div class="bg-primary h-100 d-flex align-items-center justify-content-end pe-2 text-white fw-bold small"
                                    style="width: {{ $widthForecast }}%;">
                                </div>
                            </div>
                            <div class="ms-3 fw-bold text-primary text-end" style="width: 100px;">
                                {{ number_format($total_forecast, 0, ',', '.') }}</div>
                        </div>
                        <small class="text-muted d-block ms-1" style="font-size: 11px;">Target /
                            Forecast</small>
                    </div>

                    {{-- Horizontal Bar: Real (Yellow) --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-1">
                            <div class="flex-grow-1 bg-light rounded-pill" style="height: 25px; overflow: hidden;">
                                <div class="bg-warning h-100 d-flex align-items-center justify-content-end pe-2 text-white fw-bold small"
                                    style="width: {{ $widthReal }}%;">
                                </div>
                            </div>
                            <div class="ms-3 fw-bold text-warning text-end" style="width: 100px;">
                                {{ number_format($total_real, 0, ',', '.') }}</div>
                        </div>
                        <small class="text-muted d-block ms-1" style="font-size: 11px;">Aktual
                            (Real)</small>
                    </div>

                    <div class="mt-auto">
                        <span
                            class="badge {{ $persen_capaian >= 100 ? 'bg-success-subtle text-success' : 'bg-success-subtle text-success' }} fs-5 fw-bold px-3 py-2">
                            +{{ number_format($persen_capaian - 100, 1) }}%
                        </span>
                        <span class="text-muted ms-2 small">dari target forecast</span>
                    </div>

                </div>
            </div>
        </div>

        {{-- Right Column: Chart per OPSEL --}}
        <div class="col-xl-8">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title mb-0 text-primary fw-bold">Tren Jumlah Orang dan Pergerakan
                        per OPSEL</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                        <div id="chart-opsel" class="w-100" style="height: 380px;"></div>
                    </div>

                    <div
                        class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start rounded-3">
                        <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1 text-info font-size-14">Analisis
                                Trend Orang</h6>
                            <p class="mb-0 small" style="line-height: 1.5;">{!! $analysis['opsel'] !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Chart: Per Moda --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="card-title mb-0 text-primary fw-bold">Tren Pergerakan Harian per Moda
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Chart --}}
                    <div id="chart-moda" class="w-100" style="height: 400px;"></div>
                    <p class="text-center fw-bold mt-2 text-muted small fst-italic">Grafik menunjukkan
                        tren pergerakan
                        harian untuk setiap moda transportasi (13 Mar - 29 Mar 2026)</p>


                    <div
                        class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start rounded-3">
                        <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1 text-info font-size-14">Analisis Moda
                                Transportasi</h6>
                            <p class="mb-0 small" style="line-height: 1.5;">{!! $analysis['moda'] !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* CSS for Monthly Grid Calendar */
        .calendar-grid-table th,
        .calendar-grid-table td {
            border: 1px solid #ced4da;
            vertical-align: top;
        }

        .calendar-grid-table th {
            text-align: center;
            padding: 8px;
            font-size: 13px;
            background-color: #f8f9fa;
        }

        .calendar-grid-table td {
            padding: 10px;
            width: 14.28%;
            font-size: 13px;
        }

        .calendar-date-header {
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .calendar-task-list {
            padding-left: 20px;
            margin-bottom: 0;
            color: #495057;
        }

        .calendar-task-list li {
            margin-bottom: 4px;
        }

        .calendar-task-list li:last-child {
            margin-bottom: 0;
        }

        /* Custom Background Colors */
        .bg-empty-day {
            background-color: #e9ecef;
            opacity: 0.5;
        }

        .bg-beta-ready {
            background-color: #ffebd6;
            /* Light orange/yellow */
        }

        .bg-posko-period {
            background-color: #e0f2f1;
            /* Light teal/cyan */
        }

        .bg-deliverable {
            background-color: #ffebd6;
            /* Match the Beta Ready color for emphasis */
        }

        /* Ensure table is responsive */
        .table-responsive-xl {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .min-w-1000 {
            min-width: 1000px;
        }

        /* Horizontal Calendar Timeline */
        .timeline-calendar {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 20px;
        }

        .timeline-wrapper {
            min-width: 900px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .timeline-grid {
            display: grid;
            grid-template-columns: repeat(18, 1fr);
            /* 18 days: 13 to 30 Mar */
            text-align: center;
        }

        .timeline-day-header {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #495057;
            padding: 5px 0;
            background-color: #f1f5f7;
            border: 1px solid #fff;
        }

        .timeline-date-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid #fff;
            padding: 10px 0;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            height: 60px;
        }

        /* Date Colors based on mockup */
        .date-bg-gray {
            background-color: #fff;
            border: 1px solid #e9ecef;
            color: #495057;
        }

        .date-bg-red {
            background-color: #f46a6a;
        }

        .date-bg-yellow {
            background-color: #f1b44c;
        }

        .date-bg-green {
            background-color: #34c38f;
        }

        .timeline-h-label {
            font-size: 10px;
            color: #74788d;
            background-color: #e9ecef;
            padding: 4px 0;
            border: 1px solid #fff;
        }

        /* Event Bars Area */
        .event-bars-container {
            display: grid;
            grid-template-columns: repeat(18, 1fr);
            position: relative;
            gap: 2px;
            margin-top: 10px;
        }

        .event-bar {
            border-radius: 4px;
            padding: 4px 0;
            font-size: 9px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
        }

        /* Event Colors */
        .event-wfa {
            background-color: #fef0d7;
            color: #c0852e;
            border-top: 2px solid #f1b44c;
        }

        .event-nyepi-cuti {
            background-color: #f1b44c;
            color: #fff;
        }

        .event-nyepi-hari {
            background-color: #34c38f;
            color: #fff;
        }

        .event-lebaran-cuti {
            background-color: #f1b44c;
            color: #fff;
            border-top: 2px solid #34c38f;
        }

        .event-lebaran-hari {
            background-color: #34c38f;
            color: #fff;
            border-top: 2px solid #34c38f;
        }

        .event-sekolah {
            background-color: #e2e3e5;
            color: #383d41;
            border-left: 2px solid #6c757d;
            border-right: 2px solid #6c757d;
            border-radius: 0;
        }

        .event-posko {
            background-color: #2ab57d;
            /* BKT Blue/Teal */
            color: #ffffff;
            background-color: #3b82f6;
            /* Blueish */
            border-radius: 4px;
        }

        .timeline-grid-lines {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            display: grid;
            grid-template-columns: repeat(18, 1fr);
            pointer-events: none;
        }

        .timeline-grid-lines>div {
            border-right: 1px dashed #eff2f7;
        }

        .timeline-grid-lines>div:last-child {
            border-right: none;
        }

        /* Grid Spans for Events */
        .col-span-1 {
            grid-column: span 1;
        }

        .col-span-2 {
            grid-column: span 2;
        }

        .col-span-3 {
            grid-column: span 3;
        }

        .col-span-4 {
            grid-column: span 4;
        }

        .col-span-5 {
            grid-column: span 5;
        }

        .col-span-18 {
            grid-column: span 18;
        }

        .start-col-4 {
            grid-column-start: 4;
        }

        .start-col-6 {
            grid-column-start: 6;
        }

        .start-col-7 {
            grid-column-start: 7;
        }

        .start-col-8 {
            grid-column-start: 8;
        }

        .start-col-13 {
            grid-column-start: 13;
        }
    </style>
@endpush

@section('content')

@endsection

@push('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartOpsel = @json($chart_opsel);
            const chartModa = @json($chart_moda);

            // Chart 1: Opsel (Column: Real vs Forecast)
            Highcharts.chart('chart-opsel', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: ''
                },
                xAxis: {
                    categories: chartOpsel.categories,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Jumlah Pergerakan'
                    },
                    labels: {
                        formatter: function() {
                            return this.value / 1000 + 'rb';
                        }
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y:,.0f}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.1,
                        borderWidth: 0,
                        borderRadius: 5
                    }
                },
                series: chartOpsel.series,
                credits: {
                    enabled: false
                }
            });

            // Chart 2: Moda (Line: Daily Trend)
            Highcharts.chart('chart-moda', {
                chart: {
                    type: 'spline' // Smoother line
                },
                title: {
                    text: ''
                },
                yAxis: {
                    title: {
                        text: 'Jumlah Pergerakan'
                    },
                    labels: {
                        formatter: function() {
                            return this.value / 1000 + 'rb';
                        }
                    }
                },
                xAxis: {
                    categories: chartModa.categories,
                    accessibility: {
                        rangeDescription: 'Range: 13 Mar to 29 Mar'
                    }
                },
                legend: {
                    layout: 'horizontal',
                    align: 'center',
                    verticalAlign: 'bottom'
                },
                plotOptions: {
                    series: {
                        label: {
                            connectorAllowed: false
                        },
                        marker: {
                            enabled: true,
                            radius: 4
                        }
                    }
                },
                tooltip: {
                    shared: true,
                    valueValues: true,
                    valueDecimals: 0,
                    valueSuffix: ' pergerakan'
                },
                series: chartModa.series,
                credits: {
                    enabled: false
                }
            });
        });
    </script>
@endpush
