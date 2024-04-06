@extends('admin.layouts.app')

@section('panel')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Driver Details</h5>
                        <!-- Add your driver ride details content here -->
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-warning mr-3" style="width: 60px; height: 60px;"></div>
                                <div>
                                    <h6 class="mb-0">{{ $sos->ride->driver->fullName }}</h6>
                                    <small>Rating: {{ $sos->ride->driver->avg_rating }}</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="mb-2">Ride ID: <span class="text-muted">{{ getOrderId($sos->ride->uuid) }}</span></div>
                                <div class="mb-2">Start At: <span class="text-muted">{{ showDateTime($sos->ride_start_at) }}</span></div>
                            </div>
                            <div class="mb-3">
                                <div class="mb-2">Pickup Location:</div>
                                <ul class="list-unstyled">
                                    <li>{{ $sos->ride->pickup_address }}</li>
                                </ul>
                            </div>
                            <div>
                                <div class="mb-2">Destinations:</div>
                                <ul class="list-unstyled">
                                    @foreach($sos->ride->destinations as $destination)
                                        <li>{{ $destination->destination_address }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        <!-- Add your driver ride details content here -->
                    </div>
                </div>
            </div>

            <div class="col-md-4">
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">User Details</h5>
                        <!-- Add your user details content here -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-warning mr-3" style="width: 60px; height: 60px;"></div>
                            <div>
                                <h6 class="mb-0">{{ $sos->ride->user->fullName }}</h6>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="mb-2">Ride ID: <span class="text-muted">{{ getOrderId($sos->ride->uuid) }}</span></div>
                            <div class="mb-2">Start At: <span class="text-muted">{{ showDateTime($sos->ride_start_at) }}</span></div>
                        </div>
                        <div class="mb-3">
                            <div class="mb-2">@lang('Pickup Location:')</div>
                            <ul class="list-unstyled">
                                <li>{{ $sos->ride->pickup_address }}</li>
                            </ul>
                        </div>
                        <div>
                            <div class="mb-2">@lang('Current Locations:')</div>
                            <ul class="list-unstyled">
                                <li>{{ __($sos->lat) }}, {{ __($sos->long) }}</li>
                            </ul>
                        </div>
                        <!-- Add your user details content here -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Google Map</h5>
                        <!-- Add your Google Map integration here -->
                        <div id="map" style="height: 400px;">
                            <div class="form-group">
                                <label>@lang('Select Area')</label>
                                <input class="controls" id="searchBox" placeholder="@lang('Search Here')" type="text">
                                <textarea class="d-none" id="coordinates" name="coordinates"></textarea>
                                <div class="google-map" id="map"></div>
                            </div>
                        </div>
                        <!-- Add your Google Map integration here -->
                    </div>
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
    </style>
@endpush
@php
$apiKey = gs('location_api');
@endphp

@push('script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&libraries=places,directions"></script>
    <script>
        (function($) {
            "use strict";

            function initMap() {
                let pickupLatLng = new google.maps.LatLng({{ $sos->ride->pickup_lat }}, {{ $sos->ride->pickup_long }});
                let userLatLng = new google.maps.LatLng({{ $sos->lat }}, {{ $sos->long }});

                let mapOptions = {
                    zoom: 15,
                    center: userLatLng
                };

                let map = new google.maps.Map(document.getElementById('map'), mapOptions);

                // User location marker
                let pickupMarker = new google.maps.Marker({
                    position: pickupLatLng,
                    map: map,
                    title: 'Pickup Location',
                    icon: {
                        url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
                        scaledSize: new google.maps.Size(50, 50),
                    }
                });
                let userMarker = new google.maps.Marker({
                    position: userLatLng,
                    map: map,
                    title: 'User Location',
                    icon: {
                        url: 'http://maps.gstatic.com/mapfiles/ms2/micons/caution.png',
                        scaledSize: new google.maps.Size(50, 50)
                    }
                });

                // Pickup location marker

                // Directions service
                let directionsService = new google.maps.DirectionsService();
                let directionsRenderer = new google.maps.DirectionsRenderer();
                directionsRenderer.setMap(map);

                // Calculate and display the route
                let request = {
                    origin: userLatLng,
                    destination: pickupLatLng,
                    travelMode: google.maps.TravelMode.DRIVING
                };

                directionsService.route(request, function(result, status) {
                    if (status == 'OK') {
                        directionsRenderer.setDirections(result);
                    }
                });
            }

            google.maps.event.addDomListener(window, 'load', initMap);
        })(jQuery);
    </script>
@endpush
