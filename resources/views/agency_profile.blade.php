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
            width: 100%;
            background: #222;
            padding: 15px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
        }
        .settings-sidebar h2 {
            text-align: center;
            color: #ff9800;
            margin: 0;
            padding: 10px 0;
        }
        .settings-content, .member-content, .charge-content {
            width: 100%;
            padding: 15px;
            box-sizing: border-box;
        }
        .container, .agency-container, .charge-container {
            background: #222;
            padding: 15px;
            border-radius: 5px;
            width: 100%;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        .avatar img {
            width: 100px;
            height: 100px;
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
            font-size: 14px;
            word-break: break-word;
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
        .card-title {
            text-align: left;
            margin-bottom: 15px;
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: center;
            border: 1px solid #444;
            font-size: 12px;
        }
        th {
            background-color: #333;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        .pagination-container {
            margin-top: 15px;
        }
        
        /* Media queries for responsive adjustments */
        @media (min-width: 768px) {
            body {
                flex-direction: row;
                flex-wrap: wrap;
            }
            .settings-sidebar {
                width: 250px;
                min-height: 100vh;
            }
            .settings-content, .member-content, .charge-content {
                width: calc(100% - 250px);
            }
            .container {
                width: 90%;
                max-width: 800px;
            }
            .agency-container, .charge-container {
                width: 90%;
                max-width: 1100px;
            }
            th, td {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="settings-content">
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
    </div>

    <div class="member-content">
        <div class="container agency-container">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ __('members') }}</h4>
    
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
    </div>
    
    <br>
    @if($agency->chargeAgency)
        <div class="charge-content">
            <div class="container charge-container">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('charge') }}</h4>
                
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
        </div>
    @endif  
</body>
</html>