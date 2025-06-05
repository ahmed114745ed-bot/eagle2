<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    .filter-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        padding: 20px;
        margin-bottom: 24px;
    }

    .filter-content {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-end;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-actions {
        display: flex;
        gap: 12px;
        margin-left: auto;
    }

    .form-floating {
        position: relative;
    }

    .form-select {
        height: 48px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
        padding: 12px 16px;
        font-size: 14px;
        background-color: #f9f9f9;
    }

    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
        background-color: #ffffff;
    }

    .form-label {
        color: #000000;
        transition: all 0.3s ease;
    }

    .btn-filter {
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        height: 48px;
    }

    .btn-filter:hover {
        background-color: var(--primary-hover-color);
        transform: translateY(-1px);
    }

    .btn-reset {
        background-color: #f8f9fa;
        color: #6c757d;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        height: 48px;
    }

    .btn-reset:hover {
        background-color: #f1f3f5;
        color: var(--danger-color);
        border-color: var(--danger-light-color);
    }

    @media (max-width: 768px) {
        .filter-content {
            flex-direction: column;
            gap: 16px;
        }

        .filter-group {
            width: 100%;
        }

        .filter-actions {
            width: 100%;
            justify-content: flex-end;
            margin-left: 0;
        }
    }

    @media (max-width: 576px) {
        .filter-actions {
            flex-direction: column;
            gap: 12px;
        }

        .btn-filter, .btn-reset {
            width: 100%;
            justify-content: center;
        }
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

.agency-header {
    display: flex;
    align-items: flex-start;
    gap: 25px;
    margin-bottom: 30px;
    position: relative;
    padding: 20px;
    background: var(--secondary-color);
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    /* filter: brightness(0.5); */

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
    text-transform: uppercase;
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

.rtl .btn-back {
    position: absolute;
    top: 5px;
    left: 20px;
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

.performers-card {
    background: var(--secondary-color);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    /* filter: brightness(0.5); */

}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
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

.section-badge {
    background: #3498db;
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
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

.card-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--secondary-color);
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
    color: #2c3e50;
}

.count-badge {
    background: #ecf0f1;
    color: #7f8c8d;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
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
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
}

.data-table td {
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
    display: flex;
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


</head>
<body>

    <div class="agency-profile-container">
        <!-- Header Section -->
        <div class="agency-header">
            <div class="agency-avatar">
                <img src="{{getImagePath( @$user->profile->avatar ) }}" alt="Agency Logo" class="logo-img">
            </div>
            <div class="agency-info">
                <h1 class="agency-name">{{ @$user?->name ?? ''}}</h1>
                <div class="agency-meta">
                    <div class="meta-item">
                        @if (@$user->uuid == @$user->original_uuid)
                            <span class="meta-label">{{ __("uuid") }}:</span>
                            <span class="meta-value">{{ @$user->uuid }}</span>
                        @else
                            <span class="meta-label">{{ __("uuid") }}:</span>
                            <span class="meta-value">{{ @$user->uuid }}</span><br>
                            <span class="meta-label">{{ __("special uuid") }}:</span>
                            <span class="meta-value">{{ @$user->original_uuid }}</span>
                        @endif
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{__("Phone")}}:</span>
                        <span class="meta-value">{{ @$user->phone ?? 'N/A' }}</span>
                    </div>

                </div>
                <div class="agency-stats">
                    <div class="agency-meta">
                        <div class="meta-item">
                                <span class="meta-label">{{ __('Balance') }}:</span>
                                <span class="meta-value">{{ @$user->salary }}</span>

                        </div>
                        <div class="meta-item">
                            <span class="meta-label">{{__('Level')}}:</span>
                            <span class="meta-value">{{\App\Helpers\Common::level_center($user)['sender_level'] }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">{{__('worth')}}:</span>
                            <span class="meta-value">{{\App\Helpers\Common::level_center($user)['receiver_level'] }}</span>
                        </div>

                    </div>

                    <div class="agency-meta">
                        <div class="meta-item">
                                <span class="meta-label">{{ __('diamonds') }}:</span>
                                <span class="meta-value">{{ @$user->getTotalDiamond() }}</span>

                        </div>
                        <div class="meta-item">
                            <span class="meta-label">{{__('coins')}}:</span>
                            <span class="meta-value">{{@$user->di }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">{{__('type')}}:</span>
                            <span class="meta-value">{{@$user->userType() }}</span>
                        </div>

                    </div>
                </div>
            </div>
            <button class="btn-back" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i> {{__("Go Back")}}
            </button>
        </div>



       @php
                    $activeTab = request('tab', 'tab=salary'); 
        @endphp
        <!-- Navigation Tabs -->
        <div class="agency-tabs">
            <a href="?tab=packs" class="tab-btn" data-target="packs-tab">{{ __('packs') }}</a>
            <a href="?tab=vips" class="tab-btn" data-target="vips-tab">{{ __('vips') }}</a>
            @if (\Encore\Admin\Facades\Admin::user()->can('salary-switch-' . 'users') || \Encore\Admin\Facades\Admin::user()->can('*'))
                <a href="?tab=salary" class="tab-btn {{ $activeTab == 'salary' ? 'active' : '' }}" data-target="salary-tab">{{ __('user wallet') }}</a>
            @endif


        </div>
        <div id="tab-loading" style="
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            /* transform: translate(-50%, -50%); */
            background: var(--primary-color);
            color: var(--text-primary-color);
            z-index: 9999;
            padding: 30px 40px;
            border-radius: 10px;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        ">
            {{ __('Loading...') }}
        </div>






            <!-- packs Section -->
            <div class="tab-content active" id="packs-tab">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-align: left;">{{ __('pack') }}</h4>
                    </div>

                            <div class="table-responsive">
                                <div class="box-body ">
                                    <table class="data-table" id="pack">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('get type') }}</th>
                                                <th>{{ __('type') }}</th>
                                                <th>{{ __('img') }}</th>
                                                <th>{{ __('expire') }}</th>
                                                <th>{{ __('action') }}</th>
                                            </tr>
                                        </thead>
                                        @if($packs && $packs->count())

                                        <tbody style="color: rgb(208, 115, 43);">
                                            @foreach($packs as $index => $pack)
                                                @php
                                                    $path = @$pack->ware?->show_img ?? '';

                                                @endphp
                                                <tr>
                                                    <td>{{ $packs->firstItem() + $index }}</td>
                                                    <td>{{ $pack->getTypeGet() }}</td>
                                                    <td>{{ $pack->getType() }}</td>
                                                    <td>
                                                        <img src="{{ getImagePath(@$path) }}" width="30" height="30" style="object-fit: cover; border-radius: 50%; margin-right: 10px;">

                                                    </td>
                                                    <td>{{\Carbon\Carbon::createFromTimestamp($pack->expire)->format('Y-m-d H:i:s') }}</td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <button class="btn btn-falcon-info w-100 me-3 edit_item_model_btn" data-id="{{ $pack->id }}">
                                                                {{ __('dashboard.free') }}
                                                            </button>
                                                            <button class="btn btn-danger delete-btn" data-id="{{ $pack->id }}">
                                                                {{ __('dashboard.delete') }}
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        @endif

                                    </table>
                                </div>
                            </div>

                            <div class="pagination-wrapper">
                                {{ $packs?->appends([
                                    'vip_page' => $userVips?->currentPage(),
                                    'salary_page' => $salaries?->currentPage(),
                                ])->links('vendor.pagination.default') }}
                            </div>


                </div>

            </div>

            <!-- vips Section -->
            <div class="tab-content" id="vips-tab">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-align: left;">{{ __('vips') }}</h4>
                    </div>
                            <div class="table-responsive">
                                <div class="box-body ">
                                    <table class="data-table" id="vip">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('level') }}</th>
                                                <th>{{ __('expire') }}</th>
                                                <th>{{ __('qty') }}</th>
                                                <th>{{ __('total Price') }}</th>
                                                <th>{{ __('action') }}</th>

                                            </tr>
                                        </thead>
                                        @if($userVips && $userVips->count())
                                            <tbody style="color: rgb(208, 115, 43);">
                                                @foreach($userVips as $index => $userVip)
                                                    <tr>
                                                        <td>{{ $index + 1 + (($userVips->currentPage() - 1) * $userVips->perPage()) }}</td>
                                                        <td>{{ $userVip->level }}</td>
                                                        <td>{{\Carbon\Carbon::createFromTimestamp($userVip->expire)->format('Y-m-d H:i:s')}}</td>
                                                        <td>{{ @$userVip->qty ?? 0 }}</td>
                                                        <td>{{ @$userVip->total ?? 0 }}</td>
                                                         <td>
                                                        <div class="d-flex">
                                                            
                                                            <button class="btn btn-danger delete-vip-btn" data-id="{{ $userVip->id }}">
                                                                {{ __('dashboard.delete') }}
                                                            </button>
                                                        </div>
                                                    </td>

                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        @endif
                                    </table>

                                    @if($userVips)
                                            <div class="pagination-container">
                                                {{ $userVips->appends([
                                                    'pack_page' => $packs?->currentPage(),
                                                    'salary_page' => $salaries?->currentPage(),
                                                ])->links('vendor.pagination.bootstrap-4') }}
                                            </div>
                                        @endif
                                </div>
                            </div>



                </div>
            </div>

            <div class="tab-content" id="salary-tab">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title" style="text-align: left;">{{ __('user wallet') }}</h4>
                    </div>
                
                    {{-- <div class="card-body">
                        <div class="filter-container">
                            <form method="GET" action="{{ url('admin/users/' . $user->id ) }}" class="filter-form">
                                <input type="hidden" name="tab" value="salary">
                                
                                <div class="filter-content">
                                    <!-- Month Selector -->
                                    <div class="filter-group">
                                        <div class="form-floating">
                                            <select name="month" id="month" class="form-select">
                                                <option value="">All Months</option>
                                                @for($m = 1; $m <= 12; $m++)
                                                    <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>
                                            <label for="month" class="form-label">{{ __('Month') }}</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Year Selector -->
                                    <div class="filter-group">
                                        <div class="form-floating">
                                            <select name="year" id="year" class="form-select">
                                                <option value="">All Years</option>
                                                @for($y = now()->year; $y >= 2020; $y--)
                                                    <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                                        {{ $y }}
                                                    </option>
                                                @endfor
                                            </select>
                                            <label for="year" class="form-label">{{ __('Year') }}</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="filter-actions">
                                        <button type="submit" class="btn btn-filter">
                                            <i class="fas fa-filter"></i>
                                            <span>{{ __('Apply') }}</span>
                                        </button>
                                        
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>      --}}

                   <form method="GET" action="{{ url('admin/users/' . $user->id) }}" class="form-horizontal" pjax-container="">
                      <input type="hidden" name="tab" value="salary">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="box-body">
                                    <div class="fields-group">

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">السنة</label>
                                            <div class="col-sm-8">
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-pencil"></i>
                                                    </div>
                                                    <input type="text" class="form-control year" placeholder="السنة" name="year" value="{{ request('year') }}" style="text-align: right;">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">الشهر</label>
                                            <div class="col-sm-8">
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-pencil"></i>
                                                    </div>
                                                    <input type="text" class="form-control month" placeholder="الشهر" name="month" value="{{ request('month') }}" style="text-align: right;">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                            <!-- /.box-body -->
                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="col-md-2"></div>
                                        <div class="col-md-8">
                                            <div class="btn-group pull-left">
                                                <button class="btn btn-info submit btn-sm">
                                                    <i class="fa fa-search"></i>&nbsp;&nbsp;بحث
                                                </button>
                                            </div>
                                            <div class="btn-group pull-left" style="margin-left: 10px;">
                                                <a href="{{ url('admin/users/' . $user->id. '?'.'tab=salary') }}" class="btn btn-default btn-sm">
                                                    <i class="fa fa-undo"></i>&nbsp;&nbsp;تفريغ
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                     </form>

                            <div class="table-responsive">
                                <div class="box-body ">
                                    <table class="data-table" id="vip">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ __('agency') }}</th>
                                                <th>{{ __('salary') }}</th>
                                                <th>{{ __('expenses') }}</th>
                                                <th>{{ __('net salary') }}</th>
                                                <th>{{ __('days') }}</th>
                                                <th>{{ __('hours') }}</th>
                                                <th>{{ __('diamonds') }}</th>
                                                <th>{{ __('date') }}</th>

                                            </tr>
                                        </thead>
                                        @if($salaries && $salaries->count())
                                            <tbody style="color: rgb(208, 115, 43);">
                                                @foreach($salaries as $index => $salary)

                                                        @php
                                                            $agency = $salary->agency;
                                                            $name = $agency->name ?? '';

                                                            $path = @$agency->img;
                                                            $defaultImage = asset("images/icon-agency.jpg");
                                                            $url = getImagePath($path) ?? $defaultImage;

                                                            if (!isImageExists($url)) {
                                                                $url = $defaultImage;
                                                            }

                                                            $image = handleShowImageWithTypes($user->id, $url, 40, 40);
                                                            $profileUrl = route('admin.agency.profile', ['id' => @$agency->id ?? 0]);
                                                        @endphp

                                                        <tr>
                                                            <td>{{ $index + 1 + (($salaries->currentPage() - 1) * $salaries->perPage()) }}</td>
                                                            <td>
                                                                <a href="{{ $profileUrl }}" style="text-decoration: none; color: inherit;">
                                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                                        {!! $image !!}
                                                                        <div style="display: flex; flex-direction: column;">
                                                                            <span style="text-decoration: underline; cursor: pointer;">{{ $name }}</span>
                                                                            <span style="font-size: smaller;">ID: {{ @$agency->id ?? 0 }}</span>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </td>
                                                   

                                                        <td>{{$salary->sallary}}</td>
                                                        <td>{{ $salary->cut_amount}}</td>
                                                        <td>{{ $salary->sallary - $salary->cut_amount }}</td>
                                                        <td>{{ $salary->achieved_days }}</td>
                                                        <td>{{ $salary->achieved_hours }}</td>
                                                        <td>{{ $salary->achieved_diamond }}</td>
                                                        <td>{{ $salary->month .'/'. $salary->year }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        @endif
                                    </table>

                                    @if($salaries)
                                            <div class="pagination-container">
                                                {{ $salaries->appends([
                                                    'pack_page' => $packs?->currentPage(),
                                                    'vip_page' => $userVips?->currentPage(),

                                                ])->links('vendor.pagination.bootstrap-4') }}
                                            </div>
                                        @endif
                                </div>
                            </div>
                </div>
            </div>








    </div>


    <div class="modal fade" id="Add_model" tabindex="-1" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg mt-6" role="document">
            <div class="modal-content border-0">
                <div class="modal-content position-relative">
                    <div class="position-absolute top-0 end-0 mt-2 me-2 z-index-1">
                        <button class="btn-close btn btn-sm btn-circle d-flex flex-center transition-base"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.pack.free') }}" method="POST" id="add_form">
                        @csrf
                        <div class="modal-body p-0">
                            <div class="rounded-top-lg py-3 ps-4 pe-6 bg-light">
                                <h4 class="mb-1" id="modalExampleDemoLabel"> {{ __('dashboard.free') }}</h4>
                            </div>
                            <div class="p-4">

                                <div class="row" style="justify-content:space-evenly">

                                    <input type="hidden" name=id class="item_id">
                                    <div class="mb-3 col-md-12">
                                        <label for="type" class="form-label">{{ __('type') }}</label>
                                        <select name="type" id="type" class="form-select">
                                            <option value="0">{{ __('dashboard.raise') }}</option>
                                            <option value="1">{{ __('dashboard.lower') }}</option>
                                        </select>
                                    </div>

                                    <!-- Days Input -->
                                    <div class="mb-3 col-md-6">
                                        <label for="days" class="form-label">{{ __('days') }}</label>
                                        <input type="number" class="form-control" id="days" name="days" placeholder="{{ __('days') }}">
                                    </div>

                                    <!-- Use Num Input -->
                                    <div class="mb-3 col-md-6">
                                        <label for="use_num" class="form-label">{{ __('num') }}</label>
                                        <input type="number" class="form-control" id="use_num" name="use_num" placeholder="{{ __('num') }}">
                                    </div>

                                </div>

                            </div>
                        </div>
                        <div class="modal-footer mt-3">
                            <button class="btn btn-secondary" type="button"
                                data-bs-dismiss="modal">{{ __('cancel') }} </button>
                            <button class="btn btn-primary add_country" type="submit">{{ __('save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- jQuery أولاً -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const urlParams = new URLSearchParams(window.location.search);
            const selectedTab = urlParams.get('tab') || 'packs';

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

    $(document).on('click', '.edit_item_model_btn', function () {
        let itemId = $(this).data('id');

        // Clear the form
        $('#add_form')[0].reset();

        // Set the hidden ID field
        $('.item_id').val(itemId);

        // Open the modal
        $('#Add_model').modal('show');
    });


   $(document).on('click', '.delete-btn', function () {
    let itemId = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: '/admin/delete-pack/' + itemId,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.fire('Deleted!', response.message, 'success').then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'An error occurred.'
                    });
                }
            });
        }
    });
});

   $(document).on('click', '.delete-vip-btn', function () {
    let itemId = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: '/admin/delete-user-vip/' + itemId,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.fire('Deleted!', response.message, 'success').then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'An error occurred.'
                    });
                }
            });
        }
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

