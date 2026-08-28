@extends('layouts.guest')
@section('title', $title)
@section('content')

<div class="container">
    <div class="spacer"></div>
    <div class="row">
        <div class="col-xs-12 col-md-6 col-md-offset-3">
            <div class="box box-primary">
                <div class="box-body">
                    <table class="table no-border">
                        <tr>
                            @if(!empty($transaction->business->logo))
                                <td class="width-50 text-center">
                                    <img src="{{ url( '/uploads/business_logos/' . $transaction->business->logo ) }}" alt="Logo" style="max-width: 80%;">
                                </td>
                            @endif
                            <td class="text-center">
                                <address>
                                <strong>{{ $transaction->business->name }}</strong><br>
                                {{ $transaction->location->name ?? '' }}
                                @if(!empty($transaction->location->landmark))
                                    <br>{{$transaction->location->landmark}}
                                @endif
                                @if(!empty($transaction->location->city) || !empty($transaction->location->state) || !empty($transaction->location->country))
                                    <br>{{implode(',', array_filter([$transaction->location->city, $transaction->location->state, $transaction->location->country]))}}
                                @endif
                              
                                @if(!empty($transaction->business->tax_number_1))
                                    <br>{{$transaction->business->tax_label_1}}: {{$transaction->business->tax_number_1}}
                                @endif

                                @if(!empty($transaction->business->tax_number_2))
                                    <br>{{$transaction->business->tax_label_2}}: {{$transaction->business->tax_number_2}}
                                @endif

                                @if(!empty($transaction->location->mobile))
                                    <br>@lang('contact.mobile'): {{$transaction->location->mobile}}
                                @endif
                                @if(!empty($transaction->location->email))
                                    <br>@lang('business.email'): {{$transaction->location->email}}
                                @endif
                            </address>
                            </td>
                        </tr>
                    </table>
                    <h4 class="box-title">@lang('lang_v1.payment_for_invoice_no'): {{$transaction->invoice_no}}</h4>
                    <table class="table no-border">
                        <tr>
                            <td>
                                <strong>@lang('contact.customer'):</strong><br>
                                {!!$transaction->contact->contact_address!!}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>@lang('sale.sale_date'):</strong> {{$date_formatted}}</td>
                        </tr>
                        <tr>
                            <td>
                                <h4>@lang('sale.total_amount'): <span>{{$total_amount}}</span></h4>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h4>@lang('sale.total_paid'): <span>{{$total_paid}}</span></h4>
                            </td>
                        </tr>
                    </table>

                    @if($transaction->payment_status != 'paid')
                    <table class="table no-border">
                        <tr>
                            <td><h4>@lang('sale.total_payable'): <span>{{$total_payable_formatted}}</span></h4></td>
                        </tr>
                    </table>
                    <div class="spacer"></div>
                    <div class="spacer"></div>
                    <div class="width-50 text-center f-left">
                        <form action="{{route('confirm_payment', ['id' => $transaction->id])}}" method="POST">
                            <input type="hidden" name="gateway" value="razorpay">
                                <!-- Note that the amount is in paise -->
                            <script
                                src="https://checkout.razorpay.com/v1/checkout.js"
                                data-key="{{$pos_settings['razor_pay_key_id']}}"
                                data-amount="{{$total_payable*100}}"
                                data-buttontext="Pay with Razorpay"
                                data-name="{{$transaction->business->name}}"
                                data-theme.color="#3c8dbc"
                            ></script>
                            {{ csrf_field() }}
                        </form>
                    </div>
                        @if(!empty($pos_settings['stripe_public_key']) && !empty($pos_settings['stripe_secret_key']))
                            @php
                                $code = strtolower($business_details->currency_code);
                            @endphp

                            <div class="width-50 text-center f-left">
                                <form action="{{route('confirm_payment', ['id' => $transaction->id])}}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="gateway" value="stripe">
                                    <script
                                            src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                            data-key="{{$pos_settings['stripe_public_key']}}"
                                            data-amount="@if(in_array($code, ['bif','clp','djf','gnf','jpy','kmf','krw','mga','pyg','rwf','ugx','vnd','vuv','xaf','xof','xpf'])) {{$total_payable}} @else {{$total_payable*100}} @endif"
                                            data-name="{{$transaction->business->name}}"
                                            data-description="Pay with stripe"
                                            data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
                                            data-locale="auto"
                                            data-currency="{{$code}}">
                                    </script>
                                </form>
                            </div>
                        @endif

                        @if(!empty($pos_settings['enable_midtrans']) && !empty($pos_settings['midtrans_client_key']))
                            <div class="width-50 text-center f-left" style="margin-top: 10px;">
                                <button type="button" class="btn btn-primary" id="pay-midtrans-btn">
                                    <i class="fa fa-credit-card"></i> Pay with Midtrans
                                </button>
                            </div>

                            @php
                                $snap_js_url = (!empty($pos_settings['midtrans_mode']) && $pos_settings['midtrans_mode'] === 'production')
                                    ? 'https://app.midtrans.com/snap/snap.js'
                                    : 'https://app.sandbox.midtrans.com/snap/snap.js';
                            @endphp
                            <script src="{{$snap_js_url}}" data-client-key="{{$pos_settings['midtrans_client_key']}}"></script>
                            <script type="text/javascript">
                                document.getElementById('pay-midtrans-btn').onclick = function(){
                                    var btn = $(this);
                                    btn.prop('disabled', true);
                                    $.ajax({
                                        url: "{{route('midtrans.create_snap_token', [$transaction->id])}}",
                                        type: 'POST',
                                        data: {
                                            _token: "{{ csrf_token() }}"
                                        },
                                        success: function(response) {
                                            btn.prop('disabled', false);
                                            if(response.success && response.token) {
                                                snap.pay(response.token, {
                                                    onSuccess: function(result){
                                                        location.reload();
                                                    },
                                                    onPending: function(result){
                                                        location.reload();
                                                    },
                                                    onError: function(result){
                                                        alert("Payment failed!");
                                                    },
                                                    onClose: function(){
                                                        // Customer closed the popup without finishing payment
                                                    }
                                                });
                                            } else {
                                                alert(response.message || 'Error initializing Midtrans.');
                                            }
                                        },
                                        error: function(err) {
                                            btn.prop('disabled', false);
                                            alert('Failed to connect to Midtrans payment gateway.');
                                        }
                                    });
                                };
                            </script>
                        @endif
                    @else
                        <table class="table no-border">
                            <tr>
                                <td><h4>@lang('sale.payment_status'): <span class="text-success">@lang('lang_v1.paid')</span></h4></td>
                            </tr>
                        </table>
                    @endif
                    <div class="spacer"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection