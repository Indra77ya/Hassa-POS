@extends('layouts.app')
@section('title', __('manufacturing::lang.import_recipe'))

@section('content')
@include('manufacturing::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('manufacturing::lang.import_recipe')</h1>
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
                {!! Form::open(['url' => action([\Modules\Manufacturing\Http\Controllers\RecipeController::class, 'postImportRecipe']), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    {!! Form::label('name', __( 'product.file_to_import' ) . ':') !!}
                                    {!! Form::file('recipes_csv', ['accept'=> '.xls, .xlsx, .csv', 'required' => 'required']); !!}
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
                        <a href="{{ action([\Modules\Manufacturing\Http\Controllers\RecipeController::class, 'downloadImportTemplate'], ['type' => 'csv']) }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-rounded-xl"><i class="fa fa-download"></i> @lang('lang_v1.download_template_file') (CSV)</a>
                        &nbsp;
                        <a href="{{ action([\Modules\Manufacturing\Http\Controllers\RecipeController::class, 'downloadImportTemplate'], ['type' => 'xls']) }}" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-rounded-xl"><i class="fa fa-download"></i> @lang('lang_v1.download_template_file') (Excel)</a>
                    </div>
                </div>
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.instructions')])
                <strong>@lang('lang_v1.instruction_line1')</strong><br>
                @lang('manufacturing::lang.import_recipe_instruction_line2')
                <br><br>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.col_no')</th>
                            <th>@lang('lang_v1.col_name')</th>
                            <th>@lang('lang_v1.instruction')</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>@lang('manufacturing::lang.product_sku') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('manufacturing::lang.product_sku_ins')</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>@lang('manufacturing::lang.output_quantity') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('manufacturing::lang.output_quantity_ins')</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>@lang('manufacturing::lang.output_sub_unit') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('manufacturing::lang.output_sub_unit_ins')</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>@lang('manufacturing::lang.extra_cost') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('manufacturing::lang.extra_cost_ins')</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>@lang('manufacturing::lang.production_cost_type') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('manufacturing::lang.production_cost_type_ins')</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>@lang('manufacturing::lang.instructions') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('manufacturing::lang.recipe_instructions_ins')</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>@lang('manufacturing::lang.ingredient_sku') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('manufacturing::lang.ingredient_sku_ins')</td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>@lang('manufacturing::lang.ingredient_quantity') <small class="text-muted">(@lang('lang_v1.required'))</small></td>
                            <td>@lang('manufacturing::lang.ingredient_quantity_ins')</td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td>@lang('manufacturing::lang.ingredient_sub_unit') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('manufacturing::lang.ingredient_sub_unit_ins')</td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td>@lang('manufacturing::lang.ingredient_waste_percent') <small class="text-muted">(@lang('lang_v1.optional'))</small></td>
                            <td>@lang('manufacturing::lang.ingredient_waste_percent_ins')</td>
                        </tr>
                    </tbody>
                </table>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection
