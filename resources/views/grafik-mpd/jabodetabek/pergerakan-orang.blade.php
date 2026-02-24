@extends('layout.app')

@section('title', $title)

@push('css')
    <style>
        .chart-container {
            height: 400px;
            width: 100%;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        @foreach ($breadcrumb as $crumb)
                            <li class="breadcrumb-item">{{ $crumb }}</li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS ONLY (KPIs Removed per Request) --}}

    <div class="row">
        {{-- CHART 1: PERGERAKAN HARIAN --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4 text-center">Grafik Pergerakan Harian Jabodetabek (Real vs Forecast)</h4>
                    <div id="chart-movement" dir="ltr" class="chart-container"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- CHART 2: ORANG HARIAN --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4 text-center">Grafik Orang Harian Jabodetabek (Real vs Forecast)</h4>
                    <div id="chart-people" dir="ltr" class="chart-container"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- INTRA/INTER BREAKDOWN --}}
    @if (isset($data['chart_intra_mov']))
        <div class="row">
            <div class="col-12">
                <h5 class="fw-bold text-primary mb-3"><i class="mdi mdi-arrow-split-vertical me-1"></i> Breakdown Intra &
                    Inter Jabodetabek</h5>
            </div>
            {{-- Summary KPIs --}}
            @if (isset($data['summary']))
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-4 border-info h-100">
                        <div class="card-body py-3">
                            <h6 class="text-muted small mb-1">Intra Jabo (Real)</h6>
                            <h4 class="mb-0 fw-bold text-info">
                                {{ number_format($data['summary']['intra_mov_real'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">Pergerakan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-4 border-warning h-100">
                        <div class="card-body py-3">
                            <h6 class="text-muted small mb-1">Inter/Outbound (Real)</h6>
                            <h4 class="mb-0 fw-bold text-warning">
                                {{ number_format($data['summary']['inter_mov_real'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">Pergerakan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-4 border-info h-100">
                        <div class="card-body py-3">
                            <h6 class="text-muted small mb-1">Intra Jabo Orang (Real)</h6>
                            <h4 class="mb-0 fw-bold text-info">
                                {{ number_format($data['summary']['intra_ppl_real'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">Unique Subscriber</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-start border-4 border-warning h-100">
                        <div class="card-body py-3">
                            <h6 class="text-muted small mb-1">Inter/Outbound Orang (Real)</h6>
                            <h4 class="mb-0 fw-bold text-warning">
                                {{ number_format($data['summary']['inter_ppl_real'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">Unique Subscriber</small>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Intra Charts --}}
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3 text-center">Intra Jabodetabek - Pergerakan</h5>
                        <div id="chart-intra-mov" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3 text-center">Intra Jabodetabek - Orang</h5>
                        <div id="chart-intra-ppl" class="chart-container"></div>
                    </div>
                </div>
            </div>

            {{-- Inter Charts --}}
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3 text-center">Inter/Outbound Jabodetabek - Pergerakan</h5>
                        <div id="chart-inter-mov" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3 text-center">Inter/Outbound Jabodetabek - Orang</h5>
                        <div id="chart-inter-ppl" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const data = @json($data);
            const dates = data.dates || [];

            // Common Highcharts Options (Matched with Nasional Pergerakan)
            const commonOptions = {
                chart: {
                    type: 'column'
                },
                title: {
                    text: undefined
                },
                xAxis: {
                    categories: dates,
                    crosshair: true,
                    labels: {
                        formatter: function() {
                            // Robust Date Formatting (YYYY-MM-DD -> DD-MM-YYYY)
                            if (typeof this.value !== 'string') return this.value;
                            const parts = this.value.split('-');
                            if (parts.length === 3) {
                                return parts[2] + '-' + parts[1] + '-' + parts[0];
                            }
                            return this.value;
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: null
                    },
                    labels: {
                        formatter: function() {
                            if (this.value >= 1000000) return (this.value / 1000000).toFixed(1) + 'M';
                            if (this.value >= 1000) return (this.value / 1000).toFixed(0) + 'k';
                            return this.value;
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
                        pointPadding: 0.2,
                        borderWidth: 0,
                        minPointLength: 3,
                        dataLabels: {
                            enabled: true,
                            rotation: -90,
                            color: '#999',
                            align: 'right',
                            format: '{point.y:,.0f}',
                            y: -5,
                            style: {
                                fontSize: '9px',
                                fontFamily: 'Verdana, sans-serif',
                                textOutline: 'none'
                            }
                        }
                    }
                },
                credits: {
                    enabled: false
                },
                legend: {
                    align: 'center',
                    verticalAlign: 'bottom',
                    layout: 'horizontal'
                }
            };

            // Render Movement Chart
            Highcharts.chart('chart-movement', Highcharts.merge(commonOptions, {
                series: data.chart_movement.map(s => ({
                    name: s.name,
                    data: s.data,
                    color: s.color
                }))
            }));

            // Render People Chart
            Highcharts.chart('chart-people', Highcharts.merge(commonOptions, {
                series: data.chart_people.map(s => ({
                    name: s.name,
                    data: s.data,
                    color: s.color
                }))
            }));

            // Render Intra/Inter Charts (if data exists)
            const intraInterCharts = [{
                    id: 'chart-intra-mov',
                    key: 'chart_intra_mov'
                },
                {
                    id: 'chart-intra-ppl',
                    key: 'chart_intra_ppl'
                },
                {
                    id: 'chart-inter-mov',
                    key: 'chart_inter_mov'
                },
                {
                    id: 'chart-inter-ppl',
                    key: 'chart_inter_ppl'
                },
            ];

            intraInterCharts.forEach(cfg => {
                if (data[cfg.key] && document.getElementById(cfg.id)) {
                    Highcharts.chart(cfg.id, Highcharts.merge(commonOptions, {
                        series: data[cfg.key].map(s => ({
                            name: s.name,
                            data: s.data,
                            color: s.color
                        }))
                    }));
                }
            });
        });
    </script>
@endpush
