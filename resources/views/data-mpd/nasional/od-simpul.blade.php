@extends('layout.app')

@section('title', $title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ $title }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        @foreach ($breadcrumb as $crumb)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                {{ $crumb }}
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @push('css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            .table th,
            .table td {
                vertical-align: middle;
                font-size: 11px;
            }

            .hoverTable tbody tr:hover td {
                background-color: #e9f5ff !important;
            }

            .bg-soft-primary {
                background-color: rgba(85, 110, 230, 0.1) !important;
            }

            .section-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 30px;
                height: 30px;
                background-color: #007bff;
                color: white;
                border-radius: 50%;
                font-weight: bold;
                font-size: 0.9rem;
                margin-right: 1rem;
                flex-shrink: 0;
            }

            .content-card {
                border: none;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                margin-bottom: 2rem;
            }

            #sankey-container {
                height: 650px;
            }

            #desire-line-map {
                z-index: 1;
            }

            .table-custom th {
                background-color: #c8d9e8 !important;
                font-weight: 600;
                font-size: 12px;
                border-color: #999;
            }

            .table-custom td {
                font-size: 12px;
                border-color: #ccc;
            }
        </style>
    @endpush

    <!-- 01 O-D PROVINSI ASAL & DESIRE LINE -->
    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">01</span>
                    <h5 class="fw-bold text-navy mb-0">O-D Provinsi Asal (10 besar provinsi asal favorit Nasional) & Desire
                        line</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">

                    <div class="row align-items-stretch">
                        <!-- Left: Sankey -->
                        <div class="col-xl-7 col-lg-12 mb-4 mb-xl-0 d-flex flex-column">
                            <div class="border rounded p-3 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <div id="sankey-container" class="w-100"></div>
                            </div>
                        </div>

                        <!-- Right: Table -->
                        <div class="col-xl-5 col-lg-12 d-flex flex-column">
                            <div class="border rounded p-3 mb-4 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <h5 class="text-center fw-bold text-primary mb-3 mt-2" style="font-size: 1.25rem;">10 Besar
                                    Provinsi Asal Favorit Nasional</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;">Rank</th>
                                                <th>Kode</th>
                                                <th class="text-start">Provinsi Asal</th>
                                                <th class="text-end">Total Pergerakan</th>
                                                <th class="text-end">Persen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($top_origin as $index => $row)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $row['code'] }}</td>
                                                    <td class="text-start">{{ $row['name'] }}</td>
                                                    <td class="text-end fw-bold">
                                                        {{ number_format($row['total'], 0, ',', '.') }}</td>
                                                    <td class="text-end">{{ number_format($row['pct'], 2, ',', '.') }} %
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3 text-muted">Belum ada data
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Summary Box -->
                            <div class="rounded p-4 d-flex align-items-center justify-content-center text-center"
                                style="background-color: #e9f0f7; border: 1px solid #d2e0ed; min-height:100px;">
                                @if (count($top_origin) > 0)
                                    <span class="text-navy fw-medium" style="font-size: 1.05rem; line-height:1.5;">
                                        Pada periode <strong>13 Maret 2026 s/d 30 Maret 2026</strong>, <strong
                                            style="font-size:1.15rem; color:#1e3a8a;">Provinsi
                                            {{ ucwords(strtolower($top_origin[0]['name'])) }}</strong> merupakan Provinsi
                                        Asal dengan jumlah pergerakan terbanyak, yaitu sekitar <strong
                                            style="font-size:1.15rem;">{{ number_format($top_origin[0]['total'] / 1000000, 2, ',', '.') }}
                                            juta pergerakan</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Map Desire Line -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="border rounded p-3"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <div id="desire-line-map" style="height: 500px; border-radius: 8px;"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/sankey.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet-polylinedecorator@1.6.0/dist/leaflet.polylineDecorator.min.js">
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    once: true,
                    offset: 50,
                    duration: 600
                });
            }

            // === SANKEY CHART ===
            const sankeyData = @json($sankey);
            Highcharts.chart('sankey-container', {
                title: {
                    text: ''
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

            const map = L.map('desire-line-map', {
                scrollWheelZoom: false
            }).setView([-2.5, 118], 5);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);

            const top20 = sankeyData.sort((a, b) => (b[2] || b.weight) - (a[2] || a.weight)).slice(0, 20);

            if (top20.length > 0) {
                const maxWeight = top20[0][2] || top20[0].weight;
                top20.forEach(od => {
                    const fromRaw = (od[0] || od.from || '').toUpperCase();
                    const toRaw = (od[1] || od.to || '').toUpperCase();
                    const weight = od[2] || od.weight;

                    const fromClean = fromRaw.replace('(O) ', '').trim();
                    const toClean = toRaw.replace('(D) ', '').trim();

                    if (provCoords[fromClean] && provCoords[toClean]) {
                        const width = Math.max(1, Math.round((weight / maxWeight) * 8));
                        const opacity = Math.max(0.3, weight / maxWeight);

                        const polyline = L.polyline([provCoords[fromClean], provCoords[toClean]], {
                                color: '#e74c3c',
                                weight: width,
                                opacity: opacity
                            })
                            .addTo(map).bindPopup(
                                `<b>${od[0] || od.from}</b> → <b>${od[1] || od.to}</b><br>Total: <b>${weight.toLocaleString('id-ID')}</b>`
                            );

                        L.polylineDecorator(polyline, {
                            patterns: [{
                                offset: '100%',
                                repeat: 0,
                                symbol: L.Symbol.arrowHead({
                                    pixelSize: 10 + width,
                                    polygon: false,
                                    pathOptions: {
                                        stroke: true,
                                        color: '#e74c3c',
                                        weight: width + 1
                                    }
                                })
                            }]
                        }).addTo(map);

                        L.circleMarker(provCoords[fromClean], {
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
                        L.circleMarker(provCoords[toClean], {
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
