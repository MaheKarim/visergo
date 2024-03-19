@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>@lang('Coupon')</th>
                                <th>@lang('Discount Value')</th>
                                <th>@lang('Discount Type')</th>
                                <th>@lang('Points Deduct')</th>
                                <th>@lang('Start Date')</th>
                                <th>@lang('Expire Date')</th>
                                <th>@lang('Description')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($coupons as $coupon)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ __($coupon->name) }}</span>
                                    </td>
                                    <td>
                                        {{ __(showAmount($coupon->discount_value)) }}
                                        {{ $coupon->discount_type == Status::PERCENTAGE ? __('%') : $general->cur_text }}
                                    </td>
                                    <td>
                                        {{ $coupon->discount_type == Status::PERCENTAGE ? __('Percent (%)') : __('Fixed') }}
                                    </td>
                                    <td>{{ __(getAmount($coupon->points_deduct)) }}</td>
                                    <td>{{ __(date($coupon->start_at)) }}</td>
                                    <td>{{ __(date($coupon->expired_at)) }}</td>
                                    <td>{{ __(strLimit($coupon->description)) }} </td>
                                    <td>
                                        @php
                                            echo $coupon->statusBadge
                                        @endphp
                                    </td>
                                    <td>
                                        <div class="button--group">
                                            @if($coupon->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to enable this coupon?')"
                                                        data-action="{{ route('admin.coupon.status',$coupon->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to disable this coupon?')"
                                                        data-action="{{ route('admin.coupon.status',$coupon->id) }}">
                                                    <i class="la la-eye-slash"></i> @lang('Disable')
                                                </button>
                                            @endif
                                            <button class="btn btn-outline--primary cuModalBtn btn-sm"
                                                    data-modal_title="@lang('Update')" data-resource="{{ $coupon }}">
                                                <i class="las la-pen"></i>@lang('Edit')
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($coupons->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($coupons) }}
                    </div>
                @endif
            </div>
        </div>
        <div class="modal fade" id="cuModal" role="dialog" tabindex="-1">
            <div class="modal-dialog " role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.coupon.store' )}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>@lang('Coupon Name')</label>
                                <input class="form-control" name="name" type="text" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('Discount Value')</label>
                                <input class="form-control" name="discount_value" type="number" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('Discount Type')</label>
                                <select class="form-control" name="discount_type" required>
                                    <option value="{{ Status::FIXED }}">@lang('Fixed')</option>
                                    <option value="{{ Status::PERCENTAGE }}">@lang('Percent')</option>
                                </select>

                            </div>
                            <div class="form-group">
                                <label>@lang('Start at')</label>
                                <input class="form-control" name="start_at" type="date" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('Expire at')</label>
                                <input class="form-control" name="expired_at" type="date" required>
                            </div>

                            <div class="form-group">
                                <label>@lang('Description')</label>
                                <input class="form-control" name="description" type="text">
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <x-confirmation-modal/>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Coupon"/>
    <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="@lang('Add New Coupon')"><i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush
