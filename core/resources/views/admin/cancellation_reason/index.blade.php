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
                                <th>@lang('For')</th>
                                <th>@lang('Reason')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($reasons as $reason)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $reason->for == Status::DRIVER ? 'Driver' : 'Rider' }}</span>
                                    </td>
                                    <td>
                                        {{ __($reason->reason) }}
                                    </td>

                                    <td>
                                        @php
                                            echo $reason->statusBadge
                                        @endphp
                                    </td>
                                    <td>
                                        <div class="button--group">
                                            <button class="btn btn-outline--primary cuModalBtn btn-sm"
                                                    data-modal_title="@lang('Update')" data-resource="{{ $reason }}">
                                                <i class="las la-pen"></i>@lang('Edit')
                                            </button>
                                            @if($reason->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to enable this cancellation reason?')"
                                                        data-action="{{ route('admin.cancellation.status',$reason->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to disable this cancellation reason?')"
                                                        data-action="{{ route('admin.cancellation.status',$reason->id) }}">
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
                @if ($reasons->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($reasons) }}
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
                    <form action="{{ route('admin.cancellation.store' )}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>@lang('Reason For')</label>
                                <select class="form-control" name="for" required>
                                    <option value="{{ Status::RIDER }}">@lang('Rider')</option>
                                    <option value="{{ Status::DRIVER }}">@lang('Driver')</option>
                                </select>
                            </div>
                            <div class="from-group">
                                <label>@lang('Reason')</label>
                                <input class="form-control" name="reason" type="text" required>
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
    <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="@lang('Add New Reason')"><i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush
