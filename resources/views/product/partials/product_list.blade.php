@php 
    $colspan = 15;
    $custom_labels = json_decode(session('business.custom_labels'), true);
@endphp
<table class="table table-bordered table-striped ajax_view hide-footer" id="product_table" style="width: 100%;">
    <thead>
        <tr>
            <th class="not-export" style="white-space: nowrap !important;"><input type="checkbox" id="select-all-row" data-table-id="product_table"></th>
            {{-- Remove data-pdf-include to hide product images from the PDF export --}}
            <th style="width: 50px !important; white-space: nowrap !important;" class="not-export" data-pdf-include="image">{{__('lang_v1.product_image')}} </th>
            <th class="not-export" style="white-space: nowrap !important;">@lang('messages.action')</th>
            <th style="white-space: nowrap !important;">@lang('sale.product')</th>
            <th style="white-space: nowrap !important;">@lang('purchase.business_location') @show_tooltip(__('lang_v1.product_business_location_tooltip'))</th>
            @can('view_purchase_price')
                @php 
                    $colspan++;
                @endphp
                <th style="white-space: nowrap !important;">@lang('lang_v1.unit_perchase_price')</th>
            @endcan
            @can('access_default_selling_price')
                @php 
                    $colspan++;
                @endphp
                <th style="white-space: nowrap !important;">@lang('lang_v1.selling_price')</th>
            @endcan
            <th style="white-space: nowrap !important;">@lang('report.current_stock')</th>
            <th style="white-space: nowrap !important;">@lang('product.product_type')</th>
            <th style="white-space: nowrap !important;">@lang('product.category')</th>
            <th style="white-space: nowrap !important;">@lang('product.brand')</th>
            <th style="white-space: nowrap !important;">@lang('product.tax')</th>
            <th style="white-space: nowrap !important;">@lang('product.sku')</th>
            <th id="cf_1" style="white-space: nowrap !important;">{{ $custom_labels['product']['custom_field_1'] ?? '' }}</th>
            <th id="cf_2" style="white-space: nowrap !important;">{{ $custom_labels['product']['custom_field_2'] ?? '' }}</th>
            <th id="cf_3" style="white-space: nowrap !important;">{{ $custom_labels['product']['custom_field_3'] ?? '' }}</th>
            <th id="cf_4" style="white-space: nowrap !important;">{{ $custom_labels['product']['custom_field_4'] ?? '' }}</th>
            <th id="cf_5" style="white-space: nowrap !important;">{{ $custom_labels['product']['custom_field_5'] ?? '' }}</th>
            <th id="cf_6" style="white-space: nowrap !important;">{{ $custom_labels['product']['custom_field_6'] ?? '' }}</th>
            <th id="cf_7" style="white-space: nowrap !important;">{{ $custom_labels['product']['custom_field_7'] ?? '' }}</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="{{$colspan}}">
            <div style="display: flex; width: 100%;">
                @can('product.delete')
                    {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'massDestroy']), 'method' => 'post', 'id' => 'mass_delete_form' ]) !!}
                    {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']); !!}
                    {!! Form::submit(__('lang_v1.delete_selected'), array('class' => 'tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error', 'id' => 'delete-selected')) !!}
                    {!! Form::close() !!}
                @endcan

                
                    @can('product.update')
                    
                        @if(config('constants.enable_product_bulk_edit'))
                            &nbsp;
                            {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'bulkEdit']), 'method' => 'post', 'id' => 'bulk_edit_form' ]) !!}
                            {!! Form::hidden('selected_products', null, ['id' => 'selected_products_for_edit']); !!}
                            <button type="submit" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary" id="edit-selected"> <i class="fa fa-edit"></i>{{__('lang_v1.bulk_edit')}}</button>
                            {!! Form::close() !!}
                        @endif
                        &nbsp;
                        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-accent  update_product_location" data-type="add">@lang('lang_v1.add_to_location')</button>
                        &nbsp;
                        <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-neutral update_product_location" data-type="remove">@lang('lang_v1.remove_from_location')</button>
                    @endcan
                
                &nbsp;
                {!! Form::open(['url' => action([\App\Http\Controllers\ProductController::class, 'massDeactivate']), 'method' => 'post', 'id' => 'mass_deactivate_form' ]) !!}
                {!! Form::hidden('selected_products', null, ['id' => 'selected_products']); !!}
                {!! Form::submit(__('lang_v1.deactivate_selected'), array('class' => 'tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning', 'id' => 'deactivate-selected')) !!}
                {!! Form::close() !!} @show_tooltip(__('lang_v1.deactive_product_tooltip'))
                &nbsp;
                @if($is_woocommerce)
                    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning toggle_woocomerce_sync">
                        @lang('lang_v1.woocommerce_sync')
                    </button>
                @endif
                </div>
            </td>
        </tr>
    </tfoot>
</table>
