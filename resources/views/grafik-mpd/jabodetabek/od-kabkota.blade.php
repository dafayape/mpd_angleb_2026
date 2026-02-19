@extends('layout.app')

@section('title', $title)

@push('css')
<style>
    .highcharts-figure, .highcharts-data-table table {
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
                    @foreach($breadcrumb as $crumb)
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

    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/sankey.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sankeyData = @json($data['sankey']);

        // Limit Sankey links if too many (performance/clutter)
        // Sort by weight desc, take top 50?
        // sankeyData.sort((a,b) => b.weight - a.weight);
        // const limitedSankey = sankeyData.slice(0, 50);

        Highcharts.chart('sankey-container', {
            title: { text: '' },
            accessibility: {
                point: { valueDescriptionFormat: '{point.from} to {point.to}, {point.weight}.' }
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
            credits: { enabled: false }
        });
    });
</script>
@endpush
