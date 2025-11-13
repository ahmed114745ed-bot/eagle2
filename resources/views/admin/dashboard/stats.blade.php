<div class="stats-container">
    <div class="row g-3">

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-light-blue">
                <span class="info-box-icon"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Users Count') }}</span>
                    <span class="info-box-number" data-stat="usersCount"></span>
                    <a href="{{ admin_url('users') }}" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-user"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Online Users Count') }}</span>
                    <span class="info-box-number" data-stat="onlineUser"></span>
                    <a href="{{ admin_url('users') }}?online=1" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-gray">
                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Peak Hour') }}</span>
                    <span class="info-box-number" data-stat="peakHour"></span>
                    <a href="{{ admin_url('users') }}" class="info-box-more text-dark">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-purple">
                <span class="info-box-icon"><i class="fa fa-user-plus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('New Sign Ups Today') }}</span>
                    <span class="info-box-number" data-stat="newSignUpsToday"></span>
                    <a href="{{ admin_url('users') }}?signups=today" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('New Sign Ups This Week') }}</span>
                    <span class="info-box-number" data-stat="newSignUpsThisWeek"></span>
                    <a href="{{ admin_url('users') }}?signups=week" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-orange">
                <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('New Sign Ups This Month') }}</span>
                    <span class="info-box-number" data-stat="newSignUpsThisMonth"></span>
                    <a href="{{ admin_url('users') }}?signups=month" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-envelope"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Messages Today') }}</span>
                    <span class="info-box-number" data-stat="messagesToday"></span>
                    <a href="{{ admin_url('users') }}?messages=today" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-comments"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Messages This Month') }}</span>
                    <span class="info-box-number" data-stat="messagesThisMonth"></span>
                    <a href="{{ admin_url('users') }}?messages=month" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-blue">
                <span class="info-box-icon"><i class="fa fa-user"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Users Who Send Messages') }}</span>
                    <span class="info-box-number" data-stat="usersWhoSend"></span>
                    <a href="{{ admin_url('users') }}?sent_messages=1" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-gray">
                <span class="info-box-icon"><i class="fa fa-user-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Users Who Never Send') }}</span>
                    <span class="info-box-number" data-stat="usersWhoNeverSend"></span>
                    <a href="{{ admin_url('users') }}?never_send=1" class="info-box-more text-dark">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-comments-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Open Conversations Today') }}</span>
                    <span class="info-box-number" data-stat="openConversationsToday"></span>
                    <a href="{{ admin_url('users') }}" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Avg Conversation Duration (min)') }}</span>
                    <span class="info-box-number" data-stat="avgConversationDuration"></span>
                    <a href="{{ admin_url('users') }}" class="info-box-more text-white">
                        <i class="fa fa-arrow-circle-right me-1"></i> {{ __('More') }}
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .info-box { position: relative; min-height: 100px; border-radius: 8px; overflow: hidden; padding: 12px; }
    .info-box .info-box-more { position: absolute; bottom: 0; left: 0; right: 0; display: flex;
        justify-content: center; align-items: center; background: rgba(0,0,0,0.15);
        height: 30px; font-weight: 600; color: #fff; text-decoration: none; transition: background 0.2s ease; }
    .info-box:hover .info-box-more { background: rgba(0,0,0,0.3); }
</style>

@php
    if (request()->is('superadmin*')) {
        $prefix = 'superadmin';
    } elseif (request()->is('areaManager*')) {
        $prefix = 'areaManager';
    } else {
        $prefix = 'admin';
    }
@endphp

<script>
    $(function() {
        function updateStats() {
            $.ajax({
                url: '{{ url($prefix . "/statistics/stats-data") }}',
                type: 'GET',
                success: function(data) {
                    if (data.success) data = data.data;

                    $('[data-stat="usersCount"]').text(data.usersCount.toLocaleString());
                    $('[data-stat="onlineUser"]').text(data.onlineUser.toLocaleString());
                    $('[data-stat="peakHour"]').text(data.peakHour);
                    $('[data-stat="newSignUpsToday"]').text(data.newSignUpsToday.toLocaleString());
                    $('[data-stat="newSignUpsThisWeek"]').text(data.newSignUpsThisWeek.toLocaleString());
                    $('[data-stat="newSignUpsThisMonth"]').text(data.newSignUpsThisMonth.toLocaleString());
                    $('[data-stat="messagesToday"]').text(data.messagesToday.toLocaleString());
                    $('[data-stat="messagesThisMonth"]').text(data.messagesThisMonth.toLocaleString());
                    $('[data-stat="usersWhoSend"]').text(data.usersWhoSend.toLocaleString());
                    $('[data-stat="usersWhoNeverSend"]').text(data.usersWhoNeverSend.toLocaleString());
                    $('[data-stat="openConversationsToday"]').text(data.openConversationsToday.toLocaleString());
                    $('[data-stat="avgConversationDuration"]').text(Math.round(data.avgConversationDuration));
                },
                error: function() {
                    alert('{{ __("Error loading stats") }}');
                }
            });
        }

        updateStats();
        setInterval(updateStats, 120000);
    });
</script>
