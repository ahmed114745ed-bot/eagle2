{{-- admin/dashboard/stats.blade.php --}}
<div class="stats-container" id="stats-container">
    <!-- Loading Indicator -->
{{--    <div id="stats-loading" class="text-center py-4">--}}
{{--        <i class="fa fa-spinner fa-spin fa-2x"></i>--}}
{{--        <p>{{ __('admin.loading_statistics') }}</p>--}}
{{--    </div>--}}

    <!-- Stats will be loaded here -->
    <div id="stats-content" style="display: none;"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadStatsData() {
    $.ajax({
        url: '{{ url("admin/statistics/stats-data") }}',
        type: 'GET',
        beforeSend: function() {
            $('#stats-loading').show();
            $('#stats-content').hide();
        },
        success: function(response) {
            if (response.success) {
                renderStatsWithInfoBoxes(response.data);
                $('#stats-loading').hide();
                $('#stats-content').show();
            }
        },
        error: function() {
            $('#stats-loading').html('<div class="alert alert-danger">{{ __('admin.error_loading_data') }}</div>');
        }
    });
}

function renderStatsWithInfoBoxes(data) {
    const statsContent = $('#stats-content');

    const infoBoxesHtml = `
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-light-blue" onclick="window.location.href='{{ admin_url('users') }}'">
                    <span class="info-box-icon">
                        <i class="fa fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Users Count') }}</span>
                        <span class="info-box-number">${data.usersCount.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-green" onclick="window.location.href='{{ admin_url('users') }}?online=1'">
                    <span class="info-box-icon">
                        <i class="fa fa-user"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Online Users Count') }}</span>
                        <span class="info-box-number">${data.onlineUser.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-gray" onclick="window.location.href='{{ admin_url('users') }}'">
                    <span class="info-box-icon">
                        <i class="fa fa-clock-o"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Peak Hour') }}</span>
                        <span class="info-box-number" style="font-size: 16px;">${data.peakHour}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-purple" onclick="window.location.href='{{ admin_url('users') }}?signups=today'">
                    <span class="info-box-icon">
                        <i class="fa fa-user-plus"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('New Sign Ups Today') }}</span>
                        <span class="info-box-number">${data.newSignUpsToday.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-yellow" onclick="window.location.href='{{ admin_url('users') }}?signups=week'">
                    <span class="info-box-icon">
                        <i class="fa fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('New Sign Ups This Week') }}</span>
                        <span class="info-box-number">${data.newSignUpsThisWeek.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-purple" onclick="window.location.href='{{ admin_url('users') }}?signups=month'">
                    <span class="info-box-icon">
                        <i class="fa fa-user"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('New Sign Ups This Month') }}</span>
                        <span class="info-box-number">${data.newSignUpsThisMonth.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-red" onclick="window.location.href='{{ admin_url('users') }}?messages=today'">
                    <span class="info-box-icon">
                        <i class="fa fa-envelope"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Messages Today') }}</span>
                        <span class="info-box-number">${data.messagesToday.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-red" onclick="window.location.href='{{ admin_url('users') }}?messages=month'">
                    <span class="info-box-icon">
                        <i class="fa fa-comments"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Messages This Month') }}</span>
                        <span class="info-box-number">${data.messagesThisMonth.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-blue" onclick="window.location.href='{{ admin_url('users') }}?sent_messages=1'">
                    <span class="info-box-icon">
                        <i class="fa fa-user"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Users Who Send Messages') }}</span>
                        <span class="info-box-number">${data.usersWhoSend.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-gray" onclick="window.location.href='{{ admin_url('users') }}?never_send=1'">
                    <span class="info-box-icon">
                        <i class="fa fa-user-times"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Users Who Never Send') }}</span>
                        <span class="info-box-number">${data.usersWhoNeverSend.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-green" onclick="window.location.href='{{ admin_url('users') }}'">
                    <span class="info-box-icon">
                        <i class="fa fa-comments-o"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Open Conversations Today') }}</span>
                        <span class="info-box-number">${data.openConversationsToday.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-yellow" onclick="window.location.href='{{ admin_url('users') }}'">
                    <span class="info-box-icon">
                        <i class="fa fa-clock-o"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Avg Conversation Duration (min)') }}</span>
                        <span class="info-box-number">${Math.round(data.avgConversationDuration)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    statsContent.html(infoBoxesHtml);
}

function startAutoRefresh() {
    setInterval(loadStatsData, 120000);
}

$(document).ready(function() {
    loadStatsData();
    startAutoRefresh();

    $(document).on('click', '#refresh-stats-btn', function() {
        loadStatsData();
    });
});

$(document).on('mouseenter', '.info-box', function() {
    $(this).css('cursor', 'pointer');
    $(this).css('transform', 'translateY(-3px)');
    $(this).css('transition', 'all 0.3s ease');
    $(this).css('box-shadow', '0 6px 12px rgba(0,0,0,0.3)');
});

$(document).on('mouseleave', '.info-box', function() {
    $(this).css('transform', 'translateY(0)');
    $(this).css('box-shadow', '0 2px 4px rgba(0,0,0,0.1)');
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const charts = document.querySelectorAll('canvas');

        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const url = entry.target.dataset.url;
                    if (url) fetch(url)
                        .then(r => r.json())
                        .then(data => {
                            const ctx = entry.target.getContext('2d');
                            new Chart(ctx, { type: 'bar', data: { labels: data.labels, datasets: [{ data: data.data }] } });
                        });
                    obs.unobserve(entry.target);
                }
            });
        });
        charts.forEach(c => io.observe(c));
    });
</script>

<style>
.info-box {
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 15px;
    border-radius: 5px;
    display: block;
    min-height: 100px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    color: white !important;
    padding: 0;
    overflow: hidden;
}

.info-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.3) !important;
}

.info-box-icon {
    display: block;
    float: left;
    height: 100px;
    width: 100px;
    text-align: center;
    font-size: 50px;
    line-height: 100px;
    background: rgba(255,255,255,0.2);
    border-right: 1px solid rgba(255,255,255,0.1);
}

.info-box-content {
    padding: 15px 20px;
    margin-left: 100px;
    color: white;
}

.info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 5px;
    opacity: 0.9;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 24px;
    opacity: 1;
}

.bg-light-blue {
    background: linear-gradient(135deg, #3c8dbc 0%, #367fa9 100%) !important;
    border-left: 4px solid #2d7ca7;
}

.bg-blue {
    background: linear-gradient(135deg, #0073b7 0%, #0065a3 100%) !important;
    border-left: 4px solid #005c8f;
}

.bg-green {
    background: linear-gradient(135deg, #00a65a 0%, #008d4c 100%) !important;
    border-left: 4px solid #007d41;
}

.bg-yellow {
    background: linear-gradient(135deg, #f39c12 0%, #e08e0b 100%) !important;
    border-left: 4px solid #d17e09;
}

.bg-orange {
    background: linear-gradient(135deg, #ff851b 0%, #ff7701 100%) !important;
    border-left: 4px solid #e66900;
}

.bg-red {
    background: linear-gradient(135deg, #dd4b39 0%, #d73925 100%) !important;
    border-left: 4px solid #c23321;
}

.bg-purple {
    background: linear-gradient(135deg, #605ca8 0%, #555299 100%) !important;
    border-left: 4px solid #4a4786;
}

.bg-gray {
    background: linear-gradient(135deg, #d2d6de 0%, #b5bbc9 100%) !important;
    border-left: 4px solid #a8afbf;
    color: #333 !important;
}

.bg-gray .info-box-content {
    color: #333 !important;
}

.bg-gray .info-box-text {
    color: #555 !important;
}

.bg-gray .info-box-number {
    color: #222 !important;
}

@media (max-width: 768px) {
    .col-md-3 {
        margin-bottom: 10px;
    }

    .info-box {
        min-height: 80px;
    }

    .info-box-icon {
        width: 80px;
        height: 80px;
        line-height: 80px;
        font-size: 40px;
    }

    .info-box-content {
        margin-left: 80px;
        padding: 10px 15px;
    }

    .info-box-content .info-box-number {
        font-size: 20px;
    }

    .info-box-content .info-box-text {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .info-box {
        min-height: 70px;
    }

    .info-box-icon {
        width: 70px;
        height: 70px;
        line-height: 70px;
        font-size: 35px;
    }

    .info-box-content {
        margin-left: 70px;
        padding: 8px 12px;
    }

    .info-box-content .info-box-number {
        font-size: 18px;
    }

    .info-box-content .info-box-text {
        font-size: 11px;
    }
}
</style>
