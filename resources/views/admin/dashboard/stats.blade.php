{{-- admin/dashboard/stats.blade.php --}}
<div class="stats-container" id="stats-container">
    <!-- Loading Indicator -->
    <div id="stats-loading" class="text-center py-4">
        <i class="fa fa-spinner fa-spin fa-2x"></i>
        <p>{{ __('admin.loading_statistics') }}</p>
    </div>

    <!-- Stats will be loaded here -->
    <div id="stats-content" style="display: none;"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// دالة تحميل الإحصائيات
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

// دالة عرض الإحصائيات بنفس شكل InfoBoxes
function renderStatsWithInfoBoxes(data) {
    const statsContent = $('#stats-content');
    
    const infoBoxesHtml = `
        <div class="row">
            <!-- Users Count -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}'">
                    <span class="info-box-icon bg-aqua">
                        <i class="fa fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Users Count') }}</span>
                        <span class="info-box-number">${data.usersCount.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- Online Users Count -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}?online=1'">
                    <span class="info-box-icon bg-blue">
                        <i class="fa fa-user"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Online Users Count') }}</span>
                        <span class="info-box-number">${data.onlineUser.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- Peak Hour -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}'">
                    <span class="info-box-icon bg-green">
                        <i class="fa fa-clock-o"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Peak Hour') }}</span>
                        <span class="info-box-number" style="font-size: 16px;">${data.peakHour}</span>
                    </div>
                </div>
            </div>

            <!-- New Sign Ups Today -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}?signups=today'">
                    <span class="info-box-icon bg-yellow">
                        <i class="fa fa-user-plus"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('New Sign Ups Today') }}</span>
                        <span class="info-box-number">${data.newSignUpsToday.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- New Sign Ups This Week -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}?signups=week'">
                    <span class="info-box-icon bg-red">
                        <i class="fa fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('New Sign Ups This Week') }}</span>
                        <span class="info-box-number">${data.newSignUpsThisWeek.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- New Sign Ups This Month -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}?signups=month'">
                    <span class="info-box-icon bg-purple">
                        <i class="fa fa-user"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('New Sign Ups This Month') }}</span>
                        <span class="info-box-number">${data.newSignUpsThisMonth.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- Messages Today -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}?messages=today'">
                    <span class="info-box-icon bg-maroon">
                        <i class="fa fa-envelope"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Messages Today') }}</span>
                        <span class="info-box-number">${data.messagesToday.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- Messages This Month -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}?messages=month'">
                    <span class="info-box-icon bg-teal">
                        <i class="fa fa-comments"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Messages This Month') }}</span>
                        <span class="info-box-number">${data.messagesThisMonth.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- Users Who Send Messages -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}?sent_messages=1'">
                    <span class="info-box-icon bg-gray">
                        <i class="fa fa-user"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Users Who Send Messages') }}</span>
                        <span class="info-box-number">${data.usersWhoSend.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- Users Who Never Send -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}?never_send=1'">
                    <span class="info-box-icon bg-orange">
                        <i class="fa fa-user-times"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Users Who Never Send') }}</span>
                        <span class="info-box-number">${data.usersWhoNeverSend.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- Open Conversations Today -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}'">
                    <span class="info-box-icon bg-lime">
                        <i class="fa fa-comments-o"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Open Conversations Today') }}</span>
                        <span class="info-box-number">${data.openConversationsToday.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- Avg Conversation Duration -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" onclick="window.location.href='{{ admin_url('users') }}'">
                    <span class="info-box-icon bg-olive">
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

// تحديث الإحصائيات تلقائياً كل دقيقتين
function startAutoRefresh() {
    setInterval(loadStatsData, 120000); // 2 دقيقة
}

// تحميل البيانات عند فتح الصفحة
$(document).ready(function() {
    loadStatsData();
    startAutoRefresh();
    
    // إضافة زر تحديث يدوي إذا أردت
    $(document).on('click', '#refresh-stats-btn', function() {
        loadStatsData();
    });
});

// إضافة تأثير hover على الـ InfoBoxes
$(document).on('mouseenter', '.info-box', function() {
    $(this).css('cursor', 'pointer');
    $(this).css('transform', 'translateY(-2px)');
    $(this).css('transition', 'all 0.3s ease');
    $(this).css('box-shadow', '0 4px 8px rgba(0,0,0,0.2)');
});

$(document).on('mouseleave', '.info-box', function() {
    $(this).css('transform', 'translateY(0)');
    $(this).css('box-shadow', 'none');
});
</script>

<style>
.info-box {
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 15px;
}

.info-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
}

/* ألوان الـ InfoBoxes */
.bg-aqua { background-color: #00c0ef !important; }
.bg-blue { background-color: #0073b7 !important; }
.bg-green { background-color: #00a65a !important; }
.bg-yellow { background-color: #f39c12 !important; }
.bg-red { background-color: #dd4b39 !important; }
.bg-purple { background-color: #605ca8 !important; }
.bg-maroon { background-color: #d81b60 !important; }
.bg-teal { background-color: #39cccc !important; }
.bg-gray { background-color: #d2d6de !important; color: #333 !important; }
.bg-orange { background-color: #ff851b !important; }
.bg-lime { background-color: #01ff70 !important; color: #333 !important; }
.bg-olive { background-color: #3d9970 !important; }

/* تحسين التصميم للشاشات الصغيرة */
@media (max-width: 768px) {
    .col-md-3 {
        margin-bottom: 10px;
    }
    
    .info-box-content .info-box-number {
        font-size: 20px;
    }
    
    .info-box-content .info-box-text {
        font-size: 13px;
    }
}
</style>