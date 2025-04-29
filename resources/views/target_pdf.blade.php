<!-- resources/views/pdf/target.blade.php -->

@php
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;

    $logo = asset('images/app-logo.png'); // Default logo

    if (Schema::hasTable('settings')) {
        $logoDb = DB::table('settings')->where('key', 'app_logo')->value('value');

        
        if ($logoDb) {
            $logo = getImagePath( $logoDb);
        }
    }
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} - {{ __('Salary Policy') }}</title>
    <style>
        :root {
        --primary-color: {{ config('themes.primaryColor') }};
        --secondary-color: {{ config('themes.secondaryColor') }};
        --green-color: {{ config('themes.greenColor') }};
        --text-primary-color: {{ config('themes.textPrimaryColor') }};
        --text-secondary-color: {{ config('themes.textSecondaryColor') }};
        --box-background-color: {{ config('themes.boxBackgroundColor') }};
        --table-background-color: {{ config('themes.tableBackGroundColor')}}
        --background-image: {{ config('themes.backgroundImage') }};
        --brand_background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) }});
        --second-alpha: {{ adjustColor(config('themes.boxBackgroundColor'), -30, -30, -30) }}55;
        --primary-hover-alpha: {{ config('themes.primaryColor')}}33;
        --scroll-second-color: {{ config('themes.boxBackgroundColor') }}cc;
        --scroll-first-color: {{ adjustColor(config('themes.primaryColor'), 40, 40, 40) }}33;


        --inverse-color: {{getLighterColor(config('themes.primaryColor'))}};
        --inverse-box-color: {{adjustTextColor(config('themes.boxBackgroundColor'))}};
        --success-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);
        --primary-button: linear-gradient(90deg, {{adjustColor(config('themes.primaryColor'))}} 0%, {{config('themes.primaryColor')}} 100%);
    }
        body {
            font-family: DejaVu Sans, sans-serif;
        }
        thead.custom-header {
            background-color: var(--primary-color) !important;
            color: var(--text-secondary-color) !important;
        }
        h1.green-bordered {
            border: 2px solid green;
            padding: 10px;
            display: inline-block;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        /* th {
            background-color: #f2f2f2;
        } */
        .content-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }
        .app-logo img {
            max-width: 160px;
        }

        .tbody-colored tr:nth-child(even) {
                background-color: var(--box-background-color) !important;
                color: var(--text-secondary-color) !important;
            }
            .tbody-colored tr:nth-child(odd) {
                background-color: var(--box-background-color) !important;
                color: var(--text-secondary-color) !important;
            }
            .tbody-colored tr:first-child {
                background-color: var(--box-background-color) !important; /* light blue for "Honor" */
                color: var(--text-secondary-color) !important;
            }
    </style>
</head>
<body>

    <h1 class="green-bordered">{{ config('app.name') }} {{ __('Salary Policy') }}</h1>

    <div class="content-wrapper">
        <table>
            <thead class="custom-header">
                <tr>
                    <th>{{ __('Level') }}</th>
                    <th>{{ __('Diamond Target') }}</th>
                    <th>{{ __('Day & Hours') }}</th>
                    <th>{{ __('Host Salary') }}</th>
                    <th>{{ __('Agents Salary') }}</th>
                    <th>{{ __('BD Admin') }}</th>
                </tr>
            </thead>
            <tbody class="tbody-colored">
                @if($targets->count())
                    @php $honor = $targets->first(); @endphp
                    <tr>
                        <td><strong>{{ __('Honor') }}</strong></td>
                        <td>{{ $honor->diamonds }}</td>
                        <td>{{ $honor->days }} D / {{ $honor->hours }} h</td>
                        <td>{{ calculateUserUsd($honor->diamonds, $honor->usd ?? 0) }} $</td>
                        <td>{{ calculateUserUsd($honor->diamonds, $honor->agency_share ?? 0) }} $</td>
                        <td>{{ calculateUserUsd($honor->diamonds, $honor->db_percentage ?? 0 )  }}$</td>
                    </tr>

                    @foreach($targets->skip(1) as $index => $target)
                        <tr>
                            <td>{{ 'S' . ($index + 1) }}</td>
                            <td>{{ $target->diamonds }}</td>
                            <td>{{ $target->days }} D / {{ $target->hours }} h</td>
                            <td>{{ calculateUserUsd($target->diamonds, $target->usd ?? 0) }} $</td>
                            <td>{{ calculateUserUsd($target->diamonds, $target->agency_share ?? 0) }} $</td>
                            <td>{{ calculateUserUsd($target->diamonds, $target->db_percentage ?? 0) }}$</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6">{{ __('No data available.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</body>
</html>

