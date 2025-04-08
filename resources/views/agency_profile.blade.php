<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: white;
            display: flex;
            flex-direction: column;
        }
        .settings-sidebar {
            width: 250px;
            background: #222;
            min-height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
        }
        .settings-sidebar h2 {
            text-align: center;
            color: #ff9800;
        }
        .main-content {
            display: flex;
            flex-direction: column;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto 20px;
            text-align: center;
        }
        .agency-container, .charge-container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto 20px;
            text-align: center;
        }
        .avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #ff9800;
            margin-bottom: 15px;
        }
        .details {
            text-align: left;
            margin-top: 10px;
        }
        .details p {
            margin: 5px 0;
            font-size: 16px;
        }
        .details strong {
            color: #ff9800;
        }
        button {
            background: #ff9800;
            padding: 10px;
            border: none;
            cursor: pointer;
            color: black;
            font-weight: bold;
            width: 100%;
            margin-top: 15px;
        }
        button:hover {
            background: #e68900;
        }
        
        /* Table styles */
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #444;
        }
        th {
            background-color: #333;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .settings-sidebar {
                width: 100%;
                min-height: auto;
            }
            .container, .agency-container, .charge-container {
                padding: 15px;
            }
            .avatar img {
                width: 80px;
                height: 80px;
            }
            table {
                font-size: 14px;
            }
            th, td {
                padding: 6px 4px;
            }
        }
        
        @media (max-width: 480px) {
            .details p {
                font-size: 14px;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 4px 2px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <div class="avatar">
                <img src="{{ getImagePath(@$agency->img) }}" alt="Agency Logo">
            </div>
            <h2>{{ @$agency?->name ?? ''}}</h2>
            <div class="details">
                <p><strong>{{__("ID")}}:</strong> {{ $agency->id }}</p>
                <p><strong>{{__("Phone")}}:</strong> {{ @$agency->phone ?? '' }}</p>
                <p><strong>{{__("Owner")}}:</strong> {{ @$agency?->owner?->name ?? '' }}, UUID: {{ @$agency?->owner?->uuid ?? '' }}</p>
                <p><strong>{{__("Notice")}}:</strong> {{ @$agency->notice ?? '' }}</p>
                <p><strong>{{__("coins")}}:</strong> {{ number_format(@$agency->coins) ?? 0 }}</p>
                <p><strong>{{__("salary")}}:</strong> {{ number_format(@$agency->salary) ?? 0 }}</p>
            </div>
            <button onclick="window.history.back()">{{__("Go Back")}}</button>
        </div>

        <div class="agency-container">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" style="text-align: left;">{{ __('members') }}</h4>
    
                    @if($members && $members->count())
                        <div class="table-responsive">
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover grid-table" id="member">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('uuid') }}</th>
                                            <th>{{ __('image') }}</th>
                                            <th>{{ __('reals_count') }}</th>
                                            <th>{{ __('total_days') }}</th>
                                            <th>{{ __('total_hours') }}</th>
                                            <th>{{ __('Monthly DI') }}</th>
                                            <th>{{ __('salary') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody style="color: rgb(208, 115, 43);">
                                        @foreach($members as $index => $member)
                                            <tr>
                                                <td>{{ $index + 1 + (($members->currentPage() - 1) * $members->perPage()) }}</td>
                                                <td>{{ @$member->name ?? '' }}</td>
                                                <td>{{ @$member->uuid ?? '' }}</td>
                                                
                                                <td>
                                                    <img src="{{ getImagePath(@$member->profile->avatar) }}" width="50" height="50" style="object-fit: cover; border-radius: 50%;">
                                                </td>
                                                <td>{{count($member->reals) ?? 0 }}</td>
                                                <td>{{ $member->total_days ?? 0 }}</td>
                                                <td>{{ $member->liveTime->sum("hours") }}</td>
                                                <td>{{ $member->monthly_diamond_received ?? 0 }}</td>
                                                <td>{{ $member->userSallary->sallary ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
    
                                <!-- Pagination Links -->
                                <div class="pagination-container">
                                    {{ $members->appends(['charges_page' => $charges->currentPage()])->links('vendor.pagination.bootstrap-4') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <p>{{ __('No members found.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    
        @if($agency->chargeAgency)
            <div class="charge-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="text-align: left;">{{ __('charge') }}</h4>
                
                        @if($charges && $charges->count())
                            <div class="table-responsive">
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-hover grid-table" id="charge">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('coin') }}</th>
                                                <th>{{ __('created') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody style="color: rgb(208, 115, 43);">
                                            @foreach($charges as $index => $charge)
                                                <tr>
                                                    <td>{{ $charges->firstItem() + $index }}</td>
                                                    <td>
                                                        <img src="{{ getImagePath(@$charge->admin->avatar) }}" width="30" height="30" style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                                        {{ @$charge->admin->name ?? '' }}
                                                    </td>
                                                    <td>{{ number_format(@$charge->amount ?? 0) }}</td>
                                                    <td>{{ @$charge->created_at ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                    
                            <div class="pagination-container">
                                {{ $charges->appends(['members_page' => $members->currentPage()])->links('vendor.pagination.bootstrap-4') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif  
    </div>
</body>
</html>
