@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Ride Information')</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Status')</span>
                            <h6> @php echo $ride->statusBadge @endphp </h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('UUID')</span>
                            <h6 class="text--primary"> #{{ getOrderId($ride->uuid) }}</h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Rider')</span>
                            <h6 class="text--primary"><a href="{{ route('admin.users.detail', $ride->user_id) }}"><span>@</span>{{ $ride->user->username }}</a></h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Driver')</span>
                                @if($ride->driver_id)
                                <h6 class="text--primary">
                                    <a href="{{ route('admin.drivers.detail', $ride->driver_id) }}"><span>@</span>{{ $ride->driver->username }}</a>
                                </h6>
                                @else
                                <h6 class="text--danger">
                                    @lang('No Driver Assigned')
                                </h6>
                                @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Service')</span>
                            <h6> {{ __($ride->service->name) }} </h6>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Vehicle Type') > @lang('Class')</span>
                            <h6>{{ __($ride->vehicleType->name) }} > {{ __($ride->vehicleClass->name ?? 'No Class') }}</h6>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Zone')</span>
                            <h6>{{ __($ride->zone->name) }} </h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Distance')</span>
                            <h6> {{ showAmount($ride->distance) }} / km</h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Duration')</span>
                            <h6> {{ showAmount($ride->duration) }} </h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Pickup Date Time')</span>
                            <h6> {{ showDateTime($ride->pickup_date_time) }} </h6>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Otp')</span>
                            <h6> {{ $ride->otp }} </h6>
                        </li>

                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Otp Accepted')</span>
                            <h6> {{ showDateTime($ride->ride_start_at, 'd M Y i:s A') }} </h6>
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
                            <span class="text--muted">@lang('Ride Start At')</span>
                            <h6> {{ showDateTime($ride->ride_start_at, 'd M Y i:s A') }} </h6>
                        </li>

                        @if ($ride->status == Status::RIDE_COMPLETED)
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Payment Type')</span>
                                <h6> @php echo $ride->paymentTypes @endphp </h6>
                            </li>
                        @endif
                        @if($ride->driver_id && $ride->messages_count > 1)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text--muted">@lang('Messages')</span>
                            <h6><a class="badge badge--primary" href="{{ route('admin.rides.messages', $ride->id) }}"> {{ $ride->messages_count }} </a> </h6>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            @if ($ride->status == Status::RIDE_COMPLETED)
                <div class="card mt-0">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Completed Ride Information')</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Complete At')</span>
                                <h6> {{ showDateTime($ride->ride_completed_at) }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Payment Type')</span>
                                <h6> @php echo $ride->paymentTypes @endphp </h6>
                            </li>
                            @if ($ride->payment_type == Status::ONLINE_PAYMENT)
                                <li class="list-group-item d-flex justify-content-between">
                                    <span class="text--muted">@lang('Payment Method')</span>
                                    <h6> {{ __(@$ride->payment->gateway->name) }} </h6>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endif
            @if ($ride->payment_status == Status::PAYMENT_SUCCESS)
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Payment Information')</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Tips')</span>
                                <h6> {{ gs('cur_sym') }} {{ @$ride->tips ?? '0.00' }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Coupon')</span>
                                <h6> {{ @$ride->appliedCoupon->coupon->coupon_code ?? 'N/A' }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Coupon Discount')</span>
                                <h6> {{ gs('cur_sym') }}{{ showAmount(@$ride->appliedCoupon->amount) }} </h6>
                            </li>

                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Driver Received')</span>
                                <h6> {{ gs('cur_sym') }}{{ showAmount(@$ride->driver_amount) }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Admin Commission')</span>
                                <h6> {{ gs('cur_sym') }}{{ showAmount(@$ride->admin_commission) }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">{{ gs('vat_title') }}</span>
                                <h6> {{ gs('cur_sym') }}{{ showAmount(@$ride->vat_amount) }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Gateway Charge')</span>
                                <h6> {{ gs('cur_sym') }}{{ showAmount(@$ride->payment->charge) }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Rider Payment')</span>
                                <h6> {{ gs('cur_sym') }}{{ showAmount(@$ride->payment->amount) }} </h6>
                            </li>

                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Total Amount Paid')</span>
                                <h6> {{ gs('cur_sym') }}{{ showAmount(@$ride->payment->final_amount) }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Transaction Details')</span>
                                <h6> {{ @$ride->payment->detail }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Payment Status')</span>
                                <h6> @php echo @$ride->paymentStatusType @endphp</h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Transition Id')</span>
                                <h6> @php echo @$ride->payment->trx @endphp </h6>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif

            @if ($ride->status == Status::RIDE_CANCELED)
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Ride Cancel Information')</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Canceled At')</span>
                                <h6> {{ showDateTime($ride->cancelled_at, 'd M Y i:s A') }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Canceled Reason')</span>
                                <h6> {{ __($ride->cancel_reason) }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Canceled  By')</span>
                                <h6> {{ __($ride->canceled_user_type == 1 ? 'User' : 'Driver') }} </h6>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
            @if ($ride->review)
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Rider Review & Rating')</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Rating')</span>
                                <h6> {{ $ride->review->rating }} </h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text--muted">@lang('Review')</span>
                                <h6> {{ __($ride->review->review) }} </h6>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('style')
    <style>
        .list-group-item h6 {
            text-align: right;
        }
    </style>
@endpush
