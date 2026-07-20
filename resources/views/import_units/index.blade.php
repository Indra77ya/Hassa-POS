@extends('layouts.app')
@section('title', __('unit.import_units'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('unit.import_units')
    </h1>
</section>

<!-- Main content -->
<section class="content">

    @if (session('notification') || !empty($notification))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    @if(!empty($notification['msg']))
                        {{$notification['msg']}}
                    @elseif(session('notification.msg'))
                        {{ session('notification.msg') }}
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary'])
                {!! Form::open(['url' => action([\App\Http\Controllers\ImportUnitsController::class, 'store']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    {!! Form::label('name', __( 'unit.file_to_import' ) . ':') !!}
                                    {!! Form::file('units_csv', ['accept'=> '.xls, .xlsx, .csv', 'required' => 'required']); !!}
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <br>
                                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white theme-btn-bg tw-rounded-xl">@lang('messages.submit')</button>
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
                <br><br>
                <div class="row">
                    <div class="col-sm-6">
                        <a href="{{ url('files/import_units_template.csv') }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-rounded-xl" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file') (CSV)</a>
                        &nbsp;
                        <a href="{{ url('files/import_units_template.xls') }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-rounded-xl" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file') (Excel)</a>
                    </div>
                </div>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.instructions')])
                <strong>@lang('unit.instruction_line1')</strong><br>
                @lang('unit.instruction_line2')
                <br><br>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>@lang('unit.col_no')</th>
                            <th>@lang('unit.col_name')</th>
                            <th>@lang('unit.instruction')</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>@lang('unit.name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('unit.actual_name_ins')</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>@lang('unit.short_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('unit.short_name_ins')</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>@lang('unit.allow_decimal') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('unit.allow_decimal_ins')</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>@lang('unit.define_base_unit') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('unit.define_base_unit_ins')</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>@lang('unit.times_base_unit') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('unit.base_unit_multiplier_ins')</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>@lang('unit.base_unit') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('unit.base_unit_name_ins')</td>
                        </tr>
                    </tbody>
                </table>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection