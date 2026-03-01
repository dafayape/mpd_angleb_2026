@extends('layout.app')

@section('title', $title)

@section('content')
    @component('layout.partials.page-header', ['number' => '09', 'title' => $title])
        <ol class="breadcrumb m-0 mb-0">
            @foreach ($breadcrumb as $crumb)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $crumb }}</li>
                @else
                    <li class="breadcrumb-item"><a href="#">{{ $crumb }}</a></li>
                @endif
            @endforeach
        </ol>
    @endcomponent

    @push('css')
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

            #sankey-container-tujuan {
                height: 650px;
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

    <!-- 01 O-D PROVINSI TUJUAN DARI JABODETABEK -->
    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">01</span>
                    <h5 class="fw-bold text-navy mb-0">O-D Inter Jabodetabek (Top 10 Provinsi tujuan favorit dari
                        Jabodetabek)</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">

                    <div class="row align-items-stretch">
                        <!-- Left: Sankey -->
                        <div class="col-xl-7 col-lg-12 mb-4 mb-xl-0 d-flex flex-column">
                            <div class="border rounded p-3 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important;">
                                <div id="sankey-container-tujuan" class="w-100" style="height: 650px;"></div>
                            </div>
                        </div>

                        <!-- Right: Table -->
                        <div class="col-xl-5 col-lg-12 d-flex flex-column">
                            <h5 class="fw-bold text-navy text-center mb-3 mt-2" style="font-size: 1.25rem;">Provinsi Tujuan
                                Favorit dari Jabodetabek</h5>
                            <div class="border rounded p-0 mb-4 flex-grow-1"
                                style="border-width:2px !important; border-color: #aab5c3 !important; overflow: hidden;">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                        <thead>
                                            <tr>
                                                <th class="text-start">Provinsi Tujuan</th>
                                                <th class="text-end">Jumlah Pergerakan</th>
                                                <th style="width: 60px;">Rank</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($top_dest->take(20) as $index => $row)
                                                <tr
                                                    @if ($index === 0) style="border: 2px solid #ef4444;" @endif>
                                                    <td class="text-start">{{ $row['name'] }}</td>
                                                    <td class="text-end" style="font-size: 13px;">
                                                        {{ number_format($row['total'], 0, ',', '.') }}</td>
                                                    <td>{{ $index + 1 }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-3 text-muted">Belum ada data
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Summary Box -->
                            <div class="rounded p-4 d-flex align-items-center justify-content-center text-center"
                                style="background-color: #f1f5f9; min-height:100px;">
                                @if (count($top_dest) > 0)
                                    <span class="text-dark fw-medium" style="font-size: 1.05rem; line-height:1.5;">
                                        Provinsi tujuan yang menjadi favorit dari Jabodetabek untuk melakukan perjalanan
                                        adalah<br><br>
                                        <span class="highlight text-primary"
                                            style="background-color: #fef08a !important; padding: 10px 15px; font-size: 1.35rem; font-weight: 800; border-radius: 4px; display:inline-block; margin-top: 5px; width: 100%;">
                                            @if (count($top_dest) > 1)
                                                {{ strtoupper($top_dest[0]['name']) }} DAN
                                                {{ strtoupper($top_dest[1]['name']) }}
                                            @else
                                                {{ strtoupper($top_dest[0]['name']) }}
                                            @endif
                                        </span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/sankey.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    once: true,
                    offset: 50,
                    duration: 600
                });
            }

            const formatNumber = (num) => {
                return new Intl.NumberFormat('id-ID').format(num);
            };

            const sankeyData = {!! json_encode($sankey) !!};
            if (sankeyData && sankeyData.length > 0 && document.getElementById('sankey-container-tujuan')) {
                const colors = ['#29769e', '#f59e0b', '#ef4444', '#10b981', '#6366f1', '#8b5cf6', '#ec4899',
                    '#14b8a6', '#f97316', '#0ea5e9'
                ];

                const nodesMap = new Map();
                let colorIdx = 0;

                sankeyData.forEach(item => {
                    if (!nodesMap.has(item.from)) {
                        nodesMap.set(item.from, colors[colorIdx % colors.length]);
                        colorIdx++;
                    }
                    if (!nodesMap.has(item.to)) {
                        nodesMap.set(item.to, colors[colorIdx % colors.length]);
                        colorIdx++;
                    }
                });

                const nodes = Array.from(nodesMap.entries()).map(([id, color]) => ({
                    id,
                    color
                }));

                Highcharts.chart('sankey-container-tujuan', {
                    chart: {
                        spacingBottom: 30,
                        spacingTop: 30,
                        spacingLeft: 10,
                        spacingRight: 10
                    },
                    title: {
                        text: null
                    },
                    tooltip: {
                        formatter: function() {
                            if (this.point.isNode) {
                                return `<b>${this.point.name}</b><br/>Total: ${formatNumber(this.point.weight)} pergerakan`;
                            }
                            return `<b>${this.point.from}</b> \u2192 <b>${this.point.to}</b><br/>Jumlah: ${formatNumber(this.point.weight)}`;
                        }
                    },
                    series: [{
                        keys: ['from', 'to', 'weight'],
                        data: sankeyData.map(d => [d.from, d.to, d.weight]),
                        nodes: nodes,
                        type: 'sankey',
                        name: 'Pergerakan Inter Jabodetabek',
                        dataLabels: {
                            style: {
                                color: '#333',
                                textOutline: 'none',
                                fontSize: '10px'
                            }
                        }
                    }],
                    credits: {
                        enabled: false
                    }
                });
            }
        });
    </script>
@endpush
