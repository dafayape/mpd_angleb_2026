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
            height: 800px;
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
                    <h4 class="card-title mb-4">O-D Provinsi Real (Asal &rarr; Tujuan)</h4>
                    <div id="sankey-container"></div>
                </div>
            </div>
        </div>

        <!-- TABLES (RIGHT) -->
        <div class="col-xl-5 col-lg-12">

            <!-- TOP ORIGIN -->
            <div class="card mb-3">
                <div class="card-body">
                    <h4 class="card-title mb-3">10 Besar Provinsi Asal (Favorit)</h4>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Kode</th>
                                    <th>Provinsi Asal</th>
                                    <th class="text-end">Total Pergerakan</th>
                                    <th class="text-end">Persen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['top_origin'] as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row['code'] }}</td>
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

            <!-- TOP DESTINATION -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">10 Besar Provinsi Tujuan (Favorit)</h4>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Kode</th>
                                    <th>Provinsi Tujuan</th>
                                    <th class="text-end">Total Pergerakan</th>
                                    <th class="text-end">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['top_dest'] as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row['code'] }}</td>
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

    {{-- DESIRE LINE MAP (F3) --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4"><i class="mdi mdi-map-marker-path me-1 text-primary"></i> Desire Line Map —
                        Arus Pergerakan Antar Provinsi</h4>
                    <div id="desire-line-map" style="height: 500px; border-radius: 8px;"></div>
                    <p class="text-muted small mt-2 fst-italic"><i class="mdi mdi-information-outline me-1"></i> Garis
                        menunjukkan 20 rute OD teratas. Ketebalan garis proporsional terhadap volume pergerakan.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #desire-line-map {
            z-index: 1;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/sankey.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === SANKEY CHART ===
            const sankeyData = @json($data['sankey']);
            Highcharts.chart('sankey-container', {
                title: {
                    text: ''
                },
                accessibility: {
                    point: {
                        valueDescriptionFormat: '{index}. {point.from} to {point.to}, {point.weight}.'
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
                            textOutline: 'none'
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

            // === DESIRE LINE MAP ===
            const provCoords = {
                'ACEH': [-4.695, 96.749],
                'SUMATERA UTARA': [2.116, 99.546],
                'SUMATERA BARAT': [-0.740, 100.800],
                'RIAU': [0.293, 101.768],
                'JAMBI': [-1.615, 103.613],
                'SUMATERA SELATAN': [-3.319, 104.914],
                'BENGKULU': [-3.800, 102.265],
                'LAMPUNG': [-4.871, 104.983],
                'KEPULAUAN BANGKA BELITUNG': [-2.741, 106.440],
                'KEPULAUAN RIAU': [3.946, 108.143],
                'DKI JAKARTA': [-6.208, 106.846],
                'JAWA BARAT': [-6.889, 107.609],
                'JAWA TENGAH': [-7.151, 110.140],
                'DI YOGYAKARTA': [-7.797, 110.371],
                'JAWA TIMUR': [-7.536, 112.237],
                'BANTEN': [-6.405, 106.064],
                'BALI': [-8.350, 115.088],
                'NUSA TENGGARA BARAT': [-8.650, 117.362],
                'NUSA TENGGARA TIMUR': [-8.658, 121.079],
                'KALIMANTAN BARAT': [-0.278, 111.475],
                'KALIMANTAN TENGAH': [-1.681, 113.382],
                'KALIMANTAN SELATAN': [-3.092, 115.283],
                'KALIMANTAN TIMUR': [1.693, 116.419],
                'KALIMANTAN UTARA': [3.073, 116.604],
                'SULAWESI UTARA': [0.625, 123.975],
                'SULAWESI TENGAH': [-1.430, 121.445],
                'SULAWESI SELATAN': [-3.669, 119.974],
                'SULAWESI TENGGARA': [-4.145, 122.175],
                'GORONTALO': [0.696, 122.447],
                'SULAWESI BARAT': [-2.844, 119.232],
                'MALUKU': [-3.239, 130.145],
                'MALUKU UTARA': [1.570, 127.809],
                'PAPUA BARAT': [-1.337, 133.174],
                'PAPUA': [-4.269, 138.081],
                'PAPUA SELATAN': [-6.729, 139.997],
                'PAPUA TENGAH': [-3.590, 136.260],
                'PAPUA PEGUNUNGAN': [-4.081, 138.504],
                'PAPUA BARAT DAYA': [-1.959, 132.298]
            };

            const map = L.map('desire-line-map').setView([-2.5, 118], 5);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);

            // Get top 20 OD pairs from sankey
            const top20 = sankeyData
                .sort((a, b) => (b[2] || b.weight) - (a[2] || a.weight))
                .slice(0, 20);

            if (top20.length > 0) {
                const maxWeight = top20[0][2] || top20[0].weight;

                top20.forEach(od => {
                    const from = (od[0] || od.from || '').toUpperCase();
                    const to = (od[1] || od.to || '').toUpperCase();
                    const weight = od[2] || od.weight;

                    const fromCoords = provCoords[from];
                    const toCoords = provCoords[to];

                    if (fromCoords && toCoords) {
                        const width = Math.max(1, Math.round((weight / maxWeight) * 8));
                        const opacity = Math.max(0.3, weight / maxWeight);

                        L.polyline([fromCoords, toCoords], {
                                color: '#e74c3c',
                                weight: width,
                                opacity: opacity
                            }).addTo(map)
                            .bindPopup(
                                `<b>${od[0] || od.from}</b> → <b>${od[1] || od.to}</b><br>Total: <b>${weight.toLocaleString('id-ID')}</b>`
                                );

                        // Origin marker
                        L.circleMarker(fromCoords, {
                                radius: 4,
                                fillColor: '#3498db',
                                fillOpacity: 0.8,
                                color: '#fff',
                                weight: 1
                            })
                            .addTo(map).bindTooltip(od[0] || od.from, {
                                direction: 'top',
                                offset: [0, -5]
                            });
                        // Dest marker
                        L.circleMarker(toCoords, {
                                radius: 4,
                                fillColor: '#e74c3c',
                                fillOpacity: 0.8,
                                color: '#fff',
                                weight: 1
                            })
                            .addTo(map).bindTooltip(od[1] || od.to, {
                                direction: 'top',
                                offset: [0, -5]
                            });
                    }
                });
            }
        });
    </script>
@endpush
