@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Ride Information')</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Ride Id')</span>
                            <h6 class="text--primary"><a href="{{ route('admin.rides.detail', @$ride->id) }}">#{{ getOrderId($ride->uuid) }}</a></h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Rider')</span>
                            <h6 class="text--primary"><a href="{{ route('admin.users.detail', @$ride->user_id) }}"><span>@</span>{{ $ride->user->username }}</a></h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Driver')</span>
                            <h6 class="text--primary"><a href="{{ route('admin.drivers.detail', @$ride->driver_id) }}"><span>@</span>{{ $ride->driver->username }}</a></h6>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Pickup Location')</span>
                            <h6> {{ __($ride->pickup_address) }} </h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Destination')</span>
                            @foreach($ride->destinations as $key => $rideDestination)
                                <h6>{{ __($rideDestination->destination_address) }}</h6>
                                <br>
                                @if (!$loop->last)
                                    <i class="las la-arrow-right"></i>
                                    <br>
                                @endif
                            @endforeach
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Duration')</span>
                            <h6> {{ showAmount($ride->duration) }} </h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Status')</span>
                            <h6> @php echo $ride->statusBadge @endphp </h6>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="msg-container">
                <div class="card">
                    <div class="card-header">
                        <div class="text-end">
                            <button class="btn btn--primary btn-sm reloadButton" data-ride-id="{{ $ride->id }}">
                                <i class="las la-redo-alt me-0"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body msg_history p-0">
                        @include('admin.partials.message')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .list-group-item h6 {
            text-align: right;
        }

        #scrollable {
            height: 200px;
            overflow-y: scroll;
            border: 1px solid #ccc;
        }
    </style>
@endpush

@push('style-lib')
    <link href="{{ asset('assets/admin/css/chat.css') }}" rel="stylesheet">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $(".msg_history").animate({
                scrollTop: $('.msg_history').prop("scrollHeight")
            }, 1);

            $('.reloadButton').on('click', function() {
                $.ajax({
                    type: 'GET',
                    url: `{{ route('admin.rides.messages', $ride->id) }}`,
                    success: function(response) {
                        $('.msg_history').html(response);
                        $(".msg_history").animate({
                            scrollTop: $('.msg_history').prop("scrollHeight")
                        }, 1);
                    },
                });
            });

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        #scrollable {
            height: 200px;
            overflow-y: scroll;
            border: 1px solid #ccc;
        }
    </style>
@endpush
