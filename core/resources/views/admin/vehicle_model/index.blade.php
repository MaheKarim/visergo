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
                                <th>@lang('Model Name')</th>
                                <th>@lang('Vehicle Type')</th>
                                <th>@lang('Vehicle Class')</th>
                                <th>@lang('Brand')</th>
                                <th>@lang('Year')</th>
                                <th>@lang('Color')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($models as $model)
                                <tr>
                                    <td>
                                        {{ __($model->name) }}
                                    </td>

                                    <td>
                                         {{ __($model->vehicleType->name) }}
                                    </td>
                                    <td>
                                         {{ __($model->vehicleClass->name) }}
                                    </td>
                                    <td>
                                        {{ __($model->brand->name) }}
                                    </td>

                                    <td>
                                        {{ __($model->year) }}
                                    </td>

                                    <td>
                                        {{implode(',', $model->colors->pluck('name')->toArray())}}
                                    </td>

                                    <td>
                                        @php
                                            echo $model->statusBadge
                                        @endphp
                                    </td>

                                    <td>
                                        <div class="button--group">
                                            <button class="btn btn-outline--primary cuModalBtn btn-sm"
                                                    data-modal_title="@lang('Update Vehicle Model')" data-resource="{{ $model }}">
                                                <i class="las la-pen"></i>@lang('Edit')
                                            </button>
                                            @if($model->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to enable this vehicle type?')"
                                                        data-action="{{ route('admin.model.status',$model->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to disable this vehicle type?')"
                                                        data-action="{{ route('admin.model.status',$model->id) }}">
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
                @if ($models->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($models) }}
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
                    <form action="{{ route('admin.model.store' )}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>@lang('Brand')</label>
                                <select class="form-control" name="brand_id" required>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>@lang('Vehicle type')</label>
                                <select class="form-control" name="vehicle_type_id" required>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>@lang('Vehicle class')</label>
                                <select class="form-control" name="vehicle_class_id" required>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>@lang('Model Name')</label>
                                <input class="form-control" name="model" type="text" required>
                            </div>

                            <div class="form-group">
                                <label>@lang('Year')</label>
                                <input class="form-control" name="year" type="number" required>
                            </div>

                            <div class="form-group">
                                <label>@lang('Vehicle color')</label>
                                <select class="form-control" name="color_id" required>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                                    @endforeach
                                </select>
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
    <x-search-form placeholder="Year"/>
    <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="Add new vehicle model"><i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush
