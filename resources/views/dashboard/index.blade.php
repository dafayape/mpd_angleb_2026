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

<div class="row mt-4">
    {{-- Left Column: Stats --}}
    <div class="col-xl-4">
        {{-- Total Orang dan Pergerakan --}}
        <div class="card">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 text-primary">Jumlah Orang dan Pergerakan</h5>
            </div>
            <div class="card-body">
                <p class="mb-1 text-muted fw-medium">Total Orang dan Pergerakan</p>
                <div class="mb-4">
                    <h2 class="mb-0 fw-bold text-primary">700.000 <span class="fs-6 text-muted fw-normal">Orang</span></h2>
                    <h2 class="mb-0 fw-bold text-warning">1.845.050 <span class="fs-6 text-muted fw-normal">pergerakan</span></h2>
                </div>
            </div>
        </div>

        {{-- Perbandingan Pergerakan --}}
        <div class="card">
             <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 text-primary">Perbandingan Pergerakan Dengan Tahun Lalu</h5>
            </div>
            <div class="card-body">
                <p class="mb-3 fw-bold">Perbandingan Pergerakan</p>
                
                {{-- Custom Progress/Bar --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-end mb-1">
                         <span class="fw-bold text-white small px-2 py-1 bg-primary rounded">1.500.000</span>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                
                 <div class="mb-3">
                    <div class="d-flex justify-content-end mb-1">
                         <span class="fw-bold text-white small px-2 py-1 bg-warning rounded">1.845.050</span>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                 <div class="mt-3">
                    <span class="badge bg-success-subtle text-success fs-5 fw-bold px-3 py-2">+25%</span>
                </div>

            </div>
        </div>
    </div>

    {{-- Right Column: Chart per OPSEL --}}
    <div class="col-xl-8">
        <div class="card h-100">
             <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title mb-0 text-primary">Tren Jumlah Orang dan Pergerakan per OPSEL</h5>
            </div>
            <div class="card-body">
                <div id="chart-opsel" style="height: 350px;"></div>
                
                <div class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start">
                    <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1 text-info">Analisis Trend Orang</h6>
                        <p class="mb-0 small">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Amet nihil adipisci sunt quasi culpa, tempore expedita similique at quisquam fugiat sed omnis! Minima, expedita consequatur!</p>
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
                <h5 class="card-title mb-0 text-primary">Jumlah Pergerakan Per moda</h5>
            </div>
            <div class="card-body">
                {{-- Placeholder for map/chart --}}
                <div id="chart-moda" class="w-100" style="height: 400px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center;"></div>
                 <p class="text-center fw-bold mt-2">Line Chart Dalam 1 hari memunculkan semua total pergerakan Permoda</p>


                 <div class="alert alert-info bg-info-subtle text-info border-0 mt-3 mb-0 d-flex gap-3 align-items-start">
                    <i class="mdi mdi-information-outline fs-4 mt-1"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1 text-info">Analisis Pergerakan</h6>
                        <p class="mb-0 small">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Amet nihil adipisci sunt quasi culpa, tempore expedita similique at quisquam fugiat sed omnis! Minima, expedita consequatur!</p>
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
        const chartData = @json($chartData);

        // Chart 1: Opsel (Column)
        Highcharts.chart('chart-opsel', {
            chart: {
                type: 'column'
            },
            title: {
                text: ''
            },
            xAxis: {
                categories: chartData.tren_orang_pergerakan_opsel.categories,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: ''
                },
                labels: {
                    formatter: function () {
                        return this.value / 1000 + 'rb';
                    }
                }
            },
            tooltip: { // Basic tooltip
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.1,
                    borderWidth: 0,
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            series: chartData.tren_orang_pergerakan_opsel.series,
            credits: {
                enabled: false
            }
        });

        // Chart 2: Moda (Line)
        Highcharts.chart('chart-moda', {
            title: {
                text: ''
            },
            yAxis: {
                title: {
                    text: 'Jumlah Pergerakan'
                }
            },
            xAxis: {
                categories: chartData.jumlah_pergerakan_per_moda.categories,
                accessibility: {
                    rangeDescription: 'Range: 00:00 to 23:59'
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            plotOptions: {
                series: {
                    label: {
                        connectorAllowed: false
                    },
                }
            },
            series: chartData.jumlah_pergerakan_per_moda.series,
            credits: {
                enabled: false
            },
             responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            }
        });
    });
</script>
@endpush
