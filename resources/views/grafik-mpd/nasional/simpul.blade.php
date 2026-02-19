@extends('layout.app')

@section('title', $title)

@push('css')
<style>
    .nav-pills .nav-link {
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 20px;
        color: #495057;
        background-color: #f8f9fa;
        margin-right: 10px;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: white;
    }
    .kpi-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .kpi-icon {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 10px;
    }
    .kpi-value {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .text-real { color: #1E88E5; }
    .text-forecast { color: #FBC02D; }
    
    .chart-container-daily { height: 350px; }
    .chart-container-top { height: 500px; }
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

<!-- TABS -->
<div class="row mb-4">
    <div class="col-12">
        <ul class="nav nav-pills" id="simpul-tabs" role="tablist">
            @foreach(['DARAT', 'LAUT', 'UDARA', 'KERETA'] as $index => $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                        id="tab-{{ strtolower($tab) }}" 
                        data-bs-toggle="pill" 
                        data-bs-target="#content-{{ strtolower($tab) }}" 
                        type="button" 
                        role="tab">
                    SIMPUL {{ $tab }}
                </button>
            </li>
            @endforeach
        </ul>
    </div>
</div>

<!-- TAB CONTENT -->
<div class="tab-content">
    @foreach(['DARAT', 'LAUT', 'UDARA', 'KERETA'] as $index => $tab)
    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="content-{{ strtolower($tab) }}" role="tabpanel">
        
        @php $tabData = $data['tabs'][$tab] ?? null; @endphp
        
        @if($tabData)
            <!-- DAILY CHARTS SECTION -->
            <div class="row">
                @foreach($tabData['daily_charts'] as $cIdx => $chart)
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- Chart -->
                                <div class="col-xl-9 col-lg-8">
                                    <h5 class="card-title mb-3">Pergerakan Harian {{ $chart['label'] }} (Real & Forecast)</h5>
                                    <div id="chart-daily-{{ $tab }}-{{ $cIdx }}" class="chart-container-daily"></div>
                                </div>
                                <!-- KPI -->
                                <div class="col-xl-3 col-lg-4">
                                    <div class="kpi-card">
                                        <div class="mb-3">
                                            <h6 class="text-uppercase text-muted">{{ $chart['label'] }}</h6>
                                        </div>
                                        <div class="mb-2">
                                            <span class="text-real fw-bold" style="font-size: 1.1rem;">Total Real:</span>
                                            <div class="text-real" style="font-size: 1.8rem; font-weight:800;">
                                                {{ number_format($chart['total_real'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-forecast fw-bold">Total Forecast:</span>
                                            <div class="text-forecast" style="font-size: 1.2rem; font-weight:bold;">
                                                {{ number_format($chart['total_forecast'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- TOP 10 SECTION -->
            <div class="row">
                <!-- ORIGIN -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Top 10 Origin Simpul {{ ucfirst(strtolower($tab)) }}</h5>
                            <div id="chart-top-origin-{{ $tab }}" class="chart-container-top"></div>
                        </div>
                    </div>
                </div>
                <!-- DEST -->
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Top 10 Dest Simpul {{ ucfirst(strtolower($tab)) }}</h5>
                            <div id="chart-top-dest-{{ $tab }}" class="chart-container-top"></div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">No Data Available for {{ $tab }}</div>
        @endif

    </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dates = @json($data['dates']);
    const tabsData = @json($data['tabs']);

    // Loop through tabs to init chars
    Object.keys(tabsData).forEach(tab => {
        const tData = tabsData[tab];
        
        // 1. Init Daily Charts
        tData.daily_charts.forEach((chartData, idx) => {
            Highcharts.chart(`chart-daily-${tab}-${idx}`, {
                chart: { type: 'column' },
                title: { text: '' },
                xAxis: { categories: dates, crosshair: true },
                yAxis: { title: { text: 'Jumlah Pergerakan' } },
                colors: ['#1E88E5', '#FBC02D'],
                tooltip: { shared: true },
                plotOptions: {
                    column: {
                        borderRadius: 2,
                        dataLabels: { enabled: true, format: '{point.y:,.0f}', style: { fontSize: '9px' } }
                    }
                },
                series: [
                    { name: 'Real', data: chartData.series_real },
                    { name: 'Forecast', data: chartData.series_forecast }
                ],
                credits: { enabled: false }
            });
        });

        // 2. Init Top Origin
        const topOrgParams = {
            id: `chart-top-origin-${tab}`,
            data: tData.top_origin,
            color: '#1E88E5' // Blue default, or varied
        };
        renderBarChart(topOrgParams);

        // 3. Init Top Dest
        const topDestParams = {
            id: `chart-top-dest-${tab}`,
            data: tData.top_dest,
            color: '#7B1FA2' // Purple/Indigo as in ref
        };
        renderBarChart(topDestParams);
    });

    function renderBarChart(params) {
        // Prepare Categories and Data
        if (!params.data || params.data.length === 0) return;

        const categories = params.data.map(d => d.name);
        // Use a color array to mimic the colorful reference if desired, or single color
        // Ref image 2 has colorful bars (Cyan, Purple, Green, Orange...)
        // Let's generate a palette
        const palette = ['#29b6f6', '#5c6bc0', '#00e676', '#ff7043', '#5c6bc0', '#ab47bc', '#26c6da', '#ef5350', '#ffca28', '#8d6e63'];
        
        const seriesData = params.data.map((d, i) => ({
            y: parseInt(d.total),
            color: palette[i % palette.length]
        }));

        Highcharts.chart(params.id, {
            chart: { type: 'bar' },
            title: { text: '' },
            xAxis: { 
                categories: categories,
                title: { text: null }
            },
            yAxis: {
                min: 0,
                title: { text: 'Total Jumlah', align: 'high' },
                labels: { overflow: 'justify' }
            },
            tooltip: { valueSuffix: '' },
            plotOptions: {
                bar: {
                    dataLabels: { enabled: true, format: '{point.y:,.0f}' },
                    colorByPoint: true // Use the palette in data
                }
            },
            legend: { enabled: false },
            series: [{
                name: 'Total',
                data: seriesData
            }],
            credits: { enabled: false }
        });
    }
});
</script>
@endpush
