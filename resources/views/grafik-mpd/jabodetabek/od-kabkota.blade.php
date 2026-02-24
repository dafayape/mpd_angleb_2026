@extends('layout.app')

@section('title', $title)

@push('css')
    <style>
        .highcharts-figure,
        .highcharts-data-table table {
            min-width: 310px;
            max-width: 100%;
            margin: 1em auto;
        }

        #sankey-container {
            height: 700px;
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
                        @foreach ($breadcrumb as $crumb)
                            <li class="breadcrumb-item">{{ $crumb }}</li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- SANKEY DIAGRAM (LEFT) -->
        <div class="col-xl-7 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">O-D Kabupaten/Kota Real (Asal &rarr; Tujuan)</h4>
                    <div id="sankey-container"></div>
                </div>
            </div>
        </div>

        <!-- TABLES (RIGHT) -->
        <div class="col-xl-5 col-lg-12">

            <!-- TOP ORIGIN -->
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title mb-3">10 Besar Asal (Favorit)</h4>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Kab/Kota Asal</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">%</th>
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
                                        <td colspan="4" class="text-center py-3 text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TOP DESTINATION -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">10 Besar Tujuan (Favorit)</h4>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Kab/Kota Tujuan</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">%</th>
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
                                        <td colspan="4" class="text-center py-3 text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- TOP INTER PROVINSI TUJUAN --}}
            @if (isset($data['top_inter_provinsi']) && count($data['top_inter_provinsi']) > 0)
                <div class="card mt-3">
                    <div class="card-body">
                        <h4 class="card-title mb-3"><i class="mdi mdi-map-marker-distance me-1 text-warning"></i> 10 Besar
                            Provinsi Tujuan (Inter Jabo)</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Provinsi Tujuan</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['top_inter_provinsi'] as $index => $row)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->prov_name }}</td>
                                            <td class="text-end fw-bold">
                                                {{ number_format($row->total_volume, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- DAILY CHARTS — only if data available -->
    @if (!empty($data['chart_internal_mov']))
        <div class="row mt-4">
            <div class="col-12">
                <h4 class="card-title mb-4">AKUMULASI PERGERAKAN HARIAN TOTAL (INTER JABODETABEK & OUTBOUND)</h4>
            </div>

            <!-- INTERNAL FLOW -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Internal Jabodetabek - Pergerakan Harian</h4>
                        <div id="chart-internal-mov" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Internal Jabodetabek - Orang Harian</h4>
                        <div id="chart-internal-ppl" style="height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- OUTBOUND FLOW -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Keluar Jabodetabek - Pergerakan Harian</h4>
                        <div id="chart-outbound-mov" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Keluar Jabodetabek - Orang Harian</h4>
                        <div id="chart-outbound-ppl" style="height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/sankey.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const data = @json($data);
            const sankeyData = data.sankey || [];
            const dates = data.dates || [];

            // 1. Sankey Chart
            if (sankeyData.length > 0) {
                Highcharts.chart('sankey-container', {
                    title: {
                        text: ''
                    },
                    accessibility: {
                        point: {
                            valueDescriptionFormat: '{point.from} to {point.to}, {point.weight}.'
                        }
                    },
                    series: [{
                        keys: ['from', 'to', 'weight'],
                        data: sankeyData,
                        type: 'sankey',
                        name: 'Pergerakan O-D',
                        dataLabels: {
                            style: {
                                color: '#1a1a1a',
                                textOutline: 'none',
                                fontSize: '10px'
                            }
                        }
                    }],
                    tooltip: {
                        headerFormat: '',
                        pointFormat: '<b>{point.from}</b> &rarr; <b>{point.to}</b><br/>Total: <b>{point.weight:,.0f}</b>'
                    },
                    credits: {
                        enabled: false
                    }
                });
            }

            // 2. Common Options for Daily Charts
            const commonOptions = {
                chart: {
                    type: 'column'
                },
                title: {
                    text: undefined
                },
                xAxis: {
                    categories: dates,
                    crosshair: true,
                    labels: {
                        formatter: function() {
                            if (typeof this.value !== 'string') return this.value;
                            const parts = this.value.split('-');
                            if (parts.length === 3) return parts[2] + '-' + parts[1] + '-' + parts[0];
                            return this.value;
                        }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: null
                    },
                    labels: {
                        formatter: function() {
                            if (this.value >= 1000000) return (this.value / 1000000).toFixed(1) + 'M';
                            if (this.value >= 1000) return (this.value / 1000).toFixed(0) + 'k';
                            return this.value;
                        }
                    }
                },
                tooltip: {
                    shared: true
                },
                plotOptions: {
                    column: {
                        borderRadius: 2,
                        dataLabels: {
                            enabled: false
                        }
                    }
                },
                credits: {
                    enabled: false
                },
                legend: {
                    enabled: false
                }
            };

            // 3. Render Daily Charts (only if data exists)
            const chartConfigs = {
                'chart-internal-mov': {
                    key: 'chart_internal_mov',
                    color: '#2caffe'
                },
                'chart-internal-ppl': {
                    key: 'chart_internal_ppl',
                    color: '#6610f2'
                },
                'chart-outbound-mov': {
                    key: 'chart_outbound_mov',
                    color: '#fec107'
                },
                'chart-outbound-ppl': {
                    key: 'chart_outbound_ppl',
                    color: '#ff3d60'
                }
            };

            for (const [id, cfg] of Object.entries(chartConfigs)) {
                if (data[cfg.key] && document.getElementById(id)) {
                    Highcharts.chart(id, Highcharts.merge(commonOptions, {
                        series: [{
                            name: 'Total',
                            data: data[cfg.key],
                            color: cfg.color
                        }]
                    }));
                }
            }
        });
    </script>
@endpush
