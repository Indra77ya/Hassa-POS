<div class="col-md-12">
@php
$currency_code = strtolower($system_currency->code);
@endphp
    <form method="get" action="{{url('myfatoorah')}}">
        {{ csrf_field() }}
        <!-- customer details -->
        <input type="hidden" name="email" value="{{$user['email']}}">{{-- required --}}
        <input type="hidden" name="name" value="{{$user['surname']}} {{$user['first_name']}} {{ $user['last_name']  }}">{{-- required --}}

        <!-- order info -->
        <input type="hidden" name="amount" value="{{$package->price}}">{{-- required in kobo --}}

        <input type="hidden" name="currency" value="{{$currency_code}}"> {{--Ghana:GHS, Nigeria:NGN, USD--}}

        <input type="hidden" name="coupon_code" value="{{request()->get('code') ?? null}}">
        <input type="hidden" name="package_id" value="{{ $package->id }}">
        <input type="hidden" name="business_id" value="{{$user['business_id']}}">
        <input type="hidden" name="user_id" value="{{$user['id']}}">
        <input type="hidden" name="language" value="{{$user['language']}}">


        <!-- additional info -->

        <!-- transaction ref -->
        <button class="btn btn-sm text-white" type="submit" style="background: #08A5DB;border-color: #08A5DB;">
            <i class="fas fa-align-left text-white"></i>
            {{$v}}
        </button>
    </form>
</div>