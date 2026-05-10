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
            --text-primary-color: {{ config('themes.textPrimaryColor') }};
            --text-secondary-color: {{ config('themes.textSecondaryColor') }};
            --box-background-color: {{ config('themes.boxBackgroundColor') }};
            --table-background-color: {{ config('themes.tableBackGroundColor') }};
            --background-image: {{ config('themes.backgroundImage') }};
            --brand_background-image: url({{ getImagePath(config('themes.brandBackgroundImage')) }});
            --second-alpha: {{ adjustColor(config('themes.boxBackgroundColor'), -30, -30, -30) }}55;
            --primary-hover-alpha: {{ config('themes.primaryColor') }}33;
            --scroll-second-color: {{ config('themes.boxBackgroundColor') }}cc;
            --scroll-first-color: {{ adjustColor(config('themes.primaryColor'), 40, 40, 40) }}33;
            --inverse-color: {{ getLighterColor(config('themes.primaryColor')) }};
            --inverse-box-color: {{ adjustTextColor(config('themes.boxBackgroundColor')) }};
            --success-button: linear-gradient(90deg, {{ adjustColor(config('themes.primaryColor')) }} 0%, {{ config('themes.primaryColor') }} 100%);
            --primary-button: linear-gradient(90deg, {{ adjustColor(config('themes.primaryColor')) }} 0%, {{ config('themes.primaryColor') }} 100%);
        }

        .agency-profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .agency-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .agency-avatar .logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .agency-info { flex: 1; }

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

        .meta-label { font-weight: 600; color: #7f8c8d; }
        .meta-value { color: #34495e; }
        .meta-uuid { color: #95a5a6; font-size: 0.9em; }

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

        .btn-back:hover { background: #d6e0e3; color: #34495e; }

        /* Level Section */
        .level-section {
            display: block !important;
        }

        .level-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .level-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .level-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
        }

        .level-details h3 {
            margin: 0;
            font-size: 20px;
            color: #2c3e50;
        }

        .level-details span {
            font-size: 13px;
            color: #7f8c8d;
        }

        .level-progress {
            margin-top: 10px;
        }

        .progress-bar-container {
            background: #ecf0f1;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), #3498db);
            border-radius: 10px;
            transition: width 0.5s ease;
            min-width: 2%;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .level-stats {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .level-stat-card {
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            flex: 1;
        }

        .level-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #3498db;
        }

        .level-stat-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Tabs */
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
            text-decoration: none;
            color: inherit;
        }

        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .tab-btn:hover:not(.active) { color: #34495e; }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Cards & Tables */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .card-header h4 { margin: 0; font-size: 18px; color: #2c3e50; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--secondary-color);
        }

        .data-table th {
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

        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover { background: #f8f9fa; }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-info strong { font-size: 14px; }
        .user-info small { font-size: 11px; color: #95a5a6; }

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

        .empty-table i { font-size: 40px; margin-bottom: 10px; }
        .empty-table p { margin: 0; font-size: 14px; }

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

        /* Filter */
        .filter-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 24px;
        }

        /* Action Buttons */
        .btn-kick {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-kick:hover { background: #c0392b; }

        .btn-toggle-admin {
            background: #3498db;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-toggle-admin:hover { background: #2980b9; }

        .btn-toggle-admin.is-admin {
            background: #f39c12;
        }

        .btn-toggle-admin.is-admin:hover { background: #e67e22; }

        .action-btns {
            display: flex;
            gap: 5px;
        }

        .btn-toggle-admin {
            min-width: 130px;
            text-align: center;
        }

        .btn-kick {
            min-width: 70px;
            text-align: center;
        }

        /* Stats Row */
        .stats-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            text-align: center;
            min-width: 150px;
            flex: 1;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 6px;
            color: white;
            font-size: 20px;
        }

        .stat-icon.bg-blue { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
        .stat-icon.bg-green { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); }
        .stat-icon.bg-purple { background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); }
        .stat-icon.bg-orange { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }

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

        #tab-loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            background: var(--primary-color);
            color: var(--text-primary-color);
            z-index: 9999;
            padding: 30px 40px;
            border-radius: 10px;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .stats-row { flex-direction: column; }
            .level-stats { flex-direction: column; }
            .agency-meta { justify-content: center; }
            .action-btns { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="agency-profile-container" id="pjax-container">
    {{-- Header Section --}}
    <div class="agency-header">
        <div class="agency-avatar">
            <img src="{{ getImagePath(@$family->image) }}" alt="Family Logo" class="logo-img">
        </div>
        <div class="agency-info">
            <h1 class="agency-name">{{ @$family->name ?? '' }}</h1>
            <div class="agency-meta">
                <div class="meta-item">
                    <span class="meta-label">{{ __('ID') }}:</span>
                    <span class="meta-value">{{ $family->id }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">{{ __('Owner') }}:</span>
                    <span class="meta-value">{{ @$family->owner->name ?? '' }}</span>
                    <span class="meta-uuid">({{ @$family->owner->uuid ?? '' }})</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">{{ __('created_at') }}:</span>
                    <span class="meta-value">{{ $family->created_at }}</span>
                </div>
            </div>
        </div>
        <a href="{{ url('admin/families') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> {{ __('Go Back') }}
        </a>
    </div>

    {{-- Level Section --}}
    <div class="agency-header level-section">
        <div class="level-header">
            <div class="level-info">
                @if(!empty($familyLevel['level_img']))
                    <img src="{{ getImagePath($familyLevel['level_img']) }}" alt="Level" class="level-img">
                @endif
                <div class="level-details">
                    <h3>{{ $familyLevel['level_name'] ?: __('No Level') }}</h3>
                    <span>{{ __('Total Diamonds') }}: {{ number_format($familyLevel['family_exp']) }}</span>
                </div>
            </div>
            @if(!$familyLevel['is_last_level'])
                <div style="text-align: right;">
                    <strong>{{ __('Next') }}: {{ $familyLevel['next_name'] }}</strong><br>
                    <small style="color: #7f8c8d;">{{ number_format($familyLevel['rem']) }} {{ __('remaining') }}</small>
                </div>
            @else
                <span class="badge bg-success" style="padding: 8px 15px; font-size: 14px;">{{ __('Max Level') }}</span>
            @endif
        </div>

        <div class="level-progress">
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: {{ round($familyLevel['per'] * 100) }}%"></div>
            </div>
            <div class="progress-text">
                <span>{{ number_format($familyLevel['level_exp']) }}</span>
                <span>{{ round($familyLevel['per'] * 100) }}%</span>
                <span>{{ number_format($familyLevel['next_exp']) }}</span>
            </div>
        </div>

        <div class="level-stats">
            <div class="level-stat-card">
                <div class="level-stat-value">{{ $family->members_count }}</div>
                <div class="level-stat-label">{{ __('members') }}</div>
            </div>
            <div class="level-stat-card">
                <div class="level-stat-value">{{ $family->admins_num }}</div>
                <div class="level-stat-label">{{ __('admin.admin') }}</div>
            </div>
            <div class="level-stat-card">
                <div class="level-stat-value">{{ $family->num }}</div>
                <div class="level-stat-label">{{ __('Max Members') }}</div>
            </div>
            <div class="level-stat-card">
                <div class="level-stat-value">{{ $family->num_admins }}</div>
                <div class="level-stat-label">{{ __('Max Admins') }}</div>
            </div>
        </div>
    </div>

    @php
        $activeTab = request('tab', 'members');
    @endphp

    {{-- Navigation Tabs --}}
    <div class="agency-tabs">
        <a href="?tab=members" class="tab-btn {{ $activeTab == 'members' ? 'active' : '' }}" data-target="members-tab">
            <i class="fas fa-users"></i> {{ __('members') }}
        </a>
        <a href="?tab=targets" class="tab-btn {{ $activeTab == 'targets' ? 'active' : '' }}" data-target="targets-tab">
            <i class="fas fa-bullseye"></i> {{ __('Targets') }}
        </a>
    </div>

    <div id="tab-loading">{{ __('Loading...') }}</div>

    {{-- Members Tab --}}
    <div class="tab-content {{ $activeTab == 'members' ? 'active' : '' }}" id="members-tab">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title text-left"><i class="fas fa-users"></i> {{ __('members') }}</h4>
            </div>

            {{-- Filter --}}
            <div class="box-header with-border" style="padding: 15px;">
                <form action="{{ url('admin/families/' . $family->id) }}" method="get" class="form-horizontal">
                    <input type="hidden" name="tab" value="members">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ __('type') }}</label>
                                @php $filterType = is_array(request('type')) ? null : request('type'); @endphp
                                <select class="form-control" name="type" style="width: 100%;">
                                    <option value="">{{ __('select') }}</option>
                                    <option value="2" {{ $filterType === '2' ? 'selected' : '' }}>{{ __('Owner') }}</option>
                                    <option value="1" {{ $filterType === '1' ? 'selected' : '' }}>{{ __('admin.admin') }}</option>
                                    <option value="0" {{ $filterType === '0' ? 'selected' : '' }}>{{ __('members') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4" style="display: flex; align-items: flex-end; gap: 10px; padding-bottom: 15px;">
                            <button class="btn btn-info btn-sm">
                                <i class="fa fa-search"></i> {{ __('Search') }}
                            </button>
                            <a href="{{ url('admin/families/' . $family->id . '?tab=members') }}" class="btn btn-default btn-sm">
                                <i class="fa fa-undo"></i> {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <div class="box-body">
                    <table class="data-table table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('user') }}</th>
                            <th>{{ __('type') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        @if($familyMembers && $familyMembers->count())
                            <tbody>
                            @foreach($familyMembers as $index => $familyMember)
                                <tr id="member-row-{{ $familyMember->id }}">
                                    <td>{{ $familyMembers->firstItem() + $index }}</td>
                                    <td class="user-cell">
                                        <div class="user-avatar">
                                            <img src="{{ getImagePath(@$familyMember->user->profile->avatar) }}"
                                                 alt="{{ @$familyMember->user->name }}"
                                                 style="width:35px;height:35px;border-radius:50%;object-fit:cover;">
                                        </div>
                                        <div class="user-info">
                                            <strong>{{ @$familyMember->user->name }}</strong><br>
                                            <small>UID: {{ @$familyMember->user->uuid }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($familyMember->user_type == 2)
                                            <span class="badge bg-primary">{{ __('Owner') }}</span>
                                        @elseif($familyMember->user_type == 1)
                                            <span class="badge bg-secondary">{{ __('admin.admin') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('members') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($familyMember->user_type != 2)
                                            <div class="action-btns">
                                                <button class="btn-toggle-admin {{ $familyMember->user_type == 1 ? 'is-admin' : '' }}"
                                                        data-id="{{ $familyMember->id }}"
                                                        title="{{ $familyMember->user_type == 1 ? __('Remove Admin') : __('Make Admin') }}">
                                                    <i class="fas {{ $familyMember->user_type == 1 ? 'fa-user-minus' : 'fa-user-shield' }}"></i>
                                                    {{ $familyMember->user_type == 1 ? __('Remove Admin') : __('Make Admin') }}
                                                </button>
                                                <button class="btn-kick" data-id="{{ $familyMember->id }}"
                                                        title="{{ __('Kick Member') }}">
                                                    <i class="fas fa-times"></i> {{ __('Kick') }}
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        @else
                            <tbody>
                            <tr>
                                <td colspan="4" class="text-center text-muted">{{ __('No members found.') }}</td>
                            </tr>
                            </tbody>
                        @endif
                    </table>
                </div>
            </div>

            <div class="pagination-wrapper">
                {{ $familyMembers->appends(['tab' => 'members', 'type' => $filterType])->links('vendor.pagination.default') }}
            </div>
        </div>
    </div>

    {{-- Targets Tab --}}
    <div class="tab-content {{ $activeTab == 'targets' ? 'active' : '' }}" id="targets-tab">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-bullseye"></i> {{ __('Targets') }}</h4>
                <span class="badge bg-primary" style="padding: 5px 12px;">{{ $memberTargets->total() }}</span>
            </div>

            {{-- Target Filter --}}
            <div style="padding: 15px;">
                <form method="GET" action="{{ url('admin/families/' . $family->id) }}">
                    <input type="hidden" name="tab" value="targets">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="month">{{ __('Month') }}</label>
                                <select name="month" id="month" class="form-control">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="year">{{ __('Year') }}</label>
                                <select name="year" id="year" class="form-control">
                                    @for($y = now()->year; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" style="display: flex; align-items: flex-end; gap: 10px; padding-bottom: 15px;">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter"></i> {{ __('Apply') }}
                            </button>
                            <a href="{{ url('admin/families/' . $family->id . '?tab=targets') }}" class="btn btn-default btn-sm">
                                <i class="fas fa-times"></i> {{ __('Reset') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table table table-bordered">
                    <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="25%">{{ __('User') }}</th>
                        <th width="12%">{{ __('Diamonds') }}</th>
                        <th width="18%">{{ __('Remaining') }}</th>
                        <th width="12%">{{ __('Days') }}</th>
                        <th width="12%">{{ __('Hours') }}</th>
                        <th width="16%">{{ __('Supporters') }}</th>
                    </tr>
                    </thead>
                    @if($memberTargets && $memberTargets->count())
                        <tbody>
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

                                $giftLogs = \App\Models\GiftLog::where('receiver_family_id', $family->id)
                                    ->where('receiver_id', $memberTarget->id)
                                    ->whereHas('sender')
                                    ->with('sender.profile')
                                    ->whereYear('created_at', $year)
                                    ->whereMonth('created_at', $month)
                                    ->selectRaw("sum(giftPrice) as exp, sender_id")
                                    ->groupBy('sender_id')
                                    ->orderByRaw("exp desc")
                                    ->limit(3)
                                    ->get()
                                    ->reject(fn($q) => $q->exp == 0);

                                $target = $memberTarget->targets->first();
                            @endphp
                            <tr>
                                <td>{{ $memberTargets->firstItem() + $index }}</td>
                                <td>
                                    <div class="user-info-cell" style="display:flex;align-items:center;gap:10px;">
                                        <img src="{{ $avatarUrl }}" class="user-avatar" alt="{{ $name }}">
                                        <div>
                                            <div><strong>{{ $name }}</strong></div>
                                            <div style="font-size:11px;color:#95a5a6;">{{ $uid }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="number-badge">{{ $target->user_diamonds ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="number-badge warning">{{ $target->next_diamond ?? 0 }}</span>
                                </td>
                                <td>{{ ($target->user_days ?? 0) . '/' . ($target->target_days ?? 0) }}</td>
                                <td>{{ ($target->user_hours ?? 0) . '/' . ($target->target_hours ?? 0) }}</td>
                                <td>
                                    <div class="supporters-avatars" style="display:flex;gap:5px;align-items:center;">
                                        @foreach($giftLogs as $supporter)
                                            @php
                                                $sender = $supporter->sender;
                                                $supporterAvatar = $sender->profile->avatar ?? null;
                                                $supporterUrl = getImagePath($supporterAvatar) ?? $defaultImage;
                                                if (!isImageExists($supporterUrl)) {
                                                    $supporterUrl = $defaultImage;
                                                }
                                            @endphp
                                            <img src="{{ $supporterUrl }}"
                                                 style="width:35px;height:35px;border-radius:50%;object-fit:cover;"
                                                 title="{{ $sender->name ?? '' }}" alt="Supporter">
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    @else
                        <tbody>
                        <tr>
                            <td colspan="7">
                                <div class="empty-table">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <p>{{ __('No target data available') }}</p>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    @endif
                </table>
            </div>

            @if($memberTargets && $memberTargets->count())
                <div class="pagination-wrapper">
                    {{ $memberTargets->appends(['tab' => 'targets', 'month' => $month, 'year' => $year])->links('vendor.pagination.default') }}
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const selectedTab = urlParams.get('tab') || 'members';
        const allTabs = document.querySelectorAll('.tab-btn');

        allTabs.forEach(tab => {
            const target = tab.getAttribute('data-target');
            const content = document.getElementById(target);
            if (!content) return;

            if (target === selectedTab + '-tab') {
                tab.classList.add('active');
                content.style.display = 'block';
                content.classList.add('active');
            } else {
                tab.classList.remove('active');
                content.style.display = 'none';
                content.classList.remove('active');
            }

            tab.addEventListener('click', function (e) {
                e.preventDefault();
                allTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                allTabs.forEach(t => {
                    const panelId = t.getAttribute('data-target');
                    const panel = document.getElementById(panelId);
                    if (!panel) return;
                    if (panelId === target) { panel.style.display = 'block'; panel.classList.add('active'); }
                    else { panel.style.display = 'none'; panel.classList.remove('active'); }
                });
                const url = new URL(window.location.href);
                const tabParam = new URLSearchParams(this.getAttribute('href').replace('?', ''));
                url.searchParams.set('tab', tabParam.get('tab'));
                window.history.replaceState({}, '', url.toString());
            });
        });
    });

    function showLoader() {
        Swal.fire({
            title: '{{ __("Loading...") }}',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });
    }

    function showSuccess(message, callback) {
        Swal.fire({
            type: 'success',
            title: message,
            confirmButtonText: 'OK'
        }).then(() => { if (callback) callback(); });
    }

    function showError(message) {
        Swal.fire({ type: 'error', title: message, confirmButtonText: 'OK' });
    }

    function confirmAction(message, onConfirm) {
        Swal.fire({
            title: message,
            type: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ __("Yes") }}',
            cancelButtonText: '{{ __("Cancel") }}'
        }).then(result => {
            if (result.value) onConfirm();
        });
    }

    function bindActionButtons() {
        // Kick member
        $(document).off('click', '.btn-kick').on('click', '.btn-kick', function () {
            const id = $(this).data('id');
            confirmAction('{{ __("Are you sure you want to remove this member?") }}', () => {
                showLoader();
                $.post(`/admin/families/kick/${id}`, {
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    Swal.close();
                    if (response.status) {
                        showSuccess(response.message, () => { $.pjax.reload('#pjax-container'); });
                    } else {
                        showError(response.message);
                    }
                }).fail(function (xhr) {
                    Swal.close();
                    const res = xhr.responseJSON;
                    showError(res?.message ?? '{{ __("Operation failed") }}');
                });
            });
        });

        // Toggle admin
        $(document).off('click', '.btn-toggle-admin').on('click', '.btn-toggle-admin', function () {
            const id = $(this).data('id');
            const isAdmin = $(this).hasClass('is-admin');
            const message = isAdmin
                ? '{{ __("Are you sure you want to remove admin privileges?") }}'
                : '{{ __("Are you sure you want to make this user an admin?") }}';

            confirmAction(message, () => {
                showLoader();
                $.post(`/admin/families/toggle-admin/${id}`, {
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    Swal.close();
                    if (response.status) {
                        showSuccess(response.message, () => { $.pjax.reload('#pjax-container'); });
                    } else {
                        showError(response.message);
                    }
                }).fail(function (xhr) {
                    Swal.close();
                    const res = xhr.responseJSON;
                    showError(res?.message ?? '{{ __("Operation failed") }}');
                });
            });
        });
    }

    $(document).ready(function () {
        bindActionButtons();
    });
</script>

</body>
</html>
