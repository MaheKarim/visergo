@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <form action="{{ route('admin.vehicle.type.store') }}" method="POST">
                    @csrf
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Type Name')</label>
                                    <input class="form-control" name="name" type="text" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="row">

                                        <label> @lang('Select Services') </label>
                                        @foreach ($services as $service)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="service"
                                                        value="{{ $service->id }}" id="service-{{ $service->id }}">
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
                                            value="1">
                                        <label class="form-check-label" for="yesRadio">
                                            Yes
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_class" id="noRadio"
                                            value="0">
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
                                        <input class="form-control" name="base_fare" type="number">
                                        <span class="input-group-text">{{ $general->cur_text }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-none classArea">
                                <div class="form-group">
                                    <label>@lang('Select Class')</label>
                                    <select class="form-control" name="classes[]" multiple>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row fareList">

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

            $('[name=manage_class]').on('change', function() {
                let manageClass = $(this).val();
                if (manageClass == 1) {
                    $('.classArea').removeClass('d-none');
                    $('.fareArea').addClass('d-none');
                } else {
                    $('.classArea').addClass('d-none');
                    $('.fareArea').removeClass('d-none');
                }
            });

            let selectedService = [];

            $('[name=service]').on('change', function() {
                selectedService = [];

                $('[name=service]:checked').each(function() {
                    selectedService.push($(this).val());
                });

                console.log(selectedService);

                generateFareHtml();

            });

            let selectedClasses = [];

            $('[name="classes[]"]').on('change', function() {
                selectedClasses = $(this).find(':selected').map(function() {
                    return $(this).val();
                }).get();
                generateFareHtml();
            });


            let classes = @json($classes);
            let services = @json($services);

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

            function generateFareHtml()
            {
                let html = '';
                selectedService.forEach(service => {
                    console.log(service, getNameUsingId(service));
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























            $('select[multiple]').select2();

            // Hide the Base Fare and classInput inputs initially
            // $('input[name="base_fare"]').closest('.col-md-6').hide();
            // $('select[name="classes[]"]').closest('.col-md-6').hide();

            // // Event listener for radio buttons
            // $('input[name="manage_class"]').on('change', function() {
            //     // Check if 'Yes' radio is selected
            //     if ($(this).val() === '1') {
            //         // Show the classInput input
            //         $('select[name="classes[]"]').closest('.col-md-6').show();
            //         // Hide the Base Fare input
            //         $('input[name="base_fare"]').closest('.col-md-6').hide();
            //     } else {
            //         // Hide the classInput input if 'No' radio is selected
            //         $('select[name="classes[]"]').closest('.col-md-6').hide();
            //         // Show the Base Fare input
            //         $('input[name="base_fare"]').closest('.col-md-6').show();
            //     }
            // });


        });
    </script>
@endpush
