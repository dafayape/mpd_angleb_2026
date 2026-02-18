@extends('layout.app')

@section('title', 'Map Monitor')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Map Monitor - Kepadatan Simpul</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Map Monitor</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <div id="map" style="width: 100%; height: 600px; border-radius: 4px;"></div>
                
                <!-- Legend Overlay -->
                <div id="legend" class="bg-white p-3 rounded shadow-sm m-3" style="position: absolute; bottom: 20px; right: 20px; max-width: 250px; z-index: 1000; display: none;">
                    <h6 class="mb-2 border-bottom pb-2">Legenda Kepadatan</h6>
                    <div class="d-flex align-items-center mb-2">
                        <span class="rounded-circle d-inline-block me-2" style="width: 12px; height: 12px; background-color: #00ff00; opacity: 0.6;"></span>
                        <small>Rendah (< 33%)</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="rounded-circle d-inline-block me-2" style="width: 12px; height: 12px; background-color: #ffff00; opacity: 0.6;"></span>
                        <small>Sedang (33% - 66%)</small>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="rounded-circle d-inline-block me-2" style="width: 12px; height: 12px; background-color: #ff0000; opacity: 0.6;"></span>
                        <small>Tinggi (> 66%)</small>
                    </div>
                    <div class="text-muted" style="font-size: 11px; margin-top: 5px;">
                        * Radius dan warna menunjukkan volume pergerakan (Orang/Hari).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Load Google Maps API (Use Env Variable if available or Placeholder) --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', '') }}&callback=initMap" async defer></script>

<script>
    let map;
    let circles = [];

    function initMap() {
        // Default Center (Indonesia)
        const center = { lat: -2.5489, lng: 118.0149 }; 
        const mapOptions = {
            zoom: 5,
            center: center,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles: [
                {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }
            ]
        };

        map = new google.maps.Map(document.getElementById("map"), mapOptions);

        // Show Legend
        const legend = document.getElementById('legend');
        map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);
        legend.style.display = 'block';

        // Fetch Data
        fetchData();
    }

    function fetchData() {
        fetch("{{ route('map-monitor.data') }}")
            .then(response => response.json())
            .then(data => {
                if (data.features) {
                    renderSimpul(data.features);
                    
                    // Fit bounds if data exists
                    if (data.features.length > 0) {
                        const bounds = new google.maps.LatLngBounds();
                        data.features.forEach(f => {
                            if (f.geometry.coordinates) {
                                bounds.extend({ lat: f.geometry.coordinates[1], lng: f.geometry.coordinates[0] });
                            }
                        });
                        map.fitBounds(bounds);
                    }
                }
            })
            .catch(error => console.error('Error fetching map data:', error));
    }

    function renderSimpul(features) {
        // Clear existing circles
        circles.forEach(c => c.setMap(null));
        circles = [];

        const infowindow = new google.maps.InfoWindow();

        features.forEach(feature => {
            if (!feature.geometry.coordinates) return;

            const lat = feature.geometry.coordinates[1];
            const lng = feature.geometry.coordinates[0];
            const props = feature.properties;

            const circle = new google.maps.Circle({
                strokeColor: props.color,
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: props.color,
                fillOpacity: 0.35,
                map: map,
                center: { lat: lat, lng: lng },
                radius: props.radius // meters
            });

            // Add Click Listener
            circle.addListener("click", () => {
                const content = `
                    <div style="font-family: Poppins, sans-serif;">
                        <h6 class="mb-1">${props.name}</h6>
                        <span class="badge bg-secondary mb-2">${props.category}</span>
                        <div class="mt-2">
                            <strong>Volume:</strong> ${props.volume.toLocaleString('id-ID')}
                        </div>
                    </div>
                `;
                infowindow.setContent(content);
                infowindow.setPosition(circle.getCenter());
                infowindow.open(map);
            });

            circles.push(circle);
        });
    }
</script>
@endpush
