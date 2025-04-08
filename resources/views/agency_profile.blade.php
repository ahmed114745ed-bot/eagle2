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
        .settings-content {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            width: 1200px;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 800px;
            text-align: center;
        }
        .agency-content {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            width: 1200px;
            justify-content: center;
            align-items: center;
        }
        .charge-container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width: 1100px;
            text-align: center;
        }
        .settings-content {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            width: 1200px;
            justify-content: center;
            align-items: center;
        }
        .agency-container {
            background: #222;
            padding: 20px;
            border-radius: 5px;
            width:1100px;
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
                    <!-- Align h4 to the left -->
                    <h4 class="card-title" style="text-align: left;">{{ __('members') }}</h4> <!-- Aligning to the left -->
    
                    @if($members && $members->count())
                        <div class="table-responsive">
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover grid-table" id="member">
                                    <tr>
                                        <th style="text-align: center;">#</th>
                                        <th style="text-align: center;">{{ __('Name') }}</th>
                                        <th style="text-align: center;">{{ __('uuid') }}</th>
                                        <th style="text-align: center;">{{ __('image') }}</th>
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
    
                            <!-- Pagination Links -->
                            <div class="pagination-container">
                                {{ $members->appends(['charges_page' => $charges->currentPage()])->links('vendor.pagination.bootstrap-4') }}
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
    <div class="charge-content">
        <div class="container charge-container">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" style="text-align: left;">{{ __('charge') }}</h4>
            
                    @if($charges && $charges->count())
                        <div class="table-responsive">
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover grid-table" id="charge">
                                    <tr>
                                        <th style="text-align: center;">#</th>
                                        <th style="text-align: center;">{{ __('Name') }}</th>
                                        <th style="text-align: center;">{{ __('coin') }}</th>
                                        <th style="text-align: center;">{{ __('created') }}</th>
                                    </tr>
                                </thead>
                                <tbody style="color: rgb(208, 115, 43);">
                                    @foreach($charges as $index => $charge)
                                        <tr>
                                            <td>{{ $charges->firstItem() + $index }}</td> <!-- To correctly show the index based on pagination -->
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
            
                        <!-- Pagination Links -->
                        <div class="pagination-container">
                            {{ $charges->appends(['members_page' => $members->currentPage()])->links('vendor.pagination.bootstrap-4') }}
                        </div>
            
                    @else
                        {{-- <p>{{ __('No charges found.') }}</p> --}}
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    
</body>
</html>
