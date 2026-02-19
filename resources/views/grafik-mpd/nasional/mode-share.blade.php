@extends('layout.app')

@section('title', $title)

@push('css')
<style>
    .highcharts-figure {
        min-width: 310px; 
        max-width: 100%;
        margin: 1em auto;
    }
    .chart-container {
        height: 400px;
    }
    .pie-container {
        height: 450px;
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
                    @foreach($breadcrumb as $crumb)
                        <li class="breadcrumb-item">{{ $crumb }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- PIE CHARTS (DONUTS) -->
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Distribusi Angkutan (Pergerakan - Real)</h4>
                <div id="pie-movement" class="pie-container"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Distribusi Angkutan (Orang - Real)</h4>
                <div id="pie-people" class="pie-container"></div>
            </div>
        </div>
    </div>
</div>

<!-- DAILY CHARTS BY MODE -->
<div class="row" id="daily-charts-wrapper">
    <!-- Generated via JS loop or Blade loop -->
    @foreach($data['daily_charts'] as $index => $chart)
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">{{ $chart['title'] }}</h4>
                <div id="chart-mode-{{ $index }}" class="chart-container"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dates = @json($data['dates']);
        const piePeople = @json($data['pie_people']);
        const pieMovement = @json($data['pie_movement']);
        const dailyCharts = @json($data['daily_charts']);

        // 1. PIE CHARTS
        // 1. PIE CHARTS
        const pieConfig = (title, subtitle, data) => ({
            chart: { 
                type: 'pie',
                // height: 600 // Reference uses 600px
            },
            title: { text: title },
            subtitle: { text: subtitle },
            tooltip: {
                pointFormat: '<b>{point.y:,.0f}</b> ({point.percentage:.2f}%)'
            },
            legend: {
                enabled: true,
                align: 'center',
                verticalAlign: 'bottom',
                layout: 'horizontal',
                useHTML: true,
                labelFormatter: function () {
                    return `
                        <b>${this.name}</b><br/>
                        <span style="font-size:11px;color:#555">
                            ${Highcharts.numberFormat(this.y, 0)} |
                            ${Highcharts.numberFormat(this.percentage, 2)}%
                        </span>
                    `;
                }
            },
            plotOptions: {
                pie: {
                    innerSize: '60%', // DONUT
                    allowPointSelect: true,
                    cursor: 'pointer',
                    showInLegend: true,
                    dataLabels: {
                        enabled: true,
                        useHTML: true,
                        format: '<b>{point.name}</b><br>{point.y:,.0f}<br><span style="color:#666">({point.percentage:.2f}%)</span>'
                    }
                }
            },
            series: [{
                name: 'Total',
                colorByPoint: true,
                data: data
            }],
            credits: { enabled: false }
        });

        Highcharts.chart('pie-movement', pieConfig('Distribusi Angkutan (Pergerakan - Real)', '[Kode : 1.3.d.1] Berdasarkan jenis angkutan', pieMovement));
        Highcharts.chart('pie-people', pieConfig('Distribusi Angkutan (Orang - Real)', '[Kode : 1.3.d.2] Berdasarkan jenis angkutan', piePeople));

        // 2. DAILY BAR CHARTS
        dailyCharts.forEach((chartData, index) => {
            Highcharts.chart(`chart-mode-${index}`, {
                chart: { type: 'column' },
                title: { text: '' }, // Handled by Card Title
                subtitle: { text: `[Kode : 1.3.c.${index % 2 === 0 ? '1' : '2'}]` }, // 1=Pergerakan, 2=Orang
                colors: ['#1E88E5', '#FBC02D'], // Real Blue, Forecast Yellow
                xAxis: {
                    categories: dates,
                    crosshair: true
                },
                yAxis: {
                    title: { text: 'Total' }
                },
                tooltip: {
                    shared: true,
                    pointFormat: '<span style="color:{series.color}">\u25CF</span> {series.name}: <b>{point.y:,.0f}</b><br/>'
                },
                plotOptions: {
                    column: {
                        grouping: true,
                        dataLabels: {
                            enabled: true,
                            formatter: function () {
                                return Highcharts.numberFormat(this.y, 0);
                            }
                        }
                    }
                },
                series: chartData.series,
                credits: { enabled: false }
            });
        });
    });
</script>
@endpush
