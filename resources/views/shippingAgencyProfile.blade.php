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
    /* display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 15px; */
    align-content: center;
    width: 14%;
    margin: auto;
    border: 2px solid;
    height: 63px;
    padding: 8px;
    border-radius: 31px;
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
        width: 49%;
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
        width: 49%;
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
                <img src="{{ $agency->display_image }}" alt="Agency Logo" class="logo-img">
            </div>
            <div class="agency-info">
                <h1 class="agency-name">{{ @$agency?->name ?? ''}}</h1>
                <div class="agency-meta">
                    <div class="meta-item">
                        <span class="meta-label">{{__("ID")}}:</span>
                        <span class="meta-value">{{ $agency->id }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{__("Phone")}}:</span>
                        <span class="meta-value">{{ @$agency->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{__("Owner")}}:</span>
                        <span class="meta-value">{{ @$agency?->owner?->name ?? 'N/A' }}</span>
                        <span class="meta-uuid">({{ @$agency?->owner?->uuid ?? 'N/A' }})</span>
                    </div>
                </div>
                <div class="agency-stats">
                    <!-- <div class="stat-card">
                        <div class="stat-value">{{ number_format(@$agency->coins) ?? 0 }}</div>
                        <div class="stat-label">{{__("coins")}}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format(@$agency->salary) ?? 0 }}</div>
                        <div class="stat-label">{{__("salary")}}</div>
                    </div> -->
                </div>
            </div>
            <button class="btn-back" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i> {{__("Go Back")}}
            </button>
        </div>
    
        <!-- Notice Section -->
        @if(@$agency->notice)
        <div class="notice-section">
            <div class="notice-header">
                <i class="fas fa-info-circle"></i>
                <span>{{__("Notice")}}</span>
            </div>
            <div class="notice-content">
                {{ @$agency->notice }}
            </div>
        </div>
        @endif
    
        <!-- Stars & Admins Section -->
        <div class="top-performers-section">
            <!-- Stars Section -->
            <div class="performers-card">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-star"></i>
                        {{ __('Agency Coins') }}
                    </h2>
                </div>
                
                    <div class="avatar-grid">
                      
                            
                                    {{ @$agency->coins }}
                               
                    </div>
              
            </div>
    

        </div>

        <div class="card-target-filter-phone ">

        <form method="GET" action="{{ url('admin/shipping-agencies/profile/' . $agency->id ) }}" class="filter-form">
                    <div class="row">
                        <input type="hidden" name="tab" value="charge">
                        <div class="col-sm-12 col-md-7 ">
                            <div class="form-group">
                                <!-- <label for="month">{{ __('Month') }}</label> -->
                                    <select name="filter_by" id="filter_by" class="form-control">
                                        <option value="">{{ __('Select type') }}</option>
                                        <option value="user" {{ request('filter_by') == 'user' ? 'selected' : '' }}>{{ __('User') }}</option>
                                        <option value="agency" {{ request('filter_by') == 'agency' ? 'selected' : '' }}>{{ __('Agency') }}</option>
                                    </select>

                            </div>
                        </div>
                        
                        
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> {{ __('Apply') }}
                            </button>
                            @if(request()->has('month') || request()->has('year'))
                            <a href="{{ url('admin/agencies/profile/' . $agency->id) }}" class="btn btn-outline-secondary ml-2" title="Reset filters">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            </div>
        @php
                    $activeTab = request('tab', 'charges'); 
        @endphp
        <!-- Navigation Tabs -->
        <div class="agency-tabs">
            <a href="?tab=charge" class="tab-btn" data-target="charges-tab">{{ __('Charges') }}</a>
            <a href="?tab=resived" class="tab-btn" data-target="resived-tab">{{ __('receiver') }}</a>
         
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

           
    
              
    

            <!-- Charges Section -->
            @if($charges && $charges->count())
            <div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('receiver') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($charges as $index => $charge)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    
                    <td>
                        @if($charge->resiver instanceof \App\Models\User)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $charge->resiver->profile->avatar ?? asset('/default-user.png') }}" alt="user" width="40" height="40" style="border-radius: 50%;">
                                <div>
                                    <strong>{{ $charge->resiver->name }}</strong><br>
                                    <small>ID: {{ $charge->resiver->id }}</small>
                                </div>
                            </div>
                        @elseif($charge->agency instanceof \App\Models\Agency)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $charge->agency->logo_url ?? asset('/default-agency.png') }}" alt="agency" width="40" height="40" style="border-radius: 50%;">
                                <div>
                                    <strong>{{ $charge->agency->name }}</strong><br>
                                    <small>ID: {{ $charge->agency->id }}</small>
                                </div>
                            </div>
                        @else
                            -
                        @endif
                    </td>

                    <td>{{ $charge->amount ?? '-' }}</td>
                    <td>{{ $charge->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $charges->withQueryString()->links() }}
</div>

@else
<!-- <p class="text-center text-muted">{{ __('No charges found.') }}</p> -->
@endif
       
    @if($resiveds && $resiveds->count())
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Sender') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resiveds as $index => $res)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td> 
                            
                        @if($res->sender instanceof \App\Models\User)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $res->sender->profile->avatar ?? asset('/default-user.png') }}" alt="user" width="40" height="40" style="border-radius: 50%;">
                                <div>
                                    <strong>{{ $res->sender->name }}</strong><br>
                                    <small>ID: {{ $res->sender->id }}</small>
                                </div>
                            </div>
                        @elseif($res->sender instanceof \App\Models\Agency)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $res->sender->logo_url ?? asset('/default-agency.png') }}" alt="agency" width="40" height="40" style="border-radius: 50%;">
                                <div>
                                    <strong>{{ $res->sender->name }}</strong><br>
                                    <small>ID: {{ $res->sender->id }}</small>
                                </div>
                            </div>
                        @else
                            -
                        @endif
                    
                    </td>
                        <td>{{ $res->amount ?? '-' }}</td>
                        <td>{{ $res->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $resiveds->withQueryString()->links() }}
    </div>
@else
    <!-- <p class="text-center text-muted">{{ __('No received charges found.') }}</p> -->
@endif



        
    </div>
 
<!-- jQuery أولاً -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>


    <script>
       document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const selectedTab = (urlParams.get('tab') || 'charges') + '-tab';

    const allTabs = document.querySelectorAll('.tab-btn');
    const allTabContents = document.querySelectorAll('[id$="-tab"]');

    let targetElement = null;

    allTabs.forEach(tab => {
        const target = tab.getAttribute('data-target');
        const content = document.getElementById(target);

        if (target === selectedTab) {
            tab.classList.add('active');
            content.style.display = 'block';
            targetElement = content;
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
            targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 400);
    }
});






</script>

