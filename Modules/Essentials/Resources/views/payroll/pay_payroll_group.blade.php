@extends('layouts.app')
@section('title', __('essentials::lang.add_payment_for_payroll_group'))
@section('content')
@include('essentials::layouts.nav_hrm')
<section class="content-header">
	<h1 class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">
    	@lang('essentials::lang.add_payment_for_payroll_group')
    	<small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold"><code>({{$payroll_group->name}})</code></small>
    </h1>
</section>
<!-- Main content -->
<section class="content">
	<div class="row">
		{!! Form::open(['url' => action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'postAddPayment']), 'method' => 'post', 'id' => 'payroll_group_payment' ]) !!}
		{!! Form::hidden('payroll_group_id', $payroll_group->id); !!}
		<div class="col-md-12">
			<div class="box box-solid" id="payroll-group">
				<div class="box-body">
					<div class="row">
						<div class="col-md-12">
							<h3 class="text-center">
	                            <u>{!! __('essentials::lang.payroll_for_month', ['date' => $month_name . ' ' . $year]) !!}</u>
	                        </h3>
						</div>
					</div>
					<div class="row margin-bottom-20">
						<div class="col-md-6 text-center">
							<strong class="font-23">{{$payroll_group->business->name}}</strong> <br>
							@if(!empty($payroll_group->businessLocation))
								{{$payroll_group->businessLocation->name}} <br>
								{!!$payroll_group->businessLocation->location_address!!}
							@else
								{{__('report.all_locations')}}
							@endif
						</div>
						<div class="col-md-6 text-center">
							<b class="font-17">
								@lang('essentials::lang.payroll_group'):
							</b>
							{{$payroll_group->name}} <br>
							<b class="font-17">
								@lang('sale.status'):
							</b>
							@lang('sale.'.$payroll_group->status)
						</div>
					</div>
	                <div class="table-responsive mt-15">
	                    <table class="table table-bordered" id="payroll-group-table" style="width: 100% !important;">
                            <thead>
                                <tr>
                                    <th style="padding: 12px; min-width: 150px;">@lang( 'essentials::lang.employee' )</th>
                                    <th style="padding: 12px; min-width: 120px;">@lang( 'essentials::lang.gross_amount' )</th>
                                    <th style="padding: 12px; min-width: 220px;">@lang('lang_v1.bank_details')</th>
                                    <th style="padding: 12px; min-width: 200px;">@lang('sale.payments')</th>
                                    <th style="padding: 12px; min-width: 260px;">@lang('purchase.add_payment')</th>
                                </tr>
                            </thead>
                        	@foreach($payrolls as $id => $payroll)
	                        	<tr data-id="{{$id}}">
	                        		<input type="hidden" name="payments[{{$id}}][transaction_id]" value="{{$payroll['transaction_id']}}">
                        			<input type="hidden" name="payments[{{$id}}][employee_id]" value="{{$payroll['employee_id']}}">
						<td style="padding: 12px;">
							<strong>{{$payroll['employee']}}</strong>
	                        		</td>
						<td style="padding: 12px; font-weight: bold;">
	                        			@format_currency($payroll['final_total'])
	                        		</td>
						<td style="padding: 12px; line-height: 1.7;">
	                        			<strong>@lang('lang_v1.bank_name'):</strong>
									{{$payroll['bank_details']['bank_name'] ?? '-'}}
				      					<br>

				      					<strong>@lang('lang_v1.branch'):</strong>
									{{$payroll['bank_details']['branch'] ?? '-'}}
				      					<br>

				      					<strong>@lang('lang_v1.bank_code'):</strong>
									{{$payroll['bank_details']['bank_code'] ?? '-'}}
				      					<br>
				      					
				      					<strong>@lang('lang_v1.account_holder_name'):</strong>
									{{$payroll['bank_details']['account_holder_name'] ?? '-'}}
				      					<br>

				      					<strong>@lang('lang_v1.bank_account_no'):</strong>
									{{$payroll['bank_details']['account_number'] ?? '-'}}
				      					<br>
				      					<strong>@lang('lang_v1.tax_payer_id'):</strong>
									{{$payroll['bank_details']['tax_payer_id'] ?? '-'}}
				      					<br>
	                        		</td>
									<td style="padding: 12px; line-height: 1.7;">
										@forelse($payroll['payments'] as $payment)
											<strong>@lang('messages.date'): </strong> {{ @format_datetime($payment->paid_on) }} <br>
											<strong>@lang('purchase.amount'): </strong> <span class="display_currency" data-currency_symbol="true">{{ $payment->amount }}</span> <br>
											<strong>@lang('purchase.payment_method'): </strong>  {{ $payment->method ?? '' }} <br>
											<strong>@lang('lang_v1.payment_note'): </strong>  {{ $payment->note ?? '-' }}
											@if(!$loop->last)
												<hr style="margin: 8px 0; border-top: 1px dashed #ddd;">
											@endif
										@empty
											<span class="text-muted">@lang('purchase.no_records_found')</span>
										@endforelse
									</td>
						<td style="padding: 12px;">
	                        			@if($payroll['payment_status'] == 'paid')
								<span class="label bg-light-green" style="font-size: 13px; padding: 6px 12px;">
	                        					<i class="fas fa-check-circle"></i>
	                        					@lang('lang_v1.paid')
	                        				</span>
	                        			@else
		                        			@includeIf('essentials::payroll.payment_row')
                                    	@endif
	                        		</td>
	                        	</tr>
	                        @endforeach
	                    </table>
	                </div>
					<div class="col-md-12 text-center">
						<button type="submit" class="btn btn-primary m-8 btn-big" id="submit_user_button">
							{{__( 'lang_v1.pay' )}}
						</button>
					</div>
            	</div>
            </div>
		</div>
		{!! Form::close() !!}
  	</div>
