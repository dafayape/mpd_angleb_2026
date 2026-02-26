@extends('layout.app')

@section('title', 'Executive Summary')

@push('css')
    <style>
        .page-header-container {
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #1a3353;
        }

        .bg-navy {
            background-color: #1e2d4a !important;
            color: white;
        }

        .bg-amber {
            background-color: #f59e0b !important;
            color: white;
        }

        .bg-light-blue {
            background-color: #e2e8f0;
        }

        /* Matches reference image light blue sections */
        .text-navy {
            color: #1e2d4a !important;
        }

        .card-number-badge {
            font-weight: bold;
            font-size: 1.2rem;
            color: #1e2d4a;
            border-right: 2px solid #cbd5e1;
            padding-right: 12px;
            margin-right: 12px;
        }

        .section-box {
            background-color: #f1f5f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: inset 0 0 0 1px #e2e8f0;
        }

        .kpi-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .kpi-title {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
            opacity: 0.9;
            text-align: center;
        }

        .kpi-value {
            font-size: 2.2rem;
            font-weight: bold;
            text-align: center;
            margin: 0;
        }

        .kpi-subtitle {
            font-size: 0.75rem;
            text-align: center;
            opacity: 0.8;
        }

        .kpi-value-box {
            background-color: #ffe4c4;
            color: #000;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #fcd34d;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }

        .sticky-filter {
            position: sticky;
            top: 70px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .narrative-box {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #3b82f6;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #334155;
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
    </style>
@endpush

@section('content')

    <!-- Layout Container -->
    <div class="container-fluid py-4">

        <!-- Sticky Filter Bar -->
        <div class="sticky-filter d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex flex-column">
                    <label class="small fw-bold text-muted mb-1">Tipe Data</label>
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check filter-toggle" name="data_type" id="dt_real" value="real"
                            autocomplete="off" checked>
                        <label class="btn btn-outline-primary btn-sm" for="dt_real">Realisasi</label>
                        <input type="radio" class="btn-check filter-toggle" name="data_type" id="dt_fore"
                            value="forecast" autocomplete="off">
                        <label class="btn btn-outline-primary btn-sm" for="dt_fore">Prakiraan</label>
                    </div>
                </div>
                <div class="d-flex flex-column ms-3">
                    <label class="small fw-bold text-muted mb-1">Operator</label>
                    <select class="form-select form-select-sm" id="opselSelect" style="width: auto; min-width: 150px;">
                        <option value="">Semua (Agregat)</option>
                        <option value="TSEL">Telkomsel</option>
                        <option value="IOH">Indosat</option>
                        <option value="XL">XL Axiata</option>
                    </select>
                </div>
            </div>
            <div class="d-flex align-items-center bg-light px-3 py-2 rounded border">
                <i class="bx bx-calendar fs-4 me-2 text-primary"></i>
                <div class="fw-bold text-navy">Periode: 13 Maret 2026 – 30 Maret 2026</div>
            </div>
        </div>

        <div id="contentSkeletons">
            @for ($i = 0; $i < 4; $i++)
                <div class="section-box mb-4">
                    <div class="skeleton-text w-25 mb-3"></div>
                    <div class="row">
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
            @endfor
        </div>

        <div id="contentData" style="display: none;">
            <!-- BLOCK 1: Latar Belakang -->
            <div class="section-box d-flex align-items-start">
                <span class="card-number-badge">01</span>
                <div>
                    <h5 class="fw-bold text-navy mb-3">Latar Belakang</h5>
                    <div class="narrative-box border-0 shadow-sm">
                        Pemantauan pergerakan masyarakat pada periode Lebaran (Angleb) 2026 dilakukan melalui pemanfaatan
                        Mobile Positioning Data (MPD) yang diperoleh dari tiga operator seluler: Telkomsel, Indosat Ooredoo
                        Hutchison (IOH), dan XL Axiata/Smartfren. Analisis ini mendukung pengambilan kebijakan berbasis data
                        oleh Kementerian Perhubungan dalam rangka kesiapan infrastruktur dan transportasi.
                    </div>
                </div>
            </div>

            <!-- BLOCK 2: Definisi Metrik -->
            <div class="section-box d-flex align-items-start">
                <span class="card-number-badge">02</span>
                <div class="w-100">
                    <h5 class="fw-bold text-navy mb-3">Definisi Metrik</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100 kpi-card">
                                <div class="card-body">
                                    <h6 class="fw-bold text-navy"><i class="bx bx-run me-2"></i>Jumlah Pergerakan (Movement
                                        count)</h6>
                                    <p class="small text-muted mb-0">Total frekuensi perjalanan yang terjadi per hari. Satu
                                        orang dapat dihitung lebih dari satu kali jika melakukan lebih dari satu perjalanan.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 kpi-card">
                                <div class="card-body">
                                    <h6 class="fw-bold text-navy"><i class="bx bx-user me-2"></i>Jumlah Unique Subscriber
                                    </h6>
                                    <p class="small text-muted mb-0">Jumlah individu unik yang melakukan perjalanan dihitung
                                        satu kali meskipun melakukan perjalanan berkali-kali sepanjang periode.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOCK 3 & 4: Nasional & Puncak -->
            <div class="section-box">
                <div class="d-flex align-items-start mb-4">
                    <span class="card-number-badge">03</span>
                    <h5 class="fw-bold text-navy m-0 mt-1">Hasil Pengolahan Data Nasional</h5>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card kpi-card bg-navy">
                            <div class="card-body p-3">
                                <div class="kpi-title">Jumlah Pergerakan</div>
                                <div class="kpi-value-box fs-4 font-monospace" id="val_nas_pergerakan">-</div>
                                <div class="kpi-subtitle mt-2">Pergerakan</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card kpi-card bg-amber">
                            <div class="card-body p-3">
                                <div class="kpi-title">Rata-Rata Koefisien</div>
                                <div class="kpi-value-box text-white bg-transparent border-white fs-3 font-monospace"
                                    id="val_nas_koefisien">-</div>
                                <div class="kpi-subtitle mt-2 text-white">Perjalanan per orang</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card kpi-card bg-navy">
                            <div class="card-body p-3">
                                <div class="kpi-title">Orang Melakukan Perjalanan</div>
                                <div class="d-flex gap-2 align-items-center mt-2">
                                    <div class="bg-light text-navy fw-bold px-2 py-1 rounded small text-center"
                                        style="width: 40%; font-size: 0.65rem;">Jumlah Unik<br />Subscriber</div>
                                    <div class="kpi-value-box flex-grow-1 m-0 fs-4 font-monospace" id="val_nas_orang">-
                                    </div>
                                </div>
                                <div class="kpi-subtitle mt-2">Masyarakat</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 row">
                    <div class="col-md-12">
                        <div class="narrative-box" id="nar_nas_pergerakan">Memuat...</div>
                    </div>
                </div>

                <!-- Block 4 Chart -->
                <div class="d-flex align-items-start mt-4 mb-3">
                    <span class="card-number-badge">04</span>
                    <h5 class="fw-bold text-navy m-0 mt-1">Puncak Pergerakan (Daily Trend)</h5>
                </div>
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body p-0">
                        <div id="chart_nas_trend" style="height: 350px;"></div>
                    </div>
                </div>
                <div class="row" id="peak_cards_container">
                    <!-- populated via js -->
                </div>
            </div>

            <!-- BLOCK 5 & 6: Unique Subscriber & Kontribusi Opsel -->
            <div class="row g-4 mb-4">
                <div class="col-xl-6">
                    <div class="section-box h-100 mb-0">
                        <div class="d-flex align-items-start mb-3">
                            <span class="card-number-badge">05</span>
                            <h5 class="fw-bold text-navy m-0 mt-1">Unique Subscriber Nasional</h5>
                        </div>
                        <div id="chart_nas_orang_trend" style="height: 250px;"></div>
                        <div class="narrative-box mt-3" id="nar_kstmp">Memuat...</div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="section-box h-100 mb-0">
                        <div class="d-flex align-items-start mb-3">
                            <span class="card-number-badge">06</span>
                            <h5 class="fw-bold text-navy m-0 mt-1">Kontribusi per Opsel</h5>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div id="chart_opsel_pergerakan" style="height: 220px;"></div>
                            </div>
                            <div class="col-6">
                                <div id="chart_opsel_orang" style="height: 220px;"></div>
                            </div>
                        </div>
                        <div class="narrative-box mt-3" id="nar_nas_opsel">Memuat...</div>
                    </div>
                </div>
            </div>

            <!-- BLOCK 8 & 9: Real vs Forecast & YoY -->
            <div class="row g-4 mb-4">
                <div class="col-xl-8">
                    <div class="section-box h-100 mb-0">
                        <div class="d-flex align-items-start mb-3">
                            <span class="card-number-badge">08</span>
                            <h5 class="fw-bold text-navy m-0 mt-1">Perbandingan Survei vs Realisasi</h5>
                        </div>
                        <div id="chart_forecast_comparison" style="height: 300px;"></div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="section-box h-100 mb-0">
                        <div class="d-flex align-items-start mb-3">
                            <span class="card-number-badge">09</span>
                            <h5 class="fw-bold text-navy m-0 mt-1">Perbandingan Tahun Sebelumnya</h5>
                        </div>
                        <div id="chart_yoy" style="height: 220px;"></div>
                        <div class="narrative-box mt-3 fs-6" id="nar_yoy">Memuat...</div>
                    </div>
                </div>
            </div>

            <!-- BLOCK 10 & 11: Jabodetabek -->
            <div class="section-box border border-info" style="background-color: #f8fafc;">
                <div class="d-flex align-items-start mb-4">
                    <div class="card-number-badge" style="border-right-color: #bae6fd;">10</div>
                    <h5 class="fw-bold text-navy m-0 mt-1">Hasil Pengolahan Intra dan Inter Jabodetabek</h5>
                </div>

                <div class="row g-4">
                    <!-- INTRA -->
                    <div class="col-xl-6 border-end">
                        <h6 class="text-center fw-bold text-navy mb-3">INTRA JABODETABEK</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-4">
                                <div class="card kpi-card bg-navy h-100">
                                    <div class="card-body p-2 text-center">
                                        <div class="kpi-title" style="font-size: 0.7rem;">Jumlah Pergerakan</div>
                                        <div class="kpi-value-box p-1 fs-6" id="val_intra_pergerakan">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card kpi-card bg-amber h-100">
                                    <div class="card-body p-2 text-center">
                                        <div class="kpi-title text-white" style="font-size: 0.7rem;">Rata-rata Koef.</div>
                                        <div class="kpi-value-box text-white bg-transparent border-white p-1 fs-5"
                                            id="val_intra_koef">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card kpi-card bg-navy h-100">
                                    <div class="card-body p-2 text-center">
                                        <div class="kpi-title" style="font-size: 0.7rem;">Jumlah Orang</div>
                                        <div class="kpi-value-box p-1 fs-6" id="val_intra_orang">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="narrative-box" id="nar_intra">Memuat...</div>
                        <div class="d-flex align-items-start mt-4 mb-2">
                            <span class="card-number-badge" style="font-size: 0.9rem;">11</span>
                            <span class="fw-bold text-navy">Trend Ordal Intra</span>
                        </div>
                        <div id="chart_intra_trend" style="height: 200px;"></div>
                    </div>

                    <!-- INTER -->
                    <div class="col-xl-6">
                        <h6 class="text-center fw-bold text-navy mb-3">INTER JABODETABEK</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-4">
                                <div class="card kpi-card bg-navy h-100">
                                    <div class="card-body p-2 text-center">
                                        <div class="kpi-title" style="font-size: 0.7rem;">Jumlah Pergerakan</div>
                                        <div class="kpi-value-box p-1 fs-6" id="val_inter_pergerakan">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card kpi-card bg-amber h-100">
                                    <div class="card-body p-2 text-center">
                                        <div class="kpi-title text-white" style="font-size: 0.7rem;">Rata-rata Koef.</div>
                                        <div class="kpi-value-box text-white bg-transparent border-white p-1 fs-5"
                                            id="val_inter_koef">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card kpi-card bg-navy h-100">
                                    <div class="card-body p-2 text-center">
                                        <div class="kpi-title" style="font-size: 0.7rem;">Jumlah Orang</div>
                                        <div class="kpi-value-box p-1 fs-6" id="val_inter_orang">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="narrative-box" id="nar_inter">Memuat...</div>
                        <div class="d-flex align-items-start mt-4 mb-2">
                            <span class="card-number-badge" style="font-size: 0.9rem;">11</span>
                            <span class="fw-bold text-navy">Trend Ordal Inter</span>
                        </div>
                        <div id="chart_inter_trend" style="height: 200px;"></div>
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
            return days[dt.getDay()] + ' ' + dt.getDate() + ' ' + months[dt.getMonth()];
        };

        $(document).ready(function() {
            $('.filter-toggle, #opselSelect').on('change', fetchSummaryData);
            fetchSummaryData(); // initial load
        });

        function fetchSummaryData() {
            $('#contentData').hide();
            $('#contentSkeletons').show();

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
                data.peak.list.forEach((p, idx) => {
                    const bg = idx === 0 ? 'bg-primary text-white' : 'bg-light';
                    peakHtml += `<div class="col-md-4">
                        <div class="card border-0 mb-2 ${bg}">
                            <div class="card-body p-3 text-center">
                                <div class="fw-bold">${formatDateShort(p.tanggal)}</div>
                                <div class="fs-5 fw-bold">${formatNumber(p.total)}</div>
                                <small>${p.pct}% dari total pergerakan</small>
                            </div>
                        </div>
                    </div>`;
                });
            }
            $('#peak_cards_container').html(peakHtml);

            // Block 4: Peak Trend Chart
            const trendPergerakan = data.trend_pergerakan || {};
            const tpCats = Object.keys(trendPergerakan).map(formatDateShort);
            const tpData = Object.values(trendPergerakan);
            Highcharts.chart('chart_nas_trend', {
                chart: {
                    type: 'areaspline'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: tpCats
                },
                yAxis: {
                    title: {
                        text: null
                    },
                    labels: {
                        format: '{value:,.0f}'
                    }
                },
                plotOptions: {
                    areaspline: {
                        fillOpacity: 0.1,
                        color: '#f59e0b',
                        lineWidth: 3
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
                }
            });

            // Block 5: Orang Trend 
            const trendOrang = data.trend_orang || {};
            Highcharts.chart('chart_nas_orang_trend', {
                chart: {
                    type: 'spline'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: Object.keys(trendOrang).map(formatDateShort)
                },
                yAxis: {
                    title: {
                        text: null
                    }
                },
                plotOptions: {
                    spline: {
                        color: '#1e2d4a',
                        lineWidth: 3
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
                }
            });

            // Block 6: Opsel Donut
            const opselRaw = data.opsel;
            $('#nar_nas_opsel').html(opselRaw.narrative);

            const pData = Object.keys(opselRaw.pergerakan || {}).map(k => ({
                name: k,
                y: opselRaw.pergerakan[k].pct
            }));
            Highcharts.chart('chart_opsel_pergerakan', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Pergerakan'
                },
                plotOptions: {
                    pie: {
                        innerSize: '60%',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.y}%'
                        }
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
                    type: 'pie'
                },
                title: {
                    text: 'Orang'
                },
                plotOptions: {
                    pie: {
                        innerSize: '60%',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.y}%'
                        }
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
                    type: 'line'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: fcCats
                },
                yAxis: {
                    title: {
                        text: 'Persen (%)'
                    },
                    labels: {
                        format: '{value}%'
                    }
                },
                plotOptions: {
                    line: {
                        lineWidth: 3,
                        marker: {
                            enabled: true
                        }
                    }
                },
                series: [{
                        name: 'Hasil Survei Prakiraan',
                        data: fcFore,
                        color: '#f59e0b'
                    },
                    {
                        name: 'Realisasi MPD',
                        data: fcReal,
                        color: '#1e2d4a'
                    }
                ],
                credits: {
                    enabled: false
                }
            });

            // Block 9: YoY
            const yoy = data.yoy || {};
            $('#nar_yoy').html(yoy.narrative);
            Highcharts.chart('chart_yoy', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: ['MPD 2025 (Prakiraan Baseline)', 'MPD Angleb 2026']
                },
                yAxis: {
                    title: {
                        text: null
                    }
                },
                plotOptions: {
                    column: {
                        dataLabels: {
                            enabled: true,
                            formatter: function() {
                                return formatNumber(this.y);
                            }
                        },
                        colorByPoint: true,
                        colors: ['#5c6e8e', '#1e2d4a']
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
                    type: 'spline'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: Object.keys(ti).map(formatDateShort)
                },
                yAxis: {
                    title: {
                        text: null
                    }
                },
                series: [{
                    name: 'Intra',
                    data: Object.values(ti),
                    color: '#3b82f6'
                }],
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
                    type: 'spline'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: Object.keys(te).map(formatDateShort)
                },
                yAxis: {
                    title: {
                        text: null
                    }
                },
                series: [{
                    name: 'Inter',
                    data: Object.values(te),
                    color: '#3b82f6'
                }],
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
