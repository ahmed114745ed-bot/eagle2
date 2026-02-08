<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Cup Targets') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #4a90d9;
            color: white;
        }
        tr:hover {
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ __('Cup Targets') }}</h1>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Visitors') }}</th>
                    <th>{{ __('Admins') }}</th>
                    <th>{{ __('Owner Profit') }}</th>
                    <th>{{ __('Admin Profit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cupTargets as $target)
                <tr>
                    <td>{{ number_format($target->total) }}</td>
                    <td>{{ $target->number_of_visitors }}</td>
                    <td>{{ $target->number_of_admins }}</td>
                    <td>{{ number_format($target->owner_profit, 2) }}</td>
                    <td>{{ number_format($target->admin_profit, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