</section>
@endsection
@section('javascript')
<script type="text/javascript">

	$(document).ready(function() {
		$("form#payroll_group_payment").validate();
	});

	$(function () {
		$('.paid_on').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });

        $('select.payment_types').on('change', function () {
        	let method = $(this).find(':selected').val();
        	let id = $(this).data('id');
        	if (method == 'card') {
        		$('#card_' + id).show();
        		$(`#custom_pay_1_${id}, #custom_pay_2_${id}, #custom_pay_3_${id}, #cheque_${id}, #bank_transfer_${id}`).hide();
        	} else if (method == 'cheque') {
        		$('#cheque_' + id).show();
        		$(`#custom_pay_1_${id}, #custom_pay_2_${id}, #custom_pay_3_${id}, #bank_transfer_${id}, #card_${id}`).hide();
        	} else if (method == 'bank_transfer') {
        		$('#bank_transfer_' + id).show();
        		$(`#custom_pay_1_${id}, #custom_pay_2_${id}, #custom_pay_3_${id}, #cheque_${id}, #card_${id}`).hide();
        	} else if (method == 'custom_pay_1') {
        		$('#custom_pay_1_' + id).show();
        		$(`#custom_pay_2_${id}, #custom_pay_3_${id}, #cheque_${id}, #bank_transfer_${id}, #card_${id}`).hide();
        	} else if (method == 'custom_pay_2') {
        		$('#custom_pay_2_' + id).show();
        		$(`#custom_pay_1_${id}, #custom_pay_3_${id}, #cheque_${id}, #bank_transfer_${id}, #card_${id}`).hide();
        	} else if (method == 'custom_pay_3') {
        		$('#custom_pay_3_' + id).show();
        		$(`#custom_pay_1_${id}, #custom_pay_2_${id}, #cheque_${id}, #bank_transfer_${id}, #card_${id}`).hide();
        	} else {
        		$(`#custom_pay_1_${id}, #custom_pay_2_${id}, #custom_pay_3_${id}, #cheque_${id}, #bank_transfer_${id}, #card_${id}`).hide();
        	}
        });
	});
</script>
@endsection