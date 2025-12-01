{{-- Ultra-Modern Glassmorphism Dashboard --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --bg-primary: #0a0a0a;
    --bg-secondary: #111111;
    --bg-tertiary: #1a1a1a;
    --bg-glass: rgba(255, 255, 255, 0.05);
    --bg-glass-hover: rgba(255, 255, 255, 0.08);
    --accent-primary: #00d4ff;
    --accent-secondary: #ff0080;
    --accent-tertiary: #00ff88;
    --text-primary: #ffffff;
    --text-secondary: #b8b8b8;
    --text-muted: #888888;
    --border-light: rgba(255, 255, 255, 0.1);
    --border-glow: rgba(0, 212, 255, 0.3);
    --shadow-soft: 0 8px 32px rgba(0, 0, 0, 0.3);
    --shadow-glow: 0 0 20px rgba(0, 212, 255, 0.15);
    --shadow-danger: 0 0 20px rgba(255, 0, 128, 0.15);
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-accent: linear-gradient(135deg, #00d4ff 0%, #ff0080 100%);
    --gradient-success: linear-gradient(135deg, #00ff88 0%, #00d4ff 100%);
    --gradient-danger: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    --blur: blur(20px);
    --blur-light: blur(10px);
}

* {
    box-sizing: border-box;
}

body {
    background: var(--bg-primary);
    color: var(--text-primary);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

.dashboard-container {
    background:
        radial-gradient(circle at 20% 50%, rgba(0, 212, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 0, 128, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(0, 255, 136, 0.1) 0%, transparent 50%),
        var(--bg-primary);
    min-height: 100vh;
    padding: 24px;
    position: relative;
}

.dashboard-container::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        radial-gradient(circle at 25% 25%, rgba(0, 212, 255, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(255, 0, 128, 0.05) 0%, transparent 50%);
    pointer-events: none;
    z-index: -1;
}

.section-header {
    font-size: 2.5rem;
    font-weight: 800;
    text-align: center;
    margin: 60px 0 40px 0;
    position: relative;
    background: var(--gradient-accent);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
}

.section-header::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: var(--gradient-accent);
    border-radius: 2px;
    box-shadow: var(--shadow-glow);
}

.glass-card {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-light);
    border-radius: 20px;
    box-shadow: var(--shadow-soft);
    margin-bottom: 32px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.glass-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
}

.glass-card:hover {
    background: var(--bg-glass-hover);
    border-color: var(--border-glow);
    box-shadow: var(--shadow-soft), var(--shadow-glow);
    transform: translateY(-8px);
}

.card-header-glass {
    background: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid var(--border-light);
    padding: 24px 32px;
    font-weight: 600;
    font-size: 1.25rem;
    color: var(--accent-primary);
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-body-glass {
    padding: 32px;
}

.balance-card-premium {
    background: var(--gradient-primary);
    color: white;
    position: relative;
    overflow: hidden;
}

.balance-card-premium::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: conic-gradient(from 0deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    animation: rotate 8s linear infinite;
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.balance-table-premium {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.balance-table-premium th {
    background: rgba(0, 212, 255, 0.15);
    color: var(--accent-primary);
    border: none;
    padding: 20px 24px;
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.balance-table-premium td {
    background: rgba(255, 255, 255, 0.03);
    color: var(--text-primary);
    border: none;
    padding: 20px 24px;
    font-weight: 500;
    font-size: 1.1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.balance-table-premium tr:last-child td {
    border-bottom: none;
}

.chart-container-premium {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 24px;
    margin-top: 24px;
    backdrop-filter: blur(10px);
}

.btn-premium {
    background: var(--gradient-accent);
    border: none;
    border-radius: 50px;
    padding: 16px 32px;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(0, 212, 255, 0.3);
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.btn-premium::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-premium:hover::before {
    left: 100%;
}

.btn-premium:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0, 212, 255, 0.4);
}

.alert-premium {
    background: var(--gradient-danger);
    border: none;
    border-radius: 16px;
    color: white;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-danger);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 0, 128, 0.2);
}

.stats-masonry {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.widget-card-premium {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-light);
    border-radius: 20px;
    padding: 24px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-soft);
    position: relative;
    overflow: hidden;
}

.widget-card-premium::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gradient-accent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.widget-card-premium:hover::before {
    opacity: 1;
}

.widget-card-premium:hover {
    background: var(--bg-glass-hover);
    border-color: var(--border-glow);
    box-shadow: var(--shadow-soft), var(--shadow-glow);
    transform: translateY(-6px) scale(1.02);
}

.filter-form-premium {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-light);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 32px;
    box-shadow: var(--shadow-soft);
}

.filter-form-premium input,
.filter-form-premium button {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-light);
    color: var(--text-primary);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.filter-form-premium input:focus,
.filter-form-premium button:focus {
    outline: none;
    border-color: var(--accent-primary);
    box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
    background: rgba(255, 255, 255, 0.08);
}

.filter-form-premium input::placeholder {
    color: var(--text-muted);
}

.fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    opacity: 0;
    transform: translateY(30px);
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stagger-animation {
    animation-delay: calc(var(--stagger) * 0.1s);
}

.pulse-glow {
    animation: pulseGlow 2s ease-in-out infinite alternate;
}

@keyframes pulseGlow {
    from {
        box-shadow: var(--shadow-soft);
    }
    to {
        box-shadow: var(--shadow-soft), var(--shadow-glow);
    }
}

@media (max-width: 1024px) {
    .stats-masonry {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 16px;
    }

    .section-header {
        font-size: 2rem;
        margin: 40px 0 30px 0;
    }

    .stats-masonry {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .card-body-glass {
        padding: 20px;
    }

    .card-header-glass {
        padding: 20px 24px;
        font-size: 1.1rem;
    }

    .balance-card-premium .row > div {
        margin-bottom: 24px;
    }
}

@media (max-width: 480px) {
    .dashboard-container {
        padding: 12px;
    }

    .section-header {
        font-size: 1.75rem;
    }

    .card-body-glass {
        padding: 16px;
    }

    .btn-premium {
        padding: 14px 24px;
        font-size: 0.95rem;
    }
}

/* Tab Styles */
.tabs-container {
    margin-bottom: 40px;
}

.nav-tabs-glass {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-light);
    border-radius: 16px;
    padding: 8px;
    margin-bottom: 32px;
    box-shadow: var(--shadow-soft);
}

.nav-tabs-glass .nav-link {
    background: transparent;
    border: none;
    color: var(--text-secondary);
    font-weight: 600;
    font-size: 1rem;
    padding: 16px 24px;
    margin: 0 4px;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
}

.nav-tabs-glass .nav-link:hover {
    background: var(--bg-glass-hover);
    color: var(--text-primary);
}

.nav-tabs-glass .nav-link.active {
    background: var(--gradient-accent);
    color: white;
    box-shadow: 0 4px 20px rgba(0, 212, 255, 0.3);
}

.nav-tabs-glass .nav-link.active::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
    border-radius: 12px;
}

.tab-content-glass {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-light);
    border-radius: 20px;
    padding: 32px;
    box-shadow: var(--shadow-soft);
}

.tab-pane {
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    opacity: 0;
    transform: translateY(20px);
}

.tab-pane.show {
    opacity: 1;
    transform: translateY(0);
}

/* Loading states */
.loading-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
</style>

<div class="dashboard-container">
    @if (\Encore\Admin\Facades\Admin::user()->can('pay-switch' . 'dashboard') || \Encore\Admin\Facades\Admin::user()->can('*'))
    <div class="filter-form-premium fade-in-up">
        <form method="GET" action="{{ url()->current() }}" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-calendar-alt" style="color: var(--accent-primary);"></i>
                <input type="month" name="date" id="date-filter" value="{{ request('date') }}">
            </div>
            <button type="submit" class="btn-premium">
                <i class="fas fa-filter"></i> {{ __('admin.filter') }}
            </button>
        </form>
    </div>
    @endif

    @if (\Encore\Admin\Facades\Admin::user()->can('pay-switch' . 'dashboard') || \Encore\Admin\Facades\Admin::user()->can('*'))
    <div class="glass-card balance-card-premium fade-in-up" style="--stagger: 1">
        <div class="card-header-glass">
            <i class="fas fa-wallet"></i> {{ __('admin.balance') }} & {{ __('admin.game_recharge_rate') }}
        </div>
        <div class="card-body-glass">
            <div class="row">
                <!-- Left content with table -->
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <table class="table balance-table-premium" id="balance-table">
                        <tbody>
                            <tr>
                                <th><i class="fas fa-coins"></i> {{ __('admin.balance') }}</th>
                                <td id="all-balance">0</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-check-circle"></i> {{ __('admin.availableBalance') }}</th>
                                <td id="available-balance">0</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-dollar-sign"></i> {{ __('admin.balance') }} $</th>
                                <td id="balance-dollar">0</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-minus-circle"></i> {{ __('admin.used') }}</th>
                                <td id="used-balance">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Right content with chart -->
                <div class="col-md-6 chart-container-premium">
                    <canvas id="myChart"></canvas>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <div id="payment-alert-container"></div>
                    <button type="button" class="btn-premium mt-3" onclick="window.location.href='admin/payment-with-method';">
                        <i class="fas fa-credit-card"></i> {{ __('admin.pay') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="col-md-12 fade-in-up" style="--stagger: 2">
        @include('admin.dashboard.stats')
    </div>

    <div class="tabs-container fade-in-up" style="--stagger: 3">
        <ul class="nav nav-tabs nav-tabs-glass" id="statsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">
                    <i class="fas fa-users"></i> {{ __('Users') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rooms-tab" data-bs-toggle="tab" data-bs-target="#rooms" type="button" role="tab" aria-controls="rooms" aria-selected="false">
                    <i class="fas fa-home"></i> {{ __('Rooms') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="agencies-tab" data-bs-toggle="tab" data-bs-target="#agencies" type="button" role="tab" aria-controls="agencies" aria-selected="false">
                    <i class="fas fa-building"></i> {{ __('Agencies') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bd-tab" data-bs-toggle="tab" data-bs-target="#bd" type="button" role="tab" aria-controls="bd" aria-selected="false">
                    <i class="fas fa-briefcase"></i> {{ __('BD') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="game-tab" data-bs-toggle="tab" data-bs-target="#game" type="button" role="tab" aria-controls="game" aria-selected="false">
                    <i class="fas fa-gamepad"></i> {{ __('Game') }}
                </button>
            </li>
        </ul>

        <div class="tab-content tab-content-glass" id="statsTabContent">
            <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                <div class="stats-masonry">
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.users_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.top_users_visits_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.signups_weekly_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.peak_hours_card')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.top_followers_table')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.users_online_chart')</div>
                </div>
            </div>

            <div class="tab-pane fade" id="rooms" role="tabpanel" aria-labelledby="rooms-tab">
                <div class="col-md-12 mb-4">@include('admin.dashboard.widgets.room_tab')</div>
                <div class="stats-masonry">
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.rooms_distribution_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.rooms_activity_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.top_gifted_rooms_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.avg_session_duration_chart')</div>
                </div>
            </div>

            <div class="tab-pane fade" id="agencies" role="tabpanel" aria-labelledby="agencies-tab">
                <div class="col-md-12 mb-4">@include('admin.dashboard.widgets.agency_tab')</div>
                <div class="stats-masonry">
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.agencies_targets_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.top_senders_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.top_receivers_chart')</div>
                    <div class="widget-card-premium">@include('admin.dashboard.widgets.agencies_compare_chart')</div>
                </div>
            </div>

            <div class="tab-pane fade" id="bd" role="tabpanel" aria-labelledby="bd-tab">
                <div class="col-md-12">@include('admin.dashboard.widgets.bd_tab')</div>
            </div>

            <div class="tab-pane fade" id="game" role="tabpanel" aria-labelledby="game-tab">
                <div class="col-md-12">@include('admin.dashboard.widgets.game_tab')</div>
            </div>
        </div>
    </div>
</div>

@php
    if (request()->is('superadmin*')) {
        $prefix = 'superadmin';
    } elseif (request()->is('areaManager*')) {
        $prefix = 'areaManager';
    } else {
        $prefix = 'admin';
    }
@endphp

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
