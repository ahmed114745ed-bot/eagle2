<!DOCTYPE html>
<html>
<head>
    <title>Checkout with PayPal</title>
    <script src="https://www.paypal.com/sdk/js?client-id={{ config('paypal.client_id') }}&currency={{ config('paypal.currency','USD') }}"></script>
</head>
<body>
<br>
<div id="paypal-button"></div>
<br>
<div id="card-button"></div>

<script>
    // ✅ BUTTON 1 (Yellow PayPal button - redirect)
    paypal.Buttons({
        fundingSource: paypal.FUNDING.PAYPAL,
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
                    // Redirect instead of popup
                    window.location.href = orderData.approval_url;
                    return false; // stop popup
                });
        }
    }).render('#paypal-button');

    // ✅ BUTTON 2 (Debit/Credit - popup continues as normal)
    paypal.Buttons({
        fundingSource: paypal.FUNDING.CARD,
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
    }).render('#card-button');
</script>
</body>
</html>
