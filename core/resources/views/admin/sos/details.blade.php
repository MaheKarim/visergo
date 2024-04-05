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
                                <div class="mb-2">Pickup Locations:</div>
                                <ul class="list-unstyled">
                                    <li>Location 1</li>
                                    <li>Location 2</li>
                                </ul>
                            </div>
                            <div>
                                <div class="mb-2">Destinations:</div>
                                <ul class="list-unstyled">
                                    <li>Destination 1</li>
                                    <li>Destination 2</li>
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
                                <small>Rating: 4.8</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="mb-2">Ride ID: <span class="text-muted">12345</span></div>
                            <div class="mb-2">Start At: <span class="text-muted">10:30 AM</span></div>
                        </div>
                        <div class="mb-3">
                            <div class="mb-2">Pickup Locations:</div>
                            <ul class="list-unstyled">
                                <li>Location 1</li>
                                <li>Location 2</li>
                            </ul>
                        </div>
                        <div>
                            <div class="mb-2">Destinations:</div>
                            <ul class="list-unstyled">
                                <li>Destination 1</li>
                                <li>Destination 2</li>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
