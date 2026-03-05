@extends('layout.app')

@section('title', 'Mode Share Nasional')

@section('content')
    @component('layout.partials.page-header', ['number' => '05', 'title' => 'Mode Share Nasional'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#">Nasional</a></li>
            <li class="breadcrumb-item active">Mode Share Nasional</li>
        </ol>
    @endcomponent

    @push('css')
        <style>
            .content-card {
                border-radius: 12px;
                border: 1px solid rgba(0, 0, 0, 0.125);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                background: white;
            }

            .chart-title-center {
                text-align: center;
                margin-bottom: 5px;
            }

            .chart-title-center h5 {
                font-weight: bold;
                color: #333;
                margin-bottom: 2px;
                font-size: 1.1rem;
            }

            .chart-subtitle-center {
                text-align: center;
                color: #777;
                font-size: 0.85rem;
                margin-bottom: 20px;
            }

            .custom-legend {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                margin-top: 15px;
                padding: 0 20px;
            }

            .legend-item {
                display: flex;
                flex-direction: column;
                width: 230px;
                margin-bottom: 12px;
                font-size: 0.85rem;
            }

            .legend-header {
                display: flex;
                align-items: center;
                font-weight: bold;
                color: #444;
                margin-bottom: 2px;
            }

            .legend-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                margin-right: 8px;
                display: inline-block;
            }

            .legend-value {
                padding-left: 18px;
                color: #666;
            }

            .section-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 30px;
                height: 30px;
                background-color: #007bff;
                color: white;
                border-radius: 50%;
                font-weight: bold;
                font-size: 0.9rem;
                margin-right: 1rem;
                flex-shrink: 0;
            }

            .text-navy {
                color: #2a3042 !important;
            }

            .export-dropdown {
                position: relative;
                display: inline-block;
                margin-left: auto;
            }

            .export-dropdown .export-btn {
                background-color: #2a3042;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 8px 16px;
                font-size: 0.85rem;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s ease;
            }

            .export-dropdown .export-btn:hover {
                background-color: #1e2230;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(42, 48, 66, 0.3);
            }

            .export-dropdown .export-menu {
                display: none;
                position: absolute;
                right: 0;
                top: 100%;
                margin-top: 4px;
                min-width: 180px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                overflow: hidden;
                border: 1px solid #e2e8f0;
            }

            .export-dropdown .export-menu.show {
                display: block;
            }

            .export-dropdown .export-menu-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 16px;
                font-size: 0.85rem;
                color: #334155;
                cursor: pointer;
                transition: background 0.15s ease;
                border: none;
                background: none;
                width: 100%;
                text-align: left;
            }

            .export-dropdown .export-menu-item:hover {
                background-color: #f1f5f9;
                color: #2a3042;
            }

            .export-dropdown .export-menu-item i {
                font-size: 1.1rem;
                width: 20px;
                text-align: center;
            }
        </style>
    @endpush

    <div class="row mb-4" data-aos="fade-up" data-aos-duration="600">
        <div class="col-12">
            <div class="card content-card w-100 flex-column border-0" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); border-radius: 12px 12px 0 0;">
                    <span class="section-badge">01</span>
                    <h5 class="fw-bold text-navy mb-0">Mode share (pemilihan moda transportasi) berdasarkan jumlah
                        pergerakan dan jumlah orang</h5>
                    <div class="export-dropdown ms-auto">
                        <button class="export-btn" onclick="toggleExportMenu('export-menu-ms01')">
                            <i class="bx bx-download"></i> Export
                        </button>
                        <div class="export-menu" id="export-menu-ms01">
                            <button class="export-menu-item"
                                onclick="exportSectionPNG('section-ms01-content','Mode_Share_Distribusi','export-menu-ms01')">
                                <i class="bx bx-image text-primary"></i> PNG
                            </button>
                            <button class="export-menu-item" onclick="exportMS01CSV()">
                                <i class="bx bx-spreadsheet text-success"></i> CSV (XLSX)
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light" id="section-ms01-content"
                    style="padding: 1.5rem; border-radius: 0 0 12px 12px;">
                    <div class="row g-3">
                        <!-- 1. Distribusi Angkutan (Pergerakan) -->
                        <div class="col-lg-6 d-flex">
                            <div class="card content-card w-100 h-100 p-4 border-0 shadow-sm">
                                <div class="chart-title-center mb-3">
                                    <h5>Distribusi Angkutan (Pergerakan - Real)</h5>
                                </div>
                                <div id="chart-pergerakan" style="height: 350px;"></div>
                                <div class="custom-legend mt-4" id="legend-pergerakan">
                                    <!-- Legend generated by JS -->
                                </div>
                            </div>
                        </div>

                        <!-- 2. Distribusi Angkutan (Orang) -->
                        <div class="col-lg-6 d-flex">
                            <div class="card content-card w-100 h-100 p-4 border-0 shadow-sm">
                                <div class="chart-title-center mb-3">
                                    <h5>Distribusi Angkutan (Orang - Real)</h5>
                                </div>
                                <div id="chart-orang" style="height: 350px;"></div>
                                <div class="custom-legend mt-4" id="legend-orang">
                                    <!-- Legend generated by JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4" data-aos="fade-up" data-aos-duration="600">
        <div class="col-12">
            <div class="card content-card w-100 flex-column border-0" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); border-radius: 12px 12px 0 0;">
                    <span class="section-badge">02</span>
                    <h5 class="fw-bold text-navy mb-0">Pergerakan harian berdasarkan mode share (pemilihan moda
                        transportasi)</h5>
                    <div class="export-dropdown ms-auto">
                        <button class="export-btn" onclick="toggleExportMenu('export-menu-ms02')">
                            <i class="bx bx-download"></i> Export
                        </button>
                        <div class="export-menu" id="export-menu-ms02">
                            <button class="export-menu-item"
                                onclick="exportSectionPNG('section-ms02-content','Mode_Share_Harian','export-menu-ms02')">
                                <i class="bx bx-image text-primary"></i> PNG
                            </button>
                            <button class="export-menu-item" onclick="exportMS02CSV()">
                                <i class="bx bx-spreadsheet text-success"></i> CSV (XLSX)
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light" id="section-ms02-content"
                    style="padding: 1.5rem; border-radius: 0 0 12px 12px;">
                    @php
                        $chartCategories = [];
                        foreach ($dates as $d) {
                            $c = \Carbon\Carbon::parse($d)->locale('id');
                            $chartCategories[] =
                                '<div class="text-center">' .
                                $c->isoFormat('dddd, D') .
                                '<br/>' .
                                $c->isoFormat('MMMM') .
                                '<br/>' .
                                $c->isoFormat('YYYY') .
                                '</div>';
                        }
                    @endphp
                    <div class="row g-4">
                        @foreach ($dailyData as $code => $modeData)
                            <div class="col-12">
                                <div class="card w-100 p-4 shadow-sm"
                                    style="border: 2px solid #5a647d; border-radius: 25px; background: white;">
                                    <div class="d-flex justify-content-end mb-3">
                                        <div class="d-flex flex-column align-items-end">
                                            <div class="mb-2 text-center"
                                                style="background-color: #dbe4eb; border: 1px solid #999; border-radius: 4px; padding: 6px 40px; font-weight: bold; font-size: 1.1rem; color: #333; min-width: 250px;">
                                                {{ strtoupper($modeData['name']) }}
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3 text-end" style="line-height: 1.1;">
                                                    <small class="text-muted fw-bold">Total Pergerakan</small><br />
                                                    <span
                                                        class="text-dark fw-bold">{{ strtoupper($modeData['name']) }}</span>
                                                </div>
                                                <div
                                                    style="background-color: #fef0cd; padding: 6px 15px; font-weight: bold; color: #333; font-size: 1.1rem; border-radius: 4px;">
                                                    @php
                                                        $tot = $modeData['total_pergerakan'];
                                                        if ($tot >= 1000000) {
                                                            echo number_format($tot / 1000000, 2, ',', '.') . ' Juta';
                                                        } else {
                                                            echo number_format($tot, 0, ',', '.');
                                                        }
                                                    @endphp
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="chart-daily-{{ $code }}" style="height: 300px;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/export-data.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script>
            $(document).ready(function() {
                var formatNum = function(num) {
                    return num.toLocaleString('id-ID');
                };

                // The backend data
                var dataPergerakan = @json($data['pergerakan']);
                var dataOrang = @json($data['orang']);

                // Filter out zeroes if desired, though Highcharts handles them
                var validPergerakan = dataPergerakan.filter(function(d) {
                    return d.y > 0;
                });
                var validOrang = dataOrang.filter(function(d) {
                    return d.y > 0;
                });

                var commonColors = [
                    '#40B0FF', // Light Blue (Motor)
                    '#6A5ACD', // Purple (Mobil)
                    '#00C97F', // Green (Kereta Perkotaan)
                    '#FF7F50', // Orange (Udara)
                    '#8190B5', // Grey/Blue (Laut)
                    '#DA70D6', // Orchid (Bus AKDP)
                    '#48D1CC', // Turquoise (Penyeberangan)
                    '#DC143C', // Crimson (Kereta Antarkota)
                    '#F4A460', // Sandy (Bus AKAP)
                    '#AFEEEE', // Pale Turquoise (KCJB)
                    '#D3D3D3' // Light Grey (Sepeda)
                ];

                function renderChartAndLegend(containerId, legendId, seriesData) {
                    var chart = Highcharts.chart(containerId, {
                        chart: {
                            type: 'pie'
                        },
                        colors: commonColors,
                        title: {
                            text: ''
                        },
                        tooltip: {
                            formatter: function() {
                                return '<b>' + this.point.name + '</b><br/>' +
                                    formatNum(this.point.y) + ' (' + this.point.pct.toString().replace('.',
                                        ',') + '%)';
                            }
                        },
                        plotOptions: {
                            pie: {
                                innerSize: '65%',
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    useHTML: true,
                                    style: {
                                        fontWeight: 'normal',
                                        fontSize: '10px'
                                    },
                                    formatter: function() {
                                        if (this.point.pct < 1) return null;

                                        return '<div style="text-align:center;">' +
                                            '<span style="font-weight:bold; color:#333;">' + this.point
                                            .name + '</span><br/>' +
                                            '<span style="font-weight:bold; color:#000;">' + formatNum(this
                                                .point.y) + '</span><br/>' +
                                            '<span style="color:#666;">(' + this.point.pct.toString()
                                            .replace('.', ',') + '%)</span>' +
                                            '</div>';
                                    }
                                },
                                showInLegend: false
                            }
                        },
                        series: [{
                            name: 'Angkutan',
                            colorByPoint: true,
                            data: seriesData
                        }],
                        credits: {
                            enabled: false
                        }
                    });

                    // Build Custom Legend
                    var legendHtml = '';
                    var points = chart.series[0].points;
                    for (var i = 0; i < points.length; i++) {
                        var p = points[i];
                        if (p.y > 0) {
                            legendHtml += '<div class="legend-item">' +
                                '<div class="legend-header">' +
                                '<span class="legend-dot" style="background-color:' + p.color + '"></span>' +
                                '<span>' + p.name + '</span>' +
                                '</div>' +
                                '<div class="legend-value">' +
                                formatNum(p.y) + ' | ' + p.pct.toString().replace('.', ',') + '%' +
                                '</div>' +
                                '</div>';
                        }
                    }
                    $('#' + legendId).html(legendHtml);
                }

                renderChartAndLegend('chart-pergerakan', 'legend-pergerakan', validPergerakan);
                renderChartAndLegend('chart-orang', 'legend-orang', validOrang);

                // Daily Chart logic
                var dailyData = @json($dailyData);
                var chartCategories = @json($chartCategories);

                Object.keys(dailyData).forEach(function(code) {
                    var modeData = dailyData[code];
                    var seriesData = [];
                    var rawDates = Object.keys(modeData.daily).sort();
                    rawDates.forEach(function(d) {
                        seriesData.push(modeData.daily[d]);
                    });

                    Highcharts.chart('chart-daily-' + code, {
                        chart: {
                            type: 'column',
                            backgroundColor: 'transparent'
                        },
                        title: {
                            text: null
                        },
                        xAxis: {
                            categories: chartCategories,
                            labels: {
                                style: {
                                    fontSize: '10px',
                                    color: '#666'
                                },
                                useHTML: true
                            },
                            lineWidth: 1,
                            lineColor: '#ccc'
                        },
                        yAxis: {
                            title: {
                                text: null
                            },
                            labels: {
                                formatter: function() {
                                    return formatNum(this.value);
                                },
                                style: {
                                    color: '#666'
                                }
                            },
                            gridLineColor: '#eee'
                        },
                        tooltip: {
                            formatter: function() {
                                var plainCat = this.x.replace(/<[^>]*>?/gm, ' ');
                                return '<b>' + plainCat + '</b><br/>' +
                                    modeData.name + ': <b>' + formatNum(this.y) + '</b>';
                            }
                        },
                        plotOptions: {
                            column: {
                                dataLabels: {
                                    enabled: true,
                                    formatter: function() {
                                        return formatNum(this.y);
                                    },
                                    style: {
                                        fontSize: '9px',
                                        fontWeight: 'normal',
                                        color: '#333',
                                        textOutline: 'none'
                                    }
                                },
                                color: '#1a6f8b',
                                borderRadius: 0,
                                pointPadding: 0.1
                            }
                        },
                        series: [{
                            name: modeData.name,
                            data: seriesData,
                            showInLegend: false
                        }],
                        credits: {
                            enabled: false
                        }
                    });
                });

                // =========================================
                // Export Helpers
                // =========================================
                window.toggleExportMenu = function(menuId) {
                    const menu = document.getElementById(menuId);
                    document.querySelectorAll('.export-menu').forEach(m => {
                        if (m.id !== menuId) m.classList.remove('show');
                    });
                    menu.classList.toggle('show');
                };
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.export-dropdown')) {
                        document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));
                    }
                });

                // Unified PNG capture (clone-based)
                function captureAsPNG(targetEl, filename, btnMenuId) {
                    const btn = document.querySelector('#' + btnMenuId).previousElementSibling;
                    const origText = btn.innerHTML;
                    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Processing...';
                    btn.disabled = true;

                    const wrapper = document.createElement('div');
                    wrapper.style.cssText =
                        'position:fixed;left:-99999px;top:0;z-index:-1;background:#fff;padding:20px;';
                    document.body.appendChild(wrapper);

                    const clone = targetEl.cloneNode(true);
                    clone.querySelectorAll('.table-responsive').forEach(el => {
                        el.style.overflow = 'visible';
                        el.style.maxWidth = 'none';
                        el.style.width = 'auto';
                    });
                    clone.querySelectorAll('[class*="col-xl-"], [class*="col-lg-"], [class*="col-md-"]').forEach(el => {
                        el.style.flex = '0 0 auto';
                        el.style.maxWidth = 'none';
                        el.style.width = 'auto';
                    });
                    clone.querySelectorAll('table').forEach(t => {
                        t.style.minWidth = '0';
                        t.style.width = 'auto';
                        t.style.tableLayout = 'auto';
                    });
                    clone.querySelectorAll('.highcharts-container').forEach(hc => {
                        hc.style.width = '100%';
                        hc.style.overflow = 'visible';
                    });
                    wrapper.appendChild(clone);

                    requestAnimationFrame(() => {
                        setTimeout(() => {
                            const naturalW = wrapper.scrollWidth;
                            wrapper.style.width = naturalW + 'px';
                            html2canvas(clone, {
                                scale: 2,
                                backgroundColor: '#ffffff',
                                useCORS: true,
                                logging: false,
                                windowWidth: naturalW,
                                scrollX: 0,
                                scrollY: 0
                            }).then(canvas => {
                                const link = document.createElement('a');
                                link.download = filename;
                                link.href = canvas.toDataURL('image/png');
                                link.click();
                            }).catch(err => {
                                console.error('PNG Export Error:', err);
                                alert('Gagal export PNG.');
                            }).finally(() => {
                                document.body.removeChild(wrapper);
                                btn.innerHTML = origText;
                                btn.disabled = false;
                            });
                        }, 200);
                    });
                }

                // Generic section PNG export
                window.exportSectionPNG = function(sectionId, filename, menuId) {
                    document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));
                    const section = document.querySelector('#' + sectionId);
                    if (!section) {
                        alert('Section tidak ditemukan.');
                        return;
                    }
                    captureAsPNG(section, filename + '.png', menuId);
                };

                // =========================================
                // CSV Exports
                // =========================================

                // Section 01 CSV — Mode Share Distribution (2 sheets: Pergerakan & Orang)
                window.exportMS01CSV = function() {
                    document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));
                    const wb = XLSX.utils.book_new();

                    // Sheet 1: Pergerakan
                    const rowsMov = [
                        ['Moda Transportasi', 'Total Pergerakan', 'Persen (%)']
                    ];
                    dataPergerakan.forEach(r => {
                        if (r.y > 0) rowsMov.push([r.name, r.y, (r.pct || 0).toFixed(2) + '%']);
                    });
                    const wsMov = XLSX.utils.aoa_to_sheet(rowsMov);
                    wsMov['!cols'] = [{
                        wch: 25
                    }, {
                        wch: 18
                    }, {
                        wch: 12
                    }];
                    XLSX.utils.book_append_sheet(wb, wsMov, 'Pergerakan');

                    // Sheet 2: Orang
                    const rowsOrg = [
                        ['Moda Transportasi', 'Total Orang', 'Persen (%)']
                    ];
                    dataOrang.forEach(r => {
                        if (r.y > 0) rowsOrg.push([r.name, r.y, (r.pct || 0).toFixed(2) + '%']);
                    });
                    const wsOrg = XLSX.utils.aoa_to_sheet(rowsOrg);
                    wsOrg['!cols'] = [{
                        wch: 25
                    }, {
                        wch: 18
                    }, {
                        wch: 12
                    }];
                    XLSX.utils.book_append_sheet(wb, wsOrg, 'Orang');

                    XLSX.writeFile(wb, 'Mode_Share_Distribusi.xlsx');
                };

                // Section 02 CSV — Daily per mode (1 sheet per mode)
                window.exportMS02CSV = function() {
                    document.querySelectorAll('.export-menu').forEach(m => m.classList.remove('show'));
                    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];

                    function fmtDateID(d) {
                        const dt = new Date(d);
                        return dayNames[dt.getDay()] + ', ' + dt.getDate() + ' ' + monthNames[dt.getMonth()] + ' ' +
                            dt.getFullYear();
                    }

                    const wb = XLSX.utils.book_new();
                    Object.keys(dailyData).forEach(code => {
                        const mode = dailyData[code];
                        const rows = [
                            ['Tanggal', 'Total Pergerakan']
                        ];
                        const sortedDates = Object.keys(mode.daily).sort();
                        sortedDates.forEach(d => {
                            rows.push([fmtDateID(d), mode.daily[d]]);
                        });
                        rows.push(['Total', mode.total_pergerakan]);
                        const ws = XLSX.utils.aoa_to_sheet(rows);
                        ws['!cols'] = [{
                            wch: 30
                        }, {
                            wch: 18
                        }];
                        const sheetName = (mode.name || code).substring(0, 31);
                        XLSX.utils.book_append_sheet(wb, ws, sheetName);
                    });
                    XLSX.writeFile(wb, 'Mode_Share_Harian.xlsx');
                };

            });
        </script>
    @endpush
@endsection
