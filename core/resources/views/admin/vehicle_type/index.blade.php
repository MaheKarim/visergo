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
                                <th>@lang('Type Name')</th>
                                <th>@lang('Base Fare')</th>
                                <th>@lang('Have Class ?')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($vehicles as $vehicle)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ __($vehicle->name) }}</span>
                                    </td>
                                    <td>
                                        {{ __(showAmount($vehicle->base_fare)) }} {{ $general->cur_text }}
                                    </td>
                                    <td>
                                        {{ $vehicle->manage_class == 1 ? __('Yes') : __('No') }}
                                    </td>

                                    <td>
                                        @php
                                            echo $vehicle->statusBadge
                                        @endphp
                                    </td>

                                    <td>
                                        <div class="button--group">
                                            <a href="{{route('admin.vehicle.type.edit', $vehicle->id)}}" class="btn btn-outline--primary btn-sm"
                                                    data-modal_title="@lang('Update Vehicle Type')"
                                                    data-resource="{{ $vehicle }}">
                                                <i class="las la-pen"></i>@lang('Edit')
                                            </a>

                                            @if($vehicle->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to enable this vehicle type?')"
                                                        data-action="{{ route('admin.vehicle.type.status',$vehicle->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to disable this vehicle type?')"
                                                        data-action="{{ route('admin.vehicle.type.status',$vehicle->id) }}">
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
                @if ($vehicles->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($vehicles) }}
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
                    <form action="{{ route('admin.vehicle.type.store' )}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>@lang('Type Name')</label>
                                <input class="form-control" name="name" type="text" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('Base Fare')</label>
                                <div class="input-group">
                                    <input class="form-control" name="base_fare" type="number" required>
                                    <span class="input-group-text">{{ $general->cur_text }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>@lang('Ride Fare /km')</label>
                                <div class="input-group">
                                    <input class="form-control" name="ride_fare_per_km" type="number" required>
                                    <span class="input-group-text">{{ $general->cur_text }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>@lang('Intercity Fare /km')</label>
                                <div class="input-group">
                                <input class="form-control" name="intercity_fare_per_km" type="number" required>
                                    <span class="input-group-text">{{ $general->cur_text }}</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>@lang('Rental Fare /km')</label>
                                <div class="input-group">
                                <input class="form-control" name="rental_fare_per_km" type="number" required>
                                    <span class="input-group-text">{{ $general->cur_text }}</span></div>
                            </div>
                            <div class="form-group">
                                <label>@lang('Reserve Fare /km')</label>
                                <div class="input-group">
                                    <input class="form-control" name="reserve_fare_per_km" type="number" required>
                                    <span class="input-group-text">{{ $general->cur_text }}</span>
                                </div>
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
    <x-search-form placeholder="Type Name"/>
    <a class="btn btn-outline--primary" href="{{ route('admin.vehicle.type.create') }}"><i class="las la-plus"></i>@lang('Add New')</a>
@endpush
