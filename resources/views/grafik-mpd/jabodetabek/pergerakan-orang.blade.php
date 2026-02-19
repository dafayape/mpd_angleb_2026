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
                        <li class="breadcrumb-item">{{ $crumb }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- INFO CARDS -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Pergerakan (Real)</span>
                        <h4 class="mb-3">
                            <span class="counter-value" data-target="{{ $data['summary']['movement_real'] }}">0</span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Pergerakan (Forecast)</span>
                        <h4 class="mb-3">
                            <span class="counter-value" data-target="{{ $data['summary']['movement_forecast'] }}">0</span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Orang (Real)</span>
                        <h4 class="mb-3">
                            <span class="counter-value" data-target="{{ $data['summary']['people_real'] }}">0</span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Orang (Forecast)</span>
                        <h4 class="mb-3">
                            <span class="counter-value" data-target="{{ $data['summary']['people_forecast'] }}">0</span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHARTS -->
<div class="row">
    <!-- CHART 1: PERGERAKAN HARIAN -->
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Grafik Pergerakan Harian Jabodetabek (Real vs Forecast)</h4>
                <div id="chart-movement" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>

    <!-- CHART 2: ORANG HARIAN -->
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Grafik Orang Harian Jabodetabek (Real vs Forecast)</h4>
                <div id="chart-people" class="apex-charts" dir="ltr"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dates = @json($data['dates']);
        
        // Common Options
        const commonOptions = {
            chart: {
                height: 350,
                type: 'line',
                zoom: { enabled: false },
                toolbar: { show: false }
            },
            stroke: { width: [3, 3], curve: 'smooth' },
            xaxis: { categories: dates },
            tooltip: {
                y: { formatter: function (val) { return val.toLocaleString('id-ID'); } }
            },
            grid: { borderColor: '#f1f1f1' },
            legend: { position: 'top' }
        };

        // Render Movement Chart
        const optionsMov = {
            ...commonOptions,
            series: @json($data['chart_movement']),
            colors: ['#2caffe', '#fec107']
        };
        new ApexCharts(document.querySelector("#chart-movement"), optionsMov).render();

        // Render People Chart
        const optionsPpl = {
            ...commonOptions,
            series: @json($data['chart_people']),
            colors: ['#2caffe', '#fec107']
        };
        new ApexCharts(document.querySelector("#chart-people"), optionsPpl).render();
    });
</script>
@endpush
