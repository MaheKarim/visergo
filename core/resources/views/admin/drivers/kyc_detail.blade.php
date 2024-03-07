@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card b-radius--10">
                <div class="card-body">
                    @if($driver->driver_verification)
                        <ul class="list-group">
                          @foreach($driver->driver_verification as $val)
                          @continue(!$val->value)
                          <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{__($val->name)}}
                            <span>
                                @if($val->type == 'checkbox')
                                    {{ implode(',',$val->value) }}
                                @elseif($val->type == 'file')
                                    @if($val->value)
                                        <a href="{{ route('admin.download.attachment',encrypt(getFilePath('verify').'/'.$val->value)) }}" class="me-3"><i class="fa fa-file"></i>  @lang('Attachment') </a>
                                    @else
                                        @lang('No File')
                                    @endif
                                @else
                                <p>{{__($val->value)}}</p>
                                @endif
                            </span>
                          </li>
                          @endforeach
                        </ul>
                        @else
                        <h5 class="text-center">@lang('Driver Verification data not found')</h5>
                    @endif

                    @if($driver->dv == 2)
                    <div class="d-flex flex-wrap justify-content-end mt-3">
                        <button class="btn btn-outline--danger me-3 confirmationBtn" data-question="@lang('Are you sure to reject this documents?')" data-action="{{ route('admin.drivers.kyc.reject', $driver->id) }}"><i class="las la-ban"></i>@lang('Reject')</button>
                        <button class="btn btn-outline--success confirmationBtn" data-question="@lang('Are you sure to approve this documents?')" data-action="{{ route('admin.drivers.kyc.approve', $driver->id) }}"><i class="las la-check"></i>@lang('Approve')</button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection
