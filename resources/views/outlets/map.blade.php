@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-body" id="mapid"></div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" />
<link rel="stylesheet" href="/css/leaflet-routing-machine.css" />
<style>
    #mapid {
        min-height: 500px;
    }
</style>
@endsection
@push('scripts')
<!-- Make sure you put this AFTER Leaflet's CSS -->
<script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
<script src="http://localhost:8000/js/leaflet-routing-machine.min.js"></script>
<script src="http://localhost:8000/js/Control.Geocoder.min.js"></script>
<script>
    var map = L.map('mapid').setView([{{ config('leaflet.map_center_latitude') }}, {{ config('leaflet.map_center_longitude') }}], {{ config('leaflet.zoom_level') }});
    var baseUrl = "{{ url('/') }}";

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    axios.get('{{ route('api.outlets.index') }}')
        .then(function(response) {
            console.log(response.data);
            L.geoJSON(response.data, {
                    pointToLayer: function(geoJsonPoint, latlng) {
                        return L.marker(latlng);
                    }
                })
                .bindPopup(function(layer) {
                    return layer.feature.properties.map_popup_content;
                }).addTo(map);
        })
        .catch(function(error) {
            console.log(error);
        });

    var control = L.Routing.control({
        waypoints: [

        ],
        router: new L.Routing.osrmv1({
            serviceUrl: 'http://127.0.0.1:5000/route/v1',
            language: 'en',
            profile: 'car'
        }),
        geocoder: L.Control.Geocoder.nominatim({})
    }).addTo(map);

    function createButton(label, container) {
        var btn = L.DomUtil.create('button', '', container);
        btn.setAttribute('type', 'button');
        btn.innerHTML = label;
        return btn;
    }

map.on('click', function(e) {
    let latitude = e.latlng.lat.toString().substring(0, 15);
        let longitude = e.latlng.lng.toString().substring(0, 15);
    var container = L.DomUtil.create('div');
    container.innerHTML ="Selected Location : " + latitude + ", " + longitude + ".",
    crtBtn = createButton('Create new Outlet', container),
        startBtn = createButton('Start from this location', container),
        destBtn = createButton('Go to this location', container);
        
        L.DomEvent.on(crtBtn, 'click', function() {
            
            location.href= '{{ route('outlets.create') }}?latitude=' + latitude + '&longitude=' + longitude + '';
        map.closePopup();
    });
        
        L.DomEvent.on(startBtn, 'click', function() {
        control.spliceWaypoints(0, 1, e.latlng);
        map.closePopup();
    });
    L.DomEvent.on(destBtn, 'click', function() {
        control.spliceWaypoints(control.getWaypoints().length - 1, 1, e.latlng);
        map.closePopup();
    });

    L.popup()
        .setContent(container)
        .setLatLng(e.latlng)
        .openOn(map);
});



</script>
@endpush