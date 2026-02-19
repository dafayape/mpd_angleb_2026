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
                <div class="mt-2 text-end">
                    <span class="badge bg-info font-size-12">Periode: 13 Mar 2026 - 29 Mar 2026</span>
                </div>
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

{{-- Charts Section --}}
{{-- 1. Pergerakan Harian (REAL vs FORECAST) --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">Pergerakan Per Hari (REAL vs FORECAST)</h4>
                <div id="chart-pergerakan-trend" dir="ltr" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- 2. Orang Harian (REAL vs FORECAST) --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">Orang Per Hari (REAL vs FORECAST)</h4>
                <div id="chart-orang-trend" dir="ltr" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- 3. Pergerakan Harian per OPSEL --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">Pergerakan Harian per OPSEL</h4>
                <div id="chart-pergerakan-opsel" dir="ltr" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- 4. Orang Harian per OPSEL --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">Orang Harian per OPSEL</h4>
                <div id="chart-orang-opsel" dir="ltr" style="height: 400px;"></div>
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
        try {
            const data = @json($charts);
            const dates = data.dates;
            
            // Common Options
            const commonOptions = {
                chart: { type: 'column' },
                xAxis: { 
                    categories: dates,
                    crosshair: true,
                    labels: {
                        formatter: function() { 
                            // Robust date formatting
                            if (typeof this.value !== 'string') return this.value;
                            var parts = this.value.split('-');
                            if(parts.length === 3) {
                                return parts[2] + '-' + parts[1] + '-' + parts[0];
                            }
                            return this.value;
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: { text: null }, // Hide title to save space as requested in reference style
                    labels: {
                        formatter: function() { 
                            if(this.value >= 1000000) return (this.value / 1000000).toFixed(1) + 'M';
                            if(this.value >= 1000) return (this.value / 1000).toFixed(0) + 'k';
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
                        minPointLength: 3, // Ensure 0 values show a small line
                        dataLabels: {
                            enabled: true,
                            rotation: -90,
                            color: '#999', // Grey if 0/inside
                            align: 'right',
                            format: '{point.y:,.0f}', 
                            y: -5, // Float above bar
                            style: {
                                fontSize: '9px',
                                fontFamily: 'Verdana, sans-serif',
                                textOutline: 'none' // Cleaner look
                            }
                        }
                    }
                },
                credits: { enabled: false },
                legend: {
                    align: 'center',
                    verticalAlign: 'bottom',
                    layout: 'horizontal'
                }
            };

            // 1. Pergerakan Trend (Real vs Forecast)
            Highcharts.chart('chart-pergerakan-trend', Highcharts.merge(commonOptions, {
                series: data.series_trend
            }));

            // 2. Orang Trend (Real vs Forecast)
            Highcharts.chart('chart-orang-trend', Highcharts.merge(commonOptions, {
                 series: data.series_trend
            }));

            // 3. Pergerakan Opsel
            Highcharts.chart('chart-pergerakan-opsel', Highcharts.merge(commonOptions, {
                 series: data.series_opsel
            }));

             // 4. Orang Opsel
             Highcharts.chart('chart-orang-opsel', Highcharts.merge(commonOptions, {
                 series: data.series_opsel
            }));

        } catch (e) {
            console.error('Chart Render Error:', e);
        }
    });
</script>
@endpush
