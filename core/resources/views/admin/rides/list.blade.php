@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--lg table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('ID')</th>
                                    <th>@lang('User')</th>
                                    <th>@lang('Location')</th>
                                    <th>@lang('Distance')</th>
                                    <th>@lang('Pickup Time')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rides as $ride)
                                    <tr>
                                        <td>
                                            #{{ getOrderId($ride->uuid) }}
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $ride->user->fullname }}</span>
                                            <br>
                                            <span class="small">
                                                <a href="{{ route('admin.users.detail', $ride->user->id) }}"><span>@</span>{{ $ride->user->username }}</a>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="text-right">
                                                <span>{{ __($ride->pickup_address) }}</span>
                                                <br>
                                                <i class="las la-arrow-down"></i>
                                                <br>
                                                @foreach($ride->destinations as $key => $rideDestination)
                                                    <span>{{ __($rideDestination->destination_address) }}</span>
                                                    <br>
                                                    @if (!$loop->last)
                                                        <i class="las la-arrow-down"></i>
                                                        <br>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            {{ __(showAmount($ride->distance)) }} @lang('Km')
                                        </td>
                                        <td>
                                            {{ showDateTime($ride->created_at) }} <br> {{ diffForHumans($ride->created_at) }}
                                        </td>
                                        <td>
                                            @php echo $ride->statusBadge @endphp
                                        </td>

                                        <td>
                                            <div class="button--group">
                                                <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.rides.detail', $ride->id) }}">
                                                    <i class="las la-desktop"></i> @lang('Details')
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
                @if ($rides->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($rides) }}
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search" />
@endpush
