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
                                <th>@lang('Brand')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($brands as $brand)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ __($brand->name) }}</span>
                                    </td>


                                    <td>
                                        @php
                                            echo $brand->statusBadge
                                        @endphp
                                    </td>

                                    <td>
                                        <div class="button--group">
                                            <button class="btn btn-outline--primary cuModalBtn btn-sm"
                                                    data-modal_title="@lang('Update')" data-resource="{{ $brand }}">
                                                <i class="las la-pen"></i>@lang('Edit')
                                            </button>
                                            @if($brand->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to enable this vehicle type?')"
                                                        data-action="{{ route('admin.brands.status',$brand->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to disable this vehicle type?')"
                                                        data-action="{{ route('admin.brands.status',$brand->id) }}">
                                                    <i class="la la-eye-slash"></i> @lang('Disable')
                                                </button>
                                            @endif
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
                @if ($brands->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($brands) }}
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
                    <form action="{{ route('admin.brands.store' )}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>@lang('Brand Name')</label>
                                <input class="form-control" name="name" type="text" required>
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
    <x-search-form placeholder="Brand"/>
    <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn"><i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush
