<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1> {{@$agency->name}}</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{__('sender')}}</th>
                    <th>{{__('receiver')}}</th>
                    <th>{{__('amount')}}</th>
                    <th>{{__('date')}}</th>
                    <th>{{__('export/import')}}</th>
                    <th>{{__('total wallet')}}</th>
                   
                </tr>
            </thead>
            <tbody>
                @foreach($charges as $charge)
                @php
                    $sender = \App\Helpers\Common::getChargerInfo($charge);
                    $receiver = \App\Helpers\Common::getReceiverInfo($charge);
                @endphp
                    <tr>
                        <td>{{ $sender['name'] }}</td>
                        <td>{{ $receiver['name'] }}</td>
                        <td>{{ $charge->amount }}</td>
                        <td>{{ $charge->created_at }}</td>
                        <td> 
                             @if ($charge->user_type == 'agency')
                                🟢↑
                            @elseif ($charge->charger_type == 'agency')
                                🔴↓
                            @endif
                    </td>
                        <td>{{ $charge->balance_before }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>