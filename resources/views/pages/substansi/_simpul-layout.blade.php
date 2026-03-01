{{-- Shared Simpul Transportasi Layout --}}
@extends('layout.app')

@section('title', $title)

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <style>
        .bg-navy {
            background-color: #2a3042 !important;
            color: white !important;
        }

        .text-navy {
            color: #2a3042 !important;
        }

        .content-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #2a3042 0%, #3b4a6b 100%);
            color: #fff;
            padding: 6px 16px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .conclusion-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-left: 5px solid #22c55e;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
        }

        .conclusion-box .highlight-route {
            color: #0284c7;
            font-weight: 700;
            font-size: 1.15rem;
        }
    </style>
@endpush

@section('content')
    @component('layout.partials.page-header', ['number' => $pageNumber, 'title' => $title])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#">Substansi Tambahan</a></li>
            <li class="breadcrumb-item active">{{ $title }}</li>
        </ol>
    @endcomponent

    {{-- Header Banner --}}
    <div class="row mb-4 mt-2" data-aos="fade-down" data-aos-duration="600">
        <div class="col-12">
            <div class="card bg-navy text-white rounded-3 border-0 shadow-lg overflow-hidden position-relative">
                <div class="position-absolute end-0 top-0 h-100"
                    style="width:30%; background:linear-gradient(90deg,transparent,rgba(255,255,255,.05))"></div>
                <div class="card-body p-4 d-flex align-items-center position-relative z-1">
                    <div class="bg-white rounded p-3 me-4 shadow-sm"><i class="bx bxs-train fs-1 text-primary"></i></div>
                    <div>
                        <h4 class="mb-1 fw-bold text-white">{{ strtoupper($title) }}</h4>
                        <p class="mb-0 text-white-50" style="font-size:1.05rem">Simpul Transportasi Terpadat —
                            {{ $title }} asal dan tujuan terpadat (Periode 13-30 Maret 2026)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" data-aos="fade-up" data-aos-delay="100">
        {{-- LEFT COLUMN: Top 10 Asal + Top 10 Tujuan --}}
        <div class="col-lg-6 col-12">
            {{-- 10 Besar Asal --}}
            <div class="card content-card">
                <div class="card-body">
                    <span class="section-badge mb-3"><i class="bx bx-upload"></i> 10 BESAR {{ strtoupper($title) }}
                        ASAL</span>
                    <div id="chart-origin" style="min-height:370px"></div>
                </div>
            </div>
            {{-- 10 Besar Tujuan --}}
            <div class="card content-card">
                <div class="card-body">
                    <span class="section-badge mb-3"><i class="bx bx-download"></i> 10 BESAR {{ strtoupper($title) }}
                        TUJUAN</span>
                    <div id="chart-dest" style="min-height:370px"></div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: O-D Simpul + Conclusion --}}
        <div class="col-lg-6 col-12">
            <div class="card content-card">
                <div class="card-body">
                    <span class="section-badge mb-3"><i class="bx bx-transfer"></i> O — D SIMPUL
                        {{ strtoupper($title) }}</span>
                    <div id="chart-od" style="min-height:370px"></div>
                </div>
            </div>
            {{-- Conclusion --}}
            <div class="card content-card">
                <div class="card-body">
                    <div class="conclusion-box">
                        <p class="mb-2"><strong>Rute <span class="highlight-route">{{ $top_od_name }}</span></strong></p>
                        <p class="mb-0 text-muted">menjadi rute {{ strtolower($title) }} yang paling banyak diminati selama
                            masa Angleb 2026.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof AOS !== 'undefined') AOS.init({
                once: true,
                offset: 50,
                duration: 600
            });

            var originData = @json($top_origin);
            var destData = @json($top_dest);
            var odData = @json($top_od);

            function renderBar(containerId, dataset, nameKey, color) {
                var cats = dataset.map(function(r) {
                    return '[' + (r.code || '') + '] ' + (r[nameKey] || r.od_name || '');
                });
                var vals = dataset.map(function(r) {
                    return parseInt(r.total_volume);
                });
                var pcts = dataset.map(function(r) {
                    return parseFloat(r.pct);
                });

                Highcharts.chart(containerId, {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: null
                    },
                    xAxis: {
                        categories: cats,
                        labels: {
                            style: {
                                fontSize: '11px',
                                color: '#334155'
                            }
                        }
                    },
                    yAxis: {
                        title: {
                            text: null
                        },
                        labels: {
                            enabled: false
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>' + this.point.category + '</b><br/>Volume: ' + Highcharts
                                .numberFormat(this.y, 0, ',', '.') + '<br/>Proporsi: ' + pcts[this.point
                                    .index] + '%';
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            pointPadding: 0.1,
                            groupPadding: 0.08,
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                align: 'left',
                                inside: false,
                                formatter: function() {
                                    return '<span style="font-size:10px;color:#64748b">' + pcts[this
                                        .point.index] + '%</span> <b>' + Highcharts.numberFormat(
                                        this.y, 0, ',', '.') + '</b>';
                                },
                                useHTML: true
                            }
                        }
                    },
                    series: [{
                        data: vals,
                        color: color
                    }],
                    credits: {
                        enabled: false
                    }
                });
            }

            renderBar('chart-origin', originData, 'name', '#60a5fa');
            renderBar('chart-dest', destData, 'name', '#60a5fa');

            // OD chart (uses od_name, no code)
            var odCats = odData.map(function(r) {
                return r.od_name;
            });
            var odVals = odData.map(function(r) {
                return parseInt(r.total_volume);
            });
            var odPcts = odData.map(function(r) {
                return parseFloat(r.pct);
            });
            Highcharts.chart('chart-od', {
                chart: {
                    type: 'bar',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: odCats,
                    labels: {
                        style: {
                            fontSize: '11px',
                            color: '#334155'
                        }
                    }
                },
                yAxis: {
                    title: {
                        text: null
                    },
                    labels: {
                        enabled: false
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    formatter: function() {
                        return '<b>' + this.point.category + '</b><br/>Volume: ' + Highcharts
                            .numberFormat(this.y, 0, ',', '.') + '<br/>Proporsi: ' + odPcts[this.point
                                .index] + '%';
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        pointPadding: 0.1,
                        groupPadding: 0.08,
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            align: 'left',
                            inside: false,
                            formatter: function() {
                                return '<span style="font-size:10px;color:#64748b">' + odPcts[this.point
                                    .index] + '%</span> <b>' + Highcharts.numberFormat(this.y, 0,
                                    ',', '.') + '</b>';
                            },
                            useHTML: true
                        }
                    }
                },
                series: [{
                    data: odVals,
                    color: '#60a5fa'
                }],
                credits: {
                    enabled: false
                }
            });
        });
    </script>
@endpush
