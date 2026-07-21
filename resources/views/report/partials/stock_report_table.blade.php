@php
  $custom_labels = json_decode(session('business.custom_labels'), true);
  $product_custom_field1 = !empty($custom_labels['product']['custom_field_1']) ? $custom_labels['product']['custom_field_1'] : __('lang_v1.product_custom_field1');
  $product_custom_field2 = !empty($custom_labels['product']['custom_field_2']) ? $custom_labels['product']['custom_field_2'] : __('lang_v1.product_custom_field2');
  $product_custom_field3 = !empty($custom_labels['product']['custom_field_3']) ? $custom_labels['product']['custom_field_3'] : __('lang_v1.product_custom_field3');
  $product_custom_field4 = !empty($custom_labels['product']['custom_field_4']) ? $custom_labels['product']['custom_field_4'] : __('lang_v1.product_custom_field4');
@endphp
<table class="table table-bordered table-striped" id="stock_report_table" style="width: 100%;">
    <thead>
        <tr>
            <th class="not-export" style="white-space: nowrap !important;">@lang('messages.action')</th>
            <th style="white-space: nowrap !important;">SKU</th>
            <th style="white-space: nowrap !important;">@lang('business.product')</th>
            <th style="white-space: nowrap !important;">@lang('lang_v1.variation')</th>
            <th style="white-space: nowrap !important;">@lang('product.category')</th>
            <th style="white-space: nowrap !important;">@lang('sale.location')</th>
            <th style="white-space: nowrap !important;">@lang('purchase.unit_selling_price')</th>
            <th style="white-space: nowrap !important;">@lang('report.current_stock')</th>
            @can('view_product_stock_value')
            <th class="stock_price" style="white-space: nowrap !important;">@lang('lang_v1.total_stock_price') <br><small>(@lang('lang_v1.by_purchase_price'))</small></th>
            <th style="white-space: nowrap !important;">@lang('lang_v1.total_stock_price') <br><small>(@lang('lang_v1.by_sale_price'))</small></th>
            <th style="white-space: nowrap !important;">@lang('lang_v1.potential_profit')</th>
            @endcan
            <th style="white-space: nowrap !important;">@lang('report.total_unit_sold')</th>
            <th style="white-space: nowrap !important;">@lang('lang_v1.total_unit_transfered')</th>
            <th style="white-space: nowrap !important;">@lang('lang_v1.total_unit_adjusted')</th>
            <th style="white-space: nowrap !important;">{{$product_custom_field1}}</th>
            <th style="white-space: nowrap !important;">{{$product_custom_field2}}</th>
            <th style="white-space: nowrap !important;">{{$product_custom_field3}}</th>
            <th style="white-space: nowrap !important;">{{$product_custom_field4}}</th>
            @if($show_manufacturing_data)
                <th class="current_stock_mfg" style="white-space: nowrap !important;">@lang('manufacturing::lang.current_stock_mfg') @show_tooltip(__('manufacturing::lang.mfg_stock_tooltip'))</th>
            @endif
        </tr>
    </thead>
    <tfoot>
        <tr class="bg-gray font-17 text-center footer-total">
            <td colspan="7"><strong>@lang('sale.total'):</strong></td>
            <td class="footer_total_stock"></td>
            @can('view_product_stock_value')
            <td class="footer_total_stock_price"></td>
            <td class="footer_stock_value_by_sale_price"></td>
            <td class="footer_potential_profit"></td>
            @endcan
            <td class="footer_total_sold"></td>
            <td class="footer_total_transfered"></td>
            <td class="footer_total_adjusted"></td>
            <td colspan="4"></td>
            @if($show_manufacturing_data)
                <td class="footer_total_mfg_stock"></td>
            @endif
        </tr>
    </tfoot>
</table>