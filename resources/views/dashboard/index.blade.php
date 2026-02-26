@extends('layout.app')

@section('title', 'Executive Summary')

@push('css')
    <style>
        .bg-navy {
            background-color: #1e2d4a !important;
            color: white !important;
        }

        .bg-amber {
            background-color: #f59e0b !important;
            color: white !important;
        }

        .text-navy {
            color: #1e2d4a !important;
        }

        .kpi-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
        }

        .kpi-title {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            opacity: 0.9;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .kpi-value-box {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .kpi-value-box-amber {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .kpi-subtitle {
            font-size: 0.75rem;
            text-align: center;
            opacity: 0.85;
            margin: 0;
        }

        .sticky-filter {
            position: sticky;
            top: 70px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
        }

        .narrative-box {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
            border-left: 4px solid #3b82f6;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #334155;
        }

        .section-badge {
            background-color: #1e2d4a;
            color: white;
            border-radius: 4px;
            padding: 4px 10px;
            font-size: 1rem;
            font-weight: bold;
            margin-right: 12px;
        }

        .skeleton-block {
            height: 200px;
            background-color: #e2e8f0;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            border-radius: 8px;
        }

        .skeleton-text {
            height: 20px;
            background-color: #e2e8f0;
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .5;
            }
        }

        .card-header.bg-transparent {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-top: 1.25rem;
            padding-bottom: 1.25rem;
        }
    </style>
@endpush

@section('content')

    @component('layout.partials.page-header', ['number' => '02', 'title' => 'Executive Summary Nasional'])
    @endcomponent

    <!-- Sticky Filter Bar -->
    <div class="sticky-filter d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div class="d-flex flex-column">
                <label class="small fw-bold text-muted mb-2 text-uppercase" style="letter-spacing: 0.5px;">Tipe Data</label>
                <div class="btn-group shadow-sm" role="group">
                    <input type="radio" class="btn-check filter-toggle" name="data_type" id="dt_real" value="real"
                        autocomplete="off" checked>
                    <label class="btn btn-outline-primary btn-sm px-4 py-2" for="dt_real">Realisasi</label>
                    <input type="radio" class="btn-check filter-toggle" name="data_type" id="dt_fore" value="forecast"
                        autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm px-4 py-2" for="dt_fore">Prakiraan</label>
                </div>
            </div>
            <div class="d-flex flex-column">
                <label class="small fw-bold text-muted mb-2 text-uppercase" style="letter-spacing: 0.5px;">Operator
                    Seluler</label>
                <select class="form-select form-select-sm shadow-sm py-2" id="opselSelect"
                    style="width: auto; min-width: 180px;">
                    <option value="">Semua (Agregat)</option>
                    <option value="TSEL">Telkomsel</option>
                    <option value="IOH">Indosat Ooredoo</option>
                    <option value="XL">XL Axiata</option>
                </select>
            </div>
        </div>
        <div class="d-flex align-items-center bg-light px-4 py-2 rounded-pill border shadow-sm">
            <i class="bx bx-calendar fs-4 me-2 text-primary"></i>
            <div class="fw-bold text-navy" style="font-size: 0.95rem;">Periode: 13 Maret 2026 – 30 Maret 2026</div>
        </div>
    </div>

    <div id="contentSkeletons">
        @for ($i = 0; $i < 3; $i++)
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body p-4">
                    <div class="skeleton-text w-25 mb-4" style="height: 30px;"></div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="skeleton-block"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="skeleton-block"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="skeleton-block"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <div id="contentData" style="display: none;">

        <!-- ROW 1: Latar Belakang & Definisi -->
        <div class="row g-4 mb-4">
            <!-- BLOCK 1: Latar Belakang -->
            <div class="col-xl-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <span class="section-badge">01</span>
                        <h5 class="fw-bold text-navy mb-0">Latar Belakang</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" style="line-height: 1.7; text-align: justify;">
                            Pemantauan pergerakan masyarakat pada periode Lebaran (Angleb) 2026 dilakukan melalui
                            pemanfaatan Mobile Positioning Data (MPD) yang diperoleh dari tiga operator seluler: Telkomsel,
                            Indosat Ooredoo Hutchison (IOH), dan XL Axiata/Smartfren.<br /><br />
                            Analisis ini mendukung pengambilan kebijakan berbasis data oleh Kementerian Perhubungan dalam
                            rangka kesiapan infrastruktur, logistik, dan transportasi nasional secara aktual.
                        </p>
                    </div>
                </div>
            </div>

            <!-- BLOCK 2: Definisi Metrik -->
            <div class="col-xl-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <span class="section-badge">02</span>
                        <h5 class="fw-bold text-navy mb-0">Definisi Metrik Utama</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 h-100">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded h-100 border">
                                    <h6 class="fw-bold text-navy mb-2"><i class="bx bx-run text-primary me-2"></i>Jumlah
                                        Pergerakan <br /><small class="text-muted">(Movement count)</small></h6>
                                    <p class="small text-muted mb-0" style="line-height: 1.5; text-align: justify;">Total
                                        frekuensi perjalanan yang terjadi per hari selama periode pengamatan. Satu orang
                                        dapat dihitung <b>lebih dari satu kali</b> apabila melakukan lebih dari satu
                                        aktivitas mobilitas.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded h-100 border">
                                    <h6 class="fw-bold text-navy mb-2"><i class="bx bx-user text-primary me-2"></i>Jumlah
                                        Unique Subscriber <br /><small class="text-muted">(Unique Travellers)</small></h6>
                                    <p class="small text-muted mb-0" style="line-height: 1.5; text-align: justify;">Jumlah
                                        individu unik yang melakukan perjalanan. Seseorang hanya dihitung <b>satu kali</b>
                                        meskipun melakukan perjalanan berkali-kali sepanjang periode pengamatan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BLOCK 3: Nasional Data -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent d-flex align-items-center">
                <span class="section-badge">03</span>
                <h5 class="fw-bold text-navy mb-0 text-uppercase">Hasil Pengolahan Data Nasional</h5>
            </div>
            <div class="card-body p-4 bg-light">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card kpi-card bg-navy h-100">
                            <div class="card-body p-4 d-flex flex-column justify-content-center">
                                <div class="kpi-title">Jumlah Pergerakan</div>
                                <div class="kpi-value-box fs-3 font-monospace" id="val_nas_pergerakan">-</div>
                                <div class="kpi-subtitle">Pergerakan Nasional</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card kpi-card bg-amber h-100">
                            <div class="card-body p-4 d-flex flex-column justify-content-center">
                                <div class="kpi-title">Rata-Rata Koefisien</div>
                                <div class="kpi-value-box-amber fs-2 font-monospace" id="val_nas_koefisien">-</div>
                                <div class="kpi-subtitle">Perjalanan per Individu</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card kpi-card bg-navy h-100">
                            <div class="card-body p-4 d-flex flex-column justify-content-center">
                                <div class="kpi-title">Orang Melakukan Perjalanan</div>
                                <div class="kpi-value-box fs-3 font-monospace" id="val_nas_orang">-</div>
                                <div class="kpi-subtitle">Jumlah Unik Subscriber</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="narrative-box shadow-sm border-0" id="nar_nas_pergerakan">Memuat narasi...</div>
            </div>
        </div>

        <!-- BLOCK 4: Peak Trend -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent d-flex align-items-center">
                <span class="section-badge">04</span>
                <h5 class="fw-bold text-navy mb-0">Puncak Pergerakan Harian (Daily Trend)</h5>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4" id="peak_cards_container">
                    <!-- populated via js -->
                </div>
                <div id="chart_nas_trend" style="height: 380px;"></div>
            </div>
        </div>

        <!-- ROW 3: Subscriber Trend & Opsel Share -->
        <div class="row g-4 mb-4">
            <!-- BLOCK 5: Unique Subscriber -->
            <div class="col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <span class="section-badge">05</span>
                        <h5 class="fw-bold text-navy mb-0">Tren Unique Subscriber Nasional</h5>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div id="chart_nas_orang_trend" style="height: 280px;" class="mb-3"></div>
                        <div class="narrative-box shadow-sm border-0 mt-auto" id="nar_kstmp">Memuat narasi...</div>
                    </div>
                </div>
            </div>

            <!-- BLOCK 6: Kontribusi Opsel -->
            <div class="col-xl-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <span class="section-badge">06</span>
                        <h5 class="fw-bold text-navy mb-0">Kontribusi per Operator Seluler</h5>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="row mb-3 flex-grow-1">
                            <div class="col-6">
                                <div class="bg-light rounded p-2 h-100">
                                    <div id="chart_opsel_pergerakan" style="height: 240px;"></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light rounded p-2 h-100">
                                    <div id="chart_opsel_orang" style="height: 240px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="narrative-box shadow-sm border-0" id="nar_nas_opsel">Memuat narasi...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 4: Forecast vs YoY -->
        <div class="row g-4 mb-4">
            <!-- BLOCK 8: Real vs Forecast -->
            <div class="col-xl-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <span class="section-badge">08</span>
                        <h5 class="fw-bold text-navy mb-0">Perbandingan Survei (Prakiraan) vs MPD (Realisasi)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div id="chart_forecast_comparison" style="height: 320px;"></div>
                        <p class="small text-muted mt-3 fst-italic text-center">Persentase dihitung berdasarkan proporsi
                            harian terhadap total pergerakan selama periode pengamatan.</p>
                    </div>
                </div>
            </div>

            <!-- BLOCK 9: YoY -->
            <div class="col-xl-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-transparent d-flex align-items-center">
                        <span class="section-badge">09</span>
                        <h5 class="fw-bold text-navy mb-0">Perbandingan YoY Baseline (Unique Subscriber)</h5>
                    </div>
                    <div class="card-body p-4 flex-column d-flex">
                        <div id="chart_yoy" style="height: 260px;" class="mb-3"></div>
                        <div class="narrative-box shadow-sm border-0 mt-auto" id="nar_yoy">Memuat narasi...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 5: Jabodetabek (Blocks 10 & 11) -->
        <div class="card shadow-sm border-0 mb-4" style="background-color: #f1f5f9;">
            <div class="card-header bg-white d-flex align-items-center py-3">
                <span class="section-badge bg-primary">10</span>
                <span class="section-badge bg-info text-dark">11</span>
                <h5 class="fw-bold text-navy mb-0 text-uppercase">Hasil Pengolahan Intra & Inter Jabodetabek</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-5">

                    <!-- LEFT COLUMN: INTRA JABODETABEK -->
                    <div class="col-xl-6 position-relative">
                        <div class="d-none d-xl-block"
                            style="position: absolute; right: 0; top: 0; bottom: 0; width: 1px; background-color: #cbd5e1;">
                        </div>

                        <div class="text-center mb-4">
                            <span class="badge bg-navy px-4 py-2 fs-6 rounded-pill shadow-sm">INTRA JABODETABEK</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card kpi-card bg-navy h-100">
                                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                                        <div class="kpi-title" style="font-size: 0.70rem;">Total Pergerakan</div>
                                        <div class="kpi-value-box p-2 fs-5 font-monospace" id="val_intra_pergerakan">-
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card kpi-card bg-amber h-100">
                                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                                        <div class="kpi-title" style="font-size: 0.70rem;">Rata-rata Koef.</div>
                                        <div class="kpi-value-box-amber p-2 fs-4 font-monospace" id="val_intra_koef">-
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card kpi-card bg-navy h-100">
                                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                                        <div class="kpi-title" style="font-size: 0.70rem;">Unique Subscriber</div>
                                        <div class="kpi-value-box p-2 fs-5 font-monospace" id="val_intra_orang">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="narrative-box shadow-sm border-0 mb-4" id="nar_intra"
                            style="background-color: white;">Memuat narasi...</div>

                        <h6 class="fw-bold text-navy mb-3"><i class="bx bx-trending-up me-2 text-primary"></i>Tren Unique
                            Subscriber Intra</h6>
                        <div class="bg-white p-3 rounded shadow-sm">
                            <div id="chart_intra_trend" style="height: 250px;"></div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: INTER JABODETABEK -->
                    <div class="col-xl-6">
                        <div class="text-center mb-4">
                            <span class="badge bg-navy px-4 py-2 fs-6 rounded-pill shadow-sm">INTER JABODETABEK</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card kpi-card bg-navy h-100">
                                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                                        <div class="kpi-title" style="font-size: 0.70rem;">Total Pergerakan</div>
                                        <div class="kpi-value-box p-2 fs-5 font-monospace" id="val_inter_pergerakan">-
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card kpi-card bg-amber h-100">
                                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                                        <div class="kpi-title" style="font-size: 0.70rem;">Rata-rata Koef.</div>
                                        <div class="kpi-value-box-amber p-2 fs-4 font-monospace" id="val_inter_koef">-
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card kpi-card bg-navy h-100">
                                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                                        <div class="kpi-title" style="font-size: 0.70rem;">Unique Subscriber</div>
                                        <div class="kpi-value-box p-2 fs-5 font-monospace" id="val_inter_orang">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="narrative-box shadow-sm border-0 mb-4" id="nar_inter"
                            style="background-color: white;">Memuat narasi...</div>

                        <h6 class="fw-bold text-navy mb-3"><i class="bx bx-trending-up me-2 text-primary"></i>Tren Unique
                            Subscriber Inter</h6>
                        <div class="bg-white p-3 rounded shadow-sm">
                            <div id="chart_inter_trend" style="height: 250px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script>
        const formatJuta = (num) => {
            if (num >= 1000000) return (num / 1000000).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' Juta';
            return num.toLocaleString('id-ID');
        };

        const formatNumber = (num) => num.toLocaleString('id-ID');

        const formatDateShort = (dtStr) => {
            const dt = new Date(dtStr);
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            return days[dt.getDay()] + ', ' + dt.getDate() + ' ' + months[dt.getMonth()];
        };

        $(document).ready(function() {
            $('.filter-toggle, #opselSelect').on('change', fetchSummaryData);
            fetchSummaryData(); // initial load
        });

        function fetchSummaryData() {
            $('#contentData').hide();
            $('#contentSkeletons').fadeIn(200);

            const dataType = $('input[name="data_type"]:checked').val();
            const opsel = $('#opselSelect').val();

            $.ajax({
                url: '{{ route('executive.summary.data') }}',
                data: {
                    data_type: dataType,
                    opsel: opsel
                },
                success: function(res) {
                    $('#contentSkeletons').hide();
                    $('#contentData').fadeIn(300);
                    renderAllBlocks(res);
                },
                error: function(err) {
                    console.error("Failed to load Executive Summary data.");
                    $('#contentSkeletons').hide();
                }
            });
        }

        function renderAllBlocks(data) {
            // Block 3: Nasional KPIs
            $('#val_nas_pergerakan').text(formatJuta(data.nasional.pergerakan));
            $('#val_nas_orang').text(formatJuta(data.nasional.orang));
            $('#val_nas_koefisien').text(data.nasional.koefisien.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            }));
            $('#nar_nas_pergerakan').html(data.nasional.narrative);
            $('#nar_kstmp').html(data.kstmp);

            // Block 4: Peak Cards
            let peakHtml = '';
            if (data.peak.list) {
                // Ensure symmetrical sizing based on the number of items
                const gridClass = data.peak.list.length === 3 ? 'col-md-4' : (data.peak.list.length === 2 ? 'col-md-6' :
                    'col-md-12');

                data.peak.list.forEach((p, idx) => {
                    // Highlight the absolute peak distinctly
                    const bg = idx === 0 ? 'bg-primary text-white border-primary' : 'bg-light border-light';
                    const text = idx === 0 ? 'text-white' : 'text-navy';
                    const icon = idx === 0 ? '<i class="bx bxs-star me-1 text-warning"></i> ' : '';

                    peakHtml += `<div class="${gridClass}">
                        <div class="card shadow-sm h-100 border transition ${bg}">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                <div class="fw-bold mb-2 ${text} opacity-75 text-uppercase" style="font-size: 0.8rem;">${icon}${formatDateShort(p.tanggal)}</div>
                                <div class="fs-4 fw-bold ${text} font-monospace">${formatNumber(p.total)}</div>
                                <div class="small mt-2 ${text} opacity-75">${p.pct}% dari akumulasi</div>
                            </div>
                        </div>
                    </div>`;
                });
            } else {
                peakHtml =
                    '<div class="col-12"><div class="alert alert-light text-center">Data puncak belum tersedia</div></div>';
            }
            $('#peak_cards_container').html(peakHtml);

            // Block 4: Peak Trend Chart
            const trendPergerakan = data.trend_pergerakan || {};
            const tpCats = Object.keys(trendPergerakan).map(formatDateShort);
            const tpData = Object.values(trendPergerakan);
            Highcharts.chart('chart_nas_trend', {
                chart: {
                    type: 'areaspline',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: tpCats,
                    crosshair: true,
                    tickmarkPlacement: 'on'
                },
                yAxis: {
                    title: {
                        text: 'Pergerakan'
                    },
                    labels: {
                        format: '{value:,.0f}'
                    },
                    gridLineDashStyle: 'Dash'
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.15,
                        color: '#f59e0b',
                        lineWidth: 3,
                        marker: {
                            enabled: true,
                            radius: 4
                        }
                    }
                },
                series: [{
                    name: 'Total Pergerakan',
                    data: tpData
                }],
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    shared: true,
                    valueSuffix: ' Pergerakan'
                }
            });

            // Block 5: Orang Trend 
            const trendOrang = data.trend_orang || {};
            Highcharts.chart('chart_nas_orang_trend', {
                chart: {
                    type: 'spline',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: Object.keys(trendOrang).map(formatDateShort),
                    crosshair: true
                },
                yAxis: {
                    title: {
                        text: 'Orang'
                    },
                    gridLineDashStyle: 'Dash'
                },
                plotOptions: {
                    spline: {
                        color: '#1e2d4a',
                        lineWidth: 3,
                        marker: {
                            enabled: true,
                            radius: 4
                        }
                    }
                },
                series: [{
                    name: 'Unique Subscriber',
                    data: Object.values(trendOrang)
                }],
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    valueSuffix: ' Orang'
                }
            });

            // Block 6: Opsel Donut
            const opselRaw = data.opsel;
            $('#nar_nas_opsel').html(opselRaw.narrative);

            const colors = ['#f59e0b', '#3b82f6', '#10b981']; // Color mapping for opsel if needed

            const pData = Object.keys(opselRaw.pergerakan || {}).map(k => ({
                name: k,
                y: opselRaw.pergerakan[k].pct
            }));
            Highcharts.chart('chart_opsel_pergerakan', {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: 'Pergerakan',
                    verticalAlign: 'middle',
                    y: 0,
                    style: {
                        fontSize: '12px',
                        fontWeight: 'bold'
                    }
                },
                plotOptions: {
                    pie: {
                        innerSize: '65%',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b><br>{point.y}%',
                            distance: 10,
                            style: {
                                fontSize: '10px'
                            }
                        },
                        borderWidth: 2
                    }
                },
                series: [{
                    name: 'Share Opsel',
                    data: pData
                }],
                credits: {
                    enabled: false
                }
            });

            const oData = Object.keys(opselRaw.orang || {}).map(k => ({
                name: k,
                y: opselRaw.orang[k].pct
            }));
            Highcharts.chart('chart_opsel_orang', {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: 'Orang',
                    verticalAlign: 'middle',
                    y: 0,
                    style: {
                        fontSize: '12px',
                        fontWeight: 'bold'
                    }
                },
                plotOptions: {
                    pie: {
                        innerSize: '65%',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b><br>{point.y}%',
                            distance: 10,
                            style: {
                                fontSize: '10px'
                            }
                        },
                        borderWidth: 2
                    }
                },
                series: [{
                    name: 'Share Opsel',
                    data: oData
                }],
                credits: {
                    enabled: false
                }
            });

            // Block 8: Forecast vs Real
            const fc = data.forecast || {};
            const fcCats = Object.keys(fc).map(formatDateShort);
            const fcReal = Object.values(fc).map(i => i.real_pct);
            const fcFore = Object.values(fc).map(i => i.fore_pct);
            Highcharts.chart('chart_forecast_comparison', {
                chart: {
                    type: 'line',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: fcCats,
                    crosshair: true
                },
                yAxis: {
                    title: {
                        text: 'Proporsi Harian (%)'
                    },
                    labels: {
                        format: '{value}%'
                    },
                    gridLineDashStyle: 'Dash'
                },
                plotOptions: {
                    line: {
                        lineWidth: 3,
                        marker: {
                            enabled: true,
                            radius: 5
                        }
                    }
                },
                series: [{
                        name: 'Survei Prakiraan',
                        data: fcFore,
                        color: '#f59e0b'
                    },
                    {
                        name: 'Realisasi MPD',
                        data: fcReal,
                        color: '#1e2d4a'
                    }
                ],
                legend: {
                    verticalAlign: 'top'
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    shared: true,
                    valueSuffix: '%'
                }
            });

            // Block 9: YoY
            const yoy = data.yoy || {};
            $('#nar_yoy').html(yoy.narrative);
            Highcharts.chart('chart_yoy', {
                chart: {
                    type: 'column',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: ['MPD Angleb 2025<br/><small>(Estimasi)</small>',
                        'MPD Angleb 2026<br/><small>(Aktual)</small>'
                    ]
                },
                yAxis: {
                    title: {
                        text: null
                    },
                    labels: {
                        enabled: false
                    },
                    gridLineWidth: 0
                },
                plotOptions: {
                    column: {
                        dataLabels: {
                            enabled: true,
                            formatter: function() {
                                return formatJuta(this.y);
                            },
                            style: {
                                fontSize: '12px'
                            }
                        },
                        colorByPoint: true,
                        colors: ['#cbd5e1', '#1e2d4a'],
                        borderRadius: 4,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Unique Subscriber',
                    data: [yoy.previous, yoy.current]
                }],
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    pointFormatter: function() {
                        return formatNumber(this.y) + ' Orang';
                    }
                }
            });

            // Block 10 & 11: Jabo
            $('#val_intra_pergerakan').text(formatJuta(data.intra.pergerakan));
            $('#val_intra_orang').text(formatJuta(data.intra.orang));
            $('#val_intra_koef').text(data.intra.koefisien.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            }));
            $('#nar_intra').html(data.intra.narrative);

            $('#val_inter_pergerakan').text(formatJuta(data.inter.pergerakan));
            $('#val_inter_orang').text(formatJuta(data.inter.orang));
            $('#val_inter_koef').text(data.inter.koefisien.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            }));
            $('#nar_inter').html(data.inter.narrative);

            // Jabo Trends
            const ti = data.trend_intra || {};
            Highcharts.chart('chart_intra_trend', {
                chart: {
                    type: 'spline',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: Object.keys(ti).map(formatDateShort),
                    crosshair: true,
                    tickmarkPlacement: 'on'
                },
                yAxis: {
                    title: {
                        text: null
                    },
                    gridLineDashStyle: 'Dash'
                },
                series: [{
                    name: 'Intra Jabodetabek',
                    data: Object.values(ti),
                    color: '#f59e0b',
                    lineWidth: 3
                }],
                plotOptions: {
                    spline: {
                        marker: {
                            radius: 3
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                }
            });
            const te = data.trend_inter || {};
            Highcharts.chart('chart_inter_trend', {
                chart: {
                    type: 'spline',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: Object.keys(te).map(formatDateShort),
                    crosshair: true,
                    tickmarkPlacement: 'on'
                },
                yAxis: {
                    title: {
                        text: null
                    },
                    gridLineDashStyle: 'Dash'
                },
                series: [{
                    name: 'Inter Jabodetabek',
                    data: Object.values(te),
                    color: '#1e2d4a',
                    lineWidth: 3
                }],
                plotOptions: {
                    spline: {
                        marker: {
                            radius: 3
                        }
                    }
                },
                legend: {
                    enabled: false
                },
                credits: {
                    enabled: false
                }
            });
        }
    </script>
@endpush
