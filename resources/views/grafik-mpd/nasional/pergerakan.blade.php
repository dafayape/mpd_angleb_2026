@extends('layout.app')

@section('title', $title)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    @foreach($breadcrumb as $crumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                            {{ $crumb }}
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row">
    <div class="col-md-4">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Real Pergerakan</p>
                        <h4 class="mb-0">{{ number_format($charts['summary']['real'], 0, ',', '.') }}</h4>
                    </div>
                    <div class="flex-shrink-0 align-self-center">
                        <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                            <span class="avatar-title">
                                <i class="bx bx-run font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Forecast Pergerakan</p>
                        <h4 class="mb-0">{{ number_format($charts['summary']['forecast'], 0, ',', '.') }}</h4>
                    </div>
                    <div class="flex-shrink-0 align-self-center">
                        <div class="mini-stat-icon avatar-sm rounded-circle bg-warning">
                            <span class="avatar-title">
                                <i class="bx bx-trending-up font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Total Orang</p>
                        <h4 class="mb-0">{{ number_format($charts['summary']['people'], 0, ',', '.') }}</h4>
                    </div>
                    <div class="flex-shrink-0 align-self-center">
                        <div class="mini-stat-icon avatar-sm rounded-circle bg-success">
                            <span class="avatar-title">
                                <i class="bx bx-user font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Main Chart: Forecast vs Real --}}
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Grafik Pergerakan Harian (Forecast vs Real)</h4>
                <div id="chart-daily-trend" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
    
    {{-- Pie Chart: Operator Share --}}
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Share Operator Seluler</h4>
                <div id="chart-opsel-share" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const data = @json($charts);
        
        // 1. Daily Trend Chart
        Highcharts.chart('chart-daily-trend', {
            chart: { type: 'spline' },
            title: { text: '' },
            xAxis: { 
                categories: data.dates,
                crosshair: true
            },
            yAxis: {
                title: { text: 'Volume Pergerakan' },
                labels: {
                    formatter: function() { return this.value / 1000 + 'k'; }
                }
            },
            tooltip: {
                shared: true,
                valueSuffix: ' pergerakan'
            },
            credits: { enabled: false },
            plotOptions: {
                spline: {
                    marker: { radius: 4, lineColor: '#666666', lineWidth: 1 }
                }
            },
            series: [{
                name: 'Real',
                data: data.series.real,
                color: '#556ee6' // Primary Blue
            }, {
                name: 'Forecast',
                data: data.series.forecast,
                color: '#f1b44c', // Warning Yellow
                dashStyle: 'ShortDash'
            }]
        });

        // 2. Operator Share Pie Chart
        Highcharts.chart('chart-opsel-share', {
            chart: { type: 'pie' },
            title: { text: '' },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b> ({point.y:,.0f})'
            },
            accessibility: {
                point: { valueSuffix: '%' }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    },
                    showInLegend: true
                }
            },
            credits: { enabled: false },
            series: [{
                name: 'Share',
                colorByPoint: true,
                data: data.pie_opsel
            }]
        });
    });
</script>
@endpush
