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
                            {{-- @dd($vehicleType) --}}
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
                                            @lang(' Yes')
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_class" id="noRadio"
                                            value="0" @checked(@$vehicleType->manage_class === 0)>
                                        <label class="form-check-label" for="noRadio">
                                            @lang('No')
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 @if (!@$vehicleType->manage_class) d-none @endif classArea">
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

                        @foreach ($services as $service)
                            <div class="row service-fields service-fields-{{ $service->id }}">
                                @php
                                    $rideFares = @$vehicleType
                                        ? $vehicleType->rideFares->where('service_id', $service->id)
                                        : [];
                                @endphp
                                @if (count($rideFares))
                                    @foreach ($rideFares as $rideFare)
                                        @if ($rideFare->vehicle_class_id)
                                            @if ($rideFare->service_id == 4)
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) . ' - ' . __($rideFare->vehicleClass->name) }}
                                                            @lang('Hourly Fare')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="hourly_fare[{{ $rideFare->service_id }}][{{ $rideFare->vehicle_class_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->hourly_fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) . ' - ' . __($rideFare->vehicleClass->name) }}
                                                            @lang('Daily Fare')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="daily_fare[{{ $rideFare->service_id }}][{{ $rideFare->vehicle_class_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->daily_fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) . ' - ' . __($rideFare->vehicleClass->name) }}
                                                            @lang('Monthly Fare')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="monthly_fare[{{ $rideFare->service_id }}][{{ $rideFare->vehicle_class_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->monthly_fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) . ' - ' . __($rideFare->vehicleClass->name) }}
                                                            @lang('Base Fare')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="fare[{{ $rideFare->service_id }}][{{ $rideFare->vehicle_class_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) . ' - ' . __($rideFare->vehicleClass->name) }}
                                                            @lang('Fare per/km')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="per_km_fare[{{ $rideFare->service_id }}][{{ $rideFare->vehicle_class_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->per_km_fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            @if ($rideFare->service_id == 4)
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) }}
                                                            @lang('Hourly Fare')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="hourly_fare[{{ $rideFare->service_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->hourly_fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) }}
                                                            @lang('Daily Fare')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="daily_fare[{{ $rideFare->service_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->daily_fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) }}
                                                            @lang('Monthly Fare')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="monthly_fare[{{ $rideFare->service_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->monthly_fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) }}
                                                            @lang('Base Fare')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="fare[{{ $rideFare->service_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>{{ __($rideFare->service->name) }}
                                                            @lang('Fare per/km')</label>
                                                        <div class="input-group">
                                                            <input type="number" step="any" min="0"
                                                                name="per_km_fare[{{ $rideFare->service_id }}]"
                                                                class="form-control"
                                                                value="{{ getAmount($rideFare->per_km_fare) }}">
                                                            <span
                                                                class="input-group-text">{{ __($general->cur_text) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        @endforeach

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-">
                                    <label>@lang('Have Brand ?')</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_brand"
                                            id="brandYesRadio" value="1" @checked(@$vehicleType->manage_brand == 1)>
                                        <label class="form-check-label" for="brandYesRadio">
                                            @lang('Yes')
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_brand"
                                            id="brandNoRadio" value="0" @checked(@$vehicleType->manage_brand === 0)>
                                        <label class="form-check-label" for="brandNoRadio">
                                            @lang('No')
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
        var vehicleType = @json(@$vehicleType);
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
                let services = $('.services:checked');

                let vehicleClasses = $('.classes').find('option:selected');
                resetAll();

                if (vehicleClasses.length > 0) {
                    makeServiceClassCombination(services, vehicleClasses);
                } else {
                    makeBaseFareElements(services);
                }
            });

            function makeServiceClassCombination(services, vehicleClasses) {
                $.each(services, function(index, service) {
                    let html = '';
                    var serviceId = $(service).val();
                    var serviceName = $(service).siblings('label').text();

                    $.each(vehicleClasses, function(i, vehicleClass) {
                        var vehicleClassId = $(vehicleClass).val();
                        var vehicleClassName = $(vehicleClass).text();
                        var hourlyFare = null;
                        var dailyFare = null;
                        var monthlyFare = null;
                        var baseFare = null;
                        var perKmFare = null;
                        var rideFareModel = null;

                        if (vehicleType != null) {
                            rideFareModel = vehicleType.ride_fares.find(rideFare => rideFare
                                .service_id == serviceId && rideFare.vehicle_class_id ==
                                vehicleClassId);
                        }

                        if (serviceId == 4) {
                            if (rideFareModel) {
                                hourlyFare = absValue(rideFareModel.hourly_fare);
                                dailyFare = absValue(rideFareModel.daily_fare);
                                monthlyFare = absValue(rideFareModel.monthly_fare);
                            }

                            html += `<div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>${serviceName} - ${vehicleClassName} Hourly Fare</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" name="hourly_fare[${serviceId}][${vehicleClassId}]" class="form-control" value="${hourlyFare ?? ''}">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>${serviceName} - ${vehicleClassName} Daily Fare</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" name="daily_fare[${serviceId}][${vehicleClassId}]" class="form-control" value="${dailyFare ?? ''}">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>${serviceName} - ${vehicleClassName} Monthly Fare</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" name="monthly_fare[${serviceId}][${vehicleClassId}]" class="form-control" value="${monthlyFare ?? ''}">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        } else {
                            if (rideFareModel) {
                                baseFare = absValue(rideFareModel.fare);
                                perKmFare = absValue(rideFareModel.per_km_fare);
                            }

                            html += `<div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>${serviceName} - ${vehicleClassName} Base Fare</label>
                                                    <div class="input-group">
                                                        <input type="number" step="any" min="0" name="fare[${serviceId}][${vehicleClassId}]" class="form-control" value="${baseFare ?? ''}">
                                                        <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>${serviceName} - ${vehicleClassName} Fare per/km</label>
                                                    <div class="input-group">
                                                        <input type="number" step="any" min="0" name="per_km_fare[${serviceId}][${vehicleClassId}]" class="form-control" value="${perKmFare ?? ''}">
                                                        <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`;
                        }
                    });

                    $(`.service-fields-${serviceId}`).html(html);
                });
            }

            function makeBaseFareElements(services) {
                $.each(services, function(index, service) {
                    var html = '';
                    var serviceId = $(service).val();
                    var serviceName = $(service).siblings('label').text();

                    var hourlyFare = null;
                    var dailyFare = null;
                    var monthlyFare = null;
                    var baseFare = null;
                    var perKmFare = null;
                    var rideFareModel = null;

                    if (vehicleType != null) {
                        rideFareModel = vehicleType.ride_fares.find(rideFare => rideFare
                            .service_id == serviceId);
                    }


                    if (serviceId == 4) {
                        if (rideFareModel) {
                            hourlyFare = absValue(rideFareModel.hourly_fare);
                            dailyFare = absValue(rideFareModel.daily_fare);
                            monthlyFare = absValue(rideFareModel.monthly_fare);
                        }

                        html += `<div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>${serviceName} Hourly Fare</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" name="hourly_fare[${serviceId}]" class="form-control" value="${hourlyFare ?? ''}">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>${serviceName} Daily Fare</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" name="daily_fare[${serviceId}]" class="form-control" value="${dailyFare ?? ''}">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>${serviceName} Monthly Fare</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" name="monthly_fare[${serviceId}]" class="form-control" value="${monthlyFare ?? ''}">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                    } else {

                        if (rideFareModel) {
                            baseFare = absValue(rideFareModel.fare);
                            perKmFare = absValue(rideFareModel.per_km_fare);
                        }

                        html += `<div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>${serviceName} Base Fare</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" name="fare[${serviceId}]" class="form-control" value="${baseFare ?? ''}">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>${serviceName} Fare per/km</label>
                                                <div class="input-group">
                                                    <input type="number" step="any" min="0" name="per_km_fare[${serviceId}]" class="form-control" value="${perKmFare ?? ''}">
                                                    <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                    }
                    $(`.service-fields-${serviceId}`).html(html);
                });
            }

            function resetAll() {
                let serviceIds = @json($services->pluck('id')->toArray());

                let checkedValues = $('.services:checked').map(function() {
                    return parseInt($(this).val());
                }).get();

                $.each(serviceIds, function(index, serviceId) {
                    if (!checkedValues.includes(serviceId)) {
                        $(`.service-fields-${serviceId}`).html('');
                    }
                });

            }

            function absValue(amount) {
                return Math.abs(parseFloat(amount).toFixed(2));
            }
        });
    </script>
@endpush
