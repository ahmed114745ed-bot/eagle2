<!DOCTYPE html>
<html>
<head>
    <title>Checkout with PayPal</title>
    <script src="https://www.paypal.com/sdk/js?client-id={{ config('paypal.client_id') }}&currency={{ config('paypal.currency','USD') }}"></script>
</head>
<body>
<br>
<div id="paypal-button-container"></div>

<script>
    paypal.Buttons({
        createOrder: function(data, actions) {
            return fetch('/paypal/create-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    referenceId: "{{ $logId }}",
                    amount: "{{ $amount }}"
                })
            })
                .then(res => res.json())
                .then(orderData => {
                    console.log("PayPal Order Created:", orderData);
                    return orderData.id;
                });
            },
        onApprove: function(data, actions) {
            return fetch('/paypal/capture-order/' + data.orderID, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(res => res.json())
                .then(orderData => {
                    window.location.href = "/api/paypal-return/{{ $logId }}";
                });
        },
        onCancel: function() {
            window.location.href = "/api/paypal-cancel";
        }
    }).render('#paypal-button-container');
</script>
</body>
</html>
