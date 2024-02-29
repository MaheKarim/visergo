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
                                            <a href="{{ route('admin.vehicle-type.edit', $vehicle->id) }}" class="btn btn-sm btn-outline--primary">
                                                <i class="las la-desktop"></i> @lang('Edit')
                                            </a>
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


    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Vehicle Type" />
@endpush
