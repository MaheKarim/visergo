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
                                    <th>@lang('Name')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($zones as $zone)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ __($zone->name) }}</span>
                                        </td>
                                        <td>
                                            @php echo $zone->statusBadge @endphp
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.zone.create', $zone->id) }}" class="btn btn-sm btn-outline--primary"><i class="las la-pencil-alt"></i>@lang('Edit')</a>
                                                @if ($zone->status)
                                                    <button class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.zone.status', $zone->id) }}" data-question="@lang('Are you sure that you want to disable this zone?')"><i class="las la-eye-slash"></i>@lang('Disable')</button>
                                                @else
                                                    <button class="btn btn-sm btn-outline--success confirmationBtn" data-action="{{ route('admin.zone.status', $zone->id) }}" data-question="@lang('Are you sure that you want to enable this zone?')"><i class="las la-eye"></i>@lang('Enable')</button>
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
                @if ($zones->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($zones) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
    <a href="{{ route('admin.zone.create') }}" class="btn btn-outline--primary h-45"><i class="las la-plus"></i>@lang('Add New')</a>
@endpush
