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
                                        value="{{ old('name', @$vehicleType->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="row">
                                        <label> @lang('Select Services') </label>
                                        @foreach ($services as $service)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input services" type="checkbox"
                                                        name="service[]" value="{{ $service->id }}"
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
                                        <input class="form-check-input manage_class" type="radio" name="manage_class"
                                            id="yesRadio" value="1" @checked(@$vehicleType->manage_class == 1)>
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
                            <div class="col-md-6 @if(!@$vehicleType->manage_class) d-none @endif classArea">
                                <div class="form-group">
                                    <label>@lang('Select Class')</label>
                                    <select class="form-control classes" name="classes[]" multiple>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id }}" @selected(@$vehicleType && in_array($class->id, @$vehicleType->rideFares->pluck('vehicle_class_id')->toArray()))>
                                                {{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row fareList">
                            @if(@$vehicleType)
                                @if(@$vehicleType->manage_class == Status::YES)
                                    @foreach($vehicleType->rideFares as $rideFare)
                                        <div class="col-md-4 ">
                                            <div class="form-group">
                                                <label>{{ $rideFare->service->name  }} - {{ $rideFare->vehicleClass->name  }} @lang('Base Fare')</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0"
                                                           value="{{ getAmount($rideFare->fare)  }}"
                                                           name="fare[{{ $rideFare->service_id  }}][{{ $rideFare->vehicle_class_id  }}]"
                                                           class="form-control">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    @foreach($vehicleType->rideFares as $rideFare)
                                        <div class="col-md-4 ">
                                            <div class="form-group">
                                                <label>{{ $rideFare->service->name  }} @lang('Base Fare')</label>
                                                <div class="input-group">
                                                    <input type="number" step="any"
                                                           value="{{ getAmount($rideFare->fare)  }}" min="0"
                                                           name="fare[{{ $rideFare->service_id  }}]"
                                                           class="form-control">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endif
                        </div>

                        <div class="row perKmFare">
                            @if(@$vehicleType)
                                @if(@$vehicleType->manage_class == Status::YES)
                                    @foreach($vehicleType->rideFares as $rideFare)
                                        <div class="col-md-4 ">
                                            <div class="form-group">
                                                <label>{{ $rideFare->service->name  }} - {{ $rideFare->vehicleClass->name  }} @lang('Fare per/km')</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" value="{{ getAmount($rideFare->per_km_fare)  }}" name="per_km_fare[{{ $rideFare->service_id  }}][{{ $rideFare->vehicle_class_id  }}]" class="form-control">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    @foreach($vehicleType->rideFares as $rideFare)
                                        <div class="col-md-4 ">
                                            <div class="form-group">
                                                <label>{{ $rideFare->service->name  }} @lang('Fare per/km')</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" value="{{ getAmount($rideFare->per_km_fare)  }}" name="per_km_fare[{{ $rideFare->service_id  }}]" class="form-control">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-">
                                    <label>@lang('Have Brand ?')</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_brand"
                                            id="brandYesRadio" value="1" @checked(@$vehicleType->manage_brand == 1)>
                                        <label class="form-check-label" for="brandYesRadio">
                                            Yes
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_brand"
                                            id="brandNoRadio" value="0" @checked(@$vehicleType->manage_brand === 0)>
                                        <label class="form-check-label" for="brandNoRadio">
                                            No
                                        </label>
                                    </div>
                                </div>
                            </div>
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
        $(document).ready(function() {

            $('select[multiple]').select2();

            $('[name=manage_class]').on('change', function() {
                if ($(this).val() == 1) {
                    $('.classArea').removeClass('d-none');
                } else {
                    $('.classes').val('').trigger('change');
                    $('.classArea').addClass('d-none');
                }
            });

            $(document).on('change', '.services, .classes', function() {
                generateFareElements();
            });

            function generateFareElements() {
                let services = $('.services:checked');
                let vehicleClasses = $('.classes').find('option:selected');

                if (vehicleClasses.length > 0) {
                    makeServiceClassCombination(services, vehicleClasses);
                } else {
                    makeBaseFareElements(services);
                }

            }

            function makeServiceClassCombination(services, vehicleClasses) {
                let baseFareElements = '';
                let kmFareElements = '';
                $.each(services, function(index, service) {
                    var serviceId = $(service).val();
                    var serviceName = $(service).siblings('label').text();

                    $.each(vehicleClasses, function(i, vehicleClass) {
                        var vehicleClassId = $(vehicleClass).val();
                        var vehicleClassName = $(vehicleClass).text();

                        baseFareElements += `<div class="col-md-4 ">
                            <div class="form-group">
                                <label>${serviceName} - ${vehicleClassName} Base Fare</label>
                                <div class="input-group">
                                    <input type="number" step="any" min="0" name="fare[${serviceId}][${vehicleClassId}]" class="form-control">
                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                </div>
                            </div>
                        </div>`;


                        kmFareElements += `<div class="col-md-4 ">
                                <div class="form-group">
                                    <label>${serviceName} - ${vehicleClassName} Fare per/km</label>
                                    <div class="input-group">
                                        <input type="number" step="any" min="0" name="per_km_fare[${serviceId}][${vehicleClassId}]" class="form-control">
                                        <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                    </div>
                                </div>
                            </div>`;
                    });
                });

                $('.fareList').html(baseFareElements);
                $('.perKmFare').html(kmFareElements);
            }

            function makeBaseFareElements(services) {
                var baseFareElements = '';
                var kmFareElements = '';
                $.each(services, function(index, service) {
                    var serviceId = $(service).val();
                    var serviceName = $(service).siblings('label').text();

                    baseFareElements += `<div class="col-md-4 ">
                            <div class="form-group">
                                <label>${serviceName} Base Fare</label>
                                <div class="input-group">
                                    <input type="number" step="any" min="0" name="fare[${serviceId}]" class="form-control">
                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                </div>
                            </div>
                        </div>`;

                    kmFareElements += `<div class="col-md-4 ">
                                <div class="form-group">
                                    <label>${serviceName} Fare per/km</label>
                                    <div class="input-group">
                                        <input type="number" step="any" min="0" name="per_km_fare[${serviceId}]" class="form-control">
                                        <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                    </div>
                                </div>
                            </div>`;
                });

                $('.fareList').html(baseFareElements);
                $('.perKmFare').html(kmFareElements);
            }
        });
    </script>
@endpush
