@php use Carbon\Carbon; @endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    @include('css.room_profile')
</head>
<body>

<div class="agency-profile-container">
    <div class="agency-header">
        <div class="agency-avatar">
            <img src="{{getImagePath( @$room->room_cover ) ?? asset("images/room.jpg") }}" alt="Room Cover"
                 class="cover-img">
        </div>
        <div class="agency-info">
            <h1 class="agency-name">{{ @$room->room_name ?? ''}}</h1>
            <div class="agency-meta">
                <div class="meta-item">
                    <span class="meta-label">{{ __("Room ID") }}:</span>
                    <span class="meta-value">{{ @$room->id }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">{{ __("Room UID") }}:</span>
                    <span class="meta-value">{{ @$room->uid }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">{{__("Room Type")}}:</span>
                    <span class="meta-value">{{ @$room->roomCategory->name ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="agency-stats">
                <div class="agency-meta">
                    <div class="meta-item">
                        <span class="meta-label">{{ __('Visitors Count') }}:</span>
                        <span class="meta-value">{{ @$room->room_visitors_count }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{__('Current Users')}}:</span>
                        <span class="meta-value">{{ @$room->count_room_socket }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{__('Max Admins')}}:</span>
                        <span class="meta-value">{{ @$room->max_admin }}</span>
                    </div>
                </div>

                <div class="agency-meta">
                    <div class="meta-item">
                        <span class="meta-label">{{ __('Room Owner') }}:</span>
                        <span class="meta-value">
                    <a href="{{ admin_url('users/' . @$room->owner->id) }}">
                        {{ @$room->owner->name }} ({{ @$room->owner->uuid }})
                    </a>
                </span>
                    </div>
                </div>
            </div>

            <div class="agency-meta">
                <div class="meta-item">
                    <span class="meta-label">{{__('Status')}}:</span>
                    {!! getRoomStatusBadge($room->room_status) !!}
                </div>
                <div class="meta-item">
                    <span class="meta-label">{{__('Room Mode')}}:</span>
                    <span class="meta-value">{{ @$room->mode }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">{{__('Features')}}:</span>
                    <span class="meta-value">
                @if($room->is_popular)
                            <span class="badge badge-success">{{ __('Popular') }}</span>
                        @endif
                        @if($room->is_top)
                            <span class="badge badge-primary">{{ __('Top') }}</span>
                        @endif
                        @if($room->is_recommended)
                            <span class="badge badge-info">{{ __('Recommended') }}</span>
                        @endif
                        @if($room->secret_chat)
                            <span class="badge badge-warning">{{ __('Secret Chat') }}</span>
                        @endif
                        @if($room->is_live)
                            <span class="badge badge-danger">{{ __('Live') }}</span>
                        @endif
            </span>
                </div>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-start; gap: 8px; margin-bottom: 8px;">
            <button class="btn-back edit-btn" onclick="openEditModal()">
                <i class="fas fa-edit"></i> {{__("Edit Room")}}
            </button>
            <button class="btn-back" onclick="window.location.href='{{ url('admin/rooms') }}'">
                <i class="fas fa-arrow-left"></i> {{__("Go Back")}}
            </button>
        </div>
    </div>

    @php
        if (!request()->has('tab')) {
            header('Location: ' . url()->current() . '?tab=admins');
            exit();
        }
    @endphp

    <div class="agency-tabs">
        <a href="?tab=admins" class="tab-btn {{ request('tab') == 'admins' ? 'active' : '' }}"
           data-target="admins-tab">{{ __('Room Admins') }}</a>
        <a href="?tab=gifts" class="tab-btn {{ request('tab') == 'gifts' ? 'active' : '' }}"
           data-target="gifts-tab">{{ __('Room Gifts') }}</a>
        <a href="?tab=visitors" class="tab-btn {{ request('tab') == 'visitors' ? 'active' : '' }}"
           data-target="visitors-tab">{{ __('Room Visitors') }}</a>
        <a href="?tab=pk" class="tab-btn {{ request('tab') == 'pk' ? 'active' : '' }}"
           data-target="pk-tab">{{ __('Pk') }}</a>
        <a href="?tab=boxes" class="tab-btn {{ request('tab') == 'boxes' ? 'active' : '' }}"
           data-target="boxes-tab">{{ __('Boxes') }}</a>
    </div>
</div>

<div class="modal fade" id="editRoomModal" tabindex="-1" role="dialog" aria-labelledby="editRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoomModalLabel">{{ __('Edit Room') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editRoomForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('Room Name') }}</label>
                        <input type="text" class="form-control" name="room_name" value="{{ $room->room_name }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Room Type') }}</label>
                        <select class="form-control" name="room_type">
                            <option value="">{{ __('Select Type') }}</option>
                            @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}" {{ $room->room_type == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Max Admins') }}</label>
                        <input type="number" class="form-control" name="max_admin" value="{{ $room->max_admin }}">
                    </div>

                    <div class="form-group">
                        <label>{{ __('Features') }}</label>
                        <div class="custom-controls">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_popular" name="is_popular" {{ $room->is_popular ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_popular">{{ __('Popular') }}</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_top" name="is_top" {{ $room->is_top ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_top">{{ __('Top') }}</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_recommended" name="is_recommended" {{ $room->is_recommended ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_recommended">{{ __('Recommended') }}</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="secret_chat" name="secret_chat" {{ $room->secret_chat ? 'checked' : '' }}>
                                <label class="custom-control-label" for="secret_chat">{{ __('Secret Chat') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="tab-loading" style="
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
        ">
    {{ __('Loading...') }}
</div>

<div class="tab-content {{ request('tab') == 'admins' ? 'active' : '' }}" id="admins-tab">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Room Administrators') }}</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Admin') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>

                @forelse($admins as $index => $admin)
                    @php
                        $path = @$admin->profile?->avatar;
                        $defaultImage = asset("images/businessman-icon.jpg");
                        $url = getImagePath($path) ?? $defaultImage;

                        if (!isImageExists($url)) {
                            $url = $defaultImage;
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <a href="{{ admin_url('users/' . $admin->id) }}" target="_blank"
                               style="display: inline-flex; align-items: center; text-decoration: none;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="{{ $url }}"
                                         width="40" height="40"
                                         style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                    <div>
                                        <div>{{ $admin->name }}</div>
                                        <small>ID: {{ $admin->id }}</small><br>
                                        <small>UID: {{ $admin->uuid }}</small>
                                    </div>
                                </div>
                            </a>
                        </td>
                        <td>
                            @if(Admin::user()->can('actions-switch-rooms') || Admin::user()->can('*'))
                                <button class="btn btn-danger btn-sm remove-admin"
                                        data-room-id="{{ $room->id }}"
                                        data-admin-id="{{ $admin->id }}">
                                        {{ __('Remove') }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="p-3">
                                <i class="fas fa-info-circle text-muted"></i>
                                {{ __('No data available') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="tab-content {{ request('tab') == 'gifts' ? 'active' : '' }}" id="gifts-tab">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Room Gift Reports') }}</h4>
        </div>

        <div class="box-body p-3">
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" class="form-horizontal gift-log-form" method="GET">
                        <input type="hidden" name="tab" value="gifts">

                        <div class="container-fluid">
                            <div class="row g-2 align-items-end justify-content-between">
                                <div class="col-md-2">
                                    <label for="sender-select"
                                           class="form-label d-flex align-items-center justify-content-end fw-bold">
                                        <span>{{ __('Sender') }}</span>
                                    </label>
                                    <select class="form-control" name="sender_id" id="sender-select"></select>
                                </div>
                                <div class="col-md-2">
                                    <label for="receiver-select"
                                           class="form-label d-flex align-items-center justify-content-end fw-bold">
                                        <span>{{ __('Receiver') }}</span>
                                    </label>
                                    <select class="form-control" name="receiver_id" id="receiver-select"></select>
                                </div>
                                <div class="col-md-2">
                                    <label for="date-from"
                                           class="form-label d-flex align-items-center justify-content-end fw-bold">
                                        <span>{{ __('From Date') }}</span>
                                        <i class="fa fa-calendar ms-1"></i>
                                    </label>
                                    <input type="date" class="form-control" name="start_at" id="date-from"
                                           value="{{ request('start_at') }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="date-to"
                                           class="form-label d-flex align-items-center justify-content-end fw-bold">
                                        <span>{{ __('To Date') }}</span>
                                        <i class="fa fa-calendar ms-1"></i>
                                    </label>
                                    <input type="date" class="form-control" name="end_at" id="date-to"
                                           value="{{ request('end_at') }}">
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-info d-flex align-items-center gap-2">
                                            <i class="fa fa-search"></i> {{__('Search')}}
                                        </button>
                                        <a href="?tab=gifts"
                                           class="btn btn-default d-flex align-items-center gap-2">
                                            <i class="fa fa-undo"></i> {{__('Reset')}}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="diamond-summary-container">
                <div class="diamond-summary-box">
                    <div class="diamond-title">
                        {{ __('Total Diamonds') }}
                    </div>
                    <div class="diamond-count">
                        <span>{{ number_format($totalDiamonds) }}</span>
                        <div class="diamond-icon-container">
                            <img src="{{ asset('images/diamond.jpg') }}" alt="Diamond" class="diamond-icon">
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Sender') }}</th>
                        <th>{{ __('Receiver') }}</th>
                        <th>{{ __('Gift') }}</th>
                        <th>{{ __('Quantity') }}</th>
                        <th>{{ __('Price') }}</th>
                        <th>{{ __('Created at') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($gifts as $index => $gift)
                        <tr>
                            <td>{{ $gift->id }}</td>
                            <td>
                                <a href="{{ admin_url('users/' . $gift->sender->id) }}" target="_blank"
                                   class="d-flex align-items-center text-decoration-none">
                                    <img
                                        src="{{ getImagePath($gift->sender->profile->avatar) ?? asset('images/businessman-icon.jpg') }}"
                                        width="40" height="40"
                                        style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                    <div>
                                        <strong style="font-size: 14px;">{{ $gift->sender->name }}</strong><br>
                                        <small class="text-muted">UUID: {{ $gift->sender->uuid }}</small>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <a href="{{ admin_url('users/' . $gift->receiver->id) }}" target="_blank"
                                   class="d-flex align-items-center text-decoration-none">
                                    <img
                                        src="{{ getImagePath($gift->receiver->profile->avatar) ?? asset('images/businessman-icon.jpg') }}"
                                        width="40" height="40"
                                        style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                    <div>
                                        <strong style="font-size: 14px;">{{ $gift->receiver->name }}</strong><br>
                                        <small class="text-muted">UUID: {{ $gift->receiver->uuid }}</small>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <a href="#" class="d-flex align-items-center text-decoration-none">
                                    <img src="{{ getImagePath($gift->gift->giftIcon) }}"
                                         width="30" height="30"
                                         style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                    <span>{{ $gift->gift->giftName }}</span>
                                </a>
                            </td>
                            <td>{{ $gift->giftNum }}</td>
                            <td>{{ $gift->giftPrice }}</td>
                            <td>{{ \Carbon\Carbon::parse($gift->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="p-3">
                                    <i class="fas fa-info-circle text-muted"></i>
                                    {{ __('No data available') }}
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $gifts->appends(request()->all())->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<div class="tab-content {{ request('tab') == 'visitors' ? 'active' : '' }}" id="visitors-tab">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Room Visitors') }}</h4>
            @if(Admin::user()->can('actions-switch-rooms') || Admin::user()->can('*'))
                <button class="btn btn-success float-right" id="add-visitor-btn">
                    {{ __('Add Visitor') }}
                </button>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Visitor') }}</th>
                    <th>{{ __('Mic Position') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Join Time') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>

                @forelse($visitors as $index => $visitor)
                    <tr>
                        <td>{{ ($visitors->currentPage() - 1) * $visitors->perPage() + $loop->iteration }}</td>
                        <td>
                            <a href="{{ admin_url('users/' . $visitor->user->id) }}" target="_blank"
                               style="display: inline-flex; align-items: center; text-decoration: none;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img
                                        src="{{ getImagePath($visitor->user->profile->avatar) ?? asset('images/businessman-icon.jpg') }}"
                                        width="40" height="40"
                                        style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                    <div>
                                        <div>{{ $visitor->user->name }}</div>
                                        <small>ID: {{ $visitor->user->id }}</small><br>
                                        <small>UID: {{ $visitor->user->uuid }}</small>
                                    </div>
                                </div>
                            </a>
                        </td>
                        <td>
                            @if($visitor->mic_position)
                                <span class="badge badge-success">{{ __('Mic') }} #{{ $visitor->mic_position }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('Not on mic') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($visitor->kick_info)
                                <span class="badge badge-danger">
                                    {{ __('Kicked') }} ({{ $visitor->kick_info['remaining'] }} {{ __('min remaining') }})
                                </span>
                            @else
                                <span class="badge badge-success">{{ __('Active') }}</span>
                            @endif
                        </td>
                        <td>{{ Carbon::parse($visitor->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            @if(Admin::user()->can('actions-switch-rooms') || Admin::user()->can('*'))
                                <button class="btn btn-danger btn-sm kick-visitor"
                                        data-room-id="{{ $room->id }}"
                                        data-user-id="{{ $visitor->user_id }}">
                                        {{ __('Kick') }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="p-3">
                                <i class="fas fa-info-circle text-muted"></i>
                                {{ __('No data available') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $visitors->appends(request()->all())->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

<div class="tab-content {{ request('tab') == 'pk' ? 'active' : '' }}" id="pk-tab">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Room PK') }}</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Team 1') }}</th>
                    <th>{{ __('Team 2') }}</th>
                    <th>{{ __('Score') }}</th>
                    <th>{{ __('Prize') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Time') }}</th>
                </tr>
                </thead>
                <tbody>

                @forelse($pks as $index => $pk)
                    <tr>
                        <td>{{ $pk->id }}</td>
                        <td>
                            <div class="pk-title">{{ $pk->title }}</div>
                            @if($pk->conditions)
                                <small class="text-muted">{{ $pk->conditions }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="team-info">
                                <div class="team-title">{{ $pk->team_1_title }}</div>
                                @if($pk->team1Boss)
                                    <a href="{{ admin_url('users/' . $pk->team1Boss->id) }}" class="team-boss">
                                        <img src="{{ getImagePath($pk->team1Boss->profile->avatar) ?? asset('images/businessman-icon.jpg') }}"
                                             class="boss-avatar">
                                        <span>{{ $pk->team1Boss->name }}</span>
                                    </a>
                                @endif
                                <a href="javascript:void(0)"
                                   class="show-team-members"
                                   data-team-members="{{ $pk->team_1 }}"
                                   data-team-name="{{ $pk->team_1_title }}">
                                    <small class="members-count">{{ __('Members') }}: {{ count(array_filter(explode(',', $pk->team_1), function($value) {
                                        return $value !== '' && $value !== '0' && $value > 0;
                                    })) }}</small>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="team-info">
                                <div class="team-title">{{ $pk->team_2_title }}</div>
                                @if($pk->team2Boss)
                                    <a href="{{ admin_url('users/' . $pk->team2Boss->id) }}" class="team-boss">
                                        <img src="{{ getImagePath($pk->team2Boss->profile->avatar) ?? asset('images/businessman-icon.jpg') }}"
                                             class="boss-avatar">
                                        <span>{{ $pk->team2Boss->name }}</span>
                                    </a>
                                @endif
                                <a href="javascript:void(0)"
                                   class="show-team-members"
                                   data-team-members="{{ $pk->team_2 }}"
                                   data-team-name="{{ $pk->team_2_title }}">
                                    <small class="members-count">{{ __('Members') }}: {{ count(array_filter(explode(',', $pk->team_2), function($value) {
                                        return $value !== '' && $value !== '0' && $value > 0;
                                    })) }}</small>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="score-info">
                                <div class="team-score">
                                    <span class="score-label">{{ __('Team 1') }}:</span>
                                    <span class="score-value">{{ $pk->t1_score }}</span>
                                </div>
                                <div class="team-score">
                                    <span class="score-label">{{ __('Team 2') }}:</span>
                                    <span class="score-value">{{ $pk->t2_score }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $pk->prize_value }}</td>
                        <td>
                            <div class="status-container">
                                @php
                                    $statusBadge = match($pk->status) {
                                        'active' => 'success',
                                        'ended' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $statusBadge }}">
                                    {{ ucfirst($pk->status) }}
                                </span>
                                @if($pk->show_status)
                                    <span class="badge badge-info">{{ __('Visible') }}</span>
                                @endif
                                @if($pk->winner)
                                    <div class="winner-badge">
                                        {{ __('Winner') }}: {{ __('Team') }} {{ $pk->winner }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="time-info">
                                <div>{{ Carbon::parse($pk->start_at)->format('Y-m-d H:i') }}</div>
                                <div>{{ Carbon::parse($pk->end_at)->format('Y-m-d H:i') }}</div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="p-3">
                                <i class="fas fa-info-circle text-muted"></i>
                                {{ __('No data available') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $pks->appends(request()->all())->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

<div class="tab-content {{ request('tab') == 'boxes' ? 'active' : '' }}" id="boxes-tab">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Boxes in Room') }}</h4>
        </div>

        <div class="box-body p-3">
            <div class="card mb-4">
                <div class="card-body">
                    <form action="" class="form-horizontal  gift-log-form" method="GET">
                        <input type="hidden" name="tab" value="boxes">
                        <div class="container-fluid">
                            <div class="row g-2 align-items-end justify-content-between">
                                <div class="col-md-4">
                                    <label for="type-select" class="form-label d-flex align-items-center justify-content-end fw-bold">
                                        <span>{{ __('Box Type') }}</span>
                                    </label>
                                    <select class="form-control" name="type" id="type-select">
                                        <option value="">{{ __('All Types') }}</option>
                                        <option value="0" {{ request('type') === '0' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                                        <option value="1" {{ request('type') === '1' ? 'selected' : '' }}>{{ __('Super') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="status-select" class="form-label d-flex align-items-center justify-content-end fw-bold">
                                        <span>{{ __('Status') }}</span>
                                    </label>
                                    <select class="form-control" name="status" id="status-select">
                                        <option value="">{{ __('All Status') }}</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                                    </select>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-info d-flex align-items-center gap-2">
                                            <i class="fa fa-search"></i> {{__('Search')}}
                                        </button>
                                        <a href="?tab=boxes" class="btn btn-default d-flex align-items-center gap-2">
                                            <i class="fa fa-undo"></i> {{__('Reset')}}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Box Type') }}</th>
                    <th>{{ __('Owner') }}</th>
                    <th>{{ __('Coins') }}</th>
                    <th>{{ __('Users Picked') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Created At') }}</th>
                    <th>{{ __('Expires At') }}</th>
                </tr>
                </thead>
                <tbody>

                @forelse($boxes as $index => $box)
                    <tr>
                        <td>{{ $box->id }}</td>
                        <td>
                            <span class="badge badge-{{ $box->type == 0 ? 'primary' : 'warning' }}">
                                {{ $box->type == 0 ? __('Normal') : __('Super') }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ admin_url('users/' . $box->user->id) }}" target="_blank"
                               class="d-flex align-items-center text-decoration-none">
                                <img src="{{ getImagePath($box->user->profile->avatar) ?? asset('images/businessman-icon.jpg') }}"
                                     width="40" height="40"
                                     style="object-fit: cover; border-radius: 50%; margin-right: 10px;">
                                <div>
                                    <strong>{{ $box->user->name }}</strong><br>
                                    <small class="text-muted">UUID: {{ $box->user->uuid }}</small>
                                </div>
                            </a>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="fw-bold">{{ __('Total') }}:</span>
                                    {{ number_format($box->coins) }}
                                    <img src="{{ asset('images/diamond.jpg') }}"
                                         alt="Diamond"
                                         style="width: 20px; height: 20px; margin-left: 5px;">
                                </div>
                                <div class="d-flex align-items-center text-muted">
                                    <span class="fw-bold">{{ __('Used') }}:</span>
                                    {{ number_format($box->used_coins) }}
                                    <img src="{{ asset('images/diamond.jpg') }}"
                                         alt="Diamond"
                                         style="width: 15px; height: 15px; margin-left: 5px;">
                                </div>
                                <div class="d-flex align-items-center text-muted">
                                    <span class="fw-bold">{{ __('Remains') }}:</span>
                                    {{ number_format($box->unused_coins) }}
                                    <img src="{{ asset('images/diamond.jpg') }}"
                                         alt="Diamond"
                                         style="width: 15px; height: 15px; margin-left: 5px;">
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="progress-info">
                                <div class="progress" style="height: 20px;">
                                    @php
                                        $percentage = ($box->used_num / $box->users_num) * 100;
                                    @endphp
                                    <div class="progress-bar bg-success"
                                         role="progressbar"
                                         style="width: {{ $percentage }}%"
                                         aria-valuenow="{{ $percentage }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <div style="text-align: center; margin-top: 5px;">
                                    ({{ number_format($percentage, 1) }}%) {{ $box->used_num }} / {{ $box->users_num }}
                                    <br>
                                    <a href="javascript:void(0)"
                                       class="show-picked-users"
                                       data-picked-users="{{ $box->picks->pluck('user_id')->implode(',') }}">
                                        <span class="show-users-text">{{ __('Show Users') }}</span>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $now = \Carbon\Carbon::now()->timestamp;
                                $isExpired = $box->end_at < $now;
                                $isClosed = $box->is_closed;
                            @endphp
                            <span class="badge badge-{{ $isExpired || $isClosed ? 'danger' : 'success' }}">
                                {{ $isExpired ? __('Expired') : ($isClosed ? __('Closed') : __('Active')) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::createFromTimestamp($box->start_at)->format('Y-m-d H:i:s') }}</td>
                        <td>{{ \Carbon\Carbon::createFromTimestamp($box->end_at)->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="p-3">
                                <i class="fas fa-info-circle text-muted"></i>
                                {{ __('No boxes available') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
            {{ $boxes->appends(request()->all())->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function openEditModal() {
        $('#editRoomModal').modal('show');
    }

    $(document).ready(function() {
        $('#editRoomForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route("admin.rooms.basic_update", $room->id) }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __("Success") }}',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __("Error") }}',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("Error") }}',
                        text: xhr.responseJSON.message || '{{ __("Something went wrong") }}'
                    });
                }
            });
        });
    });

    $(document).on('click', '.show-picked-users', function() {
        const pickedUsers = $(this).data('picked-users');
        const boxTitle = '{{ __("Box Picked Users") }}';

        if (!pickedUsers) {
            Swal.fire('{{ __("Error") }}', '{{ __("No users found") }}', 'error');
            return;
        }

        const userIds = pickedUsers.toString().split(',').filter(id => id.trim());

        Swal.fire({
            title: '{{ __("Loading...") }}',
            allowOutsideClick: false,
            onOpen: () => {
                Swal.showLoading();

                $.ajax({
                    url: '{{ route("admin.get.users") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        user_ids: userIds
                    },
                    success: function(response) {
                        let membersList = '';

                        response.forEach(function(user) {
                            membersList += `
                            <div class="member-item">
                                <a href="{{ admin_url('users') }}/${user.id}"
                                   class="member-info" target="_blank">
                                    <img src="${user.profile?.avatar || '{{ asset("images/businessman-icon.jpg") }}'}"
                                         class="member-avatar">
                                    <div class="member-details">
                                        <div class="member-name">${user.name}</div>
                                        <div class="member-id">
                                            <span>ID: ${user.id}</span><br>
                                            <span>UID: ${user.uuid}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        `;
                        });

                        Swal.fire({
                            title: boxTitle,
                            html: `<div class="team-members-list">${membersList}</div>`,
                            width: '800px',
                            showConfirmButton: false,
                            showCloseButton: true
                        });
                    },
                    error: function() {
                        Swal.fire('{{ __("Error") }}', '{{ __("Failed to load users") }}', 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.show-team-members', function() {
        const teamMembers = $(this).data('team-members');
        const teamName = $(this).data('team-name');

        if (!teamMembers) {
            Swal.fire('{{ __("Error") }}', '{{ __("No team members found") }}', 'error');
            return;
        }

        const memberIds = teamMembers.split(',').filter(id => id.trim());

        Swal.fire({
            title: '{{ __("Loading...") }}',
            allowOutsideClick: false,
            onOpen: () => {
                Swal.showLoading();

                $.ajax({
                    url: '{{ route("admin.get.users") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {
                        user_ids: memberIds
                    },
                    success: function(response) {
                        let membersList = '';

                        response.forEach(function(user) {
                            membersList += `
                            <div class="member-item">
                                <a href="{{ admin_url('users') }}/${user.id}"
                                   class="member-info" target="_blank">
                                    <img src="${user.profile?.avatar || '{{ asset("images/businessman-icon.jpg") }}'}"
                                         class="member-avatar">
                                    <div class="member-details">
                                        <div class="member-name">${user.name}</div>
                                        <div class="member-id">
                                            <span>ID: ${user.id}</span><br>
                                            <span>UID: ${user.uuid}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        `;
                        });

                        Swal.fire({
                            title: `${teamName} {{ __("Members") }}`,
                            html: `<div class="team-members-list">${membersList}</div>`,
                            width: '800px',
                            showConfirmButton: false,
                            showCloseButton: true
                        });
                    },
                    error: function() {
                        Swal.fire('{{ __("Error") }}', '{{ __("Failed to load team members") }}', 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '#add-visitor-btn', function () {
        Swal.fire({
            title: '{{ __("Add Visitor") }}',
            html: '<select id="visitor-select" style="width: 100%"></select>',
            showCancelButton: true,
            confirmButtonText: '{{ __("Add") }}',
            cancelButtonText: '{{ __("Cancel") }}',
            onOpen: () => {
                $('#visitor-select').select2({
                    dropdownParent: $(".swal2-container"),
                    width: '100%',
                    ajax: {
                        url: '{{ route("search.users") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term || '',
                                type: 'public'
                            };
                        },
                        processResults: function (response) {
                            let data = response.data || response || [];
                            return {
                                results: data.map(function (user) {
                                    return {
                                        id: user.id,
                                        text: user.name + (user.uuid ? ' - ' + user.uuid : '')
                                    };
                                })
                            };
                        }
                    },
                    minimumInputLength: 1,
                    placeholder: '{{ __("Search for user...") }}'
                });
            },
            preConfirm: () => {
                const selectedId = $('#visitor-select').val();

                if (!selectedId) {
                    Swal.showValidationMessage('{{ __("Please select a user") }}');
                    return false;
                }

                return new Promise((resolve) => {
                    $.ajax({
                        url: '{{ admin_url("rooms/{$room->id}/add-visitor") }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            user_id: selectedId
                        },
                        success: function (response) {
                            resolve(response);
                        },
                        error: function (xhr) {
                            resolve({
                                success: false,
                                message: xhr.responseJSON?.message || '{{ __("An error occurred") }}'
                            });
                        }
                    });
                });
            }
        }).then((result) => {
            if (result.value) {
                if (result.value.success) {
                    window.location.href = window.location.href;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("Error") }}',
                        text: result.value.message
                    });
                }
            }
        });
    });

    $(document).on('click', '.kick-visitor', function () {
        const userId = $(this).data('user-id');
        const roomId = $(this).data('room-id');

        Swal.fire({
            title: '{{ __("Kick Visitor") }}',
            html: `
            <div class="form-group">
                <label>{{ __("Duration (minutes)") }}</label>
                <input type="number" id="kick-duration" class="form-control" value="5" min="1">
            </div>
        `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ __("Yes, kick them") }}',
            cancelButtonText: '{{ __("Cancel") }}',
            preConfirm: () => {
                const duration = $('#kick-duration').val();
                if (!duration || duration < 1) {
                    Swal.showValidationMessage('{{ __("Please enter a valid duration") }}');
                    return false;
                }

                return new Promise((resolve) => {
                    $.ajax({
                        url: '{{ admin_url("rooms/{$room->id}/kick-visitor") }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            user_id: userId,
                            minutes: duration
                        },
                        success: function (response) {
                            console.log('Response:', response);
                            resolve(response);
                        },
                        error: function (xhr) {
                            resolve({
                                success: false,
                                message: xhr.responseJSON?.message || '{{ __("An error occurred") }}'
                            });
                        }
                    });
                });
            }
        }).then((result) => {
            if (result.value) {
                if (result.value.success) {
                    window.location.href = window.location.href;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __("Error") }}',
                        text: result.value.message
                    });
                }
            }
        });
    });

    $(document).ready(function () {
        $('#sender-select, #receiver-select').select2({
            ajax: {
                url: '{{ route('search.users') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    var query = {
                        search: params.term,
                        type: 'public',
                        page: params.page || 1
                    };

                    return query;
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;

                    var users = Array.isArray(data) ? data : (data.data || []);

                    return {
                        results: $.map(users, function (user) {
                            return {
                                id: user.id,
                                text: user.name + (user.uuid ? ' - ' + user.uuid : '')
                            };
                        }),
                        pagination: {
                            more: false
                        }
                    };
                },
                cache: true
            },
            placeholder: "{{ __('Search for user...') }}",
            minimumInputLength: 1
        });
    });

    $(document).ready(function () {
        $('.remove-admin').click(function () {
            const roomId = $(this).data('room-id');
            const adminId = $(this).data('admin-id');
            const url = '{{ admin_url('rooms') }}/' + roomId + '/remove-admin';

            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': LA.token
                },
                data: {
                    admin_id: adminId
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            title: '{{ __("Success!") }}',
                            text: response.message,
                            type: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: '{{ __("Error!") }}',
                            text: response.message || '{{ __("Something went wrong!") }}',
                            type: 'error'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error details:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    Swal.fire({
                        title: '{{ __("Error!") }}',
                        text: '{{ __("Something went wrong!") }}',
                        type: 'error'
                    });
                }
            });
        });
    });

    $(document).ready(function () {
        $('#add_form').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = form.serialize();

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                success: function (response) {
                    $('#Add_model').modal('hide');

                    $('#Add_model').modal('hide');

                    location.reload();

                },
                error: function (xhr) {
                    let errors = xhr.responseJSON.errors;
                    let msg = '';
                    for (let key in errors) {
                        msg += errors[key][0] + '\n';
                    }
                    alert(msg || 'Something went wrong!');
                }
            });
        });
    });

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
                targetElement = content;
            } else {
                tab.classList.remove('active');
                content.style.display = 'none';
            }

            tab.addEventListener('click', function (e) {
                e.preventDefault();

                const currentUrl = new URL(window.location.href);
                const href = tab.getAttribute('href');
                const targetUrl = new URL(href, currentUrl.origin);

                if (currentUrl.pathname === targetUrl.pathname && targetUrl.searchParams.get('tab')) {
                    document.getElementById('tab-loading').style.display = 'block';
                    allTabs.forEach(t => t.style.pointerEvents = 'none');

                    setTimeout(() => {
                        window.location.href = href;
                    }, 300);
                } else {
                    window.location.href = href;
                }
            });
        });

        if (targetElement) {
            setTimeout(() => {
                targetElement.scrollIntoView({behavior: 'smooth'});
            }, 500);
        }
    });

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            const target = btn.getAttribute('data-target');
            document.getElementById(target).classList.add('active');
        });
    });

    $(document).ready(function () {
        $('#agency_id').select2({
            placeholder: 'Select agency',
            allowClear: true,
            ajax: {
                url: '/api/search/host-agency',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.data.map(item => ({
                            id: item.id,
                            text: item.name
                        })),
                        pagination: {
                            more: data.next_page_url !== null
                        }
                    };
                },
                cache: true
            }
        });

        function showLoader() {
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        function showSuccess(message, callback = null) {
            Swal.fire({
                icon: 'success',
                title: message,
                confirmButtonText: 'OK'
            }).then(() => {
                if (callback) {
                    callback();
                }
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: message,
                confirmButtonText: 'OK'
            });
        }

        function confirmAction(message, onConfirm) {
            Swal.fire({
                title: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.value) {
                    onConfirm();
                } else {
                }
            });
        }

        $('.accept-btn').click(function () {
            const id = $(this).data('id');
            confirmAction('{{ __("are_you_sure_accept") }}', () => {
                showLoader();
                $.post(`/admin/agencies/accept_join/${id}`, {
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    Swal.close();
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

            $('#add_form')[0].reset();

            $('.item_id').val(itemId);

            $('#Add_model').modal('show');
        });
        $(document).on('click', '.close-modal-btn', function () {
            $('#Add_model').modal('hide');
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

        $('.reject-btn').click(function () {
            const id = $(this).data('id');
            confirmAction('{{ __("are_you_sure_reject") }}', () => {
                showLoader();
                $.post(`/admin/agencies/reject_join/${id}`, {
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    Swal.close();
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

        $('.make-admin-btn').click(function () {
            const id = $(this).data('id');
            confirmAction('{{ __("are_you_sure_make_admin") }}', () => {
                showLoader();
                $.post(`/admin/agencies/admin/${id}`, {
                    _token: '{{ csrf_token() }}'
                }, function (response) {
                    Swal.close();
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

