@extends('layout.app')

@section('title', $title)

@push('css')
<style>
    .highcharts-figure, .highcharts-data-table table {
        min-width: 320px; 
        max-width: 800px;
        margin: 1em auto;
    }
    .chart-container {
        height: 600px; /* Taller for Pie + Legend */
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

<div class="row">
    <!-- CHART 1: MOVEMENT DISTRIBUTION -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">Distribusi Angkutan (Pergerakan - Real)</h4>
                <div id="chart-movement" class="chart-container"></div>
            </div>
        </div>
    </div>

    <!-- CHART 2: PEOPLE DISTRIBUTION -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">Distribusi Angkutan (Orang - Real)</h4>
                <div id="chart-people" class="chart-container"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pieMovement = @json($data['pie_movement']);
        const piePeople = @json($data['pie_people']);

        const pieConfig = (data, elementId) => ({
            chart: { 
                type: 'pie',
                height: 500
            },
            title: { text: '' },
            tooltip: {
                pointFormat: '<b>{point.y:,.0f}</b> ({point.percentage:.2f}%)'
            },
            accessibility: {
                point: { valueSuffix: '%' }
            },
            plotOptions: {
                pie: {
                    innerSize: '60%', // DONUT (Matches Nasional)
                    allowPointSelect: true,
                    cursor: 'pointer',
                    showInLegend: true,
                    dataLabels: {
                        enabled: true,
                        useHTML: true,
                        // Match Nasional Label Format: Name <br> Value <br> (Percent)
                        formatter: function() {
                             if(this.percentage < 1) return null; // Hide tiny labels
                             return '<b>' + this.point.name + '</b><br>' + 
                                    Highcharts.numberFormat(this.y, 0) + '<br>' + 
                                    '<span style="color:#666">(' + Highcharts.numberFormat(this.percentage, 2) + '%)</span>';
                        },
                        distance: 30,
                        color: 'black'
                    }
                }
            },
            legend: {
                enabled: true,
                align: 'center',
                verticalAlign: 'bottom',
                layout: 'horizontal',
                useHTML: true,
                // Match Nasional Legend Format
                labelFormatter: function () {
                    return '<b>' + this.name + '</b><br/>' +
                           '<span style="font-size:11px;color:#555">' +
                           Highcharts.numberFormat(this.y, 0) + ' | ' +
                           Highcharts.numberFormat(this.percentage, 2) + '%' +
                           '</span>';
                }
            },
            series: [{
                name: 'Total',
                colorByPoint: true,
                data: data
            }],
            credits: { enabled: false }
        });

        Highcharts.chart('chart-movement', pieConfig(pieMovement, 'chart-movement'));
        Highcharts.chart('chart-people', pieConfig(piePeople, 'chart-people'));
    });
</script>
@endpush
