<div class="col-md-12">
    <button type="button" class="btn btn-primary" id="pay-superadmin-midtrans-btn">
        <i class="fa fa-credit-card"></i> Pay with Midtrans
    </button>
</div>

@php
    $snap_js_url = (env('MIDTRANS_MODE') === 'production')
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
@endphp
<script src="{{$snap_js_url}}" data-client-key="{{env('MIDTRANS_CLIENT_KEY')}}"></script>
<script type="text/javascript">
    document.getElementById('pay-superadmin-midtrans-btn').onclick = function(){
        var btn = $(this);
        btn.prop('disabled', true);
        $.ajax({
            url: "{{action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'midtransCreateSnapToken'], [$package->id])}}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                code: "{{ request()->get('code') ?? '' }}"
            },
            success: function(response) {
                btn.prop('disabled', false);
                if(response.success && response.token) {
                    snap.pay(response.token, {
                        onSuccess: function(result){
                            $.ajax({
                                url: "{{action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'confirm'], [$package->id])}}",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}",
                                    gateway: 'midtrans',
                                    price: "{{ $package->price }}",
                                    coupon_code: "{{ request()->get('code') ?? '' }}"
                                },
                                success: function() {
                                    window.location.href = "{{action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'index'])}}";
                                }
                            });
                        },
                        onPending: function(result){
                            window.location.href = "{{action([\Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'index'])}}";
                        },
                        onError: function(result){
                            alert("Payment failed!");
                        },
                        onClose: function(){
                            // Customer closed popup
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
