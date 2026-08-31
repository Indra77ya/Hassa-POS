@extends('layouts.app')
@section('title', __('category.import_categories'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('category.import_categories')
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
                {!! Form::open(['url' => action([\App\Http\Controllers\ImportCategoriesController::class, 'store']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    {!! Form::label('name', __( 'category.file_to_import' ) . ':') !!}
                                    {!! Form::file('categories_csv', ['accept'=> '.xls, .xlsx, .csv', 'required' => 'required']); !!}
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
                        <a href="{{ url('files/import_categories_template.csv') }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-rounded-xl" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file') (CSV)</a>
                        &nbsp;
                        <a href="{{ url('files/import_categories_template.xls') }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-rounded-xl" download><i class="fa fa-download"></i> @lang('lang_v1.download_template_file') (Excel)</a>
                    </div>
                </div>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.instructions')])
                <strong>@lang('category.instruction_line1')</strong><br>
                @lang('category.instruction_line2')
                <br><br>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>@lang('category.col_no')</th>
                            <th>@lang('category.col_name')</th>
                            <th>@lang('category.instruction')</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>@lang('category.category_name') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('category.category_name_ins')</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>@lang('category.code') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('category.category_code_ins')</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>@lang('lang_v1.description') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('category.description_ins')</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>@lang('category.parent_category') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('category.parent_category_ins')</td>
                        </tr>
                    </tbody>
                </table>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection
