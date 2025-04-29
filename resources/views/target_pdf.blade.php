<!-- resources/views/pdf/target.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Target Data PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>

    <h1>Target Data</h1>

    <table>
        <thead>
            <tr>
                <th>{{__('Level')}}</th>
                <th>{{__('Diamond Target')}}</th>
                <th>{{__("Day & Hours")}}</th>
                <th>{{__("Host Salary")}}</th>
                <th>{{__("Agents Salary")}}</th>
                <th>{{__("BD Admin")}}</th>
            </tr>
        </thead>
        <tbody>
            {{-- First row: Honor --}}
            @if(count($targets) > 0)
                @php $honor = $targets->first(); @endphp
                <tr>
                    <td>Honor</td>
                    <td>{{ $honor->diamonds }}</td>
                    <td>{{ $honor->days }} D / {{ $honor->hours }} h</td>
                    <td>{{ calculateUserUsd($honor->diamonds, $honor->usd ?? 0) }}</td>
                    <td>{{ calculateUserUsd($honor->diamonds, $honor->agency_share ?? 0) }}</td>
                    <td>{{ calculateUserUsd($honor->diamonds, $honor->db_percentage ?? 0) }}</td>
                </tr>
            @endif
    
            {{-- Rest of rows: S1, S2, S3... --}}
            @foreach($targets->skip(1) as $index => $target)
                <tr>
                    <td>S{{ $index + 1 }}</td>
                    <td>{{ $target->diamonds }}</td>
                    <td>{{ $target->days }} D / {{ $target->hours }} h</td>
                    <td>{{ calculateUserUsd($target->diamonds, $target->usd ?? 0) }}</td>
                    <td>{{ calculateUserUsd($target->diamonds, $target->agency_share ?? 0) }}</td>
                    <td>{{ calculateUserUsd($target->diamonds, $target->db_percentage ?? 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    👇 What This Does:
    First row = Honor, with real values.
    
    Then loop through the remaining targets as S1, S2, etc.
    
    Let me know if the Honor row comes from somewhere else instead of $targets->first().
    
    
    
    
    
    
    
    You said:
    write this with clean code 
    
    
    ChatGPT said:
    Certainly! Here's a clean and readable Blade version of your table, displaying the first row as "Honor", followed by "S1", "S2", "S3", etc., using a helper function for the salary calculations.
    
    ✅ Blade View: Clean Version
    blade
    Copy
    Edit
    <table>
        <thead>
            <tr>
                <th>{{ __('Level') }}</th>
                <th>{{ __('Diamond Target') }}</th>
                <th>{{ __('Day & Hours') }}</th>
                <th>{{ __('Host Salary') }}</th>
                <th>{{ __('Agents Salary') }}</th>
                <th>{{ __('BD Admin') }}</th>
            </tr>
        </thead>
        <tbody>
            @if($targets->isNotEmpty())
                @php $honor = $targets->first(); @endphp
    
                {{-- Honor Row --}}
                <tr>
                    <td>{{__('Honor')}}</td>
                    <td>{{ $honor->diamonds }}</td>
                    <td>{{ "{$honor->days} D / {$honor->hours} h" }}</td>
                    <td>{{ calculateUserUsd($honor->diamonds, $honor->usd ?? 0) }}</td>
                    <td>{{ calculateUserUsd($honor->diamonds, $honor->agency_share ?? 0) }}</td>
                    <td>{{ calculateUserUsd($honor->diamonds, $honor->db_percentage ?? 0) }}</td>
                </tr>
    
                {{-- S1, S2, S3... --}}
                @foreach($targets->skip(1)->values() as $index => $target)
                    <tr>
                        <td>S{{ $index + 1 }}</td>
                        <td>{{ $target->diamonds }}</td>
                        <td>{{ "{$target->days} D / {$target->hours} h" }}</td>
                        <td>{{ calculateUserUsd($target->diamonds, $target->usd ?? 0) }}</td>
                        <td>{{ calculateUserUsd($target->diamonds, $target->agency_share ?? 0) }}</td>
                        <td>{{ calculateUserUsd($target->diamonds, $target->db_percentage ?? 0) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    
    </table>

</body>
</html>
