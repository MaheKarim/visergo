@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('admin.vehicle.type.store') }}" method="POST">
                        @csrf
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
                                        <div class="col-md-3">
                                            <label> @lang('Select Services') </label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_ride"  value="1" id="flexCheckDefault">
                                                <label class="form-check-label" for="flexCheckDefault">
                                                    Riding Service
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_intercity" value="1" id="is_intercity">
                                                <label class="form-check-label" for="is_intercity">
                                                    Intercity Service
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mt-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_rental" value="1" id="is_rental">
                                                <label class="form-check-label" for="is_rental">
                                                    Rental Service
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_reserve" value="1" id="is_reserve">
                                                <label class="form-check-label" for="is_reserve">
                                                    Reserve Service
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label>@lang('Is Vehicle Have Class ?')</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_class" id="yesRadio" value="1">
                                        <label class="form-check-label" for="yesRadio">
                                            Yes
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manage_class" id="noRadio" value="0">
                                        <label class="form-check-label" for="noRadio">
                                            No
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Base Fare')</label>
                                    <div class="input-group">
                                        <input class="form-control" name="base_fare" type="number">
                                        <span class="input-group-text">{{ $general->cur_text }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Status')</label>
                                    <select class="form-control" name="classInput" multiple>
                                        @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn--primary btn-block">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            // Hide the Base Fare and classInput inputs initially
            $('input[name="base_fare"]').closest('.col-md-6').hide();
            $('select[name="classInput"]').closest('.col-md-6').hide();

            // Event listener for radio buttons
            $('input[name="manage_class"]').change(function() {
                // Check if 'Yes' radio is selected
                if ($(this).val() === '1') {
                    // Show the classInput input
                    $('select[name="classInput"]').closest('.col-md-6').show();
                    // Hide the Base Fare input
                    $('input[name="base_fare"]').closest('.col-md-6').hide();
                } else {
                    // Hide the classInput input if 'No' radio is selected
                    $('select[name="classInput"]').closest('.col-md-6').hide();
                    // Show the Base Fare input
                    $('input[name="base_fare"]').closest('.col-md-6').show();
                }
            });
        });
    </script>
@endpush
