
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>


:root {
        --primary-color: {{ config('themes.primaryColor') }};
        --secondary-color: {{ config('themes.secondaryColor') }};
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
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 57px;
    color: white;
    font-size: 20px;
    margin-bottom: 6px;
}

.stat-icon.bg-blue {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.stat-icon.bg-green {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
}

.stat-label {
    font-size: 13px;
    color: #7f8c8d;
    margin-top: 5px;
}

.section-box {
    background: var(--secondary-color);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}


.section-header h4 {
    margin: 0;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-header .text-yellow {
    color: #f39c12;
}

.section-header .text-red {
    color: #e74c3c;
}


.number-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #ecf0f1;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
}

.number-badge.warning {
    background: #fef9e7;
    color: #f39c12;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 0;
    color: #95a5a6;
}

.empty-state i {
    font-size: 40px;
    margin-bottom: 10px;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

.empty-table {
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #95a5a6;
    background: white;
    border-radius: 8px;
    margin: 15px 0;
}

.empty-table i {
    font-size: 40px;
    margin-bottom: 10px;
}

.empty-table p {
    margin: 0;
    font-size: 14px;
}
.agency-profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    /* background: var(--secondary-color); */
    /* filter: brightness(0.85); */

}

.agency-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.agency-avatar .logo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.agency-info {
    flex: 1;
}

.agency-name {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 28px;
    font-weight: 700;
}

.agency-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
}

.meta-label {
    font-weight: 600;
    color: #7f8c8d;
}

.meta-value {
    color: #34495e;
}

.meta-uuid {
    color: #95a5a6;
    font-size: 0.9em;
}

.agency-stats {
    display: flex;
    gap: 15px;
}

.stat-card {
    background: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    text-align: center;
    min-width: 200px;

}

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #3498db;
}

.stat-label {
    font-size: 12px;
    color: #7f8c8d;
    text-transform: none;
    letter-spacing: 0.5px;
}

.ltr .btn-back {
    position: absolute;
    top: 5px;
    right: 20px;
    background: #ecf0f1;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    color: #7f8c8d;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-back:hover {
    background: #d6e0e3;
    color: #34495e;
}

.notice-section {
    background: #fff8e1;
    border-left: 4px solid #ffc107;
    padding: 15px;
    border-radius: 0 6px 6px 0;
    margin-bottom: 25px;
}

.notice-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    color: #ff9800;
    font-weight: 600;
}

.notice-content {
    color: #5d4037;
    line-height: 1.5;
}

.top-performers-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .top-performers-section {
        grid-template-columns: 1fr;
    }
}

.stats-row {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.section-title {
    margin: 0;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #2c3e50;
}

.avatar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 15px;
}

.avatar-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s;
}

.avatar-item:hover {
    transform: translateY(-3px);
}

