<div class="row">
	<input type="hidden" class="payment_row_index" value="{{ $row_index}}">
	@php
		$col_class = 'col-md-6';
		if(!empty($accounts)){
			$col_class = 'col-md-4';
		}
		$readonly = $payment_line['method'] == 'advance' ? true : false;
	@endphp
	<div class="{{$col_class}}">
		<div class="form-group">
			{!! Form::label("amount_$row_index" ,__('sale.amount') . ':*') !!}
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::text("payment[$row_index][amount]", @num_format($payment_line['amount']), ['class' => 'form-control payment-amount input_number', 'required', 'id' => "amount_$row_index", 'placeholder' => __('sale.amount'), 'readonly' => $readonly]); !!}
			</div>
		</div>
	</div>
	@if(!empty($show_date))
	<div class="{{$col_class}}">
		<div class="form-group">
			{!! Form::label("paid_on_$row_index" , __('lang_v1.paid_on') . ':*') !!}
			<div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </span>
              {!! Form::text("payment[$row_index][paid_on]", isset($payment_line['paid_on']) ? @format_datetime($payment_line['paid_on']) : @format_datetime('now'), ['class' => 'form-control paid_on', 'readonly', 'required']); !!}
            </div>
		</div>
	</div>
	@endif
	<div class="{{$col_class}}">
		<div class="form-group">
			{!! Form::label("method_$row_index" , __('lang_v1.payment_method') . ':*') !!}
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				@php
					$_payment_method = empty($payment_line['method']) && array_key_exists('cash', $payment_types) ? 'cash' : $payment_line['method'];
				@endphp
				{!! Form::select("payment[$row_index][method]", $payment_types, $_payment_method, ['class' => 'form-control col-md-12 payment_types_dropdown', 'required', 'id' => !$readonly ? "method_$row_index" : "method_advance_$row_index", 'style' => 'width:100%;', 'disabled' => $readonly]); !!}

				@if($readonly)
					{!! Form::hidden("payment[$row_index][method]", $payment_line['method'], ['class' => 'payment_types_dropdown', 'required', 'id' => "method_$row_index"]); !!}
				@endif
			</div>
		</div>
	</div>

	@php
            $pos_settings = !empty(session()->get('business.pos_settings')) ? json_decode(session()->get('business.pos_settings'), true) : [];
            $enable_cash_denomination_for_payment_methods = !empty($pos_settings['enable_cash_denomination_for_payment_methods']) ? $pos_settings['enable_cash_denomination_for_payment_methods'] : [];
        @endphp

        @if(!empty($pos_settings['enable_cash_denomination_on']) && ($pos_settings['enable_cash_denomination_on'] == 'all_screens' || !empty($show_in_pos)) && !empty($show_denomination))
            <input type="hidden" class="enable_cash_denomination_for_payment_methods" value="{{json_encode($enable_cash_denomination_for_payment_methods)}}">
            <div class="clearfix"></div>
            <div class="col-md-12 cash_denomination_div @if(!in_array($payment_line['method'], $enable_cash_denomination_for_payment_methods)) hide @endif">
                <hr>
                <strong>@lang( 'lang_v1.cash_denominations' )</strong>
                  @if(!empty($pos_settings['cash_denominations']))
                    <table class="table table-slim">
                      <thead>
                        <tr>
                          <th width="20%" class="text-right">@lang('lang_v1.denomination')</th>
                          <th width="20%">&nbsp;</th>
                          <th width="20%" class="text-center">@lang('lang_v1.count')</th>
                          <th width="20%">&nbsp;</th>
                          <th width="20%" class="text-left">@lang('sale.subtotal')</th>
                        </tr>
                      </thead>
                      <tbody>
                      	@php
                            $total = 0;
                        @endphp
                        @foreach(explode(',', $pos_settings['cash_denominations']) as $dnm)
                        @php
                            $count = 0;
                            $sub_total = 0;
                            if(!empty($payment_line['denominations'])){
	                            foreach($payment_line['denominations'] as $d) {
	                                if($d['amount'] == $dnm) {
	                                    $count = $d['total_count']; 
	                                    $sub_total = $d['total_count'] * $d['amount'];
	                                    $total += $sub_total;
	                                }
	                            }
	                        }
                        @endphp
                        <tr>
                          <td class="text-right">{{$dnm}}</td>
                          <td class="text-center" >X</td>
                          <td>{!! Form::number("payment[$row_index][denominations][$dnm]", $count, ['class' => 'form-control cash_denomination input-sm', 'min' => 0, 'data-denomination' => $dnm, 'style' => 'width: 100px; margin:auto;' ]); !!}</td>
                          <td class="text-center">=</td>
                          <td class="text-left">
                            <span class="denomination_subtotal">{{@num_format($sub_total)}}</span>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                        <tr>
                          <th colspan="4" class="text-center">@lang('sale.total')</th>
                          <td>
                            <span class="denomination_total">{{@num_format($total)}}</span>
                            <input type="hidden" class="denomination_total_amount" value="{{$total}}">
                            <input type="hidden" class="is_strict" value="{{$pos_settings['cash_denomination_strict_check'] ?? ''}}">
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                    <p class="cash_denomination_error error hide">@lang('lang_v1.cash_denomination_error')</p>
                  @else
                    <p class="help-block">@lang('lang_v1.denomination_add_help_text')</p>
                  @endif
            </div>
            <div class="clearfix"></div>
        @endif
	@if(!empty($accounts))
		@php
			$accounts_attributes = [];
			if (!empty($accounts)) {
				$cash_account_ids = [];
				if (class_exists(\Modules\Accounting\Entities\AccountingAccount::class)) {
					$cash_account_ids = \Modules\Accounting\Entities\AccountingAccount::where('account_sub_type_id', 3)
						->pluck('id')
						->toArray();
				}
				foreach ($accounts as $id => $name) {
					if (empty($id)) continue;
					$acc = \App\Account::find($id);
					$is_cash = false;
					if ($acc) {
						if (!empty($acc->accounting_account_id) && in_array($acc->accounting_account_id, $cash_account_ids)) {
							$is_cash = true;
						} elseif ($acc->account_type && $acc->account_type->fixed_key === 'kas_dan_bank') {
							$is_cash = true;
						}
					}
					$accounts_attributes[$id] = ['data-is-cash' => $is_cash ? 'true' : 'false'];
				}
			}
		@endphp
		<div class="{{$col_class}}">
			<div class="form-group @if($readonly) hide @endif">
				{!! Form::label("account_$row_index" , __('lang_v1.payment_account') . ':' . (!empty($payment_account_required) ? '*' : '')) !!}
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-money-bill-alt"></i>
					</span>
					{!! Form::select("payment[$row_index][account_id]", $accounts, !empty($payment_line['account_id']) ? $payment_line['account_id'] : '' , ['class' => 'form-control select2 account-dropdown', 'id' => "account_$row_index", 'style' => 'width:100%;', 'disabled' => $readonly] + (!empty($payment_account_required) ? ['required' => 'required'] : []), $accounts_attributes); !!}
				</div>
			</div>
		</div>
	@endif
	<div class="clearfix"></div>
		@include('sale_pos.partials.payment_type_details')
	<div class="col-md-12">
		<div class="form-group">
			{!! Form::label("note_$row_index", __('sale.payment_note') . ':') !!}
			{!! Form::textarea("payment[$row_index][note]", $payment_line['note'], ['class' => 'form-control', 'rows' => 3, 'id' => "note_$row_index"]); !!}
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var row_index = "{{ $row_index }}";
    var method_dropdown = $('#method_' + row_index);
    if (!method_dropdown.length) {
        method_dropdown = $('#method_advance_' + row_index);
    }

    if (method_dropdown.length) {
        method_dropdown.off('change.inline_row_handler').on('change.inline_row_handler', function() {
            var payment_type = $(this).val();
            var account_dropdown = $('#account_' + row_index);
            if (!account_dropdown.length) {
                account_dropdown = $('#account_advance_' + row_index);
            }
            if (!account_dropdown.length) {
                account_dropdown = $(this).closest('.payment_row').find('.account-dropdown');
            }

            if (payment_type === 'advance') {
                if (account_dropdown.length) {
                    account_dropdown.prop('disabled', true);
                    if (account_dropdown.hasClass('select2') && typeof account_dropdown.select2 === 'function') {
                        account_dropdown.select2({
                            dropdownParent: $('#modal_payment'),
                            width: '100%'
                        });
                    }
                    account_dropdown.trigger('change');
                    var form_group = account_dropdown.closest('.form-group');
                    form_group.addClass('hide');
                }
            } else {
                if (account_dropdown.length) {
                    account_dropdown.prop('disabled', false);
                    if (account_dropdown.hasClass('select2') && typeof account_dropdown.select2 === 'function') {
                        account_dropdown.select2({
                            dropdownParent: $('#modal_payment'),
                            width: '100%'
                        });
                    }
                    account_dropdown.trigger('change');
                    var form_group = account_dropdown.closest('.form-group');
                    form_group.removeClass('hide');
                }
            }
        });
    }
});
</script>