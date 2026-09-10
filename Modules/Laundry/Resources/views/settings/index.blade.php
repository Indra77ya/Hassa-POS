@extends('layouts.app')
@section('title', __('laundry::lang.laundry_settings'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('laundry::lang.laundry_settings')</h1>
</section>

<!-- Main content -->
<section class="content">
    {!! Form::open(['url' => route('laundry.settings.store'), 'method' => 'post', 'id' => 'laundry_settings_form', 'files' => true]) !!}
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('laundry::lang.laundry_settings')])
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('laundry_logo', __('laundry::lang.laundry_logo') . ':') !!}
                            {!! Form::file('laundry_logo', ['accept' => 'image/*']) !!}
                            <p class="help-block">
                                @lang('laundry::lang.laundry_logo_help')
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        @if(!empty($laundry_settings['laundry_logo']) && file_exists(public_path('uploads/laundry_logos/' . $laundry_settings['laundry_logo'])))
                            <div class="form-group">
                                <label>@lang('lang_v1.current_image'):</label><br>
                                <img src="{{ asset('uploads/laundry_logos/' . $laundry_settings['laundry_logo']) }}" class="img-thumbnail" style="max-height: 120px;" alt="Laundry Logo">
                                <div class="checkbox">
                                    <label>
                                        {!! Form::checkbox('remove_laundry_logo', 1, false) !!} @lang('laundry::lang.remove_laundry_logo')
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-primary btn-big">@lang('messages.update')</button>
        </div>
    </div>
    {!! Form::close() !!}
</section>
@endsection
