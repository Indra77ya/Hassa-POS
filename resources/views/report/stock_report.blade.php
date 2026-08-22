@extends('layouts.app')
@section('title', __('report.stock_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-text-black">{{ __('report.stock_report')}}</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']), 'method' => 'get', 'id' => 'stock_report_filter_form' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('category_id', __('category.category') . ':') !!}
                        {!! Form::select('category', $categories, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'category_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                        {!! Form::select('sub_category', array(), null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'sub_category_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('brand', __('product.brand') . ':') !!}
                        {!! Form::select('brand', $brands, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('unit',__('product.unit') . ':') !!}
                        {!! Form::select('unit', $units, null, ['placeholder' => __('messages.all'), 'class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>
                @if($show_manufacturing_data)
                    <div class="col-md-3">
                        <div class="form-group">
                            <br>
                            <div class="checkbox">
                                <label>
                                  {!! Form::checkbox('only_mfg', 1, false, 
                                  [ 'class' => 'input-icheck', 'id' => 'only_mfg_products']); !!} {{ __('manufacturing::lang.only_mfg_products') }}
                                </label>
                            </div>
                        </div>
                    </div>
                @endif
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    @can('view_product_stock_value')
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
            <table class="table no-border">
                <tr>
                    <td>@lang('report.closing_stock') (@lang('lang_v1.by_purchase_price'))</td>
                    <td>@lang('report.closing_stock') (@lang('lang_v1.by_sale_price'))</td>
                    <td>@lang('lang_v1.potential_profit')</td>
                    <td>@lang('lang_v1.profit_margin')</td>
                </tr>
                <tr>
                    <td><h3 id="closing_stock_by_pp" class="mb-0 mt-0"></h3></td>
                    <td><h3 id="closing_stock_by_sp" class="mb-0 mt-0"></h3></td>
                    <td><h3 id="potential_profit" class="mb-0 mt-0"></h3></td>
                    <td><h3 id="profit_margin" class="mb-0 mt-0"></h3></td>
                </tr>
            </table>
            @endcomponent
        </div>
    </div>
    @endcan
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-solid'])
                @include('report.partials.stock_report_table')
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
    @php
        $custom_labels = json_decode(session('business.custom_labels'), true);

        $is_cf1_active = false;
        if (!empty($custom_labels['product']['custom_field_1'])) {
            $val = trim($custom_labels['product']['custom_field_1']);
            if ($val !== '' && $val !== 'Custom Field1' && $val !== 'Custom Field 1' && $val !== 'Opsional 1' && $val !== __('lang_v1.product_custom_field1')) {
                $is_cf1_active = true;
            }
        }

        $is_cf2_active = false;
        if (!empty($custom_labels['product']['custom_field_2'])) {
            $val = trim($custom_labels['product']['custom_field_2']);
            if ($val !== '' && $val !== 'Custom Field2' && $val !== 'Custom Field 2' && $val !== 'Opsional 2' && $val !== __('lang_v1.product_custom_field2')) {
                $is_cf2_active = true;
            }
        }

        $is_cf3_active = false;
        if (!empty($custom_labels['product']['custom_field_3'])) {
            $val = trim($custom_labels['product']['custom_field_3']);
            if ($val !== '' && $val !== 'Custom Field3' && $val !== 'Custom Field 3' && $val !== 'Opsional 3' && $val !== __('lang_v1.product_custom_field3')) {
                $is_cf3_active = true;
            }
        }

        $is_cf4_active = false;
        if (!empty($custom_labels['product']['custom_field_4'])) {
            $val = trim($custom_labels['product']['custom_field_4']);
            if ($val !== '' && $val !== 'Custom Field4' && $val !== 'Custom Field 4' && $val !== 'Opsional 4' && $val !== __('lang_v1.product_custom_field4')) {
                $is_cf4_active = true;
            }
        }
    @endphp
    <script type="text/javascript">
        window.product_custom_labels = {
            custom_field_1: "{{ $is_cf1_active ? 1 : 0 }}",
            custom_field_2: "{{ $is_cf2_active ? 1 : 0 }}",
            custom_field_3: "{{ $is_cf3_active ? 1 : 0 }}",
            custom_field_4: "{{ $is_cf4_active ? 1 : 0 }}"
        };
    </script>
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection