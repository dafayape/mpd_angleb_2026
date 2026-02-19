@extends('layout.app')

@section('title', 'Dashboard Utama')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Dashboard Utama</h4>
        </div>
    </div>
</div>

{{-- Highlight Alert --}}
<div class="alert alert-info border-0 bg-info-subtle text-info" role="alert">
    <div class="d-flex align-items-start">
        <div class="flex-grow-1">
            <h5 class="alert-heading font-size-14 text-info"><i class="mdi mdi-information-outline me-1"></i> Highlight Survei Angkutan Lebaran 2026</h5>
            <p class="mb-0 font-size-13">Dalam rangka persiapan dan pelaksanaan layanan Angkutan Lebaran (Angleb) tahun 2026, Badan Kebijakan Transportasi melaksanakan survei online pada periode <strong>15 - 30 Januari 2026</strong> untuk mengidentifikasi preferensi dan mengukur persepsi masyarakat yang akan melakukan perjalanan. Survei tersebut bekerja sama dengan <strong>LAPI ITB, BPS, dan Kementerian Komdigi.</strong></p>
        </div>
    </div>
</div>

{{-- Info Cards --}}
<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Latar Belakang MPD</h5>
                <hr class="mt-0 mb-3 opacity-25">
                <p class="text-muted mb-0 small" style="text-align: justify;">Periode Lebaran menjadi salah satu momen dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan signifikan pergerakan antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada tanggal 21 dan 22 Maret 2026 sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal 19 Maret 2026.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Metodelogi MPD</h5>
                <hr class="mt-0 mb-3 opacity-25">
                <p class="text-muted mb-0 small" style="text-align: justify;">Periode Lebaran menjadi salah satu momen dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan signifikan pergerakan antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada tanggal 21 dan 22 Maret 2026 sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal 19 Maret 2026.</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    {{-- Left Column: Stats --}}
    <div class="col-xl-4 d-flex flex-column gap-4">
        {{-- Total Orang dan Pergerakan --}}
        <div class="card shadow-sm mb-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title mb-0 text-primary fw-bold">Total Pergerakan (Aktual)</h5>
            </div>
            <div class="card-body pb-4">
                <p class="mb-2 text-muted fw-medium small">Total Akumulasi (13-29 Mar)</p>
                <div class="mb-3">
                    <h2 class="mb-0 fw-bold text-primary display-6">{{ number_format($total_real, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">Pergerakan</span></h2>
                    <small class="text-muted">Target Forecast: {{ number_format($total_forecast, 0, ',', '.') }}</small>
                </div>
                <div class="alert alert-success bg-success-subtle text-success border-0 mb-0 py-2 px-3">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-check-circle-outline me-2 fs-5"></i>
                        <span class="small fst-italic">{!! strip_tags($analysis['general']) !!}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Perbandingan Pergerakan --}}
        <div class="card shadow-sm mb-0 flex-grow-1">
             <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title mb-0 text-primary fw-bold">Perbandingan Pergerakan Dengan Tahun Lalu</h5>
            </div>
            <div class="card-body">
                <p class="mb-4 fw-bold small text-uppercase text-muted">Perbandingan Pergerakan</p>
                
                @php
                    $maxValue = max($total_real, $total_forecast);
                    $widthReal = $maxValue > 0 ? ($total_real / $maxValue) * 100 : 0;
                    $widthForecast = $maxValue > 0 ? ($total_forecast / $maxValue) * 100 : 0;
                @endphp

                {{-- Horizontal Bar: Forecast (Blue) --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-1">
                         <div class="flex-grow-1 bg-light rounded-pill" style="height: 25px; overflow: hidden;">
                             <div class="bg-primary h-100 d-flex align-items-center justify-content-end pe-2 text-white fw-bold small" style="width: {{ $widthForecast }}%;">
                             </div>
                         </div>
                         <div class="ms-3 fw-bold text-primary text-end" style="width: 100px;">{{ number_format($total_forecast, 0, ',', '.') }}</div>
                    </div>
                    <small class="text-muted d-block ms-1" style="font-size: 11px;">Target / Forecast</small>
                </div>

                {{-- Horizontal Bar: Real (Yellow) --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-1">
                         <div class="flex-grow-1 bg-light rounded-pill" style="height: 25px; overflow: hidden;">
                             <div class="bg-warning h-100 d-flex align-items-center justify-content-end pe-2 text-white fw-bold small" style="width: {{ $widthReal }}%;">
                             </div>
                         </div>
                         <div class="ms-3 fw-bold text-warning text-end" style="width: 100px;">{{ number_format($total_real, 0, ',', '.') }}</div>
                    </div>
                    <small class="text-muted d-block ms-1" style="font-size: 11px;">Aktual (Real)</small>
                </div>
                
                 <div class="mt-auto">
                    <span class="badge {{ $persen_capaian >= 100 ? 'bg-success-subtle text-success' : 'bg-success-subtle text-success' }} fs-5 fw-bold px-3 py-2">
                        +{{ number_format($persen_capaian - 100, 1) }}%
                    </span>
                    <span class="text-muted ms-2 small">dari target forecast</span>
                </div>

            </div>
        </div>
    </div>

    {{-- Right Column: Chart per OPSEL --}}
    <div class="col-xl-8">
        <div class="card h-100 shadow-sm">
             <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title mb-0 text-primary fw-bold">Tren Jumlah Orang dan Pergerakan per OPSEL</h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                    <div id="chart-opsel" class="w-100" style="height: 380px;"></div>
                </div>
                
                <div class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start rounded-3">
                    <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1 text-info font-size-14">Analisis Trend Orang</h6>
                        <p class="mb-0 small" style="line-height: 1.5;">{!! $analysis['opsel'] !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bottom Chart: Per Moda --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
             <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title mb-0 text-primary fw-bold">Tren Pergerakan Harian per Moda</h5>
            </div>
            <div class="card-body">
                {{-- Chart --}}
                <div id="chart-moda" class="w-100" style="height: 400px;"></div>
                 <p class="text-center fw-bold mt-2 text-muted small fst-italic">Grafik menunjukkan tren pergerakan harian untuk setiap moda transportasi (13 Mar - 29 Mar 2026)</p>


                 <div class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start rounded-3">
                    <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1 text-info font-size-14">Analisis Moda Transportasi</h6>
                        <p class="mb-0 small" style="line-height: 1.5;">{!! $analysis['moda'] !!}</p>
                    </div>
                </div>
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
        const chartOpsel = @json($chart_opsel);
        const chartModa = @json($chart_moda);

        // Chart 1: Opsel (Column: Real vs Forecast)
        Highcharts.chart('chart-opsel', {
            chart: {
                type: 'column'
            },
            title: {
                text: ''
            },
            xAxis: {
                categories: chartOpsel.categories,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Jumlah Pergerakan'
                },
                labels: {
                    formatter: function () {
                        return this.value / 1000 + 'rb';
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
                    pointPadding: 0.1,
                    borderWidth: 0,
                    borderRadius: 5
                }
            },
            series: chartOpsel.series,
            credits: { enabled: false }
        });

        // Chart 2: Moda (Line: Daily Trend)
        Highcharts.chart('chart-moda', {
            chart: {
                type: 'spline' // Smoother line
            },
            title: {
                text: ''
            },
            yAxis: {
                title: {
                    text: 'Jumlah Pergerakan'
                },
                labels: {
                    formatter: function () {
                        return this.value / 1000 + 'rb';
                    }
                }
            },
            xAxis: {
                categories: chartModa.categories,
                accessibility: {
                    rangeDescription: 'Range: 13 Mar to 29 Mar'
                }
            },
            legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'bottom'
            },
            plotOptions: {
                series: {
                    label: {
                        connectorAllowed: false
                    },
                    marker: {
                        enabled: true,
                        radius: 4
                    }
                }
            },
            tooltip: {
                shared: true,
                valueValues: true,
                valueDecimals: 0,
                valueSuffix: ' pergerakan'
            },
            series: chartModa.series,
            credits: { enabled: false }
        });
    });
</script>
@endpush
