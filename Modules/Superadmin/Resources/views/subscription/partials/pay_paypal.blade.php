		
		<div id="paypal-button-container" style="padding:30px;"></div>
     
		<script src="https://www.paypal.com/sdk/js?client-id={{ config('paypal.client_id') }}&currency={{ $system_currency->code }}"></script>


<script>
paypal.Buttons({
	// Order is created on the server and the order id is returned
	createOrder() {
		const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
		return fetch("{{ route('paypalExpressCheckout') }}", {
			method: "POST",
			headers: {
			"Content-Type": "application/json",
			"X-CSRF-TOKEN": csrfToken,
			},
		// use the "body" param to optionally pass additional order information
		// like product skus and quantities
		body: JSON.stringify({
		  price: '{{ $package->price }}',
		  package_name: '{{ $package->name }}',
		}),
	  })
	  .then((response) => response.json())
	  .then((order) => order.id);
	},
	// Finalize the transaction on the server after payer approval
	onApprove(data) {
		
		const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	  	return fetch("{{ route('capturePaypalOrder') }}", {
		method: "POST",
		headers: {
		  "Content-Type": "application/json",
		  "X-CSRF-TOKEN": csrfToken,
		},
		body: JSON.stringify({
			orderID: data.orderID,
			package_id: "{{$package->id}}",
			gateway: "{{$v}}",
			business_id: "{{$user['business_id']}}",
			user_id: "{{$user['id']}}",
			coupon_code: "{{ request()->get('code') ?? null}}",
		})
	  })
	  .then((response) => response.json())
	  .then((responseData) => {
		if(responseData.success){
			toastr.success(responseData.msg);
			window.location.href = "{{ route('subscription.index') }}";
		}else{
			toastr.error(responseData.msg);
		}
	  });
	}
  }).render('#paypal-button-container');
</script>