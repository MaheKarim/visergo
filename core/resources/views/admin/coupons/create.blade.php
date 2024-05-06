@extends('admin.layouts.app')

@section('panel')
    <form action="{{ route('admin.coupon.store', $coupon->id ?? 0) }}" method="POST">
        @csrf
        <div class="row gy-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">@lang('General Information')</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label>@lang('Coupon Name') </label>
                                    </div>
                                    <div class="col-lg-10 col-md-9">
                                        <input class="form-control" name="coupon_name" type="text" value="{{ old('coupon_name', @$coupon->coupon_name) }}" required />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label>@lang('Coupon Code')</label>
                                    </div>
                                    <div class="col-lg-10 col-md-9">
                                        <input class="form-control" name="coupon_code" type="text" value="{{ old('coupon_code', @$coupon->coupon_code) }}" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label>@lang('Discount Type')</label>
                                    </div>
                                    <div class="col-lg-10 col-md-9">
                                        <select class="form-control" name="discount_type" required>
                                            <option value="" selected hidden>@lang('Select One')</option>coupon_amount>
                                            <option value="{{ Status::DISCOUNT_FIXED }}" @selected($coupon->discount_type == Status::DISCOUNT_FIXED)>@lang('Fixed')</option>
                                            <option value="{{ Status::DISCOUNT_PERCENT }}" @selected($coupon->discount_type == Status::DISCOUNT_PERCENT)>@lang('Percentage')</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label>@lang('Amount')</label>
                                    </div>
                                    <div class="col-lg-10 col-md-9">
                                        <div class="input-group">
                                            <input class="form-control" name="amount" type="number" value="{{ old('amount', @$coupon->coupon_amount) }}" step="any" required>
                                            <span class="input-group-text" id="couponAmountType"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label for="starts_from">@lang('Starts From')</label>
                                    </div>

                                    @php
                                        $startDate = @$coupon->starts_from ? showDateTime(@$coupon->starts_from, 'Y-m-d h:i A') : null;
                                    @endphp

                                    <div class="col-lg-10 col-md-9">
                                        <input class="form-control" name="starts_from" data-language='en' type="text" value="{{ old('starts_from', $startDate) }}" autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label for="ends_at">@lang('Ends At')</label>
                                    </div>

                                    @php $endDate = @$coupon->ends_at ? showDateTime(@$coupon->ends_at, 'Y-m-d h:i A') : null;@endphp

                                    <div class="col-lg-10 col-md-9">
                                        <input class="form-control" name="ends_at" data-language='en' type="text" value="{{ old('ends_at', $endDate) }}" autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label for="description">@lang('Description')</label>
                                    </div>
                                    <div class="col-lg-10 col-md-9">
                                        <textarea class="form-control" id="description" name="description" rows="3">{{ isset($coupon) ? $coupon->description : old('$coupon->description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">@lang('Usage Restrictions')</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label for="minimum_spend">@lang('Minimum Spend')</label>
                                    </div>

                                    <div class="col-lg-10 col-md-9">
                                        <div class="input-group">
                                            <input class="form-control" name="minimum_spend" type="number" value="{{ old('minimum_spend', @$coupon->minimum_spend) }}" />
                                            <span class="input-group-text"> {{ $general->cur_text }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label>@lang('Maximum Spend')</label>
                                    </div>

                                    <div class="col-lg-10 col-md-9">
                                        <div class="input-group">
                                            <input class="form-control" name="maximum_spend" type="number" value="{{ old('maximum_spend', @$coupon->maximum_spend) }}">
                                            <span class="input-group-text"> {{ $general->cur_text }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label>@lang('Usage Limit Per Coupon')</label>
                                    </div>
                                    <div class="col-lg-10 col-md-9">
                                        <input class="form-control" name="usage_limit_per_coupon" type="number" value="{{ old('usage_limit_per_coupon', @$coupon->usage_limit_per_coupon) }}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-lg-2 col-md-3">
                                        <label>@lang('Usage Limit Per Customer')</label>
                                    </div>
                                    <div class="col-lg-10 col-md-9">
                                        <input class="form-control" name="usage_limit_per_customer" type="number" value="{{ old('usage_limit_per_customer', @$coupon->usage_limit_per_user) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
            </div>
        </div>

    </form>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.coupon.index') }}"></x-back>
@endpush

@push('script')
    <script>
        'use strict';
        (function($) {

            const discountTypeField = $('[name=discount_type]');
            const startsFromField = $('[name=starts_from]');
            const endsAtField = $('[name=ends_at]');
            const discountType = `{{ old('discount_type', @$coupon->discount_type) }}`;


            $(discountTypeField).on('change', function() {
                if (discountTypeField.val() == '{{ Status::DISCOUNT_FIXED }}') {
                    $('#couponAmountType').text(`{{ $general->cur_text }}`);
                } else if (discountTypeField.val() == '{{ Status::DISCOUNT_PERCENT }}') {
                    $('#couponAmountType').text(`%`);
                }
            }).change();
            const startDatePicker = startsFromField.datepicker({
                dateFormat: 'yyyy-mm-dd'
            });

            if (startsFromField.val()) {
                startDatePicker.data('datepicker').selectDate(new Date(startsFromField.val()))
            }

            const endDatePicker = endsAtField.datepicker({
                dateFormat: 'yyyy-mm-dd'
            });

            if (endsAtField.val()) {
                endDatePicker.data('datepicker').selectDate(new Date(endsAtField.val()))
            }
        })(jQuery);
    </script>
@endpush

@push('style-lib')
    <link href="{{ asset('assets/admin/css/vendor/datepicker.min.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
    <script type="text/javascript" src="{{ asset('assets/admin/js/vendor/datepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/admin/js/vendor/datepicker.en.js') }}"></script>
@endpush
