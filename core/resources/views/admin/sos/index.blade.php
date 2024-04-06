@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>@lang('Ride ID')</th>
                                <th>@lang('User Name')</th>
                                <th>@lang('Driver Name')</th>
                                <th>@lang('Created At')</th>
                                <th>@lang('is contacted ?')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($soss as $sos)
                                <tr>
                                    <td>{{ getOrderId(data_get($sos, 'ride.uuid')) }}</td>
                                    <td>
                                        {{ __(data_get($sos, 'ride.user.fullName') .' -  ' ) }} <a href="tel:{{data_get($sos, 'ride.user.mobile')}}"><i class="las la-phone"></i>   {{ data_get($sos, 'ride.user.mobile') }}</a>
                                    </td>
                                    <td>
                                        @if(data_get($sos, 'ride.driver'))
                                            {{ data_get($sos, 'ride.driver.fullName') }}
                                            <a href="tel:{{data_get($sos, 'ride.driver.mobile')}}"><i class="las la-phone"></i> {{ data_get($sos, 'ride.driver.mobile') }}</a>
                                        @else
                                            @lang('Not assigned')
                                        @endif
                                    </td>
                                    <td>
                                        {{ showDateTime($sos->created_at) }}
                                    </td>
                                    <td>
                                        @php echo $sos->statusBadge @endphp
                                    </td>
                                    <td>
                                        <div class="button--group">
                                            @if ($sos->status)
                                                <button class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.sos.status', $sos->id) }}" data-question="@lang('Are you sure that you want to disable this SOS status?')"><i class="las la-eye-slash"></i>@lang('No')</button>
                                            @else
                                                <button class="btn btn-sm btn-outline--success confirmationBtn" data-action="{{ route('admin.sos.status', $sos->id) }}" data-question="@lang('Are you sure that you want to enable this SOS status?')"><i class="las la-eye"></i>@lang('Yes')</button>
                                            @endif
                                            <a href="{{ route('admin.sos.details', $sos->id) }}" class="btn btn-sm btn-outline--primary"><i class="las la-eye"></i>@lang('Details')</a>
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
                @if ($soss->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($soss) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
@endpush
