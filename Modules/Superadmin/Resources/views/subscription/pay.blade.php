@extends($layout)

@section('title', __('superadmin::lang.subscription'))

@section('content')

<!-- Main content -->
<section class="content">

	@include('superadmin::layouts.partials.currency')

	<div class="box box-success">
        <div class="box-header">
            <h3 class="box-title">@lang('superadmin::lang.pay_and_subscribe')</h3>
        </div>

        <div class="box-body">
    		<div class="col-md-8">
        		<h3>
        			{{$package->name}}

        			(<span class="display_currency" data-currency_symbol="true">{{$package->price}}</span>

					<small>
						/ {{$package->interval_count}} {{ucfirst($package->interval)}}
					</small>)
        		</h3>
        		<ul>
					<li>
						@if($package->location_count == 0)
							@lang('superadmin::lang.unlimited')
						@else
							{{$package->location_count}}
						@endif

						@lang('business.business_locations')
					</li>

					<li>
						@if($package->user_count == 0)
							@lang('superadmin::lang.unlimited')
						@else
							{{$package->user_count}}
						@endif

						@lang('superadmin::lang.users')
					</li>

					<li>
						@if($package->product_count == 0)
							@lang('superadmin::lang.unlimited')
						@else
							{{$package->product_count}}
						@endif

						@lang('superadmin::lang.products')
					</li>

					<li>
						@if($package->invoice_count == 0)
							@lang('superadmin::lang.unlimited')
						@else
							{{$package->invoice_count}}
						@endif

						@lang('superadmin::lang.invoices')
					</li>

					@if($package->trial_days != 0)
						<li>
							{{$package->trial_days}} @lang('superadmin::lang.trial_days')
						</li>
					@endif
				</ul>
				@php
				  if($coupon_status['status'] == 'success')	{
					$package->price =  number_format($package_price_after_discount , 2, '.', '');
				  }
				@endphp
				<div class="row">
					@if (request()->has('code'))
						<div class="alert alert-{{ $coupon_status['status'] }}">
						  @if($coupon_status['status'] == 'success')
							@lang('superadmin::lang.package_price_after_discount') = 
							<span class="display_currency" data-currency_symbol="true">{{ number_format($package_price_after_discount , 2, '.', ''); }}</span>
							(@lang('superadmin::lang.you_save') <span class="display_currency" data-currency_symbol="true">{{ number_format($discount_amount , 2, '.', ''); }}</span>)
						  @else
						 {{  $coupon_status['msg'] }}
						  @endif

						</div>
					@endif
					{!! Form::open([
						'method' => 'get',
						'id' => 'coupon_check',
					]) !!}
					<div class="col-md-4">
						<div class="form-group">
							{!! Form::label('coupon_code', __('superadmin::lang.coupon_code') . '*') !!}
							{!! Form::text('code', request()->get('code') ?? null, [
								'class' => 'form-control',
								'required',
								'placeholder' => __('superadmin::lang.coupon_code'),
							]) !!}
						</div>
					</div>
						<div class="col-md-4 ">
							<div class="form-group" style="margin-top: 28px">
								{!! Form::submit('Apply', ['class' => 'tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm']) !!}
							</div>
						</div>
					{!! Form::close() !!}
				</div>
				<ul class="list-group">
					@foreach($gateways as $k => $v)
						<div class="list-group-item">
							<b>@lang('superadmin::lang.pay_via', ['method' => $v])</b>
							<div class="row" id="paymentdiv_{{$k}}">
								@php 
									$view = 'superadmin::subscription.partials.pay_'.$k;
								@endphp
								@includeIf($view)
							</div>
						</div>
					@endforeach
				</ul>
			</div>
        </div>
    </div>
</section>
@endsection