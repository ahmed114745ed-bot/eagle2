<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout with PayPal</title>
    <script src="https://www.paypal.com/sdk/js?client-id={{ config('paypal.client_id') }}&currency={{ config('paypal.currency','USD') }}"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 16px;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .header h1 {
            font-size: 22px;
            color: #253b80;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .payment-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 20px 0;
        }
        
        .payment-button {
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .paypal-button {
            background-color: #ffc439;
            color: #000;
        }
        
        .paypal-button:hover {
            background-color: #f2b432;
        }
        
        .card-button {
            background-color: #0070ba;
            color: #fff;
        }
        
        .card-button:hover {
            background-color: #005ea6;
        }
        
        .payment-button i {
            font-size: 20px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .divider-line {
            flex: 1;
            height: 1px;
            background-color: #ddd;
        }
        
        .divider-text {
            padding: 0 15px;
            color: #777;
            font-size: 14px;
        }
        
        .secure-notice {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .secure-notice i {
            color: #28a745;
            margin-right: 5px;
        }
        
        .amount-display {
            text-align: center;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 600;
            color: #253b80;
            font-size: 18px;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .payment-button {
                padding: 14px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>إتمام عملية الدفع</h1>
            <p>اختر طريقة الدفع المناسبة لك</p>
        </div>
        
        <div class="amount-display">
            المبلغ: {{ $amount }} دولار
        </div>
        
        <div class="payment-options">
            <div id="paypal-button"></div>
            
            <div class="divider">
                <div class="divider-line"></div>
                <div class="divider-text">أو</div>
                <div class="divider-line"></div>
            </div>
            
            <div id="card-button"></div>
        </div>
        
        <div class="secure-notice">
            <i>✓</i> عملية دفع آمنة ومشفرة
        </div>
    </div>

    <script>
        // ✅ BUTTON 1 (Yellow PayPal button - redirect)
        paypal.Buttons({
            fundingSource: paypal.FUNDING.PAYPAL,
            style: {
                layout: 'vertical',
                shape: 'pill',
                height: 45,
                label: 'paypal'
            },
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
            style: {
                layout: 'vertical',
                shape: 'pill',
                height: 45,
                label: 'checkout'
            },
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