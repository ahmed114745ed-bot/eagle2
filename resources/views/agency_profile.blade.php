<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
.settings-sidebar {
    background-color: var(--table-background-color);
    display: block;
    padding: 10px 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    color: var(--text-primary-color);
    overflow-x: auto;
    white-space: nowrap;
    scrollbar-width: thin;
    width: 100%;
}

.settings-menu {
    display: flex;
    gap: 4px;
    color: var(--text-secondary-color);
    overflow-x: auto;
    white-space: nowrap;
    scrollbar-width: thin;
    margin-bottom: 20px;
}

/*.settings-menu button {*/
/*    background-color: var(--box-background-color);*/
/*    border: none;*/
/*    padding: 10px 15px;*/
/*    font-size: 16px;*/
/*    cursor: pointer;*/
/*    transition: all 0.3s ease;*/
/*    color: var(--text-secondary-color) !important;*/
/*    border-radius: 4px;*/
/*}*/

/*.settings-menu button:hover {*/
/*    background-color: #ff9800;*/
/*}*/

/*.settings-menu button.active {*/
/*    background-color: var(--primary-color);*/
/*    color: var(--text-secondary-color) !important;*/
/*}*/

.settings-content {
    width: 100%;
}

.settings-section {
    display: none;
    width: 100%;
}

.settings-section.active {
    display: block;
}
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: white;
            display: flex;
            flex-direction: column;
        }
        /* .settings-sidebar {
            width: 250px;
            background: #222;
            min-height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
        }
        .settings-sidebar h2 {
            text-align: center;
            color: #ff9800;
        } */
        .main-content {
            display: flex;
            flex-direction: column;
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background: var(--secondary-color);
            filter: brightness(0.85);
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
        .rtl .details {
            text-align: right;
        }
        .details p {
            margin: 5px 0;
            font-size: 16px;
        }
        /*.details strong {*/
        /*    color: #ff9800;*/
        /*}*/
        button {
            padding: 10px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            margin-top: 15px;
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
            background-color: var(--secondary-color);
            filter: brightness(0.85);
            text-align: center;
            border-bottom: 1px solid #444;
        }

        th {
            background-color: var(--secondary-color);
            filter: brightness(0.85);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
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
        @media (max-width: 768px) {
            .settings-menu {
                flex-wrap: wrap;
            }

            .settings-menu button {
                flex: 1 0 50%; /* Two buttons per row on small screens */
                max-width: none;
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



        <div class="settings-sidebar">
            <div class="settings-menu">
                <button onclick="showSection('showMembers')" class="active">{{ __('members') }}</button>
                <button onclick="showSection('showCharges')">{{ __('charge') }}</button>
                <button onclick="showSection('showSalary')">{{ __('salary') }}</button>
            </div>
        </div>

        <div class="settings-content">
            <!-- Members Section -->
            <div id="showMembers" class="settings-section active">
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
                                        {{ $members->appends(['charges_page' => $charges->currentPage(),'salaries_page' => $salaries->currentPage()])->links('vendor.pagination.bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <p>{{ __('No members found.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Charges Section -->
            <div id="showCharges" class="settings-section">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="text-align: left;">{{ __('charge') }}</h4>


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
                                        @if($charges && $charges->count())
                                        @if($agency->Shipping_agency == 1)
                                        <tbody style="color: rgb(208, 115, 43);">
                                            @foreach($charges as $index => $charge)
                                                @php
                                                    if ($charge->charger_type == 'dash' && $charge->user_type == 'dash') {
                                                        $user = $charge->admin;
                                                        $image = $user->avatar;
                                                    } else {
                                                        $user = $charge->sender;
                                                        $image = $user->profile->avatar ?? null;
                                                    }
                                                @endphp
                                                <tr>
                                                    <td>{{ $charges->firstItem() + $index }}</td>
                                                    <td>
                                                        <img src="{{ getImagePath($image) }}" width="30" height="30" style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                                        {{ $user->name ?? '' }}
                                                    </td>
                                                    <td>{{ number_format($charge->amount ?? 0) }}</td>
                                                    <td>{{ $charge->created_at ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        @endif
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <div class="pagination-container">
                                {{ $charges->appends(['members_page' => $members->currentPage(),'salaries_page' => $salaries->currentPage()])->links('vendor.pagination.bootstrap-4') }}
                            </div>

                    </div>
                </div>

            </div>

            <!-- salary Section -->
            <div id="showSalary" class="settings-section ">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="text-align: left;">{{ __('salary') }}</h4>
                            <div class="table-responsive">
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-hover grid-table" id="salary">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('salary') }}</th>
                                                <th>{{ __('month') }}</th>
                                                <th>{{ __('year') }}</th>


                                            </tr>
                                        </thead>
                                        @if($salaries && $salaries->count())
                                            <tbody style="color: rgb(208, 115, 43);">
                                                @foreach($salaries as $index => $salary)
                                                    <tr>
                                                        <td>{{ $index + 1 + (($salaries->currentPage() - 1) * $salaries->perPage()) }}</td>
                                                        <td>{{ @$salary->sallary - $salary->cut_amount }}</td>
                                                        <td>{{ @$salary->month?? '' }}</td>
                                                        <td>{{ @$salary->year ?? '' }}</td>


                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        @endif
                                    </table>

                                    <!-- Pagination Links -->
                                    <div class="pagination-container">
                                        {{ $salaries->appends(['charges_page' => $charges->currentPage(),'members_page' => $members->currentPage()])->links('vendor.pagination.bootstrap-4') }}
                                    </div>
                                </div>
                            </div>

                    </div>
                </div>
            </div>
        </div>

    </div>



    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

    document.addEventListener("DOMContentLoaded", function() {
        // Show members tab by default
        showSection('showMembers');

        // Check URL for active tab
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        if (activeTab) {
            showSection(activeTab);
        }
    });

    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.settings-section').forEach(section => {
            section.classList.remove('active');
        });

        // Show selected section
        document.getElementById(sectionId).classList.add('active');

        // Update button styles
        document.querySelectorAll('.settings-menu button').forEach(button => {
            button.classList.remove('active');
        });

        const activeButton = document.querySelector(`.settings-menu button[onclick="showSection('${sectionId}')"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }

        // Update URL with active tab
        const url = new URL(window.location);
        url.searchParams.set('tab', sectionId);
        window.history.pushState({}, '', url);
    }



    </script>
</body>