.avatar-img-container {
    position: relative;
    width: 60px;
    height: 60px;
    margin-bottom: 8px;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.avatar-badge {
    position: absolute;
    bottom: -5px;
    right: -5px;
    background: #e74c3c;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    border: 2px solid white;
}

.avatar-badge.admin {
    background: #27ae60;
}

.avatar-name {
    font-size: 12px;
    text-align: center;
    max-width: 80px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 0;
    color: #95a5a6;
}

.empty-state i {
    font-size: 40px;
    margin-bottom: 10px;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

.agency-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
    overflow-x: auto;
}

.tab-btn {
    padding: 12px 20px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    font-weight: 600;

    cursor: pointer;
    transition: all 0.3s;
    white-space: nowrap;
}

.tab-btn.active {
    color:var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.tab-btn:hover:not(.active) {
    color: #34495e;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
    color: #2c3e50;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background: var(--secondary-color);
}


.table-section {
    width: 100%;
    border-collapse: collapse;
    background: var(--secondary-color);
}

.data-table th {
    /* text-align: left; */
    padding: 12px 15px;
    background: var(--secondary-color);

    font-weight: 600;
    text-transform: none;
    font-size: 12px;
    letter-spacing: 0.5px;
}

.data-table td {
    width: 262px;
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.user-cell {
    /* display: flex; */
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-info strong {
    font-size: 14px;
}

.user-info small {
    font-size: 11px;
    color: #95a5a6;
}

.role-badge {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.role-badge.owner {
    background: #9b59b6;
}

.role-badge.admin {
    background: #27ae60;
}

.btn-action {
    padding: 5px 10px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-action:hover {
    background: #2980b9;
}

.empty-table {
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #95a5a6;
}

.empty-table i {
    font-size: 40px;
    margin-bottom: 10px;
}

.empty-table p {
    margin: 0;
    font-size: 14px;
}

.user-avatar,
.supporter-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
}
.supporters-avatars {
    display: flex;
    gap: 5px;
    align-items: center;
}
.user-info-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}


.pagination-wrapper {
    padding: 15px 20px;
    display: flex;
    justify-content: center;
    border-top: 1px solid #eee;
     background: var(--secondary-color);
}



    .target-card-section-1{
        /* display: inline-flex; */
        width: 100%;
        padding-top: 26px;
        margin-bottom: 35px;

    }
    .card-target-filter{
        display: inline;
        width: 34%;
        left: 33px;
        position: absolute;
    }
    .card-target-filter .form-group {
        margin-bottom: 16px;
        right: 20px;
        position: relative;
        top: 10px;
    }
    .card-target-filter button {
        position: relative;
        left: -49px;
        bottom: -29px;
    }
    .card-target-filter-phone{
        width: 51%;
        margin-bottom: 27px;
        position: relative;
    }
    .card-target-filter-phone .form-group {
        margin-bottom: 16px;
        right: 20px;
        position: relative;
        top: 10px;
    }
    .card-target-filter-phone button {
        position: relative;
        left: -49px;
        bottom: -29px;
    }

    .target-card-stat{
        width: 50%;
    }
    .filter-form{
        border-radius: 13px;
        height: 165px;

    }

    .card-target-filter-phone{
        /* display: none; */
    }
    .card-target-filter{
        display: none;
    }
    @media (max-width: 768px) {
    .stats-row {
        flex-direction: column;
        width: 108%;

    }

    .avatar-grid {
        grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
    }

    .target-card-section-1 {
            display: grid;
            width: 100%;
            padding-top: 26px;
            margin-bottom: 35px;
        }


    .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 118px;
            color: white;
            font-size: 20px;
            margin-bottom: 6px;

    }


    .target-card-stat {
         width: 92%;

    }
    .card-target-filter{
        display: none;
    }
    .card-target-filter-phone {
        display: block;
        width: 100%;
        left: 0px;
        position: relative;
        margin-bottom: 31px;

    }
    .card-target-filter-phone  .col-md-7{
       float: none;
    }

    .card-target-filter-phone .form-control {
            display: block;
            width: 89%;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857143;
            color: var(--text-secondary-color) !important;
            background-color: #fff;
            background-image: none;
            border: 1px solid var(--primary-hover-alpha) !important;
            border-radius: 4px;
        }

        .card-target-filter-phone .filter-form {
            border-radius: 13px;
            height: 238px;
        }

        .card-target-filter-phone .align-items-end{
            display: grid;
        }

        .card-target-filter-phone button {
            left: -224px;

        }
}
    </style>


<body>

<div class="agency-profile-container">

<!-- Header Section -->
<div class="agency-header">
    <div class="agency-avatar">
        <img src="{{ $bd->display_image }}" alt="Agency Logo" class="logo-img">
    </div>
    <div class="agency-info">
        <h1 class="agency-name">{{ $bd->username ??'' }}</h1>
        <div class="agency-meta">
            <div class="meta-item">
                <span class="meta-label">{{ __("ID") }}:</span>
                <span class="meta-value">{{ $bd->id }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">{{ __("username") }}:</span>
                <span class="meta-value">{{ $bd->username ?? '' }}</span>
            </div>
        </div>
    </div>
    <a href="{{ url('admin/usersBd') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> {{ __("Go Back") }}
    </a>
</div>

<div class="top-performers-section">

            <div class="performers-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-star"></i>
                        {{ __('total proft') }}
                    </h2>
                </div>
                    <div class="avatar-grid">


                            <a href="#" >
                               {{  truncateAndTrim( $bd->total_salary)  }}
                            </a>
                    </div>

            </div>

            <div class="performers-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <!-- <i class="fas fa-star"></i> -->
                        {{ __('Agency Count') }}
                    </h2>
                </div>


                    <div class="avatar-grid">


                            <a href="#" >
                               {{ $bd->agencies_count  }}
                            </a>
                    </div>

            </div>
        </div>




<!-- Tabs Navigation -->
@php $activeTab = request('tab', 'agencies'); @endphp

<div class="agency-tabs">
    <a href="?tab=agencies" class="tab-btn {{ $activeTab === 'agencies' ? 'active' : '' }}">{{ __('agencies') }}</a>
    <a href="?tab=transactions" class="tab-btn {{ $activeTab === 'transactions' ? 'active' : '' }}">{{ __('transactions') }}</a>
    <a href="?tab=target_history" class="tab-btn {{ $activeTab === 'target_history' ? 'active' : '' }}">{{ __('Target History') }}</a>


</div>

<!-- Loading Overlay -->
<div id="tab-loading" style="display: none; position: fixed; top: 50%; left: 50%; background: var(--primary-color); color: var(--text-primary-color); z-index: 9999; padding: 30px 40px; border-radius: 10px; font-size: 20px; font-weight: bold; box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);">
    {{ __('Loading...') }}
</div>

<!-- Members Tab -->
@if($activeTab === 'agencies')
    <div class="tab-content active" id="members-tab">
        <div class="card">
            <div class="card-header">
                <h3>{{ __('Agencies') }}</h3>
                <span class="badge count-badge">{{ optional($agencies)->total() ?? 0 }}</span>
            </div>
            @if($agencies && $agencies->count())
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('owner') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agencies as $index => $agency)
                                <tr>
                                    <td>{{ $index + 1 + (($agencies->currentPage() - 1) * $agencies->perPage()) }}</td>
                                    <td class="user-cell">
                                            <div class="user-avatar">
                                                <a href=" {{ url('admin/agencies/profile/' .$agency->id) }}">
                                                    <img src="{{ getImagePath($agency->img) }}" alt="{{ $agency->name ??'' }}">
                                                </a>
                                            </div>
                                            <div class="user-info">
                                                <strong>
                                                    <a href="{{url('admin/agencies/profile/' .$agency->id) }}">
                                                        {{ $agency->name ??'' }}
                                                    </a>
                                                </strong>
                                            </div>
                                        </td>

                                        <td class="user-cell">
                                            <div class="user-avatar">
                                                <a href="{{ url('admin/users/'. $agency->owner?->id ) }}">
                                                    <img src="{{ getImagePath($agency->owner?->profile?->avatar) }}" alt="{{ $agency->owner?->name ??'' }}">
                                                </a>
                                            </div>
                                            <div class="user-info">
                                                <strong>
                                                    <a href="{{ url('admin/users/'.$agency->owner?->id) }}">
                                                        {{ $agency->owner?->name ??'' }}
                                                    </a>
                                                </strong>
                                            </div>
                                        </td>
                                        <td class="">
                                            @if($agency->status == 1)
                                                <span style="color:green;" title="Active">&#10004;</span> {{-- ✔ --}}
                                            @else
                                                <span style="color:red;" title="Inactive">&#10008;</span> {{-- ✖ --}}
                                            @endif
                                        </td>


                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination-wrapper">
                    {{ $agencies->appends(['tab' => 'agencies'])->links('vendor.pagination.default') }}
                </div>
            @else
                <div class="empty-table">
                    <i class="fas fa-users-slash"></i>
                    <p>{{ __('No agencies found') }}</p>
                </div>
            @endif
        </div>
    </div>
@endif

@if($activeTab === 'transactions')

<div class="tab-content active" id="members-tab">
        <div class="card">
            <div class="card-header">
                <h3>{{ __('transactions') }}</h3>
                <span class="badge count-badge">{{ optional($transactions)->total() ?? 0 }}</span>
            </div>
<!-- Charges Tab -->
@if($transactions && $transactions->count())

    <div class="table-responsive">
        <table class="data-table" id="charge">
            <thead>
                <tr>
                    <th>#</th>
                    <!-- <th>{{ __('Name') }}</th> -->
                    <th>{{ __('receiver') }}</th>
                    <th>{{ __('usd') }}</th>
                    <th>{{ __('amount') }}</th>
                    <th>{{ __('Created') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $index => $charge)
                    @php
                        // تحديد المستخدم (charger) مع التحقق من وجوده
                        $user = null;
                        $userImage = null;
                        $userName = 'N/A';
                        $userUrl = '#';

                        if ($charge->charger_type === 'dash' && $charge->user_type === 'dash') {
                            $user = $charge->admin;
                        } else {
                            $user = $charge->sender;
                        }

                        if ($user) {
                            $userImage = $user->avatar ?? ($user->profile->avatar ?? null);
                            $userName = $user->name ?? 'N/A';

                            if ($charge->charger_type === 'dash' && $charge->user_type === 'dash') {
                                $userUrl = url("admin/admins/{$user->id}");
                            } else {
                                $userUrl = url("admin/users/{$user->id}");
                            }
                        }

                        // تحديد المستلم (receiver) مع التحقق من وجوده
                        $receiverHtml = '';

                        if ($charge->receiveragency) {
                            $agency = $charge->receiveragency;
                            if ($agency) {
                                $cacheKey = "agency_image_{$agency->id}";
                                $image = \Cache::remember($cacheKey, 3600, function () use ($agency) {
                                    $path = @$agency->img;
                                    $defaultImage = asset("images/icon-agency.jpg");
                                    $url = getImagePath($path) ?? $defaultImage;
                                    if (!isImageExists($url)) $url = $defaultImage;
                                    return handleShowImageWithTypes($agency->id, $url, 40, 40);
                                });
                               $name = $agency->name ?? '';
                                $profileUrl = route('admin.agency.profile', ['id' => $agency->id]);
                                $receiverHtml = "
                                    <a href='{$profileUrl}' style='text-decoration: none; color: inherit;'>
                                        <div style='display: flex; align-items: center; gap: 10px;'>
                                            {$image}
                                            <div style='display: flex; flex-direction: column;'>
                                                <span style='text-decoration: underline; cursor: pointer;'>{$name}</span>
                                                <span style='font-size: smaller;'>ID: {$agency->id}</span>
                                            </div>
                                        </div>
                                    </a>
                                ";
                            }
                        } else {
                            $receiverUser = null;
                            if ($charge->user_id) {
                                $receiverUser = \App\Models\User::find($charge->user_id);
                            }

                            if ($receiverUser) {
                                $path = $receiverUser->profile?->avatar ?? null;
                                $defaultImage = asset("images/businessman-icon.jpg");
                                $url = getImagePath($path) ?? $defaultImage;
                                if (!isImageExists($url)) $url = $defaultImage;
                                $image = handleShowImageWithTypes($receiverUser->id, $url, 40, 40);
                                $showUrl = url("admin/users/{$receiverUser->id}");
                                $name =$receiverUser->name ??'';
                                $receiverHtml = "
                                    <a href='{$showUrl}' style='text-decoration: none; color: inherit;'>
                                        <div style='display: flex; align-items: center; gap: 10px;'>
                                            {$image}
                                            <div>
                                                <span style='text-decoration: underline; cursor: pointer;'>{$name}</span><br>
                                                <span style='color: #aaa; font-size: smaller;'>UUID: {$receiverUser->uuid}</span>
                                            </div>
                                        </div>
                                    </a>
                                ";
                            } else {
                                $receiverHtml = '<span class="text-danger">' . __('Unknown') . '</span>';
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $transactions->firstItem() + $index }}</td>
                        <!-- <td>
                            <a href="{{ $userUrl }}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px;">
                                <img src="{{ getImagePath($userImage) }}" width="30" height="30" style="object-fit: cover; border-radius: 50%;">
                                <span>{{ $userName }}</span>
                            </a>
                        </td> -->
                        <td>{!! $receiverHtml !!}</td>
                        <td>{{ number_format($charge->usd ?? 0, 2) }}</td>
                        <td>{{ number_format($charge->amount ?? 0, 2) }}</td>
                        <td>{{ $charge->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-wrapper">
        {{ $transactions->appends(['tab' => 'transactions'])->links('vendor.pagination.default') }}
    </div>
@else
    <!-- <div class="empty-table">
        <i class="fas fa-info-circle"></i>
        <p>{{ __('No transactions found') }}</p>
    </div> -->
@endif
</div>
</div>
@endif




@if($activeTab === 'target_history')
<div class="tab-content active" id="target-history-tab">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">{{ __('Target History') }}</h4>

            <form method="GET" class="form-inline">
                <input type="hidden" name="tab" value="target_history">

                <select name="month" class="form-control mr-2">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <select name="year" class="form-control mr-2">
                    @foreach(range(now()->year-5, now()->year) as $y)
                        <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">{{ __('Filter') }}</button>
            </form>
        </div>

        @if($target_history && $target_history->count())
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Agency ID') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Month') }}</th>
                            <th>{{ __('Year') }}</th>
                            <th>{{ __('Is Paid') }}</th>
                            <th>{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($target_history as $index => $item)
                            <tr>
                                <td>{{ $target_history->firstItem() + $index }}</td>
                                <td>{{ $item->agency_id }}</td>
                                <td>{{  truncateAndTrim( $item->amount)  }}</td>
                                <td>{{ $item->month }}</td>
                                <td>{{ $item->year }}</td>
                                <td>{{ $item->is_paid ? __('Yes') : __('No') }}</td>
                                <td>{{ $item->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $target_history->appends(request()->except('page'))->links('vendor.pagination.default') }}
            </div>
        @else
            <div class="empty-table">
                <i class="fas fa-calendar-times"></i>
                <p>{{ __('No target history found') }}</p>
            </div>
        @endif
    </div>
</div>
@endif

</div>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const urlParams = new URLSearchParams(window.location.search);
            const selectedTab = urlParams.get('tab') || 'members';

            const allTabs = document.querySelectorAll('.tab-btn');
            const allTabContents = document.querySelectorAll('[id$="-tab"]');

            let targetElement = null;

            allTabs.forEach(tab => {
                const target = tab.getAttribute('data-target');
                const content = document.getElementById(target);

                if (target.startsWith(selectedTab)) {
                    tab.classList.add('active');
                    content.style.display = 'block';
                    targetElement = content; // خزن العنصر لعمل scroll إليه لاحقًا
                } else {
                    tab.classList.remove('active');
                    content.style.display = 'none';
                }

                tab.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.getElementById('tab-loading').style.display = 'block';
                    allTabs.forEach(t => t.style.pointerEvents = 'none');
                    const href = tab.getAttribute('href');
                    setTimeout(() => {
                        window.location.href = href;
                    }, 300);
                });
            });

    if (targetElement) {
        setTimeout(() => {
            targetElement.scrollIntoView({ behavior: 'smooth' });
        }, 500); // تأخير بسيط للتأكد أن العنصر ظاهر
    }
});





    document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all buttons and content
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

                // Add active class to clicked button and corresponding content
                btn.classList.add('active');
                const target = btn.getAttribute('data-target');
                document.getElementById(target).classList.add('active');
            });
        });


        $(document).ready(function () {
            console.log("Document ready");

function showLoader() {
    console.log("Showing loader");
    Swal.fire({
        title: 'Loading...',  // تغيير النص ليوضح الرسالة
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function showSuccess(message, callback = null) {
    console.log("Showing success:", message);
    Swal.fire({
        icon: 'success',  // استبدال type بـ icon
        title: message,
        confirmButtonText: 'OK'
    }).then(() => {
        console.log("Success confirmed");
        if (callback) {
            console.log("Running success callback");
            callback();
        }
    });
}

function showError(message) {
    console.log("Showing error:", message);
    Swal.fire({
        icon: 'error',  // استبدال type بـ icon
        title: message,
        confirmButtonText: 'OK'
    });
}

function confirmAction(message, onConfirm) {
    console.log("Confirm action:", message);
    Swal.fire({
        title: message,
        icon: 'question',  // استبدال type بـ icon
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel'
    }).then(result => {
        console.log("Confirmation result:", result);
        console.log("isConfirmed:", result.isConfirmed);

        if (result.value) {
            console.log("User confirmed action");
            onConfirm();
        } else {
            console.log("User cancelled action");
        }
    });
}


    // قبول الطلب
    $('.accept-btn').click(function () {
        const id = $(this).data('id');
        console.log("Accept clicked, ID:", id);
        confirmAction('{{ __("are_you_sure_accept") }}', () => {
            showLoader();
            $.post(`/admin/agencies/accept_join/${id}`, {
                _token: '{{ csrf_token() }}'
            }, function (response) {
                Swal.close();
                console.log("Accept response:", response);
                if (response.status) {
                    showSuccess(response.message, () => {
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', 'requests');
                        window.location.href = url.toString();
                    });
                } else {
                    showError(response.message);
                }
            }).fail(function (xhr) {
                Swal.close();
                console.error("Accept failed", xhr);
                const res = xhr.responseJSON;
                showError(res?.message ?? '{{ __("failed_accept_request") }}');
            });
        });
    });

    // رفض الطلب
    $('.reject-btn').click(function () {
        const id = $(this).data('id');
        console.log("Reject clicked, ID:", id);
        confirmAction('{{ __("are_you_sure_reject") }}', () => {
            showLoader();
            $.post(`/admin/agencies/reject_join/${id}`, {
                _token: '{{ csrf_token() }}'
            }, function (response) {
                Swal.close();
                console.log("Reject response:", response);
                if (response.status) {
                    showSuccess(response.message, () => {
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', 'requests');
                        window.location.href = url.toString();
                    });
                } else {
                    showError(response.message);
                }
            }).fail(function (xhr) {
                Swal.close();
                console.error("Reject failed", xhr);
                const res = xhr.responseJSON;
                showError(res?.message ?? '{{ __("failed_reject_request") }}');
            });
        });
    });

    // ترقية إلى Admin
    $('.make-admin-btn').click(function () {
        const id = $(this).data('id');
        console.log("Make admin clicked, ID:", id);
        confirmAction('{{ __("are_you_sure_make_admin") }}', () => {
            showLoader();
            $.post(`/admin/agencies/admin/${id}`, {
                _token: '{{ csrf_token() }}'
            }, function (response) {
                Swal.close();
                console.log("Make admin response:", response);
                if (response.status) {
                    showSuccess(response.message, () => {
                        location.reload();
                    });
                } else {
                    showError(response.message);
                }
            }).fail(function (xhr) {
                Swal.close();
                console.error("Make admin failed", xhr);
                const res = xhr.responseJSON;
                showError(res?.message ?? '{{ __("failed_make_admin") }}');
            });
        });
    });
});

</script>

