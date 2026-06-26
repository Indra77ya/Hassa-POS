<div class="col-md-12">
    <div class="box box-solid payment_row bg-lightgray tw-relative">
        @if ($removable)
            <button type="button" class="remove_payment_row tw-absolute tw-top-2 tw-right-2 tw-z-10 tw-p-2 tw-bg-transparent tw-border-0 tw-text-slate-400 hover:tw-text-red-600 tw-cursor-pointer tw-inline-flex tw-items-center tw-justify-center tw-leading-none active:tw-scale-95 tw-transition-colors" aria-label="Remove payment"><i class="fa fa-times"></i></button>
        @endif

        @if (!empty($payment_line['id']))
            {!! Form::hidden("payment[$row_index][payment_id]", $payment_line['id']) !!}
        @endif

        @php
            $pos_settings = !empty(session()->get('business.pos_settings')) ? json_decode(session()->get('business.pos_settings'), true) : [];
            $show_in_pos = '';
            if (isset($pos_settings['enable_cash_denomination_on']) && ($pos_settings['enable_cash_denomination_on'] == 'all_screens' || $pos_settings['enable_cash_denomination_on'] == 'pos_screen')) {
                $show_in_pos = true;
            }
        @endphp

        <div class="box-body">
            @include('sale_pos.partials.payment_row_form', [
                'row_index' => $row_index,
                'payment_line' => $payment_line,
                'show_denomination' => true,
				'show_in_pos' => $show_in_pos
            ])
        </div>
    </div>
</div>
