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
        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title i {
            margin-left: 10px;
            font-size: 20px;
        }
        .stars-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 10px 0;
        }
        .stars-section {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--box-shadow);
        }

        .star-wrapper {
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
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
        .star-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .star-wrapper:hover .star-avatar {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        .star-badge {
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--card-bg);
            font-weight: bold;
        }

        .admin-badge {
            background: var(--success-color);
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
        html {
            scroll-behavior: smooth;
        }
        .star-wrapper {
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
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

        @media (max-width: 480px) {
            

            .section-title {
                font-size: 20px;
            }

            .stars-container {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

    <div class="main-content">
        <div class="container">
            <div class="avatar">
                <img src="{{ $agency->display_image }}" alt="Agency Logo">
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

        <div class="stars-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-star"></i>
                    {{ __('نجوم الوكالة') }}
                </h2>
            </div>
            
            @if($giftLog && $giftLog->count())
                <div class="stars-container">
                    @foreach($giftLog as $log)
                        @php
                            $user = $log->receiver;
                            $path = $user->profile?->avatar ?? null;
                            $defaultImage = asset("images/businessman-icon.jpg");
                            $url = isImageExists(getImagePath($path)) ? getImagePath($path) : $defaultImage;
                            $username = htmlspecialchars($user->name ?? 'Unknown');
                            $userUrl = route('admin.users.show', $user->id);
                            $exp = number_format($log->exp);
                        @endphp
                        
                        <div class="star-wrapper" 
                            onclick="window.location.href='{{ $userUrl }}'"
                            title="{{ $username }} ({{ $exp }} EXP)">
                            <img src="{{ $url }}" 
                                alt="{{ $username }}"
                                class="star-avatar">
                            <div class="star-badge">{{ $exp }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="no-data">{{ __('No stars data available') }}</p>
            @endif
        </div>
    
        <!-- Admins Section -->
        <div class="stars-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-user-shield"></i>
                    {{ __('ادمن الوكالة') }}
                </h2>
            </div>
            
            @if($agency->admins && $agency->admins->count())
                <div class="stars-container">
                    @foreach($agency->admins as $admin)
                        @php
                            $user = $admin->user;
                            $path = $user->profile?->avatar ?? null;
                            $defaultImage = asset("images/businessman-icon.jpg");
                            $url = isImageExists(getImagePath($path)) ? getImagePath($path) : $defaultImage;
                            $username = htmlspecialchars($user->name ?? 'Unknown');
                            $userUrl = route('admin.users.show', $user->id);
                        @endphp
                        
                        <div class="star-wrapper" 
                            onclick="window.location.href='{{ $userUrl }}'"
                            title="{{ $username }}">
                            <img src="{{ $url }}" 
                                alt="{{ $username }}"
                                class="star-avatar">
                            <div class="star-badge admin-badge"><i class="fas fa-shield-alt"></i></div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="no-data">{{ __('لا يوجد ادمن للوكالة') }}</p>
            @endif
        </div>



        <div class="settings-sidebar">
            <div class="settings-menu">
                <button onclick="showSection('showMembers')" class="active">{{ __('members') }}</button>
                <button onclick="showSection('showCharges')">{{ __('charge') }}</button>
                <button onclick="showSection('showSalary')">{{ __('salary') }}</button>
                <button onclick="showSection('joinAgency')">{{ __('Agency Join Requests') }}</button>
                <button onclick="showSection('userTargets')">{{ __('targets') }}</button>
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
                                                <th>{{ __('type') }}</th>
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
                                                  
                                                    <td>
                                                        @php
                                                            $isAdmin = \App\Models\AgencyUserJob::where('user_id', $member->id)
                                                                        ->where('agency_id', $member->agency_id)
                                                                        ->where('type', 'requestManger')
                                                                        ->exists();
                                                                        $isOwner = \App\Models\Agency::where('app_owner_id', $member->id)
                                                                        ->where('id', $member->agency_id)->exists();
                                                        @endphp
                                                
                                                            @if($isOwner)
                                                            <div class="text-center">
                                                                <span class="badge badge-dark fw-bold" style="font-size: 1.5rem; padding: 10px 20px;">
                                                                    {{ __('Owner') }}
                                                                </span>
                                                            </div>
                                                        @elseif($isAdmin)
                                                        <div class="text-center">
                                                            <span class="badge badge-success fw-bold" style="font-size: 1.5rem; padding: 10px 20px;">
                                                                {{ __('Admin') }}
                                                            </span>
                                                        </div>
                                                        @else
                                                        <button class="btn btn-sm btn-primary make-admin-btn" data-id="{{ $member->id }}">
                                                            {{ __('Make Admin') }}
                                                        </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <!-- Pagination Links -->
                                    <div class="pagination-container">
                                        {{ $members->appends(['charges_page' => $charges->currentPage(),'salaries_page' => $salaries->currentPage(),'join_page' => $agencyJoinRequests->currentPage(),'target_page'  => $memberTargets->currentPage(),])->links('vendor.pagination.bootstrap-4') }}
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
                                {{ $charges->appends(['members_page' => $members->currentPage(),'join_page' => $agencyJoinRequests->currentPage(),'salaries_page' => $salaries->currentPage(),'target_page'  => $memberTargets->currentPage(),])->links('vendor.pagination.bootstrap-4') }}
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
                                        {{ $salaries->appends(['charges_page' => $charges->currentPage(),'join_page' => $agencyJoinRequests->currentPage(),'members_page' => $members->currentPage(),
                                        'target_page'  => $memberTargets->currentPage(),])->links('vendor.pagination.bootstrap-4') }}
                                    </div>
                                </div>
                            </div>

                    </div>
                </div>
            </div>

            <div id="joinAgency" class="settings-section">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title text-left">{{ __('Agency join request') }}</h4>
            
                        <div class="table-responsive">
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover grid-table" id="join">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('User') }}</th>
                                            <th>{{ __('WhatsApp') }}</th>
                                            <th>{{ __('Country') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
            
                                    @if($agencyJoinRequests && $agencyJoinRequests->count())
                                    <tbody style="color: rgb(208, 115, 43);">
                                        @foreach($agencyJoinRequests as $index => $agencyJoinRequest)
                                            @php
                                                $user = $agencyJoinRequest->user;
                                                $name = $user->name ?? '-';
                                                $uid = $user->uuid ?? '-';
                                                $avatarPath = $user->profile?->avatar;
                                                $defaultImage = asset("images/businessman-icon.jpg");
                                                $avatarUrl = getImagePath($avatarPath) ?? $defaultImage;
                                                if (!isImageExists($avatarUrl)) {
                                                    $avatarUrl = $defaultImage;
                                                }
                                                $image = handleShowImageWithTypes($user->id, $avatarUrl, 40, 40);
            
                                                $iconUrl = asset('images/whatsapp.png');
                                                $country = $user->country;
                                                $countryName = app()->getLocale() == 'ar' ? $country?->name : $country?->e_name;
                                                $countryFlag = getImagePath($country?->flag ?? '');
                                            @endphp
            
                                            <tr>
                                                <td>{{ $agencyJoinRequests->firstItem() + $index }}</td>
            
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        {!! $image !!}
                                                        <div>
                                                            <strong>{{ $name }}</strong><br>
                                                            <span style="color: #aaa; font-size: smaller;">UID: {{ $uid }}</span>
                                                        </div>
                                                    </div>
                                                </td>
            
                                                <td>
                                                    <div style="display: flex; align-items: center;">
                                                        <span>{{ $agencyJoinRequest->whatsapp }}</span>
                                                        <img src="{{ $iconUrl }}" alt="WhatsApp" width="20" height="20" style="margin-left: 5px; filter: invert(1);">
                                                    </div>
                                                </td>
            
                                                <td>
                                                    <div style="display: flex; flex-direction: column; align-items: start;">
                                                        <span>{{ $countryName }}</span>
                                                        @if($countryFlag)
                                                            <img src="{{ $countryFlag }}" alt="Flag" width="20" height="20" style="margin-top: 3px; filter: invert(1);">
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <button class="btn btn-success btn-sm accept-btn" data-id="{{ $agencyJoinRequest->id }}">
                                                        {{ __('Accept') }}
                                                    </button>
                                                
                                                    <button class="btn btn-danger btn-sm reject-btn" data-id="{{ $agencyJoinRequest->id }}">
                                                        {{ __('Reject') }}
                                                    </button>
                                                </td>
                                             
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @endif
                                </table>
                            </div>
                        </div>
            
                        <div class="pagination-container mt-3">
                            {{ $agencyJoinRequests->appends([
                                'members_page' => $members->currentPage(),
                                'salaries_page' => $salaries->currentPage(),
                                'charges_page' => $charges->currentPage(),
                                'target_page'  => $memberTargets->currentPage(),
                            ])->links('vendor.pagination.bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>

            <div id="userTargets" class="settings-section">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title text-left">{{ __('target') }}</h4>
                        <form method="GET" action="{{ url('admin/agencies/profile/' . $agency->id) }}#userTargets" class="mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <div class="form-floating">
                                        <select name="month" id="month" class="form-select">
                                            <option value="">All Months</option>
                                            @for($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                        <label for="month">{{ __('Month') }}</label>
                                    </div>
                                </div>
                        
                                <div class="col-md-5">
                                    <div class="form-floating">
                                        <select name="year" id="year" class="form-select">
                                            <option value="">All Years</option>
                                            @for($y = now()->year; $y >= 2020; $y--)
                                                <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                            @endfor
                                        </select>
                                        <label for="year">{{ __('Year') }}</label>
                                    </div>
                                </div>
                        
                                <div class="col-md-2 d-flex">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="fas fa-filter me-2"></i> {{ __('Apply') }}
                                    </button>
                                    @if(request()->has('month') || request()->has('year'))
                                    <a href="{{ url('admin/agencies/profile/' . $agency->id) }}" class="btn btn-outline-secondary ms-2" title="Reset filters">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                        <br>
                        <div class="stars-section">
                            <div class="section-header">
                                <h2 class="section-title">
                                    <i class="fas fa-star"></i>
                                    {{ __('نجوم الوكالة') }}
                                </h2>
                            </div>
                            
                            @if($stars && $stars->count())
                                <div class="stars-container">
                                    @foreach($stars as $log)
                                        @php
                                            $user = $log->receiver;
                                            $path = $user->profile?->avatar ?? null;
                                            $defaultImage = asset("images/businessman-icon.jpg");
                                            $url = isImageExists(getImagePath($path)) ? getImagePath($path) : $defaultImage;
                                            $username = htmlspecialchars($user->name ?? 'Unknown');
                                            $userUrl = route('admin.users.show', $user->id);
                                            $exp = number_format($log->exp);
                                        @endphp
                                        
                                        <div class="star-wrapper" 
                                            onclick="window.location.href='{{ $userUrl }}'"
                                            title="{{ $username }} ({{ $exp }} EXP)">
                                            <img src="{{ $url }}" 
                                                alt="{{ $username }}"
                                                class="star-avatar">
                                            <div class="star-badge">{{ $exp }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="no-data">{{ __('No stars data available') }}</p>
                            @endif
                        </div>
                    
                        <!-- Admins Section -->
                        <div class="stars-section">
                            <div class="section-header">
                                <h2 class="section-title">
                                    <i class="fas fa-user-shield"></i>
                                    {{ __('ابطال الوكالة') }}
                                </h2>
                            </div>
                            
                            @if($heroes && $heroes->count())
                                <div class="stars-container">
                                    @foreach($heroes as $log)
                                        @php
                                            $user = $log->sender;
                                            $path = $user->profile?->avatar ?? null;
                                            $defaultImage = asset("images/businessman-icon.jpg");
                                            $url = isImageExists(getImagePath($path)) ? getImagePath($path) : $defaultImage;
                                            $username = htmlspecialchars($user->name ?? 'Unknown');
                                            $userUrl = route('admin.users.show', $user->id);
                                            $exp = number_format($log->exp);
                                        @endphp
                                        
                                        <div class="star-wrapper" 
                                            onclick="window.location.href='{{ $userUrl }}'"
                                            title="{{ $username }} ({{ $exp }} EXP)">
                                            <img src="{{ $url }}" 
                                                alt="{{ $username }}"
                                                class="star-avatar">
                                            <div class="star-badge">{{ $exp }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="no-data">{{ __('لا يوجد ابطال للوكالة') }}</p>
                            @endif
                        </div>
                        <br>
                        <div class="modal-body">

                            <div class="row">
                              <!-- Card 1 -->
                              <div class="col-md-6">
                                <div class="card">
     
                                  <div class="card-body">
                                    <h3 class="card-title">{{' target'}}</h3>
                                    <p class="card-text">{{$agencyTarget}}</p>
                                  </div>
                                </div>
                              </div>
                    
                              <!-- Card 2 -->
                              <div class="col-md-6">
                                <div class="card">
                                 
                                  <div class="card-body">
                                    <h3 class="card-title">{{ 'agency rate'}}</h3>
                                    <p class="card-text">{{$rate}}</p>
                                  </div>
                                </div>
                              </div>
                            </div>
                    
                          
                        </div>
                        <div class="table-responsive">
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover grid-table" id="target">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('user') }}</th>
                                            <th>{{ __('diamond') }}</th>
                                            <th>{{ __('remaining diamonds') }}</th>
                                            <th>{{ __('days') }}</th>
                                            <th>{{ __('hours') }}</th>
                                            <th>{{ __('supporters') }}</th>
                                        </tr>
                                    </thead>
            
                                    @if($memberTargets && $memberTargets->count())
                                    <tbody style="color: rgb(208, 115, 43);">
                                        @foreach($memberTargets as $index => $memberTarget)
                                            @php
                                                
                                                $name = $memberTarget->name ?? '-';
                                                $uid = $memberTarget->uuid ?? '-';
                                                $avatarPath = $memberTarget->profile?->avatar;
                                                $defaultImage = asset("images/businessman-icon.jpg");
                                                $avatarUrl = getImagePath($avatarPath) ?? $defaultImage;
                                                if (!isImageExists($avatarUrl)) {
                                                    $avatarUrl = $defaultImage;
                                                }
                                                $image = handleShowImageWithTypes($memberTarget->id, $avatarUrl, 40, 40);
            
                                               
                                            @endphp
            
                                            <tr>
                                                <td>{{ $memberTargets->firstItem() + $index }}</td>
            
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        {!! $image !!}
                                                        <div>
                                                            <strong>{{ $name }}</strong><br>
                                                            <span style="color: #aaa; font-size: smaller;">UID: {{ $uid }}</span>
                                                        </div>
                                                    </div>
                                                </td>
            
                                                <td>
                                                  {{$memberTarget->targets->first()->user_diamonds ?? 0}}
                                                </td>
            
                                                <td>
                                                    {{$memberTarget->targets->first()->next_diamond ?? 0}}
                                                </td>
                                                <td>
                                                   {{$memberTarget->targets->first()->user_hours ?? 0}}
                                                </td>
                                                <td>
                                                  {{$memberTarget->targets->first()->user_days ?? 0}}
                                                </td>
                                                <td>
                                                    {{$memberTarget->targets->first()->user_days ?? 0}}
                                                </td>
                                             
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @endif
                                </table>
                            </div>
                        </div>
            
                        <div class="pagination-container mt-3">
                            {{ $memberTargets->appends([
                                'members_page' => $members->currentPage(),
                                'salaries_page' => $salaries->currentPage(),
                                'charges_page' => $charges->currentPage(),
                                'join_page' => $agencyJoinRequests->currentPage(),
                                'month' => request('month'),
                                'year' => request('year'),
                            ])->links('vendor.pagination.bootstrap-4') }}
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


    $(document).ready(function() {
    $('.accept-btn').click(function() {
        const id = $(this).data('id');
        if (confirm("Are you sure you want to accept this request?")) {
            $.post(`/admin/agencies/accept_join/${id}`, {
                _token: '{{ csrf_token() }}'
            }, function(response) {
                if (response.status) {
                    alert(response.message); // Show success
                    location.reload();
                } else {
                    alert(response.message); // Show error returned by backend
                }
            }).fail(function(xhr) {
                const res = xhr.responseJSON;
                alert(res?.message ?? 'Failed to accept the request.');
            });
        }
    });

    $('.reject-btn').click(function() {
        const id = $(this).data('id');
        if (confirm("Are you sure you want to reject this request?")) {
            $.post(`/admin/agencies/reject_join/${id}`, {
                _token: '{{ csrf_token() }}'
            }, function(response) {
                if (response.status) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            }).fail(function(xhr) {
                const res = xhr.responseJSON;
                alert(res?.message ?? 'Failed to reject the request.');
            });
        }
    });

    $('.make-admin-btn').click(function () {
        const id = $(this).data('id');
        if (confirm("Are you sure you want to make this user an admin?")) {
            $.post(`/admin/agencies/admin/${id}`, {
                _token: '{{ csrf_token() }}'
            }, function (response) {
                if (response.status) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            }).fail(function (xhr) {
                const res = xhr.responseJSON;
                alert(res?.message ?? 'Failed to make user an admin.');
            });
        }
    });

});





    </script>
