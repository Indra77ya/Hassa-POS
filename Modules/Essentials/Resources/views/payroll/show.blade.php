<div class="modal-dialog modal-lg" role="document">
  	<div class="modal-content">
  		<div class="modal-header no-print">
	      	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	      		<span aria-hidden="true">&times;</span>
	      	</button>
	      	<h4 class="modal-title no-print">
	      		{!! __('essentials::lang.payroll_of_employee', ['employee' => $payroll->transaction_for->user_full_name, 'date' => $month_name . ' ' . $year]) !!}
	      	</h4>
	    </div>
	    <div class="modal-body" style="padding: 20px;">
	    	<div class="table-responsive">
		      	<table class="table table-bordered" id="payroll-view">
		      		<tr>
					<td colspan="3" style="padding: 20px;">
						<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 10px;">
							<div>
								@if(!empty(Session::get('business.logo')))
					                  <img src="{{ url( '/uploads/business_logos/' . Session::get('business.logo') ) }}" alt="Logo" style="max-height: 65px; width: auto;">
					                @endif
							</div>
							<div style="text-align: right; line-height: 1.5;">
							<strong style="font-size: 20px; color: #111;">
								{{Session::get('business.name') ?? ''}}
							</strong>
							<br>
							<span style="font-size: 12px; color: #555;">
								{!!Session::get('business.business_address') ?? ''!!}
							</span>
							</div>
						</div>
			                <div style="text-align: center; padding-top: 10px; border-top: 1px dashed #ccc; margin-top: 10px;">
						<h4 style="font-weight: bold; margin: 0; color: #222; font-size: 16px;">
							@lang('essentials::lang.payslip_for_the_month', ['month' => $month_name, 'year' => $year])
						</h4>
			                </div>
		                </td>
		      		</tr>
		      		<tr>
					<td colspan="3" style="padding: 15px 18px;">
						<div class="row">
							<div class="col-xs-6" style="line-height: 1.8;">
								<strong>@lang('essentials::lang.employee'):</strong> {{$payroll->transaction_for->user_full_name}}<br>
								<strong>@lang('essentials::lang.department'):</strong> {{$department->name ?? '-'}}<br>
								<strong>@lang('essentials::lang.designation'):</strong> {{$designation->name ?? '-'}}<br>
								<strong>@lang('lang_v1.primary_work_location'):</strong> {{$location->name ?? __('report.all_locations')}}<br>
								@if(!empty($payroll->transaction_for->id_proof_name) && !empty($payroll->transaction_for->id_proof_number))
									<strong>{{ucfirst($payroll->transaction_for->id_proof_name)}}:</strong> {{$payroll->transaction_for->id_proof_number}}<br>
								@endif
								<strong>@lang('lang_v1.tax_payer_id'):</strong> {{$bank_details['tax_payer_id'] ?? '-'}}<br>
							</div>
							<div class="col-xs-6" style="line-height: 1.8;">
								<strong>@lang('lang_v1.bank_name'):</strong> {{$bank_details['bank_name'] ?? '-'}}<br>
								<strong>@lang('lang_v1.branch'):</strong> {{$bank_details['branch'] ?? '-'}}<br>
								<strong>@lang('lang_v1.bank_code'):</strong> {{$bank_details['bank_code'] ?? '-'}}<br>
								<strong>@lang('lang_v1.account_holder_name'):</strong> {{$bank_details['account_holder_name'] ?? '-'}}<br>
								<strong>@lang('lang_v1.bank_account_no'):</strong> {{$bank_details['account_number'] ?? '-'}}<br>
							</div>
		      				</div>
		      			</td>
		      		</tr>
		      		<tr>
					<td style="padding: 12px 15px; width: 33.33%;">
		      				<strong>@lang('essentials::lang.total_work_duration'):</strong>
		      				{{(int)$total_work_duration}}
		      			</td>
					<td style="padding: 12px 15px; width: 33.33%;">
		      				<strong>@lang('essentials::lang.days_present'):</strong>
		      				{{$total_days_present}}
		      			</td>
					<td style="padding: 12px 15px; width: 33.33%;">
		      				<strong>@lang('essentials::lang.days_absent'):</strong>
		      				{{$total_leaves}}
		      			</td>
		      		</tr>
		      		<tr>
						<th colspan="2" style="width: 50% !important; background-color: #f8f9fa; padding: 10px 14px;">
							<table style="width: 100%; border: none;">
								<tr>
									<td style="border: none; width: 50%; font-weight: bold; padding: 0;">@lang('essentials::lang.allowances')</td>
									<td style="border: none; width: 20%; font-weight: bold; text-align: right; padding: 0;">@lang('essentials::lang.rate')</td>
									<td style="border: none; width: 30%; font-weight: bold; text-align: right; padding: 0;">@lang('sale.amount')</td>
								</tr>
							</table>
						</th>
						<th style="width: 50% !important; background-color: #f8f9fa; padding: 10px 14px;">
							<table style="width: 100%; border: none;">
								<tr>
									<td style="border: none; width: 50%; font-weight: bold; padding: 0;">@lang('essentials::lang.deductions')</td>
									<td style="border: none; width: 20%; font-weight: bold; text-align: right; padding: 0;">@lang('essentials::lang.rate')</td>
									<td style="border: none; width: 30%; font-weight: bold; text-align: right; padding: 0;">@lang('sale.amount')</td>
								</tr>
							</table>
						</th>
					</tr>
		      		<tr>
						<td colspan="2" style="width: 50% !important; vertical-align: top; padding: 12px 14px;">
							@php
		                        $total_earnings = $payroll->essentials_duration * $payroll->essentials_amount_per_unit_duration;
		                    @endphp
							<table style="width: 100%; border: none; line-height: 1.8;">
								<tr>
									<td style="border: none; width: 50%; vertical-align: top; padding: 4px 0;">
										@lang('essentials::lang.salary')
									</td>
									<td style="border: none; width: 20%; vertical-align: top; text-align: right; padding: 4px 0;">
									</td>
									<td style="border: none; width: 30%; vertical-align: top; text-align: right; padding: 4px 0;">
										<span class="display_currency" data-currency_symbol="true">
											{{$payroll->essentials_duration * $payroll->essentials_amount_per_unit_duration}}
										</span>
										<br>
										<small style="color: #666; font-size: 85%;">
											({{@num_format($payroll->essentials_duration)}} {{$payroll->essentials_duration_unit}} * {{@num_format($payroll->essentials_amount_per_unit_duration)}})
										</small>
									</td>
								</tr>
		                    @forelse($allowances['allowance_names'] as $key => $value)
								<tr>
									<td style="border: none; width: 50%; vertical-align: top; padding: 4px 0;">
										{{$value}}
									</td>
									<td style="border: none; width: 20%; vertical-align: top; text-align: right; padding: 4px 0;">
										@if(!empty($allowances['allowance_types'][$key]) && $allowances['allowance_types'][$key] == 'percent')
								{{@num_format($allowances['allowance_percents'][$key])}}%
							@endif
									</td>
									<td style="border: none; width: 30%; vertical-align: top; text-align: right; padding: 4px 0;">
										<span class="display_currency" data-currency_symbol="true">
											{{$allowances['allowance_amounts'][$key]}}
										</span>
									</td>
								</tr>
								@php
		                            $total_earnings += !empty($allowances['allowance_amounts'][$key]) ? $allowances['allowance_amounts'][$key] : 0;
		                        @endphp
							@empty
		                    @endforelse
							</table>
						</td>
						<td style="width: 50% !important; vertical-align: top; padding: 12px 14px;">
							@php
		                        $total_deduction = 0;
		                    @endphp
							<table style="width: 100%; border: none; line-height: 1.8;">
		                    @forelse($deductions['deduction_names'] as $key => $value)
								<tr>
									<td style="border: none; width: 50%; vertical-align: top; padding: 4px 0;">
										{{$value}}
									</td>
									<td style="border: none; width: 20%; vertical-align: top; text-align: right; padding: 4px 0;">
										@if(!empty($deductions['deduction_types'][$key]) && $deductions['deduction_types'][$key] == 'percent')
								{{@num_format($deductions['deduction_percents'][$key])}}%
							@endif
									</td>
									<td style="border: none; width: 30%; vertical-align: top; text-align: right; padding: 4px 0;">
										<span class="display_currency" data-currency_symbol="true">
											{{$deductions['deduction_amounts'][$key]}}
										</span>
									</td>
								</tr>
								@php
		                            $total_deduction += !empty($deductions['deduction_amounts'][$key]) ? $deductions['deduction_amounts'][$key] : 0;
		                        @endphp
							@empty
								<tr>
									<td colspan="3" style="border: none; text-align: center; color: #777; padding: 10px 0;">
										@lang('lang_v1.none')
									</td>
								</tr>
		                    @endforelse
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2" style="width: 50% !important; padding: 10px 14px; background-color: #fcfcfc;">
							<table style="width: 100%; border: none;">
								<tr>
									<td style="border: none; width: 60%; font-weight: bold;">@lang('essentials::lang.total_earnings'):</td>
									<td style="border: none; width: 40%; font-weight: bold; text-align: right;">
										<span class="display_currency" data-currency_symbol="true">
											{{$total_earnings}}
										</span>
									</td>
								</tr>
							</table>
						</td>
						<td style="width: 50% !important; padding: 10px 14px; background-color: #fcfcfc;">
							<table style="width: 100%; border: none;">
								<tr>
									<td style="border: none; width: 60%; font-weight: bold;">@lang('essentials::lang.total_deductions'):</td>
									<td style="border: none; width: 40%; font-weight: bold; text-align: right;">
										<span class="display_currency" data-currency_symbol="true">
											{{$total_deduction}}
										</span>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="padding: 12px 14px; background-color: #f8f9fa;">
							<table style="width: 100%; border: none;">
								<tr>
									<td style="border: none; text-align: right; font-weight: bold; font-size: 15px;">
										@lang('essentials::lang.net_pay'): &nbsp;
										<span class="display_currency" data-currency_symbol="true" style="color: #2e7d32;">
											{{$total_earnings - $total_deduction}}
										</span>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="padding: 12px 14px;">
							<strong>@lang('essentials::lang.in_words'):</strong> {{ucfirst($final_total_in_words)}}
						</td>
					</tr>
					<tr>
						<td colspan="3" style="padding: 12px 14px;">
							<strong style="display: block; margin-bottom: 8px;">{{ __('sale.payment_info') }}:</strong>
							<table class="table table-bordered table-striped" style="margin-bottom: 0;">
							<tr class="bg-green">
								<th style="padding: 8px 10px;">#</th>
								<th style="padding: 8px 10px;">{{ __('messages.date') }}</th>
								<th style="padding: 8px 10px;">{{ __('purchase.ref_no') }}</th>
								<th style="padding: 8px 10px;">{{ __('sale.amount') }}</th>
								<th style="padding: 8px 10px;">{{ __('sale.payment_mode') }}</th>
								<th style="padding: 8px 10px;">{{ __('sale.payment_note') }}</th>
							</tr>
							@php
								$total_paid = 0;
							@endphp
							@forelse($payroll->payment_lines as $payment_line)
								@php
									if($payment_line->is_return == 1){
									  $total_paid -= $payment_line->amount;
									} else {
									  $total_paid += $payment_line->amount;
									}
								@endphp
								<tr>
									<td style="padding: 8px 10px;">{{ $loop->iteration }}</td>
									<td style="padding: 8px 10px;">{{ @format_date($payment_line->paid_on) }}</td>
									<td style="padding: 8px 10px;">{{ $payment_line->payment_ref_no }}</td>
									<td style="padding: 8px 10px;"><span class="display_currency" data-currency_symbol="true">{{ $payment_line->amount }}</span></td>
									<td style="padding: 8px 10px;">
										{{ $payment_types[$payment_line->method] ?? $payment_line->method }}
									</td>
									<td style="padding: 8px 10px;">@if($payment_line->note)
									  {{ ucfirst($payment_line->note) }}
									  @else
									  --
									  @endif
									</td>
								</tr>
							@empty
								<tr><td colspan="6" class="text-center" style="padding: 10px;">@lang('purchase.no_records_found')</td></tr>
							@endforelse
						</table>
						</td>
					</tr>
					<tr>
						<td colspan="3" style="padding: 12px 14px;">
							<strong>@lang('brand.note'):</strong><br>
							{{$payroll->staff_note ?? '-'}}
						</td>
					</tr>
		      	</table>
	      	</div>
	    </div>
	    <div class="modal-footer no-print">
	      	<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" aria-label="Print" onclick="$(this).closest('div.modal-content').find('.modal-body').printThis();">
	      		<i class="fa fa-print"></i> @lang( 'messages.print' )
      		</button>
	      	<button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
	    </div>
  	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<style type="text/css">
	#payroll-view {
		border: 1px solid #1d1a1a;
		border-collapse: collapse;
		width: 100%;
		margin-bottom: 0;
	}
	#payroll-view > tbody > tr > td,
	#payroll-view > tbody > tr > th {
		border: 1px solid #1d1a1a;
		padding: 10px 14px;
		vertical-align: middle;
	}
	@media print {
		.modal-dialog {
			width: 100% !important;
			margin: 0 !important;
		}
		.modal-content {
			border: none !important;
			box-shadow: none !important;
		}
		#payroll-view {
			border: 1px solid #000 !important;
		}
		#payroll-view td, #payroll-view th {
			border: 1px solid #000 !important;
			padding: 8px 12px !important;
		}
	}
</style>