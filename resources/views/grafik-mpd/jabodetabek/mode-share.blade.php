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
                <h4 class="card-title mb-4 text-center">Distribusi Pergerakan Moda Transportasi (Real)</h4>
                <div id="chart-movement" class="chart-container"></div>
            </div>
        </div>
    </div>

    <!-- CHART 2: PEOPLE DISTRIBUTION -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4 text-center">Distribusi Orang Moda Transportasi (Real)</h4>
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

        const commonOptions = {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: { text: null },
            tooltip: {
                pointFormat: '<b>{point.name}</b>: {point.y:,.0f} ({point.percentage:.1f}%)'
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
                        format: '<b>{point.name}</b>: {point.y:,.0f} ({point.percentage:.1f}%)',
                        distance: -50, // Inside or close
                        filter: {
                            property: 'percentage',
                            operator: '>',
                            value: 4
                        },
                        style: {
                            fontSize: '10px',
                            textOutline: 'none',
                            color: 'white' 
                        }
                    },
                    showInLegend: true,
                    innerSize: '50%' // Donut Chart
                }
            },
            legend: {
                align: 'center',
                verticalAlign: 'bottom',
                layout: 'horizontal',
                itemStyle: { fontSize: '10px' } 
            },
            credits: { enabled: false }
        };

        // Render Movement Chart
        Highcharts.chart('chart-movement', Highcharts.merge(commonOptions, {
            series: [{
                name: 'Moda',
                colorByPoint: true,
                data: pieMovement
            }],
            plotOptions: {
                pie: {
                    dataLabels: {
                        // Custom formatter for readability logic if needed, 
                        // but standard format above works for basic donut
                        formatter: function() {
                             if(this.percentage < 2) return null; // Hide small labels
                             return this.point.name + ': ' + Highcharts.numberFormat(this.y, 0) + ' (' + Highcharts.numberFormat(this.percentage, 1) + '%)';
                        },
                        distance: 20, // Outside label for better visibility
                        color: 'black'
                    }
                }
            }
        }));

        // Render People Chart
        Highcharts.chart('chart-people', Highcharts.merge(commonOptions, {
            series: [{
                name: 'Moda',
                colorByPoint: true,
                data: piePeople
            }],
             plotOptions: {
                pie: {
                    dataLabels: {
                        formatter: function() {
                             if(this.percentage < 2) return null;
                             return this.point.name + ': ' + Highcharts.numberFormat(this.y, 0) + ' (' + Highcharts.numberFormat(this.percentage, 1) + '%)';
                        },
                        distance: 20,
                        color: 'black'
                    }
                }
            }
        }));
    });
</script>
@endpush
