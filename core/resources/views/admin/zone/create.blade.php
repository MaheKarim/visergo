@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.zone.save', @$zone->id ?? 0) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>@lang('Name')</label>
                                    <input class="form-control" name="name" required type="text" value="{{ old('name', @$zone->name) }}">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>@lang('Select Area')</label>
                                    <input class="controls" id="searchBox" placeholder="@lang('Search Here')" type="text">
                                    <textarea class="d-none" id="coordinates" name="coordinates"></textarea>
                                    <div class="google-map" id="map"></div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .google-map {
            width: 100%;
            height: 400px;
        }

        #searchBox {
            position: absolute;
            top: 0px;
            left: 334px;
            background: #fff;
            border: none;
            margin-top: 6px;
            height: 25px;
        }
        .pac-container{
            width: 320px!important;
        }
    </style>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.zone.index') }}" />
@endpush

@push('script-lib')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ @$general->location_api }}&libraries=drawing,places&v=3.45.8"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            // Map
            var lastPolygon = null;
            var polygons = [];

            function resetMap(controlDiv) {
                const removePolygonDiv = document.createElement("div");
                removePolygonDiv.style.backgroundColor = "#fff";
                removePolygonDiv.style.border = "2px solid #fff";
                removePolygonDiv.style.borderRadius = "3px";
                removePolygonDiv.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
                removePolygonDiv.style.cursor = "pointer";
                removePolygonDiv.style.marginTop = "6px";
                removePolygonDiv.style.marginBottom = "22px";
                removePolygonDiv.style.marginRight = "8px";
                removePolygonDiv.style.textAlign = "center";
                removePolygonDiv.title = "Reset map";
                controlDiv.appendChild(removePolygonDiv);
                const controlText = document.createElement("div");
                controlText.style.color = "red";
                controlText.style.fontFamily = "Roboto,Arial,sans-serif";
                controlText.style.paddingRight = "5px";
                controlText.style.paddingLeft = "5px";
                controlText.style.fontSize = "16px";
                controlText.innerHTML = "X";
                removePolygonDiv.appendChild(controlText);
                removePolygonDiv.addEventListener("click", () => {
                    lastPolygon.setMap(null);
                    $('#coordinates').val('');

                });
            }


            function initMap() {
                let centerLat = @json($general->center_lat ?? '38.89424346526042') * 1;
                let centerLng = @json($general->center_long ?? '-77.00873884290614') * 1;

                @if (@$zone)
                    centerLat = {{ explode(' ', trim($zone->center, 'POINT()'))[1] }};
                    centerLng = {{ explode(' ', trim($zone->center, 'POINT()'))[0] }};
                @endif

                let options = {
                    zoom: 14,
                    center: {
                        lat: centerLat,
                        lng: centerLng
                    },
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }

                const map = new google.maps.Map(document.getElementById("map"), options);

                const drawingManager = new google.maps.drawing.DrawingManager({

                    drawingMode: google.maps.drawing.OverlayType.POLYGON,
                    drawingControl: true,
                    drawingControlOptions: {
                        position: google.maps.ControlPosition.TOP_CENTER,
                        drawingModes: [google.maps.drawing.OverlayType.POLYGON]
                    },
                    polygonOptions: {
                        editable: true
                    }
                });

                @if (count($coordinates))
                    const appliedCoordinates = [
                        @foreach ($coordinates as $coords)
                            {
                                lat: {{ $coords[1] }},
                                lng: {{ $coords[0] }}
                            },
                        @endforeach
                    ];

                    const area = new google.maps.Polygon({
                        paths: appliedCoordinates,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillOpacity: 0.12,
                    });

                    area.setMap(map);
                @endif

                drawingManager.setMap(map);

                @if (!$zone)
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition((position) => {
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            };
                            map.setCenter(pos);
                        })
                    }
                @endif

                google.maps.event.addListener(drawingManager, "overlaycomplete", function(event) {
                    if (lastPolygon) {
                        lastPolygon.setMap(null);
                    }
                    $('#coordinates').val(event.overlay.getPath().getArray());
                    lastPolygon = event.overlay;
                });

                const resetDiv = document.createElement("div");
                resetMap(resetDiv);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(resetDiv);

                const input = document.getElementById("searchBox");
                const searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

                input.addEventListener('keypress', function(e) {
                    if (e.keyCode == 13) {
                        e.preventDefault();
                        return true;
                    }
                });

                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });

                let markers = [];
                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    markers.forEach((marker) => {
                        marker.setMap(null);
                    });

                    markers = [];

                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            console.log("No Geometry");
                            return;
                        }
                        const icon = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25),
                        };

                        markers.push(
                            new google.maps.Marker({
                                map,
                                icon,
                                title: place.name,
                                position: place.geometry.location,
                            })
                        );

                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            }

            google.maps.event.addDomListener(window, 'load', initMap);
        })(jQuery);
    </script>
@endpush
