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

{{-- Charts Section --}}
{{-- 1. Pergerakan Harian (REAL vs FORECAST) --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">Pergerakan Per Hari (REAL vs FORECAST)</h4>
                <div id="chart-pergerakan-trend" dir="ltr" class="chart-container"></div>
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
                <div id="chart-orang-trend" dir="ltr" class="chart-container"></div>
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
                <div id="chart-pergerakan-opsel" dir="ltr" class="chart-container"></div>
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
                <div id="chart-orang-opsel" dir="ltr" class="chart-container"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    .chart-container {
        height: 400px;
        width: 100%;
    }
</style>
@endpush

@push('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const data = @json($charts);
            const dates = data.dates || [];
            
            // Common Highcharts Options
            const commonOptions = {
                chart: { type: 'column' },
                title: { text: undefined },
                xAxis: { 
                    categories: dates,
                    crosshair: true,
                    labels: {
                        formatter: function() { 
                            // Robust Date Formatting (YYYY-MM-DD -> DD-MM-YYYY)
                            if (typeof this.value !== 'string') return this.value;
                            const parts = this.value.split('-');
                            if(parts.length === 3) {
                                return parts[2] + '-' + parts[1] + '-' + parts[0];
                            }
                            return this.value;
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: { text: null }, // Minimized look
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
                        minPointLength: 3, // Ensures 0-values are visible as flat lines
                        dataLabels: {
                            enabled: true,
                            rotation: -90,
                            color: '#999', // Grey for 0/low values
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
                 series: data.series_trend // Using same data series as requested
            }));

            // 3. Pergerakan Opsel
            Highcharts.chart('chart-pergerakan-opsel', Highcharts.merge(commonOptions, {
                 series: data.series_opsel
            }));

             // 4. Orang Opsel
             Highcharts.chart('chart-orang-opsel', Highcharts.merge(commonOptions, {
                 series: data.series_opsel // Using same data series as requested
            }));

        } catch (e) {
            console.error('Highcharts Rendering Error:', e);
        }
    });
</script>
@endpush
