<div class="col-md-12">
	<form action="{{action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'confirm'], [$package->id])}}" method="POST">
	 	{{ csrf_field() }}
	 	<input type="hidden" name="gateway" value="{{$k}}">
		 <input type="hidden" name="price" value="{{$package->price}}">
		 <input type="hidden" name="coupon_code" value="{{request()->get('code') ?? null}}">

	 	<button type="submit" class="tw-dw-btn tw-dw-btn-success tw-text-white tw-dw-btn-sm"> <i class="fas fa-handshake"></i> {{$v}}</button>
	</form>
	<p class="help-block">@lang('superadmin::lang.offline_pay_helptext')</p>
	<p class="help-block">{!! nl2br($offline_payment_details) ?? '' !!}</p>
</div>