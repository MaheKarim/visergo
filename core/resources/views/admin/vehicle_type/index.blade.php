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
                                <th>@lang('Type')</th>
                                <th>@lang('Base Fare')</th>
                                <th>@lang('Ride Cost / km')</th>
                                <th>@lang('Intercity / km')</th>
                                <th>@lang('Rental / km')</th>
                                <th>@lang('Reserve / km')</th>
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
                                        {{ __(showAmount($vehicle->base_fare)) }}
                                    </td>
                                    <td>
                                        {{ __(showAmount($vehicle->ride_per_km_cost)) }}
                                    </td>
                                    <td>
                                        {{ __(showAmount($vehicle->intercity_per_km_cost)) }}
                                    </td>
                                    <td>
                                        {{ __(showAmount($vehicle->rental_per_km_cost)) }}
                                    </td>
                                    <td>
                                        {{ __(showAmount($vehicle->reserve_per_km_cost)) }}
                                    </td>
                                    <td>
                                        @if($vehicle->status == 1)
                                            <span class="badge badge--success">@lang('Active')</span>
                                        @else
                                            <span class="badge badge--danger">@lang('Inactive')</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="button--group">
                                            <button class="btn btn-outline--primary cuModalBtn btn-sm" data-modal_title="@lang('Update')" data-resource="{{ $vehicle }}">
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
                    <form action="{{ route('admin.vehicle-type.store' )}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>@lang('Vehicle type')</label>
                                <input class="form-control" name="name" type="text" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('Base Fare')</label>
                                <input class="form-control" name="base_fare" type="number" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('Ride (Cost / km)')</label>
                                <input class="form-control" name="ride_per_km_cost" type="number" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('Intercity (Cost / km)')</label>
                                <input class="form-control" name="intercity_per_km_cost" type="number" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('Rental (Cost / km)')</label>
                                <input class="form-control" name="rental_per_km_cost" type="number" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('Reserve (Cost / km)')</label>
                                <input class="form-control" name="reserve_per_km_cost" type="number" required>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Vehicle Type" />
    <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn"  ><i class="las la-plus"></i>@lang('Add New')</button>
@endpush
