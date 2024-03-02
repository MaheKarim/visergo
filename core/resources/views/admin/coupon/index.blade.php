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
                                <th>@lang('Expire Date')</th>
                                <th>@lang('Description')</th>
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
                                        @if($coupon->discount_type == Status::PERCENTAGE)
                                            @lang('%')
                                        @else
                                            {{ $general->cur_text }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($coupon->discount_type == Status::PERCENTAGE)
                                            @lang('Percent (%)')
                                        @else
                                            @lang('Fixed')
                                        @endif
                                    </td>
                                    <td>{{ __(date($coupon->expire_at)) }}</td>
                                    <td>{{ __(strLimit($coupon->description, 15)) }} </td>
                                    <td>
                                        <div class="button--group">
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
                                <label>@lang('Expire at')</label>
                                <input class="form-control" name="expire_at" type="date" required>
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
    <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn"><i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush
