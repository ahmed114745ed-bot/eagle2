{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> --}}
    @if (\Encore\Admin\Facades\Admin::user()->can('pay-switch' . 'dashboard') || \Encore\Admin\Facades\Admin::user()->can('*'))

            <div style="margin-bottom: 15px;">
                <form method="GET" action="{{ url()->current() }}" style="display: inline-flex; gap: 10px; background: none !important; filter: none;">
                    <input type="month" name="date" id="date-filter" value="{{ request('date') }}" style="padding: 5px;">
                    <button type="submit" style="padding: 6px 12px; cursor: pointer;">{{ __('admin.filter') }}</button>
                </form>
            </div>
    @endif
<style>
.card {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    width: 44%; /* Default width */
    margin: inherit;
    padding: 20px;
    background-color: #e9dbdb;
    margin-top: 10px;
}

@media (max-width: 767px) {
    .card {
        width: 100%; /* Full width for mobile screens */
        padding: 15px;
    }

    /* Centering chart on mobile */
    .chart-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }

    .chart-container canvas {
        max-width: 100%;
    }
}
ul.list-unstyled {
    font-size: 1.2em;
    font-weight: bold;
}
</style>
    @if (\Encore\Admin\Facades\Admin::user()->can('pay-switch' . 'dashboard') || \Encore\Admin\Facades\Admin::user()->can('*'))

<div class="card cardHome" style="margin-bottom: 15px;">
    <div class="row">
        <!-- Left content with table -->
        <div class="col-md-6 d-flex align-items-center justify-content-center">
            <table class="table table-bordered" id="balance-table">
                <tbody>
                    <tr>
                        <th>{{ __('admin.balance') }}</th>
                        <td id="all-balance">0</td>
                    </tr>
                    <tr>
                        <th>{{ __('admin.availableBalance') }}</th>
                        <td id="available-balance">0</td>
                    </tr>
                    <tr>
                        <th>{{ __('admin.balance') }} $</th>
                        <td id="balance-dollar">0</td>
                    </tr>
                    <tr>
                        <th>{{ __('admin.used') }}</th>
                        <td id="used-balance">0</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Right content with chart -->
        <div class="col-md-6 chart-container" style="height: 178px!important">
            <canvas id="myChart"></canvas>
        </div>
    </div>


        <div class="row">
            <div class="col-md-12 text-center">
                <div id="payment-alert-container"></div>
                <!-- Add a button here -->
                <button type="button" class="btn btn-primary mt-3" onclick="window.location.href='admin/payment-with-method';">
                    {{ __('admin.pay') }}
                </button>
            </div>
        </div>
    
</div>
@endif

<h3 style="margin:10px 0;">👤 {{ __('Users') }}</h3>
<div class="col-md-12">
    @include('admin.dashboard.stats')
</div>

<div class="stats-container" id="stats-container">
    <div id="stats-content">

        <div class="row">
            <div class="col-md-6">@include('admin.dashboard.widgets.users_chart')</div>
            <div class="col-md-6">@include('admin.dashboard.widgets.top_users_visits_chart')</div>
        </div>
        <div class="row">
            <div class="col-md-6">@include('admin.dashboard.widgets.signups_weekly_chart')</div>
            <div class="col-md-6">@include('admin.dashboard.widgets.peak_hours_card')</div>
        </div>
        <div class="row">
            <div class="col-md-6">@include('admin.dashboard.widgets.top_followers_table')</div>
            <div class="col-md-6">@include('admin.dashboard.widgets.users_online_chart')</div>
        </div>

        <h3 style="margin:10px 0;">🏠 {{ __('Rooms') }}</h3>
        <div class="col-md-12">@include('admin.dashboard.widgets.room_tab')</div>
        <div class="row">
            <div class="col-md-6">@include('admin.dashboard.widgets.rooms_distribution_chart')</div>
            <div class="col-md-6">@include('admin.dashboard.widgets.rooms_activity_chart')</div>
        </div>
        <div class="row">
            <div class="col-md-6">@include('admin.dashboard.widgets.top_gifted_rooms_chart')</div>
            <div class="col-md-6">@include('admin.dashboard.widgets.avg_session_duration_chart')</div>
        </div>

        <h3 style="margin:10px 0;">🏢 {{ __('Agencies') }}</h3>
        <div class="col-md-12">@include('admin.dashboard.widgets.agency_tab')</div>
        <div class="row">
            <div class="col-md-6">@include('admin.dashboard.widgets.agencies_targets_chart')</div>
            <div class="col-md-6">@include('admin.dashboard.widgets.top_senders_chart')</div>
        </div>
        <div class="row">
            <div class="col-md-6">@include('admin.dashboard.widgets.top_receivers_chart')</div>
            <div class="col-md-6">@include('admin.dashboard.widgets.agencies_compare_chart')</div>
        </div>

        <h3 style="margin:10px 0;">💼 {{ __('BD') }}</h3>
        <div class="col-md-12">@include('admin.dashboard.widgets.bd_tab')</div>

        <h3 style="margin:10px 0;">🎮 {{ __('Game') }}</h3>
        <div class="col-md-12">@include('admin.dashboard.widgets.game_tab')</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// المتغير العام للـ Chart
