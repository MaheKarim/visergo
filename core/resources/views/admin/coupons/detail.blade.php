@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ showAmount($totalAmount) }} {{ gs('cur_text') }}" title="Total Amount" style="4" link="0" icon="0" color="white" overlay_icon="0" bg="primary" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $couponCount }}" title="Total Applied Coupon" style="4" link="0" icon="0" color="white" overlay_icon="0" bg="success" />
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Ride Id')</th>
                                    <th>@lang('Rider')</th>
                                    <th>@lang('Discount Amount')</th>
                                    <th>@lang('Date')</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($appliedCoupons as $appliedCoupon)
                                    <tr>
{{--                                        <td>--}}
{{--                                            <a href="{{ route('admin.rides.detail', @$appliedCoupon->ride->id) }}">--}}
{{--                                                {{ $appliedCoupon->ride->uid }}--}}
{{--                                            </a>--}}
{{--                                        </td>--}}
                                        <td>

{{--                                            <span class="fw-bold">{{ __(@$appliedCoupon->user->fullname) }}</span>--}}
                                            <br>
{{--                                            <span class="small">--}}
{{--                                                <a href="{{ route('admin.users.detail', @$appliedCoupon->user->id) }}"><span>@</span>{{ @$appliedCoupon->user->username }}</a>--}}
{{--                                            </span>--}}

                                        </td>
                                        <td>
                                            {{ gs('cur_sym') }}{{ showAmount($appliedCoupon->amount) }}
                                        </td>
                                        <td>
                                            {{ showDateTime($appliedCoupon->ends_at, 'd M, Y') }}
                                            <br>
                                            {{ diffForHumans($appliedCoupon->ends_at, 'd M, Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($appliedCoupons->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($appliedCoupons) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
