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
<div class="row">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Latar Belakang MPD</h5>
                <hr class="mt-0 mb-3">
                <p class="text-muted mb-0">Periode Lebaran menjadi salah satu momen dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan signifikan pergerakan antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada tanggal 21 dan 22 Maret 2026 sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal 19 Maret 2026.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Metodelogi MPD</h5>
                <hr class="mt-0 mb-3">
                <p class="text-muted mb-0">Periode Lebaran menjadi salah satu momen dengan tingkat mobilitas tinggi di Indonesia, biasanya terjadi lonjakan signifikan pergerakan antarkota maupun lokal perkotaan. Lebaran tahun 2026 diperkirakan jatuh pada tanggal 21 dan 22 Maret 2026 sehingga berhimpitan dengan Hari Raya Nyepi pada tanggal 19 Maret 2026.</p>
            </div>
        </div>
    </div>
</div>

    {{-- Left Column: Stats --}}
    <div class="col-xl-4">
        {{-- Total Orang dan Pergerakan --}}
        <div class="card">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 text-primary">Total Pergerakan (Aktual)</h5>
            </div>
            <div class="card-body">
                <p class="mb-1 text-muted fw-medium">Total Akumulasi (13-29 Mar)</p>
                <div class="mb-4">
                    <h2 class="mb-0 fw-bold text-primary">{{ number_format($total_real, 0, ',', '.') }} <span class="fs-6 text-muted fw-normal">Pergerakan</span></h2>
                    <small class="text-muted">Target Forecast: {{ number_format($total_forecast, 0, ',', '.') }}</small>
                </div>
                <div class="alert alert-success bg-success-subtle text-success border-0 mb-0">
                    <i class="mdi mdi-check-circle-outline me-1"></i> {!! $analysis['general'] !!}
                </div>
            </div>
        </div>

        {{-- Capaian Forecast --}}
        <div class="card mt-3">
             <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 text-primary">Capaian Terhadap Forecast</h5>
            </div>
            <div class="card-body">
                <p class="mb-3 fw-bold">Progress Capaian</p>
                
                {{-- Custom Progress/Bar --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                         <span class="text-muted small">Aktual</span>
                         <span class="fw-bold text-primary small">{{ number_format($total_real, 0, ',', '.') }}</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min($persen_capaian, 100) }}%;" aria-valuenow="{{ $persen_capaian }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                 <div class="mt-3 text-center">
                    <span class="badge {{ $persen_capaian >= 100 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} fs-5 fw-bold px-3 py-2">
                        {{ number_format($persen_capaian, 1) }}%
                    </span>
                    <p class="text-muted small mt-2 mb-0">dari total target forecast</p>
                </div>

            </div>
        </div>
    </div>

    {{-- Right Column: Chart per OPSEL --}}
    <div class="col-xl-8">
        <div class="card h-100">
             <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 text-primary">Perbandingan Aktual vs Forecast per Operator</h5>
            </div>
            <div class="card-body">
                <div id="chart-opsel" style="height: 350px;"></div>
                
                <div class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start">
                    <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1 text-info">Analisis Operator</h6>
                        <p class="mb-0 small">{!! $analysis['opsel'] !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bottom Chart: Per Moda --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
             <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 text-primary">Tren Pergerakan Harian per Moda</h5>
            </div>
            <div class="card-body">
                {{-- Chart --}}
                <div id="chart-moda" class="w-100" style="height: 400px;"></div>
                 <p class="text-center fw-bold mt-2 text-muted small">Grafik menunjukkan tren pergerakan harian untuk setiap moda transportasi (13 Mar - 29 Mar 2026)</p>


                 <div class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start">
                    <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1 text-info">Analisis Moda Transportasi</h6>
                        <p class="mb-0 small">{!! $analysis['moda'] !!}</p>
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
