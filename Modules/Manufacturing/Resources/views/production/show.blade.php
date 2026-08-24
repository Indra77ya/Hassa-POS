<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'manufacturing::lang.production_details' ) (<b>@lang('purchase.ref_no'):</b> #{{ $production_purchase->ref_no }})</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($production_purchase->transaction_date) }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 invoice-col">
                    @lang('business.business'):
                    <address>
                        <strong>{{ $production_purchase->business->name }}</strong>
                        {{ $production_purchase->location->name }}
                        @if(!empty($production_purchase->location->landmark))
                          <br>{{$production_purchase->location->landmark}}
                        @endif
                        @if(!empty($production_purchase->location->city) || !empty($production_purchase->location->state) || !empty($production_purchase->location->country))
                          <br>{{implode(',', array_filter([$production_purchase->location->city, $production_purchase->location->state, $production_purchase->location->country]))}}
                        @endif
                        
                        @if(!empty($production_purchase->business->tax_number_1))
                          <br>{{$production_purchase->business->tax_label_1}}: {{$production_purchase->business->tax_number_1}}
                        @endif

                        @if(!empty($production_purchase->business->tax_number_2))
                          <br>{{$production_purchase->business->tax_label_2}}: {{$production_purchase->business->tax_number_2}}
                        @endif

                        @if(!empty($production_purchase->location->mobile))
                          <br>@lang('contact.mobile'): {{$production_purchase->location->mobile}}
                        @endif
                        @if(!empty($production_purchase->location->email))
                          <br>@lang('business.email'): {{$production_purchase->location->email}}
                        @endif
                    </address>
                </div>
                <div class="col-sm-6 invoice-col">
                    <b>@lang('purchase.ref_no'):</b> #{{ $production_purchase->ref_no }}<br/>
                    <b>@lang('messages.date'):</b> {{ @format_date($production_purchase->transaction_date) }}<br/>
                    <b>@lang('purchase.purchase_status'):</b> {{ ucfirst( $production_purchase->status ) }}<br>
                    <b>@lang('purchase.payment_status'):</b> {{ ucfirst( $production_purchase->payment_status ) }}<br>
                </div>
                <div class="col-sm-12">
                @php
                    $medias = $production_purchase->media;
                @endphp
                @if(count($medias))
                    @include('sell.partials.media_table', ['medias' => $medias])
                @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h4>@lang('manufacturing::lang.product_details')</h4>
                </div>
                <div class="col-md-6">
                    <strong>@lang('sale.product'):</strong>
                    {{$purchase_line->variations->full_name}}
                    @if(request()->session()->get('business.enable_lot_number') == 1)
                        <br><strong>@lang('lang_v1.lot_number'):</strong>
                        {{$purchase_line->lot_number}}
                    @endif
                    @if(session('business.enable_product_expiry'))
                        <br><strong>@lang('product.exp_date'):</strong>
                        @if(!empty($purchase_line->exp_date))       
                            {{@format_date($purchase_line->exp_date)}} 
                        @endif
                    @endif
                </div>
                <div class="col-md-6">
                    <strong>@lang('lang_v1.quantity'):</strong>
                    {{@format_quantity($quantity)}} {{$unit_name}}<br>
                    @if(!is_null($production_purchase->mfg_estimated_quantity))
                        <strong>@lang('manufacturing::lang.estimated_quantity'):</strong>
                        {{@format_quantity($production_purchase->mfg_estimated_quantity / (!empty($base_unit_multiplier) ? $base_unit_multiplier : 1))}} {{$unit_name}}<br>
                    @endif
                    <strong>@lang('manufacturing::lang.waste_units'):</strong>
                    {{@format_quantity($quantity_wasted)}} {{$unit_name}}
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h4>@lang('manufacturing::lang.ingredients')</h4>
                </div>
                <div class="col-md-12">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>@lang('manufacturing::lang.ingredient')</th>
                                <th>@lang('manufacturing::lang.input_quantity')</th>
                                <th>@lang('manufacturing::lang.waste_percent')</th>
                                <th>@lang('manufacturing::lang.final_quantity')</th>
                                <th>@lang('manufacturing::lang.total_price')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_ingredient_price = 0;
                            @endphp
                            @foreach($ingredients as $ingredient)
                                <tr>
                                    <td>
                                        {{$ingredient['full_name']}}
                                        @if(!empty($ingredient['lot_numbers']))
                                            <br>
                                            <small> @lang('lang_v1.lot_n_expiry'):  {{$ingredient['lot_numbers']}}</small>
                                        @endif
                                    </td>
                                    <td>{{@format_quantity($ingredient['quantity'])}} {{$ingredient['unit']}}</td>
                                    <td>{{@format_quantity($ingredient['waste_percent'])}} %</td>
                                    <td>{{@format_quantity($ingredient['final_quantity'])}} {{$ingredient['unit']}}</td>
                                    @php
                                        $price = $ingredient['total_price'];

                                        $total_ingredient_price += $price;
                                    @endphp
                                    <td>
                                         <span class="display_currency" data-currency_symbol="true">{{$price}}</span>
                                    </td>
                                </tr>
                            @endforeach
                            @if(!empty($ingredient_groups))
                                @foreach($ingredient_groups as $ingredient_group)
                                    <tr>
                                        <td colspan="5" class="bg-gray">
                                            <strong>
                                                {{$ingredient_group['ig_name'] ?? ''}}
                                            </strong>
                                            @if(!empty($ingredient_group['ig_description']))
                                                - {{$ingredient_group['ig_description']}}
                                            @endif
                                        </td>
                                    </tr>
                                    @foreach($ingredient_group['ig_ingredients'] as $ingredient)
                                        <tr>
                                            <td>
                                                {{$ingredient['full_name']}}
                                                @if(!empty($ingredient['lot_numbers']))
                                                    <br>
                                                    <small> @lang('lang_v1.lot_n_expiry'):  {{$ingredient['lot_numbers']}}</small>
                                                @endif
                                            </td>
                                            <td>{{@format_quantity($ingredient['quantity'])}} {{$ingredient['unit']}}</td>
                                            <td>{{@format_quantity($ingredient['waste_percent'])}} %</td>
                                            <td>{{@format_quantity($ingredient['final_quantity'])}} {{$ingredient['unit']}}</td>
                                            @php
                                                $price = $ingredient['total_price'];
                                                $total_ingredient_price += $price;
                                            @endphp
                                            <td>
                                                 <span class="display_currency" data-currency_symbol="true">{{$price}}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>@lang('manufacturing::lang.ingredients_cost')</strong></td>
                                <td><span class="display_currency" data-currency_symbol="true">{{$total_ingredient_price}}</span></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right"><strong>{{__('manufacturing::lang.production_cost')}}:</strong></td>
                                <td><span class="display_currency" data-currency_symbol="true">{{$total_production_cost}}</span> </td>
                            </tr>
                            <tr><td colspan="4" class="text-right"><strong>{{__('manufacturing::lang.total_cost')}}:</strong></td>
                                <td><span class="display_currency" data-currency_symbol="true">{{$production_purchase->final_total}}</span></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Accounting & Payment Account Sync Details -->
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <h4>
                        Status Sinkronisasi Akuntansi &amp; Pembayaran:
                        @if($production_purchase->mfg_is_final == 1 && (!empty($accounting_mapping) || !empty($payment_transaction)))
                            <span class="label bg-green">Tersinkronisasi</span>
                        @elseif($production_purchase->mfg_is_final == 1)
                            <span class="label bg-yellow">Final (Belum Ada Akun Terhubung)</span>
                        @else
                            <span class="label bg-red">Belum Final</span>
                        @endif
                    </h4>
                </div>

                @if(!empty($accounting_mapping) && count($accounting_mapping->transactions))
                    <div class="col-md-12">
                        <h5><strong>Jurnal Akuntansi (Double-Entry Journal)</strong></h5>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Akun</th>
                                    <th>Tipe (Debit/Kredit)</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounting_mapping->transactions as $acc_trans)
                                    <tr>
                                        <td>{{ $acc_trans->account->name ?? '-' }} (GL: {{ $acc_trans->account->gl_code ?? '-' }})</td>
                                        <td>
                                            @if($acc_trans->type == 'debit')
                                                <span class="label bg-blue">Debit</span>
                                            @else
                                                <span class="label bg-orange">Kredit</span>
                                            @endif
                                        </td>
                                        <td><span class="display_currency" data-currency_symbol="true">{{ $acc_trans->amount }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if(!empty($payment_transaction))
                    <div class="col-md-12">
                        <h5><strong>Potongan Saldo Kas &amp; Bank (Payment Account)</strong></h5>
                        <p>
                            <strong>Akun Pembayaran:</strong> {{ $payment_transaction->payment_account->name ?? '-' }}<br>
                            <strong>Jumlah Potongan Biaya:</strong> <span class="display_currency" data-currency_symbol="true">{{ $payment_transaction->amount }}</span>
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print" aria-label="Print" 
      onclick="$(this).closest('div.modal-content').printThis();"><i class="fa fa-print"></i> @lang( 'messages.print' )
      </button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->