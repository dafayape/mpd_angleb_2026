@extends('layout.app')

@section('title', 'Map Monitor')

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    #map {
        width: 100%;
        height: 600px;
        border-radius: 4px;
        z-index: 1;
    }
    .legend-control {
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        max-width: 250px;
        font-family: "Poppins", sans-serif;
        font-size: 12px;
    }
    .legend-control h6 {
        margin: 0 0 5px;
        font-weight: 600;
        font-size: 14px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
    /* Select2 Overlay */
    .search-control {
        position: absolute;
        top: 10px;
        left: 50px; /* Right of Zoom control */
        z-index: 1000;
        background: white;
        padding: 5px;
        border-radius: 4px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        width: 300px;
    }
    .select2-container {
        width: 100% !important;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Map Monitor - Kepadatan Simpul</h4>
            <div class="page-title-right">
                <form class="d-flex align-items-center gap-2" id="periodForm">
                    <label class="mb-0 fw-bold text-nowrap">Periode:</label>
                    <input type="date" id="startDate" class="form-control form-control-sm" value="2026-03-13" min="2026-03-13" max="2026-03-29" style="width: 140px;">
                    <span class="text-muted fw-bold">&mdash;</span>
                    <input type="date" id="endDate" class="form-control form-control-sm" value="2026-03-29" min="2026-03-13" max="2026-03-29" style="width: 140px;">
                    
                    <label class="mb-0 fw-bold text-nowrap ms-2">Opsel:</label>
                    <select id="opselFilter" class="form-select form-select-sm" style="width: 100px;">
                        <option value="">Semua</option>
                        <option value="TSEL">Telkomsel</option>
                        <option value="IOH">Indosat</option>
                        <option value="XL">XL Axiata</option>
                    </select>

                    <button type="submit" class="btn btn-sm btn-primary text-nowrap ms-1">
                        <i class="mdi mdi-magnify me-1"></i>Terapkan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0" style="position: relative;">
                <div id="map"></div>
                
                <!-- Search Overlay -->
                <div class="search-control">
                    <select id="simpulSearch" class="form-control" placeholder="Cari Simpul...">
                        <option value="">Cari Simpul/Lokasi...</option>
                    </select>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingOverlay" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 600px; background: rgba(255,255,255,0.7); z-index: 1000; align-items: center; justify-content: center;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 0. Init Select2
        $('#simpulSearch').select2({
            placeholder: "Cari Simpul (Terintegrasi Database)...",
            allowClear: true,
            ajax: {
                url: "{{ route('map-monitor.search-simpul') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // Search term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 0 // Allow opening without typing to see defaults
        });

        // 1. Initialize Map (Center Indonesia)
        const map = L.map('map').setView([-2.5489, 118.0149], 5);

        // 2. Add Tile Layer (CartoDB Positron for clean look)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // 3. Variables
        let circlesLayerGroup = L.layerGroup().addTo(map);
        let layersMap = {}; // Map ID -> Layer
        const dateFilter = document.getElementById('dateFilter');
        const loadingOverlay = document.getElementById('loadingOverlay');

        // 4. Add Legend Control
        const legend = L.control({position: 'bottomright'});
        legend.onAdd = function (map) {
            const div = L.DomUtil.create('div', 'legend-control');
            div.innerHTML = `
                <h6>Legenda Kepadatan</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle d-inline-block me-2" style="width: 12px; height: 12px; background-color: #00ff00; opacity: 0.6;"></span>
                    <span>Rendah (< 33%)</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle d-inline-block me-2" style="width: 12px; height: 12px; background-color: #ffff00; opacity: 0.6;"></span>
                    <span>Sedang (33% - 66%)</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <span class="rounded-circle d-inline-block me-2" style="width: 12px; height: 12px; background-color: #ff0000; opacity: 0.6;"></span>
                    <span>Tinggi (> 66%)</span>
                </div>
                <div class="mt-2 pt-2 border-top text-muted">
                    <small><strong>Data:</strong> <span id="displayDate">-</span></small><br>
                    <small style="font-size: 10px;">* Radius logaritmik volume.</small>
                </div>
            `;
            return div;
        };
        legend.addTo(map);

    // 5. Fetch Data
    async function fetchData() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const opsel = document.getElementById('opselFilter').value;
        const url = `{{ route('map-monitor.data') }}?start_date=${startDate}&end_date=${endDate}&opsel=${opsel}`;

        loadingOverlay.style.display = 'flex';

        try {
            const response = await fetch(url);
            const data = await response.json();

            // Update Legend Date with period label
            const displayDateElem = document.getElementById('displayDate');
            if (displayDateElem && data.period_label) {
                displayDateElem.textContent = data.period_label;
            }

            // Render Features
            renderSimpul(data.features, data.max_volume);

        } catch (error) {
            console.error('Error fetching map data:', error);
            
        } finally {
            loadingOverlay.style.display = 'none';
        }
    }

    // 6. Render Simpul
    function renderSimpul(features, maxVolume) {
        circlesLayerGroup.clearLayers();
        layersMap = {}; // Reset Map

        if (!features || features.length === 0) return;

        const bounds = L.latLngBounds();

        features.forEach(f => {
            if (!f.geometry || !f.geometry.coordinates) return;

            const lng = f.geometry.coordinates[0];
            const lat = f.geometry.coordinates[1];
            const props = f.properties;

            // Create Circle
            const circle = L.circle([lat, lng], {
                color: props.color,
                fillColor: props.color,
                fillOpacity: 0.5,
                radius: props.radius, // Metres
                weight: 1
            }).addTo(circlesLayerGroup);

            // Popup Content
            const popupContent = `
                <div style="font-family: Poppins, sans-serif; min-width: 150px;">
                    <h6 style="margin:0 0 5px; font-weight:600;">${props.name}</h6>
                    <span class="badge bg-secondary mb-2">${props.category}</span>
                    <div style="font-size: 12px;">
                        <strong>Volume:</strong> ${props.volume.toLocaleString('id-ID')}<br>
                        <strong>Radius:</strong> ${Math.round(props.radius).toLocaleString('id-ID')} m
                    </div>
                </div>
            `;

            circle.bindPopup(popupContent);
            bounds.extend([lat, lng]);
            
            // Store for Search Lookup
            layersMap[props.id] = circle;
        });

        // Fit Bounds if valid
        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }
    
    // 7. Handle Search Selection (From Server-Side Integration)
    $('#simpulSearch').on('select2:select', function (e) {
        const data = e.params.data;
        // data.id = code
        // data.lat, data.lng = coordinates from server
        
        const lat = data.lat;
        const lng = data.lng;
        
        if (lat && lng) {
             map.flyTo([lat, lng], 14, {
                duration: 1.5
            });

            // Try to open popup if layer exists
            const simpulId = data.id;
             if(layersMap[simpulId]) {
                setTimeout(() => {
                    layersMap[simpulId].openPopup();
                }, 1500);
            }
        }
    });

    // 8. Event Listeners
    document.getElementById('periodForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Validate: start <= end
        const s = document.getElementById('startDate');
        const ed = document.getElementById('endDate');
        if (s.value > ed.value) {
            [s.value, ed.value] = [ed.value, s.value];
        }
        fetchData();
    });

    // Also fetch on date input change for quick single-day selection
    document.getElementById('startDate').addEventListener('change', function() {
        // If endDate < startDate, auto-correct
        const ed = document.getElementById('endDate');
        if (this.value > ed.value) ed.value = this.value;
    });
    document.getElementById('endDate').addEventListener('change', function() {
        const sd = document.getElementById('startDate');
        if (this.value < sd.value) sd.value = this.value;
    });

    // Fetch on Opsel Change
    document.getElementById('opselFilter').addEventListener('change', fetchData);

    // Initial Load
    fetchData();
});
</script>
@endpush
