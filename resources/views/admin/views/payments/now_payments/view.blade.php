<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <label for="currency">{{ __('admin.currency') }}</label>
                <h3>{{ $payment['payment']['pay_currency'] }}</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="address_wallet">{{ __('wallet address') }}</label>
                <h3>{{ $payment['payment']['pay_address'] }}</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="pay_amount">{{ __('amount') }}</label>
                <h3>{{ $payment['payment']['pay_amount'] }}</h3>
            </div>
        </div>
    </div>
</body>
</html>
