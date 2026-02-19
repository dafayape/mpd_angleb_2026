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
        const pieConfig = (title, data) => ({
            chart: { type: 'pie' },
            title: { text: '' },
            plotOptions: {
                pie: {
                    innerSize: '50%', // Donut
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'Volume',
                colorByPoint: true,
                data: data
            }],
            credits: { enabled: false }
        });

        Highcharts.chart('pie-movement', pieConfig('Pergerakan', pieMovement));
        Highcharts.chart('pie-people', pieConfig('Orang', piePeople));

        // 2. DAILY BAR CHARTS
        dailyCharts.forEach((chartData, index) => {
            Highcharts.chart(`chart-mode-${index}`, {
                chart: { type: 'column' },
                title: { text: '' },
                xAxis: {
                    categories: dates,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: { text: 'Total' }
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
                        borderWidth: 0
                    }
                },
                series: chartData.series,
                credits: { enabled: false }
            });
        });
    });
</script>
@endpush
