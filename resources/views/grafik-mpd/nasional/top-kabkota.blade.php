@extends('layout.app')

@section('title', $title)

@push('css')
<style>
    .highcharts-figure, .highcharts-data-table table {
        min-width: 310px; 
        max-width: 100%;
        margin: 1em auto;
    }
    .chart-container {
        height: 400px;
    }
    .table-custom th {
        background-color: #f1f5f9;
        font-weight: 600;
        font-size: 13px;
    }
    .table-custom td {
        font-size: 13px;
        vertical-align: middle;
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
    <!-- TOP ORIGIN -->
    <div class="col-xl-6 col-lg-12">
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="card-title mb-3">Top 10 Kabupaten/Kota Asal</h4>
                <div id="chart-origin" class="chart-container mb-3"></div>
                
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Kabupaten/Kota Asal</th>
                                <th class="text-end">Total Pergerakan</th>
                                <th class="text-end">Persen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['top_origin'] as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-end fw-bold">{{ number_format($row['total'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($row['pct'], 2, ',', '.') }} %</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP DESTINATION -->
    <div class="col-xl-6 col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Top 10 Kabupaten/Kota Tujuan</h4>
                <div id="chart-dest" class="chart-container mb-3"></div>

                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Kabupaten/Kota Tujuan</th>
                                <th class="text-end">Total Pergerakan</th>
                                <th class="text-end">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['top_dest'] as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-end fw-bold">{{ number_format($row['total'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($row['pct'], 2, ',', '.') }} %</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
        const topOrigin = @json($data['top_origin']);
        const topDest = @json($data['top_dest']);

        function renderBarChart(id, data, title, color) {
            const categories = data.map(d => d.name);
            const seriesData = data.map(d => d.total);

            Highcharts.chart(id, {
                chart: { type: 'bar' },
                title: { text: '' },
                xAxis: {
                    categories: categories,
                    title: { text: null },
                    labels: {
                        style: {
                            fontSize: '11px',
                            textOverflow: 'none' // Allow full text
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: { text: 'Total Pergerakan', align: 'high' },
                    labels: { overflow: 'justify' }
                },
                tooltip: { valueSuffix: ' pergerakan' },
                plotOptions: {
                    bar: {
                        borderRadius: 3,
                        dataLabels: { enabled: true, format: '{point.y:,.0f}' },
                        groupPadding: 0.1
                    }
                },
                legend: { enabled: false },
                credits: { enabled: false },
                series: [{
                    name: 'Total',
                    data: seriesData,
                    color: color
                }]
            });
        }

        renderBarChart('chart-origin', topOrigin, 'Top Origin', '#2caffe');
        renderBarChart('chart-dest', topDest, 'Top Destination', '#fec107'); // Yellow/Orange
    });
</script>
@endpush
