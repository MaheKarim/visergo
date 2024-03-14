@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <form action="{{ route('admin.vehicle.type.store', @$vehicleType->id) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Type Name')</label>
                                    <input class="form-control" name="name" type="text"
                                           value="{{ old('name', @$vehicleType->name)  }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="row">

                                        <label> @lang('Select Services') </label>
                                        @foreach ($services as $service)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="service[]"
                                                           value="{{ $service->id }}"
                                                           id="service-{{ $service->id }}" @checked(@$vehicleType && in_array($service->id, @$vehicleType->vehicleServices->pluck('id')->toArray()))>
                                                    <label class="form-check-label" for="service-{{ $service->id }}">
                                                        {{ __($service->name) }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label>@lang('Is Vehicle Have Class ?')</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_class" id="yesRadio"
                                               value="1" @checked(@$vehicleType->manage_class == 1)>
                                        <label class="form-check-label" for="yesRadio">
                                            Yes
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_class" id="noRadio"
                                               value="0" @checked(@$vehicleType->manage_class === 0)>
                                        <label class="form-check-label" for="noRadio">
                                            No
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-none fareArea">
                                <div class="form-group">
                                    <label>@lang('Base Fare')</label>
                                    <div class="input-group">
                                        <input class="form-control" name="base_fare" type="number" value="{{ old()['base_fare'] ?? showAmount(@$vehicleType->base_fare) }}">
                                        <span class="input-group-text">{{ $general->cur_text }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-none classArea">
                                <div class="form-group">
                                    <label>@lang('Select Class')</label>
                                    <select class="form-control" name="classes[]" multiple>
                                        @foreach ($classes as $class)
                                            <option
                                                value="{{ $class->id }}" @selected(@$vehicleType && in_array($class->id,@$vehicleType->rideFares->pluck('vehicle_class_id')->toArray()))>{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row fareList">
                            @foreach(@$vehicleType->rideFares ?? [] as $rideFare)
                                <div class="col-md-4">
                                    <input type="text"
                                           name="old_value[{{$rideFare->service_id}}][{{$rideFare->vehicle_class_id}}]"
                                           value="{{ $rideFare->id }}">
                                    <div class="form-group">
                                        <label>{{ __($rideFare->service->name)  }}
                                            - {{ __($rideFare->vehicleClass->name)  }}</label>
                                        <div class="input-group">
                                            <input type="number" step="any" min="0"
                                                   value="{{getAmount($rideFare->fare)}}"
                                                   name="fare[{{$rideFare->service_id}}][{{$rideFare->vehicle_class_id}}]"
                                                   class="form-control">
                                            <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="card-footer">
                            <div class="form-group">
                                <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function () {

            $('select[multiple]').select2();

            @if(@$vehicleType)
                let selectedService = @json(array_values(array_unique(@$vehicleType->vehicleServices->pluck('id')->toArray())));
                let selectedClasses = @json(array_values(array_unique(@$vehicleType->rideFares->pluck('vehicle_class_id')->toArray())));
            @else
                let selectedService = [];
                let selectedClasses = [];
            @endif

            let classes = @json($classes);
            let services = @json($services);

            $('[name=manage_class]').on('change', function () {
                if ($(this).is(':checked')) {
                    let manageClass = $(this).val();
                    if (manageClass == 1) {
                        $('.classArea').removeClass('d-none');
                        $('.fareArea').addClass('d-none');
                        generateFareHtml();
                    } else {
                        $('.classArea').addClass('d-none');
                        $('.fareArea').removeClass('d-none');
                        $('.fareList').html('');
                    }
                }
            });

            $('[name="service[]"]').on('change', function () {
                selectedService = [];

                $('[name="service[]"]:checked').each(function () {
                    selectedService.push($(this).val());
                });
                generateFareHtml();
            });

            $('[name="classes[]"]').on('change', function () {
                selectedClasses = [];
                selectedClasses = $(this).find(':selected').map(function () {
                    return $(this).val();
                }).get();
                generateFareHtml();
            });



            function getNameUsingId(id, type = 'service') {
                if (type === 'service') {
                    const service = services.find(function (service) {
                        return service.id == id;
                    });

                    if (service) {
                        return service.name;
                    }
                } else if (type === 'class') {
                    const classObj = classes.find(function (classObj) {
                        return classObj.id == id;
                    });

                    if (classObj) {
                        return classObj.name;
                    }
                }
                return null;
            }

            function generateFareHtml() {
                let html = '';
                selectedService.forEach(service => {
                    selectedClasses.forEach(classElement => {
                        html += `<div class="col-md-4">
                                    <div class="form-group">
                                        <label>${getNameUsingId(service)} - ${getNameUsingId(classElement, 'class')}</label>
                                        <div class="input-group">
                                            <input type="number" step="any" min="0" name="fare[${service}][${classElement}]" class="form-control">
                                            <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                        </div>
                                    </div>
                                </div>`;
                    });
                });
                $('.fareList').html(html);
            }

        });
    </script>
@endpush
