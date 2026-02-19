@extends('layout.app')

@section('title', $title)

@push('css')
<style>
    .nav-pills .nav-link { font-weight: 600; border-radius: 8px; padding: 10px 20px; color: #495057; background-color: #f8f9fa; margin-right: 10px; }
    .nav-pills .nav-link.active { background-color: #0d6efd; color: white; }
    .kpi-card { background: #f8f9fa; border-radius: 10px; padding: 20px; text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; }
    .text-real { color: #1E88E5; }
    .kpi-icon { font-size: 3rem; margin-bottom: 10px; opacity: 0.5; }
    .text-forecast { color: #FBC02D; }
    .chart-container-daily { height: 350px; }
    .chart-container-top { height: 400px; }
    .section-title { border-left: 5px solid #0d6efd; padding-left: 10px; margin-bottom: 20px; font-weight: 700; color: #343a40; text-transform: uppercase;}
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
        
        @if($tabData && isset($tabData['sections']))
            @foreach($tabData['sections'] as $sIdx => $section)
            
            <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
                <div class="card-body">
                    <h5 class="section-title">{{ $section['title'] }}</h5>
                    <!-- Subtitle / Code -->
                    @if(isset($section['subtitle']))
                        <p class="text-muted mb-4" style="margin-top: -15px; margin-left: 15px; font-size: 0.9rem;">{{ $section['subtitle'] }}</p>
                    @endif
                    
                    <!-- 1. DAILY CHARTS + KPI -->
                    <div class="row">
                        @foreach($section['daily_charts'] as $cIdx => $chart)
                        <div class="col-12 mb-4">
                            <div class="row">
                                <div class="col-xl-9 col-lg-8">
                                    <h6 class="card-title text-center mb-3">Pergerakan Harian {{ $chart['label'] }} (Real & Forecast)</h6>
                                    <div id="chart-daily-{{ $tab }}-{{ $sIdx }}-{{ $cIdx }}" class="chart-container-daily"></div>
                                </div>
                                <div class="col-xl-3 col-lg-4">
                                    <div class="kpi-card">
                                        <!-- Dynamic Icon based on Title/Label -->
                                        @php
                                            $icon = 'mdi-chart-bar';
                                            $lowerLabel = strtolower($chart['label']);
                                            if(str_contains($lowerLabel, 'bus')) $icon = 'mdi-bus';
                                            elseif(str_contains($lowerLabel, 'penyeberangan')) $icon = 'mdi-ferry';
                                            elseif(str_contains($lowerLabel, 'laut')) $icon = 'mdi-anchor';
                                            elseif(str_contains($lowerLabel, 'udara')) $icon = 'mdi-airplane';
                                            elseif(str_contains($lowerLabel, 'ka')) $icon = 'mdi-train';
                                        @endphp
                                        <div class="kpi-icon"><i class="mdi {{ $icon }} text-primary"></i></div>
                                        <h6 class="text-uppercase text-muted mb-3">{{ $chart['label'] }}</h6>
                                        <div class="mb-2">
                                            <span class="text-real fw-bold">Total Real:</span>
                                            <div class="text-real h2 fw-bold">{{ number_format($chart['total_real'], 0, ',', '.') }}</div>
                                        </div>
                                        <div>
                                            <span class="text-forecast fw-bold">Total Forecast:</span>
                                            <div class="text-forecast h4 fw-bold">{{ number_format($chart['total_forecast'], 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
    
                    <hr class="my-4" style="border-top: 1px dashed #ddd;">
    
                    <!-- 2. TOP 10 ORIGIN & DEST -->
                    @if(!empty($section['top_origin']))
                    <div class="row">
                        <div class="col-xl-6">
                            <h6 class="card-title text-center mb-3">10 BESAR {{ strtoupper($section['title']) }} - ASAL</h6>
                            <div class="text-center text-muted small mb-2">{{ $section['subtitle'] ?? '' }}</div>
                            <div id="chart-top-origin-{{ $tab }}-{{ $sIdx }}" class="chart-container-top"></div>
                        </div>
                        <div class="col-xl-6">
                            <h6 class="card-title text-center mb-3">10 BESAR {{ strtoupper($section['title']) }} - TUJUAN</h6>
                            <div class="text-center text-muted small mb-2">{{ $section['subtitle'] ?? '' }}</div>
                            <div id="chart-top-dest-{{ $tab }}-{{ $sIdx }}" class="chart-container-top"></div>
                        </div>
                    </div>
                    @endif

                    <!-- 3. TOP OD ROUTE -->
                    @if(!empty($section['top_od']))
                    <div class="row mt-4">
                        <div class="col-12">
                             <h6 class="card-title text-center mb-3">O-D SIMPUL {{ strtoupper($section['title']) }}</h6>
                             <div class="text-center text-muted small mb-2">{{ $section['subtitle'] ?? '' }}</div>
                             <div id="chart-top-od-{{ $tab }}-{{ $sIdx }}" class="chart-container-top" style="height: 500px"></div>
                        </div>
                    </div>
                    @endif
                    
                </div>
            </div>

            @endforeach
        @else
            <div class="alert alert-info">Memuat Data... atau Data Kosong.</div>
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

    const colors = ['#29b6f6', '#5c6bc0', '#00e676', '#ff7043', '#8d6e63', '#ab47bc', '#ef5350', '#ffca28', '#26c6da', '#78909c'];

    Object.keys(tabsData).forEach(tab => {
        const tData = tabsData[tab];
        if(!tData.sections) return;

        tData.sections.forEach((section, sIdx) => {
            
            // 1. Daily Charts
            section.daily_charts.forEach((chartData, cIdx) => {
                Highcharts.chart(`chart-daily-${tab}-${sIdx}-${cIdx}`, {
                    chart: { type: 'column' },
                    title: { text: '' },
                    xAxis: { categories: dates, crosshair: true },
                    yAxis: { title: { text: 'Jumlah' } },
                    colors: ['#1E88E5', '#FBC02D'],
                    tooltip: { shared: true },
                    plotOptions: { column: { borderRadius: 2, dataLabels: { enabled: true, format: '{point.y:,.0f}', style: { fontSize: '9px' } } } },
                    series: [{ name: 'Real', data: chartData.series_real }, { name: 'Forecast', data: chartData.series_forecast }],
                    credits: { enabled: false }
                });
            });

            // 2. Top Origin
            if(section.top_origin && section.top_origin.length > 0) {
                renderBarChart(`chart-top-origin-${tab}-${sIdx}`, section.top_origin, colors);
            }

            // 3. Top Dest
            if(section.top_dest && section.top_dest.length > 0) {
                renderBarChart(`chart-top-dest-${tab}-${sIdx}`, section.top_dest, colors);
            }

            // 4. Top OD
            if(section.top_od && section.top_od.length > 0) {
                renderBarChart(`chart-top-od-${tab}-${sIdx}`, section.top_od, colors);
            }
        });
    });

    function renderBarChart(id, data, palette) {
        const categories = data.map(d => d.name);
        const seriesData = data.map((d, i) => ({ y: parseInt(d.total), color: palette[i % palette.length] }));

        Highcharts.chart(id, {
            chart: { type: 'bar' },
            title: { text: '' },
            xAxis: { categories: categories, title: { text: null } },
            yAxis: { min: 0, title: { text: 'Total Jumlah', align: 'high' } },
            plotOptions: { 
                bar: { 
                    borderRadius: 3,
                    pointWidth: 16, 
                    groupPadding: 0.35,
                    dataLabels: { enabled: true, format: '{point.y:,.0f}', style: { fontSize: '10px' } }, 
                    colorByPoint: true 
                } 
            },
            legend: { enabled: false },
            series: [{ name: 'Total', data: seriesData }],
            credits: { enabled: false }
        });
    }
});
</script>
@endpush
