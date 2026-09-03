@extends('layouts.app')

@section('title', 'POS')

@section('content')

<!-- Main content -->
<section class="content no-print">
	@if(!empty($pos_settings['allow_overselling']))
		<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
	<div class="row">
		<div class="@if(!empty($pos_settings['hide_product_suggestion']) && !empty($pos_settings['hide_recent_trans'])) col-md-10 col-md-offset-1 @else col-md-7 @endif col-sm-12">
			@component('components.widget', ['class' => 'box-success'])
				@slot('header')
					<div class="col-sm-6">
						<h3 class="box-title">POS Terminal <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('sale_pos.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h3>
					</div>
					<div class="col-sm-6">
						<p class="text-right"><strong>@lang('sale.location'):</strong> {{$default_location->name}}</p>
					</div>
					<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
				@endslot
				{!! Form::open(['url' => action([\App\Http\Controllers\SellPosController::class, 'store']), 'method' => 'post', 'id' => 'add_pos_sell_form' ]) !!}

				{!! Form::hidden('location_id', $default_location->id, ['id' => 'location_id', 'data-receipt_printer_type' => !empty($default_location->receipt_printer_type) ? $default_location->receipt_printer_type : 'browser', 'data-default_accounts' => $default_location->default_payment_accounts]); !!}

				<!-- /.box-header -->
				<div class="box-body">
					@include('sale_pos.partials.pos_form')

					@include('sale_pos.partials.pos_form_totals')

					@include('sale_pos.partials.payment_modal')

					@if(empty($pos_settings['disable_suspend']))
						@include('sale_pos.partials.suspend_note_modal')
					@endif

					@if(empty($pos_settings['disable_recurring_invoice']))
						@include('sale_pos.partials.recurring_invoice_modal')
					@endif
				</div>
				<!-- /.box-body -->
				@include('sale_pos.partials.pos_form_actions')
				{!! Form::close() !!}
			@endcomponent
		</div>

		<div class="col-md-5 col-sm-12">
			@include('sale_pos.partials.right_div')
		</div>
	</div>
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog"
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog"
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

<div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
<div class="modal fade" id="pos_pay_contact_due_modal" tabindex="-1" role="dialog"></div>

@include('sale_pos.partials.configure_search_modal')

@include('sale_pos.partials.recent_transactions_modal')

@include('sale_pos.partials.weighing_scale_modal')

@stop

@section('javascript')
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	@include('sale_pos.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
@if (!empty($pos_settings['enable_midtrans']) && !empty($pos_settings['midtrans_client_key']))
    @php
        $snap_js_url = (!empty($pos_settings['midtrans_mode']) && $pos_settings['midtrans_mode'] === 'production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{$snap_js_url}}" data-client-key="{{$pos_settings['midtrans_client_key']}}"></script>
    <script type="text/javascript">
        $(document).on('click', '#pos-midtrans-pay-btn', function(){
            var btn = $(this);

            if ($('table#pos_table tbody').find('.product_row').length == 0) {
                toastr.error("Keranjang belanja masih kosong!");
                return false;
            }

            btn.prop('disabled', true);

            var form = $('form#add_pos_sell_form');
            var data = form.serializeArray();

            data.push({name: 'status', value: 'draft'});
            data.push({name: 'is_direct_sale', value: 0});

            $.ajax({
                method: 'POST',
                url: form.attr('action'),
                data: $.param(data),
                dataType: 'json',
                success: function(result) {
                    if (result.success == 1) {
                        var transaction_id = result.transaction_id;
                        if (!transaction_id && result.receipt && result.receipt.transaction_id) {
                            transaction_id = result.receipt.transaction_id;
                        }

                        if (!transaction_id) {
                            btn.prop('disabled', false);
                            toastr.success(result.msg || "Transaksi berhasil dibuat.");
                            reset_pos_form();
                            return;
                        }

                        $.ajax({
                            url: "/midtrans/create-snap-token/" + transaction_id,
                            type: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(response) {
                                btn.prop('disabled', false);
                                if (response.success && response.token) {
                                    snap.pay(response.token, {
                                        onSuccess: function(res){
                                            $.ajax({
                                                url: "/midtrans/sync-payment/" + transaction_id,
                                                type: 'POST',
                                                data: {
                                                    _token: "{{ csrf_token() }}",
                                                    order_id: (res && res.order_id) ? res.order_id : ''
                                                },
                                                complete: function() {
                                                    toastr.success("Pembayaran Midtrans berhasil!");
                                                    reset_pos_form();
                                                }
                                            });
                                        },
                                        onPending: function(res){
                                            toastr.info("Pembayaran Midtrans pending.");
                                            reset_pos_form();
                                        },
                                        onError: function(res){
                                            toastr.error("Pembayaran Midtrans gagal!");
                                        }
                                    });
                                } else {
                                    toastr.error(response.message || "Gagal membuat token Midtrans.");
                                }
                            },
                            error: function() {
                                btn.prop('disabled', false);
                                toastr.error("Gagal terhubung ke Midtrans gateway.");
                            }
                        });
                    } else {
                        btn.prop('disabled', false);
                        toastr.error(result.msg || "Terjadi kesalahan saat membuat transaksi.");
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false);
                    var msg = "Gagal menyimpan transaksi POS.";
                    if (xhr.responseJSON && xhr.responseJSON.msg) {
                        msg = xhr.responseJSON.msg;
                    }
                    toastr.error(msg);
                }
            });
        });
    </script>
@endif
@endsection