var myChart = null;

// دالة لإنشاء أو تحديث الـ Chart
function createOrUpdateChart(chartData, usePercentage) {
    var ctx = document.getElementById('myChart').getContext('2d');

    // إذا كان الـ Chart موجود بالفعل، قم بتدميره أولاً
    if (myChart !== null) {
        myChart.destroy();
    }

    myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['{{ __('admin.used') }}', '{{ __('admin.availableBalance') }}'],
            datasets: [{
                label: '{{ __('admin.data_distribution') }}',
                data: chartData,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: '{{ __('admin.game_recharge_rate') }} - ' + usePercentage.toFixed(2) + '%'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

@php
    if (request()->is('superadmin*')) {
        $prefix = 'superadmin';
    } elseif (request()->is('areaManager*')) {
        $prefix = 'areaManager';
    } else {
        $prefix = 'admin';
    }
@endphp

function loadBalanceData(date = null) {
    $.ajax({
        url: '{{ url($prefix . "/statistics/balance-data") }}',
        type: 'GET',
        data: {
            date: date
        },
        beforeSend: function() {
            // إظهار مؤشر تحميل إذا أردت
            $('#balance-table').css('opacity', '0.7');
        },
        success: function(response) {
            if (response.success) {
                // تحديث البيانات في الجدول
                $('#all-balance').text(response.data.allBalance.toLocaleString());
                $('#available-balance').text(response.data.availableBalance.toLocaleString());
                $('#balance-dollar').text(response.data.balanceDollar.toLocaleString());
                $('#used-balance').text(response.data.used.toLocaleString());

                // تحديث الـ Chart
                createOrUpdateChart(response.data.chartData, response.data.usePercentage);

                // معالجة التنبيه
                handlePaymentAlert(response.data.showPaymentAlert, response.data.usePercentage);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error loading balance data:', error);
            // إظهار رسالة خطأ للمستخدم
            alert('حدث خطأ في تحميل البيانات. يرجى المحاولة مرة أخرى.');
        },
        complete: function() {
            // إعادة opacity إلى الطبيعي
            $('#balance-table').css('opacity', '1');
        }
    });
}

// دالة للتعامل مع تنبيه الدفع
function handlePaymentAlert(showAlert, usePercentage) {
    const alertContainer = $('#payment-alert-container');

    alertContainer.empty();

    if (showAlert) {
        const alertHtml = `
            <div class="row">
                <div class="col-md-12 text-center">
                    <h4 class="text-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        {{ __('admin.you_must_pay') }}
                    </h4>
                    <small class="text-muted">(${usePercentage.toFixed(2)}% مستخدم)</small>
                </div>
            </div>
        `;
        alertContainer.html(alertHtml);

        // إضافة تأثير لو أردت
        alertContainer.hide().fadeIn(500);
    }
}

// تحميل البيانات عند فتح الصفحة
$(document).ready(function() {
    // إنشاء الـ Chart الأولي بالبيانات الافتراضية
    createOrUpdateChart([0, 0], 0);

    // تحميل البيانات الفعلية
    loadBalanceData();

    // إضافة event listener لحقل التاريخ
    $('#date-filter').on('change', function() {
        loadBalanceData($(this).val());
    });

    // منع إعادة تحميل الصفحة عند الضغط على الفلتر
    $('form').on('submit', function(e) {
        e.preventDefault();
        loadBalanceData($('#date-filter').val());
    });
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
