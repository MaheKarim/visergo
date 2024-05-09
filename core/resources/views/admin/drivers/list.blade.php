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
                                <th>@lang('Driver')</th>
                                <th>@lang('Email-Phone')</th>
                                <th>@lang('Country')</th>
                                <th>@lang('Joined At')</th>
                                <th>@lang('Balance')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($drivers as $driver)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{$driver->fullname}}</span>
                                    <br>
                                    <span class="small">
                                    <a href="{{ route('admin.drivers.detail', $driver->id) }}"><span>@</span>{{ $driver->username }}</a>
                                    </span>
                                </td>


                                <td>
                                    {{ $driver->email }}<br>{{ $driver->mobile }}
                                </td>
                                <td>
                                    <span class="fw-bold" title="{{ @$driver->address->country }}">{{ $driver->country_code }}</span>
                                </td>



                                <td>
                                    {{ showDateTime($driver->created_at) }} <br> {{ diffForHumans($driver->created_at) }}
                                </td>


                                <td>
                                    <span class="fw-bold">

                                    {{ $general->cur_sym }}{{ showAmount($driver->balance) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('admin.drivers.detail', $driver->id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-desktop"></i> @lang('Details')
                                        </a>
                                        @if (request()->routeIs('admin.drivers.verification.pending'))
                                        <a href="{{ route('admin.drivers.kyc.details', $driver->id) }}" target="_blank" class="btn btn-sm btn-outline--dark">
                                            <i class="las la-user-check"></i>@lang('KYC Data')
                                        </a>
                                        @endif
                                        @if (request()->routeIs('admin.drivers.vehicle.pending'))
                                            <a href="{{ route('admin.drivers.vehicle.details', $driver->id) }}" target="_blank" class="btn btn-sm btn-outline--dark">
                                                <i class="las la-user-check"></i>@lang('Vehicle Data')
                                            </a>
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
                @if ($drivers->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($drivers) }}
                </div>
                @endif
            </div>
        </div>


    </div>
@endsection



@push('breadcrumb-plugins')
    <x-search-form placeholder="Username / Email" />
@endpush
