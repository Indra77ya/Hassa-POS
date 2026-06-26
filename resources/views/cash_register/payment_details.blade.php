<div class="row mini_print">
  <div class="col-sm-12">

    {{-- SECTION 1: CASH DRAWER SUMMARY --}}
    <h4 style="margin-top:0; margin-bottom:10px; font-weight:bold; border-bottom:2px solid #333; padding-bottom:5px;">
      @lang('cash_register.cash_drawer_summary')
    </h4>
    <table class="table table-condensed">
      <tr>
        <td>@lang('cash_register.opening_balance'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->cash_in_hand }}</span></td>
      </tr>
      <tr>
        <td>(+) @lang('cash_register.cash_received_sales'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash }}</span></td>
      </tr>
      <tr>
        <td>(+) @lang('cash_register.cash_received_due_payment'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_advance_payment }}</span></td>
      </tr>
      <tr>
        <td>(-) @lang('cash_register.cash_refunds'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_refund }}</span></td>
      </tr>
      <tr>
        <td>(-) @lang('cash_register.cash_expenses'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_expense }}</span></td>
      </tr>
      <tr class="success" style="font-weight:bold;">
        <td><b>@lang('cash_register.expected_cash_in_drawer'):</b></td>
        <td>
          <b><span class="display_currency" data-currency_symbol="true">{{ $register_details->cash_in_hand + $register_details->total_cash + $register_details->total_cash_advance_payment - $register_details->total_cash_refund - $register_details->total_cash_expense }}</span></b>
        </td>
      </tr>
    </table>

    <hr>

    {{-- SECTION 2: SALES SUMMARY --}}
    <h4 style="margin-bottom:10px; font-weight:bold; border-bottom:2px solid #333; padding-bottom:5px;">
      @lang('cash_register.sales_summary')
    </h4>
    <table class="table table-condensed">
      <tr>
        <td>@lang('cash_register.gross_sales'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $details['transaction_details']->total_sales }}</span></td>
      </tr>
      <tr>
        <td>(-) @lang('cash_register.total_refund'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_refund }}</span></td>
      </tr>
      <tr class="success" style="font-weight:bold;">
        <td><b>@lang('cash_register.net_sales'):</b></td>
        <td>
          <b><span class="display_currency" data-currency_symbol="true">{{ $details['transaction_details']->total_sales - $register_details->total_refund }}</span></b>
        </td>
      </tr>
    </table>

    <hr>

    {{-- SECTION 3: PAYMENTS COLLECTED (by method) with Due Payments column --}}
    <h4 style="margin-bottom:10px; font-weight:bold; border-bottom:2px solid #333; padding-bottom:5px;">
      @lang('cash_register.payments_collected')
    </h4>
    @php
      $has_any_payment = $register_details->total_sale > 0 || $register_details->total_advance_payment > 0 || $register_details->total_expense > 0;
    @endphp
    @if($has_any_payment)
    <table class="table table-condensed">
      <tr>
        <th>@lang('lang_v1.payment_method')</th>
        <th>@lang('sale.sale')</th>
        <th>@lang('cash_register.due_payments_collected')</th>
        <th>@lang('lang_v1.expense')</th>
      </tr>
      @if($register_details->total_cash > 0 || $register_details->total_cash_advance_payment > 0 || $register_details->total_cash_expense > 0)
      <tr>
        <td>@lang('cash_register.cash_payment'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_advance_payment }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_expense }}</span></td>
      </tr>
      @endif
      @if($register_details->total_cheque > 0 || $register_details->total_cheque_advance_payment > 0 || $register_details->total_cheque_expense > 0)
      <tr>
        <td>@lang('cash_register.checque_payment'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cheque }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cheque_advance_payment }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cheque_expense }}</span></td>
      </tr>
      @endif
      @if($register_details->total_card > 0 || $register_details->total_card_advance_payment > 0 || $register_details->total_card_expense > 0)
      <tr>
        <td>@lang('cash_register.card_payment'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card_advance_payment }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card_expense }}</span></td>
      </tr>
      @endif
      @if($register_details->total_bank_transfer > 0 || $register_details->total_bank_transfer_advance_payment > 0 || $register_details->total_bank_transfer_expense > 0)
      <tr>
        <td>@lang('cash_register.bank_transfer'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer_advance_payment }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer_expense }}</span></td>
      </tr>
      @endif
      @if($register_details->total_advance > 0 || $register_details->total_advance_expense > 0)
      <tr>
        <td>@lang('lang_v1.advance_payment'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_advance }}</span></td>
        <td>--</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_advance_expense }}</span></td>
      </tr>
      @endif
      @if(array_key_exists('custom_pay_1', $payment_types) && ($register_details->total_custom_pay_1 > 0 || $register_details->total_custom_pay_1_advance_payment > 0 || $register_details->total_custom_pay_1_expense > 0))
        <tr>
          <td>{{$payment_types['custom_pay_1']}}:</td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_1 }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_1_advance_payment }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_1_expense }}</span></td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_2', $payment_types) && ($register_details->total_custom_pay_2 > 0 || $register_details->total_custom_pay_2_advance_payment > 0 || $register_details->total_custom_pay_2_expense > 0))
        <tr>
          <td>{{$payment_types['custom_pay_2']}}:</td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_2 }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_2_advance_payment }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_2_expense }}</span></td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_3', $payment_types) && ($register_details->total_custom_pay_3 > 0 || $register_details->total_custom_pay_3_advance_payment > 0 || $register_details->total_custom_pay_3_expense > 0))
        <tr>
          <td>{{$payment_types['custom_pay_3']}}:</td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_3 }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_3_advance_payment }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_3_expense }}</span></td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_4', $payment_types) && ($register_details->total_custom_pay_4 > 0 || $register_details->total_custom_pay_4_advance_payment > 0 || $register_details->total_custom_pay_4_expense > 0))
        <tr>
          <td>{{$payment_types['custom_pay_4']}}:</td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_4 }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_4_advance_payment }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_4_expense }}</span></td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_5', $payment_types) && ($register_details->total_custom_pay_5 > 0 || $register_details->total_custom_pay_5_advance_payment > 0 || $register_details->total_custom_pay_5_expense > 0))
        <tr>
          <td>{{$payment_types['custom_pay_5']}}:</td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_5 }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_5_advance_payment }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_5_expense }}</span></td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_6', $payment_types) && ($register_details->total_custom_pay_6 > 0 || $register_details->total_custom_pay_6_advance_payment > 0 || $register_details->total_custom_pay_6_expense > 0))
        <tr>
          <td>{{$payment_types['custom_pay_6']}}:</td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_6 }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_6_advance_payment }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_6_expense }}</span></td>
        </tr>
      @endif
      @if(array_key_exists('custom_pay_7', $payment_types) && ($register_details->total_custom_pay_7 > 0 || $register_details->total_custom_pay_7_advance_payment > 0 || $register_details->total_custom_pay_7_expense > 0))
        <tr>
          <td>{{$payment_types['custom_pay_7']}}:</td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_7 }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_7_advance_payment }}</span></td>
          <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_7_expense }}</span></td>
        </tr>
      @endif
      @if($register_details->total_other > 0 || $register_details->total_other_advance_payment > 0 || $register_details->total_other_expense > 0)
      <tr>
        <td>@lang('cash_register.other_payments'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_other }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_other_advance_payment }}</span></td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_other_expense }}</span></td>
      </tr>
      @endif
      {{-- Totals row aligned under respective columns --}}
      <tr style="font-weight:bold; border-top:2px solid #333;">
        <td><b>@lang('receipt.total')</b></td>
        <td class="success" style="border:1px solid #d6e9c6;">
          <b><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_sale }}</span></b>
        </td>
        <td class="info" style="border:1px solid #bce8f1;">
          <b><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_advance_payment }}</span></b>
        </td>
        <td class="danger" style="border:1px solid #ebccd1;">
          <b><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_expense }}</span></b>
        </td>
      </tr>
      {{-- Grand Total Collected row --}}
      <tr class="success" style="font-weight:bold;">
        <td colspan="2"><b>@lang('cash_register.grand_total_collected'):</b></td>
        <td colspan="2">
          <b><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_sale + $register_details->total_advance_payment }}</span></b>
        </td>
      </tr>
    </table>
    @else
    <p class="text-muted text-center">@lang('lang_v1.no_data')</p>
    @endif

    {{-- Refund breakdown --}}
    @if($register_details->total_refund > 0)
    <table class="table table-condensed">
      <tr class="danger">
        <th>@lang('cash_register.total_refund')</th>
        <td>
          <b><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_refund }}</span></b><br>
          <small>
          @if($register_details->total_cash_refund != 0)
            Cash: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cash_refund }}</span><br>
          @endif
          @if($register_details->total_cheque_refund != 0)
            Cheque: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_cheque_refund }}</span><br>
          @endif
          @if($register_details->total_card_refund != 0)
            Card: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_card_refund }}</span><br>
          @endif
          @if($register_details->total_bank_transfer_refund != 0)
            Bank Transfer: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_bank_transfer_refund }}</span><br>
          @endif
          @if(array_key_exists('custom_pay_1', $payment_types) && $register_details->total_custom_pay_1_refund != 0)
            {{$payment_types['custom_pay_1']}}: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_1_refund }}</span><br>
          @endif
          @if(array_key_exists('custom_pay_2', $payment_types) && $register_details->total_custom_pay_2_refund != 0)
            {{$payment_types['custom_pay_2']}}: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_2_refund }}</span><br>
          @endif
          @if(array_key_exists('custom_pay_3', $payment_types) && $register_details->total_custom_pay_3_refund != 0)
            {{$payment_types['custom_pay_3']}}: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_custom_pay_3_refund }}</span><br>
          @endif
          @if($register_details->total_other_refund != 0)
            Other: <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_other_refund }}</span>
          @endif
          </small>
        </td>
      </tr>
    </table>
    @endif

    <hr>

    {{-- SECTION 4: OUTSTANDING RECEIVABLES --}}
    <h4 style="margin-bottom:10px; font-weight:bold; border-bottom:2px solid #333; padding-bottom:5px;">
      @lang('cash_register.outstanding_receivables')
    </h4>
    <table class="table table-condensed">
      <tr>
        <td>@lang('cash_register.gross_sales'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $details['transaction_details']->total_sales }}</span></td>
      </tr>
      <tr>
        <td>(-) @lang('cash_register.total_collected'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $register_details->total_sale }}</span></td>
      </tr>
      <tr>
        <td>(-) @lang('cash_register.due_applied_to_register'):</td>
        <td><span class="display_currency" data-currency_symbol="true">{{ $due_applied_to_register ?? 0 }}</span></td>
      </tr>
      <tr class="warning" style="font-weight:bold;">
        <td><b>@lang('cash_register.pending_from_customers'):</b></td>
        <td>
          <b><span class="display_currency" data-currency_symbol="true">{{ $details['transaction_details']->total_sales - $register_details->total_sale - ($due_applied_to_register ?? 0) }}</span></b>
        </td>
      </tr>
    </table>

  </div>
</div>

@include('cash_register.register_product_details')
