@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('S.N.')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Code')</th>
                                    <th>@lang('Discount Type')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Expire Date')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @forelse($coupons as $coupon)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $coupon->coupon_name }}</td>
                                        <td> {{ $coupon->coupon_code }} </td>
                                        <td>@php echo $coupon->discountTypeBadge() @endphp</td>
                                        <td>
                                            <x-toggle-switch class="change_status" :checked="$coupon->status" data-id="{{ $coupon->id }}" />

                                        </td>
                                        <td class="{{ $coupon->ends_at < now() ? 'text--danger' : '' }}">
                                            {{ showDateTime($coupon->ends_at, 'd M, Y') }}
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.coupon.edit', $coupon->id) }}" class="btn btn-outline--primary btn-sm edit-btn"><i class="la la-pencil"></i> @lang('Edit')</a>
                                                <a href="{{ route('admin.coupon.detail', $coupon->id) }}" class="btn btn-outline--primary btn-sm edit-btn"><i class="las la-desktop"></i> @lang('Details')</a>
                                            </div>
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

                @if ($coupons->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($coupons) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.coupon.create') }}" class="btn btn-sm btn-outline--primary"> <i class="las la-plus"></i> @lang('Add New')</a>
@endpush

@push('script')
    <script>
        'use strict';
        (function($) {
            const couponStatusChangeHandler = function() {
                const url = `{{ route('admin.coupon.status.change', ':id') }}`.replace(':id', $(this).data('id'));
                $.post(url, {
                        _token: `{{ csrf_token() }}`,
                    },
                    function(response) {
                        console.log(response.message);
                        notify('success', response.message);
                    }
                );
            }

            $('.change_status').on('change', couponStatusChangeHandler);
        })(jQuery)
    </script>
@endpush
