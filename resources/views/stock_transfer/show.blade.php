<style>
  @media print {
    .sn-details-row {
      display: table-row !important;
    }
  }
</style>
<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		    <h4 class="modal-title" id="modalTitle"> @lang('lang_v1.stock_transfer_details') (<b>@lang('purchase.ref_no'):</b> #{{ $sell_transfer->ref_no }})
		    </h4>
		</div>
		<div class="modal-body">
				<div class="row invoice-info">
				  <div class="col-sm-4 invoice-col">
				    @lang('lang_v1.location_from'):
				    <address>
				      <strong>{{ $location_details['sell']->name }}</strong>
				      
				      @if(!empty($location_details['sell']->landmark))
				        <br>{{$location_details['sell']->landmark}}
				      @endif

				      @if(!empty($location_details['sell']->city) || !empty($location_details['sell']->state) || !empty($location_details['sell']->country))
				        <br>{{implode(',', array_filter([$location_details['sell']->city, $location_details['sell']->state, $location_details['sell']->country]))}}
				      @endif

				      @if(!empty($sell_transfer->contact->tax_number))
				        <br>@lang('contact.tax_no'): {{$sell_transfer->contact->tax_number}}
				      @endif

				      @if(!empty($location_details['sell']->mobile))
				        <br>@lang('contact.mobile'): {{$location_details['sell']->mobile}}
				      @endif
				      @if(!empty($location_details['sell']->email))
				        <br>Email: {{$location_details['sell']->email}}
				      @endif
				    </address>
				  </div>

				  <div class="col-md-4 invoice-col">
				    @lang('lang_v1.location_to'):
				    <address>
				      <strong>{{ $location_details['purchase']->name }}</strong>
				      
				      @if(!empty($location_details['purchase']->landmark))
				        <br>{{$location_details['purchase']->landmark}}
				      @endif

				      @if(!empty($location_details['purchase']->city) || !empty($location_details['purchase']->state) || !empty($location_details['purchase']->country))
				        <br>{{implode(',', array_filter([$location_details['purchase']->city, $location_details['purchase']->state, $location_details['purchase']->country]))}}
				      @endif

				      @if(!empty($sell_transfer->contact->tax_number))
				        <br>@lang('contact.tax_no'): {{$sell_transfer->contact->tax_number}}
				      @endif

				      @if(!empty($location_details['purchase']->mobile))
				        <br>@lang('contact.mobile'): {{$location_details['purchase']->mobile}}
				      @endif
				      @if(!empty($location_details['purchase']->email))
				        <br>Email: {{$location_details['purchase']->email}}
				      @endif
				    </address>
				  </div>

				  <div class="col-sm-4 invoice-col">
				    <b>@lang('purchase.ref_no'):</b> #{{ $sell_transfer->ref_no }}<br/>
				    <b>@lang('messages.date'):</b> {{ @format_date($sell_transfer->transaction_date) }}<br/>
				    <b>@lang('sale.status'):</b> {{$statuses[$sell_transfer->status] ?? ''}}
				  </div>
				</div>

				<br>
				<div class="row">
				  <div class="col-xs-12">
				    <div class="table-responsive">
				      <table class="table bg-gray">
				        <tr class="bg-green">
				          <th>#</th>
				          <th>@lang('sale.product')</th>
				          <th>@lang('sale.qty')</th>
				          <th class="@cannot('view_purchase_price') show_price_with_permission no-print @endcan">@lang('sale.subtotal')</th>
				        </tr>
				        @php 
				          $total = 0.00;
				        @endphp
				        @foreach($sell_transfer->sell_lines as $sell_lines)
				          @php
				            $sn_records = $sell_lines->relationLoaded('serial_numbers')
				                ? $sell_lines->serial_numbers
				                : \App\ProductSerialNumber::where('transaction_sell_line_id', $sell_lines->id)->get();
				            $has_sn = !empty($sn_records) && $sn_records->count() > 0;
				            $sn_collapse_id = 'stock_transfer_sn_' . $sell_lines->id;
				          @endphp
				          <tr>
				            <td>{{ $loop->iteration }}</td>
				            <td>
				              @if($has_sn)
				                <a href="#{{ $sn_collapse_id }}" data-toggle="collapse" style="cursor: pointer; text-decoration: none; font-weight: 600;" title="Klik untuk melihat daftar Serial Number">
				                  {{ $sell_lines->product->name }}
				                  @if( $sell_lines->product->type == 'variable')
				                    - {{ $sell_lines->variations->product_variation->name}}
				                    - {{ $sell_lines->variations->name}}
				                  @endif
				                  <span class="label label-info" style="margin-left: 5px; font-weight: normal; font-size: 10px;">
				                    <i class="fa fa-barcode"></i> {{ $sn_records->count() }} SN
				                  </span>
				                </a>
				              @else
				                {{ $sell_lines->product->name }}
				                @if( $sell_lines->product->type == 'variable')
				                  - {{ $sell_lines->variations->product_variation->name}}
				                  - {{ $sell_lines->variations->name}}
				                @endif
				              @endif
				               - {{ $sell_lines->variations->sub_sku}}
				               @if($lot_n_exp_enabled && !empty($sell_lines->lot_details))
				                <br>
				                <strong>@lang('lang_v1.lot_n_expiry'):</strong> 
				                @if(!empty($sell_lines->lot_details->lot_number))
				                  {{$sell_lines->lot_details->lot_number}}
				                @endif
				                @if(!empty($sell_lines->lot_details->exp_date))
				                  - {{@format_date($sell_lines->lot_details->exp_date)}}
				                @endif
				               @endif
				            </td>
				            <td>{{ @format_quantity($sell_lines->quantity) }} @if(!empty($sell_lines->sub_unit)) {{$sell_lines->sub_unit->short_name}} @else {{$sell_lines->product->unit->short_name}} @endif</td>
				            <td class="@cannot('view_purchase_price') show_price_with_permission no-print @endcan">
				              <span class="display_currency " data-currency_symbol="true">{{ $sell_lines->unit_price_inc_tax * $sell_lines->quantity }}</span>
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
				          @php 
				            $total += ($sell_lines->unit_price_inc_tax * $sell_lines->quantity);
				          @endphp
				        @endforeach
				      </table>
				    </div>
				  </div>
				</div>
				<br>
				<div class="row">
				  
				  <div class="col-xs-12 col-md-6 col-md-offset-6">
				    <div class="table-responsive">
				      <table class="table">
				        <tr class="@cannot('view_purchase_price') show_price_with_permission no-print @endcan">
				          <th >@lang('purchase.net_total_amount'): </th>
				          <td></td>
				          <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $total }}</span></td>
				        </tr>
				        @if( !empty( $sell_transfer->shipping_charges ) )
				          <tr class="@cannot('view_purchase_price') show_price_with_permission no-print @endcan">
				            <th>@lang('purchase.additional_shipping_charges'):</th>
				            <td><b>(+)</b></td>
				            <td><span class="display_currency pull-right" data-currency_symbol="true">{{ $sell_transfer->shipping_charges }}</span></td>
				          </tr>
				        @endif
				        <tr class="@cannot('view_purchase_price') show_price_with_permission no-print @endcan">
				          <th>@lang('purchase.purchase_total'):</th>
				          <td></td>
				          <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $sell_transfer->final_total }}</span></td>
				        </tr>
				      </table>
				    </div>
				  </div>
				</div>
				<div class="row">
				  <div class="col-sm-6">
				    <strong>@lang('purchase.additional_notes'):</strong><br>
				    <p class="well well-sm no-shadow bg-gray">
				      @if($sell_transfer->additional_notes)
				        {{ $sell_transfer->additional_notes }}
				      @else
				        --
				      @endif
				    </p>
				  </div>
				</div>
				<div class="row">
			      <div class="col-md-12">
			            <strong>{{ __('lang_v1.activities') }}:</strong><br>
			            @includeIf('activity_log.activities', ['activity_type' => 'sell'])
			        </div>
			    </div>
				<div class="row print_section">
				  <div class="col-xs-12">
				    <img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($sell_transfer->ref_no, 'C128', 2,30,array(39, 48, 54), true)}}">
				  </div>
				</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print" aria-label="Print" 
			onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> @lang( 'messages.print' )
			</button>
			<button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
		</div>
	</div>
</div>