<style>
  @media print {
    .sn-details-row {
      display: table-row !important;
    }
  }
</style>
<table class="table @if(!empty($for_ledger)) table-slim mb-0 bg-light-gray @else bg-gray @endif" @if(!empty($for_pdf)) style="width: 100%;" @endif>
        <tr @if(empty($for_ledger)) class="bg-green" @endif>
        <th>#</th>
        <th>{{ __('sale.product') }}</th>
        @if( session()->get('business.enable_lot_number') == 1 && empty($for_ledger))
            <th>{{ __('lang_v1.lot_n_expiry') }}</th>
        @endif
        @if($sell->type == 'sales_order')
            <th>@lang('lang_v1.quantity_remaining')</th>
        @endif
        <th>{{ __('sale.qty') }}</th>
        @if(!empty($pos_settings['inline_service_staff']))
            <th>
                @lang('restaurant.service_staff')
            </th>
        @endif
        <th>{{ __('sale.unit_price') }}</th>
        <th>{{ __('sale.discount') }}</th>
        <th>{{ __('sale.tax') }}</th>
        <th>{{ __('sale.price_inc_tax') }}</th>
        <th>{{ __('sale.subtotal') }}</th>
    </tr>
    @foreach($sell->sell_lines as $sell_line)
        @php
            $sn_records = $sell_line->relationLoaded('serial_numbers')
                ? $sell_line->serial_numbers
                : \App\ProductSerialNumber::where('transaction_sell_line_id', $sell_line->id)->get();
            $has_sn = !empty($sn_records) && $sn_records->count() > 0;
            $sn_collapse_id = 'sell_line_sn_' . $sell_line->id;
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                @if($has_sn)
                    <a href="#{{ $sn_collapse_id }}" data-toggle="collapse" style="cursor: pointer; text-decoration: none; font-weight: 600;" title="Klik untuk melihat daftar Serial Number">
                        {{ $sell_line->product->name }}
                        @if( $sell_line->product->type == 'variable')
                        - {{ $sell_line->variations->product_variation->name ?? ''}}
                        - {{ $sell_line->variations->name ?? ''}},
                        @endif
                        {{ $sell_line->variations->sub_sku ?? ''}}
                        <span class="label label-info" style="margin-left: 5px; font-weight: normal; font-size: 10px;">
                            <i class="fa fa-barcode"></i> {{ $sn_records->count() }} SN
                        </span>
                    </a>
                @else
                    {{ $sell_line->product->name }}
                    @if( $sell_line->product->type == 'variable')
                    - {{ $sell_line->variations->product_variation->name ?? ''}}
                    - {{ $sell_line->variations->name ?? ''}},
                    @endif
                    {{ $sell_line->variations->sub_sku ?? ''}}
                @endif
                @php
                $brand = $sell_line->product->brand;
                @endphp
                @if(!empty($brand->name))
                , {{$brand->name}}
                @endif

                @if(!empty($sell_line->sell_line_note))
                <br> {{$sell_line->sell_line_note}}
                @endif
                @if($is_warranty_enabled && !empty($sell_line->warranties->first()) )
                    <br><small>{{$sell_line->warranties->first()->display_name ?? ''}} - {{ @format_date($sell_line->warranties->first()->getEndDate($sell->transaction_date))}}</small>
                    @if(!empty($sell_line->warranties->first()->description))
                    <br><small>{{$sell_line->warranties->first()->description ?? ''}}</small>
                    @endif
                @endif

                @if(in_array('kitchen', $enabled_modules) && empty($for_ledger))
                    <br><span class="label @if($sell_line->res_line_order_status == 'cooked' ) bg-red @elseif($sell_line->res_line_order_status == 'served') bg-green @else bg-light-blue @endif">@lang('restaurant.order_statuses.' . $sell_line->res_line_order_status) </span>
                @endif
            </td>
            @if( session()->get('business.enable_lot_number') == 1 && empty($for_ledger))
                <td>{{ $sell_line->lot_details->lot_number ?? '--' }}
                    @if( session()->get('business.enable_product_expiry') == 1 && !empty($sell_line->lot_details->exp_date))
                    ({{@format_date($sell_line->lot_details->exp_date)}})
                    @endif
                </td>
            @endif
            @if($sell->type == 'sales_order')
                <td><span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $sell_line->quantity - $sell_line->so_quantity_invoiced }}</span> @if(!empty($sell_line->sub_unit)) {{$sell_line->sub_unit->short_name}} @else {{$sell_line->product->unit->short_name}} @endif</td>
            @endif
            <td>
                @if(!empty($for_ledger))
                    {{@format_quantity($sell_line->quantity)}}
                @else
                    <span class="display_currency" data-currency_symbol="false" data-is_quantity="true">{{ $sell_line->quantity }}</span> 
                @endif
                    @if(!empty($sell_line->sub_unit)) {{$sell_line->sub_unit->short_name}} @else {{$sell_line->product->unit->short_name}} @endif

                @if(!empty($sell_line->product->second_unit) && $sell_line->secondary_unit_quantity != 0)
                    <br>
                    @if(!empty($for_ledger))
                        {{@format_quantity($sell_line->secondary_unit_quantity)}}
                    @else
                        <span class="display_currency" data-is_quantity="true" data-currency_symbol="false">{{ $sell_line->secondary_unit_quantity }}</span> 
                    @endif
                    {{$sell_line->product->second_unit->short_name}}
                @endif
            </td>
            @if(!empty($pos_settings['inline_service_staff']))
                <td>
                {{ $sell_line->service_staff->user_full_name ?? '' }}
                </td>
            @endif
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->unit_price_before_discount)
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->unit_price_before_discount }}</span>
                @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->get_discount_amount())
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->get_discount_amount() }}</span>
                @endif
                @if($sell_line->line_discount_type == 'percentage') ({{$sell_line->line_discount_amount}}%) @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->item_tax)
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->item_tax }}</span> 
                @endif
                @if(!empty($taxes[$sell_line->tax_id]))
                ( {{ $taxes[$sell_line->tax_id]}} )
                @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->unit_price_inc_tax)
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->unit_price_inc_tax }}</span>
                @endif
            </td>
            <td>
                @if(!empty($for_ledger))
                    @format_currency($sell_line->quantity * $sell_line->unit_price_inc_tax)
                @else
                    <span class="display_currency" data-currency_symbol="true">{{ $sell_line->quantity * $sell_line->unit_price_inc_tax }}</span>
                @endif
            </td>
        </tr>
        @if($has_sn)
            <tr id="{{ $sn_collapse_id }}" class="collapse sn-details-row">
                <td colspan="100%" style="padding: 0; border-top: none;">
                    <div style="padding: 8px 12px; background-color: #fafafa; border-bottom: 1px solid #e2e8f0;">
                        <small class="text-muted" style="font-weight: 600;"><i class="fa fa-barcode"></i> @lang('lang_v1.serial_numbers') ({{ $sn_records->count() }}):</small>
                        <table class="table table-bordered table-condensed bg-white" style="margin-top: 6px; margin-bottom: 0;">
                            <thead>
                                <tr style="background-color: #f1f5f9; color: #475569; font-size: 11px;">
                                    <th style="width: 40px;">#</th>
                                    <th>Serial Number / IMEI</th>
                                    <th class="text-right">Harga Beli (HPP)</th>
                                    <th class="text-right">Harga Jual</th>
                                    <th class="text-center" style="width: 130px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sn_records as $sn)
                                    <tr style="font-size: 11px;">
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong style="color: #2563eb;">{{ $sn->serial_number }}</strong></td>
                                        <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $sn->purchase_price }}</span></td>
                                        <td class="text-right"><span class="display_currency" data-currency_symbol="true">{{ $sn->selling_price }}</span></td>
                                        <td class="text-center">
                                            @if($sn->status == 'in_stock')
                                                <span class="label label-success">Tersedia</span>
                                            @elseif($sn->status == 'sold')
                                                <span class="label label-danger">Terjual</span>
                                            @else
                                                <span class="label label-default">{{ ucfirst($sn->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        @endif
        @if(!empty($sell_line->modifiers))
        @foreach($sell_line->modifiers as $modifier)
            <tr>
                <td>&nbsp;</td>
                <td>
                    {{ $modifier->product->name }} - {{ $modifier->variations->name ?? ''}},
                    {{ $modifier->variations->sub_sku ?? ''}}
                </td>
                @if( session()->get('business.enable_lot_number') == 1)
                    <td>&nbsp;</td>
                @endif
                <td>{{ $modifier->quantity }}</td>
                @if(!empty($pos_settings['inline_service_staff']))
                    <td>
                        &nbsp;
                    </td>
                @endif
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->unit_price)
                    @else
                        <span class="display_currency" data-currency_symbol="true">{{ $modifier->unit_price }}</span>
                    @endif
                </td>
                <td>
                    &nbsp;
                </td>
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->item_tax)
                    @else
                        <span class="display_currency" data-currency_symbol="true">{{ $modifier->item_tax }}</span> 
                    @endif
                    @if(!empty($taxes[$modifier->tax_id]))
                    ( {{ $taxes[$modifier->tax_id]}} )
                    @endif
                </td>
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->unit_price_inc_tax)
                    @else
                        <span class="display_currency" data-currency_symbol="true">{{ $modifier->unit_price_inc_tax }}</span>
                    @endif
                </td>
                <td>
                    @if(!empty($for_ledger))
                        @format_currency($modifier->quantity * $modifier->unit_price_inc_tax)
                    @else
                        <span class="display_currency" data-currency_symbol="true">{{ $modifier->quantity * $modifier->unit_price_inc_tax }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        @endif
    @endforeach
</table>