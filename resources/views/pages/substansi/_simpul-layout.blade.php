{{-- Shared Simpul Transportasi Layout — Premium Clean Design --}}
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

        .simpul-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .simpul-card:hover {
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
        }

        .simpul-card .card-header {
            background: #fff;
            border-bottom: 2px solid #e2e8f0;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .simpul-card .card-header .badge-section {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #2a3042 0%, #3b4a6b 100%);
            color: #fff;
            padding: 5px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .simpul-card .card-body {
            padding: 0.5rem 1rem 1rem;
        }

        .conclusion-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-left: 5px solid #22c55e;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
        }

        .conclusion-box .route-highlight {
            color: #0369a1;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .note-banner {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            color: #92400e;
            font-size: 0.9rem;
        }

        .note-banner i {
            color: #f59e0b;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 12px;
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
    <div class="row mb-3 mt-2" data-aos="fade-down" data-aos-duration="500">
        <div class="col-12">
            <div class="card bg-navy text-white rounded-3 border-0 shadow-lg overflow-hidden position-relative">
                <div class="position-absolute end-0 top-0 h-100"
                    style="width:30%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.05))"></div>
                <div class="card-body p-4 d-flex align-items-center position-relative z-1">
                    <div class="bg-white rounded p-3 me-4 shadow-sm">
                        @php
                            $icon = match (true) {
                                str_contains($title, 'Stasiun') => 'bx bxs-train',
                                str_contains($title, 'Pelabuhan') => 'bx bxs-ship',
                                str_contains($title, 'Bandara') => 'bx bxs-plane-alt',
                                str_contains($title, 'Terminal') => 'bx bxs-bus',
                                default => 'bx bxs-map-pin',
                            };
                        @endphp
                        <i class="{{ $icon }} fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-white">{{ strtoupper($title) }}</h4>
                        <p class="mb-0 text-white-50" style="font-size:1rem">Simpul Transportasi Terpadat — Asal dan Tujuan
                            Terpadat (Periode 13 – 30 Maret 2026)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Note Banner (for KA Regional/Cepat) --}}
    @if (!empty($note))
        <div class="row mb-3" data-aos="fade-down" data-aos-delay="50">
            <div class="col-12">
                <div class="note-banner d-flex align-items-center gap-2">
                    <i class="bx bx-info-circle fs-5"></i>
                    <span>{{ $note }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="row" data-aos="fade-up" data-aos-delay="100">
        {{-- LEFT: Top 10 Asal + Top 10 Tujuan --}}
        <div class="col-lg-6 col-12">
            {{-- 10 Besar Asal --}}
            <div class="card simpul-card">
                <div class="card-header">
                    <span class="badge-section"><i class="bx bx-upload"></i> 10 BESAR {{ strtoupper($title) }} ASAL</span>
                </div>
                <div class="card-body">
                    @if ($top_origin->isEmpty())
                        <div class="empty-state"><i class="bx bx-data"></i>
                            <p>Data tidak tersedia</p>
                        </div>
                    @else
                        <div id="chart-origin" style="min-height:360px"></div>
                    @endif
                </div>
            </div>

            {{-- 10 Besar Tujuan --}}
            <div class="card simpul-card">
                <div class="card-header">
                    <span class="badge-section"><i class="bx bx-download"></i> 10 BESAR {{ strtoupper($title) }}
                        TUJUAN</span>
                </div>
                <div class="card-body">
                    @if ($top_dest->isEmpty())
                        <div class="empty-state"><i class="bx bx-data"></i>
                            <p>Data tidak tersedia</p>
                        </div>
                    @else
                        <div id="chart-dest" style="min-height:360px"></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT: O-D Simpul + Conclusion --}}
        <div class="col-lg-6 col-12">
            {{-- O-D Simpul --}}
            <div class="card simpul-card">
                <div class="card-header">
                    <span class="badge-section"><i class="bx bx-transfer"></i> O – D SIMPUL {{ strtoupper($title) }}</span>
                </div>
                <div class="card-body">
                    @if ($top_od->isEmpty())
                        <div class="empty-state"><i class="bx bx-data"></i>
                            <p>Data tidak tersedia</p>
                        </div>
                    @else
                        <div id="chart-od" style="min-height:360px"></div>
                    @endif
                </div>
            </div>

            {{-- Conclusion --}}
            @if ($top_od_name && $top_od_name !== '-')
                <div class="card simpul-card">
                    <div class="card-body pt-3">
                        <div class="conclusion-box">
                            <p class="mb-2 fw-semibold text-dark" style="font-size:1rem">Rute <span
                                    class="route-highlight">{{ $top_od_name }}</span></p>
                            <p class="mb-0 text-muted" style="font-size:0.95rem">menjadi rute {{ strtolower($title) }} yang
                                paling banyak diminati selama masa Angleb 2026.</p>
                        </div>
                    </div>
                </div>
            @endif
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
                duration: 500
            });

            var originData = @json($top_origin);
            var destData = @json($top_dest);
            var odData = @json($top_od);

            function renderHorizontalBar(el, dataset, labelKey, barColor) {
                if (!dataset || !dataset.length || !document.getElementById(el)) return;

                var cats = dataset.map(function(r) {
                    var code = r.code ? '[' + r.code + '] ' : '';
                    return code + (r[labelKey] || r.od_name || '—');
                });
                var vals = dataset.map(function(r) {
                    return parseInt(r.total_volume) || 0;
                });
                var pcts = dataset.map(function(r) {
                    return parseFloat(r.pct) || 0;
                });

                Highcharts.chart(el, {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent',
                        style: {
                            fontFamily: 'inherit'
                        }
                    },
                    title: {
                        text: null
                    },
                    xAxis: {
                        categories: cats,
                        labels: {
                            style: {
                                fontSize: '11px',
                                color: '#475569',
                                fontWeight: '500'
                            }
                        }
                    },
                    yAxis: {
                        visible: false
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        useHTML: true,
                        formatter: function() {
                            return '<b>' + this.point.category + '</b><br>Volume: <b>' + Highcharts
                                .numberFormat(this.y, 0, ',', '.') + '</b><br>Proporsi: <b>' + pcts[this
                                    .point.index] + '%</b>';
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 3,
                            pointPadding: 0.08,
                            groupPadding: 0.06,
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                align: 'left',
                                inside: false,
                                useHTML: true,
                                formatter: function() {
                                    return '<span style="font-size:10px;color:#94a3b8;margin-right:4px">' +
                                        pcts[this.point.index] + '%</span><b style="color:#1e293b">' +
                                        Highcharts.numberFormat(this.y, 0, ',', '.') + '</b>';
                                }
                            }
                        }
                    },
                    series: [{
                        data: vals,
                        color: barColor
                    }],
                    credits: {
                        enabled: false
                    }
                });
            }

            renderHorizontalBar('chart-origin', originData, 'name', '#60a5fa');
            renderHorizontalBar('chart-dest', destData, 'name', '#38bdf8');

            // O-D Chart (no code prefix)
            if (odData && odData.length && document.getElementById('chart-od')) {
                var odCats = odData.map(function(r) {
                    return r.od_name || '—';
                });
                var odVals = odData.map(function(r) {
                    return parseInt(r.total_volume) || 0;
                });
                var odPcts = odData.map(function(r) {
                    return parseFloat(r.pct) || 0;
                });

                Highcharts.chart('chart-od', {
                    chart: {
                        type: 'bar',
                        backgroundColor: 'transparent',
                        style: {
                            fontFamily: 'inherit'
                        }
                    },
                    title: {
                        text: null
                    },
                    xAxis: {
                        categories: odCats,
                        labels: {
                            style: {
                                fontSize: '11px',
                                color: '#475569',
                                fontWeight: '500'
                            }
                        }
                    },
                    yAxis: {
                        visible: false
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        useHTML: true,
                        formatter: function() {
                            return '<b>' + this.point.category + '</b><br>Volume: <b>' + Highcharts
                                .numberFormat(this.y, 0, ',', '.') + '</b><br>Proporsi: <b>' + odPcts[
                                    this.point.index] + '%</b>';
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 3,
                            pointPadding: 0.08,
                            groupPadding: 0.06,
                            borderWidth: 0,
                            dataLabels: {
                                enabled: true,
                                align: 'left',
                                inside: false,
                                useHTML: true,
                                formatter: function() {
                                    return '<span style="font-size:10px;color:#94a3b8;margin-right:4px">' +
                                        odPcts[this.point.index] + '%</span><b style="color:#1e293b">' +
                                        Highcharts.numberFormat(this.y, 0, ',', '.') + '</b>';
                                }
                            }
                        }
                    },
                    series: [{
                        data: odVals,
                        color: '#818cf8'
                    }],
                    credits: {
                        enabled: false
                    }
                });
            }
        });
    </script>
@endpush
